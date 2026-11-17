<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Settings\Backup;
use App\Model\helpdesk\Settings\BackupPath;
use App\Model\helpdesk\Settings\System;
use App\Model\Update\BarNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use ZipArchive;

/**
 * Core auto-update.
 *
 * The browser drives the update as separate requests, in this order:
 *   1. POST upgrade/backup            (optional) start a backup worker
 *      GET  upgrade/backup/{id}/status poll until it has finished
 *   2. POST upgrade/download          fetch the release archive
 *   3. POST upgrade/apply             maintenance mode on, replace files
 *   4. POST upgrade/database          migrate/seed, clear caches, maintenance mode off
 *
 * Step 4 must be its own request: this request read the OLD faveoconfig.ini at
 * boot, so running database:sync in the same request as step 3 would see no
 * version change and skip every migration. If the browser never sends step 4,
 * the scheduled update:finalize command finishes it.
 */
class UpgradeController extends Controller
{
    /** Shared by every mutating step so two updates can never overlap. */
    public const LOCK = 'faveo-core-update';

    public function __construct(
        protected GitHubUpdateService $github
    ) {
        // Defence in depth: every action here is privileged (shells out for backups,
        // overwrites application files, runs migrations, toggles maintenance mode).
        // The routes are already in an auth+admin group; enforcing it here too means
        // a future route moved out of that group cannot silently expose them again.
        $this->middleware(['auth', 'roles']);
    }

    /**
     * Where backups are written. Fixed server-side: the destination is never taken
     * from the request, so no client can choose where a ZIP of the whole application
     * (including .env) and a full database dump are written, nor make the directory
     * world-writable, nor smuggle anything into the shell command built from it.
     */
    protected function backupDirectory(): string
    {
        return storage_path('backups');
    }

    /**
     * Marker written once new files are in place and removed once the database has
     * been synced. While it exists the site is mid-update; update:finalize uses it
     * to finish an update the browser abandoned.
     */
    public static function pendingDatabaseMarker(): string
    {
        return storage_path('app/update-db-pending');
    }

    /**
     * API: check whether a new release is available on GitHub.
     */
    public function checkUpdate()
    {
        try {
            $release = $this->github->getLatestRelease();

            return successResponse('', [
                'current_version'  => $this->getCurrentVersion(),
                'database_version' => $this->getDatabaseVersion(),
                'latest_version'   => $release['version'] ?? null,
                'update_available' => $this->isUpdateAvailable(),
                'database_outdated'=> $this->isDatabaseOutdated(),
                'release_notes'    => $release['body'] ?? null,
                'release_url'      => $release['html_url'] ?? null,
                'backup_path'      => $this->backupDirectory(),
            ]);
        } catch (Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Page: Application Updates dashboard with version comparison and release timeline.
     */
    public function fileUpdate(): View|RedirectResponse
    {
        try {
            $release = $this->github->getLatestRelease();
            $currentVersion = $this->getCurrentVersion();
            $latestVersion = $release['version'] ?? $currentVersion;
            $updateAvailable = $release && UpdateVersion::isNewer($latestVersion, $currentVersion);
            // Only full releases are installable (the installer uses /releases/latest).
            $recentReleases = collect($this->github->getRecentReleases())
                ->reject(fn ($r) => $r['prerelease'])
                ->filter(fn ($r) => UpdateVersion::isNewer($r['version'], $currentVersion))
                ->values()
                ->all();

            $backupPath = $this->backupDirectory();

            return view('themes.default1.update.update', compact(
                'currentVersion',
                'latestVersion',
                'updateAvailable',
                'recentReleases',
                'backupPath',
            ));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Page: show the upgrade progress page.
     */
    public function fileUpgrading(Request $request): View|RedirectResponse
    {
        try {
            if (!$this->isUpdateAvailable()) {
                return redirect('dashboard')->with('fails', 'No new updates available.');
            }

            $currentVersion = $this->getCurrentVersion();
            $latestVersion = $this->github->getLatestVersion();

            return view('themes.default1.update.progress', compact(
                'currentVersion',
                'latestVersion',
            ));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * AJAX: start a full backup (database + filesystem) in a background worker.
     *
     * A full backup can take longer than any web-server timeout, so it runs detached
     * and the page polls backupStatus(). The page must not start the download until
     * the backup reports success: overwriting files while the zip is still being
     * written would produce a backup of a half-updated application.
     */
    public function backup(Request $request)
    {
        $lock = $this->acquireLock();
        if (!$lock) {
            return errorResponse('Another update step is already running. Please wait for it to finish.', 409);
        }

        try {
            // Never from the request (any 'path' the page still sends is ignored).
            // Persisting it below also overwrites a path planted in backup_path by
            // an earlier unauthenticated request against v2.0.4.RC.1.
            $backupPath = $this->backupDirectory();

            if (!is_dir($backupPath)) {
                File::makeDirectory($backupPath, 0775, true, true);
            }

            if (!is_readable($backupPath) || !is_writable($backupPath)) {
                return errorResponse('Backup directory is not readable/writable. Please check permissions.');
            }

            BackupPath::updateOrCreate(['id' => 1], ['backup_path' => $backupPath]);

            $currentVersion = $this->getCurrentVersion();
            $dbType = \Config::get('database.default');
            $connection = \Config::get('database.connections.'.$dbType, []);

            $timestamp = Carbon::now()->timestamp;
            $datePath = $backupPath.DIRECTORY_SEPARATOR.date('Y/m/d');
            $filesystemZip = $datePath.DIRECTORY_SEPARATOR."filesystem-{$timestamp}";
            $dbZip = $datePath.DIRECTORY_SEPARATOR."db-{$timestamp}";

            if (!is_dir($datePath)) {
                File::makeDirectory($datePath, 0775, true, true);
            }

            $paths = $this->backupSidecarPaths($filesystemZip.'.zip');
            $folderPath = base_path();

            // Credentials go in a 0600 option file, not on the command line, where
            // any local user could read the password from `ps`. The worker deletes it.
            $optionFile = $datePath.DIRECTORY_SEPARATOR.".backup-{$timestamp}.cnf";
            File::put($optionFile, $this->mysqlOptionFile($connection));
            @chmod($optionFile, 0600);

            // Every argument is escaped. The backup path is fixed server-side, but
            // keep it that way if that ever changes.
            $q = 'escapeshellarg';
            $zipLog = $paths['log'].'.zip';
            $command = '('
                .'mysqldump --defaults-extra-file='.$q($optionFile).' --single-transaction --quick '.$q((string) ($connection['database'] ?? '')).' > '.$q($dbZip.'.sql')
                .' && zip -q -j -m '.$q($dbZip.'.zip').' '.$q($dbZip.'.sql')
                .'; rc=$?; if [ $rc -eq 0 ]; then '
                // Leave out earlier backups and the update scratch directory, or every
                // backup would contain all the previous ones. Not -q: zip only names
                // the files it could not read when it is verbose; the per-file
                // "adding:" lines are dropped from the log afterwards.
                .'zip -r '.$q($filesystemZip.'.zip').' '.$q($folderPath)
                .' -x '.$q($backupPath.'/*').' '.$q($this->github->getTempPath().'/*')
                .' > '.$q($zipLog).' 2>&1; rc=$?; grep -v "^ *adding: " '.$q($zipLog).'; rm -f '.$q($zipLog).'; fi'
                .'; echo $rc > '.$q($paths['status'])
                .'; rm -f '.$q($optionFile).' '.$q($dbZip.'.sql')
                .') > '.$q($paths['log']).' 2>&1 < /dev/null & echo $!';

            $output = [];
            exec($command, $output);
            $pid = (int) trim((string) ($output[0] ?? ''));
            if ($pid > 0) {
                File::put($paths['pid'], (string) $pid);
            }

            $backup = Backup::create([
                'filename'  => "Filesystem_{$currentVersion}",
                'db_name'   => "Database_{$currentVersion}",
                'file_path' => $filesystemZip.'.zip',
                'db_path'   => $dbZip.'.zip',
                'version'   => $currentVersion,
            ]);

            return successResponse('Backup started.', ['backup_id' => $backup->id]);
        } catch (Throwable $e) {
            return errorResponse($e->getMessage(), 500);
        } finally {
            $lock->release();
        }
    }

    /**
     * AJAX: has the backup worker finished? status is running | success | failed.
     */
    public function backupStatus(int $id): JsonResponse
    {
        $backup = Backup::find($id);
        if (!$backup) {
            return response()->json(['success' => false, 'message' => 'Backup not found.'], 404);
        }

        $paths = $this->backupSidecarPaths($backup->file_path);

        if (File::exists($paths['status'])) {
            $exit = (int) trim(File::get($paths['status']));
            // zip exit 18: finished, but some files could not be read and were left out.
            $complete = in_array($exit, [0, 18], true)
                && File::exists($backup->file_path) && File::size($backup->file_path) > 0
                && File::exists($backup->db_path) && File::size($backup->db_path) > 0;

            if (!$complete) {
                return $this->backupState('failed', 'Backup failed. '.$this->logTail($paths['log']));
            }

            $skipped = $this->unreadableFiles($paths['log']);

            return $this->backupState(
                'success',
                $skipped ? 'Backup completed, but '.count($skipped).' file(s) could not be read and are NOT in it.' : 'Backup completed.',
                ['skipped' => array_slice($skipped, 0, 20), 'skipped_count' => count($skipped)]
            );
        }

        $pid = File::exists($paths['pid']) ? (int) File::get($paths['pid']) : 0;
        if ($pid > 0 && !$this->processAlive($pid)) {
            return $this->backupState('failed', 'The backup process stopped before finishing. '.$this->logTail($paths['log']));
        }

        return $this->backupState('running', 'Backup in progress.');
    }

    /**
     * @param array<string, mixed> $extra
     */
    protected function backupState(string $status, string $message, array $extra = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => ['status' => $status] + $extra,
        ]);
    }

    /**
     * AJAX: download the latest release ZIP from GitHub (always a fresh copy).
     */
    public function download()
    {
        $lock = $this->acquireLock();
        if (!$lock) {
            return errorResponse('Another update step is already running. Please wait for it to finish.', 409);
        }

        try {
            $this->longRunning();

            if (!$this->isUpdateAvailable()) {
                return errorResponse('No new updates available.');
            }

            $this->github->downloadRelease();

            return successResponse('Release downloaded successfully.');
        } catch (Throwable $e) {
            $this->github->clearTempDirectory();

            return errorResponse($e->getMessage(), 500);
        } finally {
            $lock->release();
        }
    }

    /**
     * AJAX: put the site into maintenance mode and replace the application files.
     *
     * On success the site deliberately STAYS in maintenance mode: the new code must
     * not serve users until the database step has run. The response carries the
     * maintenance bypass cookie so this admin's browser can still send that step.
     */
    public function install()
    {
        $lock = $this->acquireLock();
        if (!$lock) {
            return errorResponse('Another update step is already running. Please wait for it to finish.', 409);
        }

        try {
            $this->longRunning();

            $downloaded = $this->github->downloadedRelease();
            if (!$downloaded || !File::exists($this->github->zipPath())) {
                throw new Exception('No downloaded update found. Please download first.');
            }

            $this->repairBlankDatabaseVersion();

            $secret = (string) Str::uuid();
            Artisan::call('down', ['--secret' => $secret]);

            try {
                $log = $this->extractAndApply();

                File::put(self::pendingDatabaseMarker(), (string) time());
                $this->dismissNotification('new-version');
                $this->clearBootstrapCache();
                $this->resetOpcache();
            } catch (Throwable $e) {
                // Nothing usable was installed (or the old vendor was restored):
                // bring the site back rather than leave it down.
                Artisan::call('up');

                throw $e;
            } finally {
                $this->github->clearTempDirectory();
            }

            return response()->json([
                'success' => true,
                'message' => 'Files updated successfully.',
                'data'    => [
                    'version' => UpdateVersion::normalize($downloaded['version'] ?? ''),
                    'log'     => $log,
                ],
            ])->withCookie(MaintenanceModeBypassCookie::create($secret));
        } catch (Throwable $e) {
            return errorResponse($e->getMessage(), 500);
        } finally {
            $lock->release();
        }
    }

    /**
     * Page: show "database update required" notification.
     */
    public function databaseUpdate(): View|RedirectResponse
    {
        try {
            if (!$this->isDatabaseOutdated()) {
                return redirect('dashboard');
            }

            $url = url('database-upgrade');

            return view('themes.default1.update.database', compact('url'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Action (POST form): run database sync (migrations + seeders).
     */
    public function databaseUpgrade(): RedirectResponse
    {
        if (!$this->isDatabaseOutdated()) {
            return redirect('dashboard');
        }

        try {
            $output = $this->runDatabaseStep();

            return redirect('dashboard')->with('success', 'Database synced successfully. '.$output);
        } catch (Throwable $e) {
            return redirect('database-update')->with('fails', $e->getMessage());
        }
    }

    /**
     * AJAX: run database sync after file update, then leave maintenance mode.
     */
    public function ajaxDatabaseSync()
    {
        try {
            $output = $this->runDatabaseStep();

            return successResponse('Database updated successfully. '.$output);
        } catch (Throwable $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Migrate/seed to the version on disk, clear runtime caches, and always bring
     * the site back up. Shared by the AJAX step, the manual page and update:finalize.
     *
     * @throws Exception when another update step holds the lock, or the sync fails
     */
    public function runDatabaseStep(): string
    {
        $lock = $this->acquireLock();
        if (!$lock) {
            throw new Exception('Another update step is already running. Please wait for it to finish.');
        }

        try {
            $this->longRunning();

            $log = (string) (new SyncFaveoToLatestVersion())->sync();

            File::delete(self::pendingDatabaseMarker());
            $this->clearRuntimeCaches();

            return trim($log);
        } finally {
            UpdateVersion::forgetDatabase();

            try {
                Artisan::call('up');
            } finally {
                $lock->release();
            }
        }
    }

    // ------------------------------------------------------------------
    //  Application-level helpers
    // ------------------------------------------------------------------

    protected function acquireLock(): ?Lock
    {
        $lock = Cache::lock(self::LOCK, 3600);

        return $lock->get() ? $lock : null;
    }

    protected function longRunning(): void
    {
        @set_time_limit(0);
        ignore_user_abort(true);
    }

    protected function clearBootstrapCache(): void
    {
        foreach (glob(base_path('bootstrap/cache/*.php')) ?: [] as $file) {
            @unlink($file);
        }
    }

    protected function resetOpcache(): void
    {
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    protected function clearRuntimeCaches(): void
    {
        $this->resetOpcache();
        $this->clearBootstrapCache();

        foreach (['config:clear', 'route:clear', 'view:clear', 'queue:restart'] as $command) {
            try {
                Artisan::call($command);
            } catch (Throwable $e) {
                Log::warning("Update: {$command} failed", ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Sites installed before the installer fix have settings_system.version = ''.
     * database:sync would read that as 0.0.0 and re-run every seeder on live data.
     * Before the files change, the database is at least at the version still on
     * disk, so record that.
     */
    protected function repairBlankDatabaseVersion(): void
    {
        $system = System::first();
        if ($system && trim((string) $system->version) === '') {
            $system->update(['version' => config('app.version')]);
            UpdateVersion::forgetDatabase();
            Log::warning('Update: settings_system.version was empty; set to '.config('app.version').' before updating.');
        }
    }

    protected function getCurrentVersion(): string
    {
        return UpdateVersion::files();
    }

    protected function getDatabaseVersion(): string
    {
        return UpdateVersion::database();
    }

    protected function isUpdateAvailable(): bool
    {
        return UpdateVersion::isNewer($this->github->getLatestVersion(), $this->getCurrentVersion());
    }

    protected function isDatabaseOutdated(): bool
    {
        return UpdateVersion::isNewer($this->getCurrentVersion(), $this->getDatabaseVersion());
    }

    protected function dismissNotification(string $key): void
    {
        BarNotification::where('key', $key)->delete();
    }

    /**
     * @return array{status: string, pid: string, log: string}
     */
    protected function backupSidecarPaths(string $filesystemZip): array
    {
        $base = preg_replace('/\.zip$/', '', $filesystemZip);

        return ['status' => $base.'.status', 'pid' => $base.'.pid', 'log' => $base.'.log'];
    }

    /**
     * @param array<string, mixed> $connection
     */
    protected function mysqlOptionFile(array $connection): string
    {
        $quote = fn ($value) => '"'.addcslashes((string) $value, '\\"').'"';

        $lines = ['[client]', 'user='.$quote($connection['username'] ?? '')];
        if (($connection['password'] ?? '') !== '') {
            $lines[] = 'password='.$quote($connection['password']);
        }
        if (!empty($connection['host'])) {
            $lines[] = 'host='.$quote($connection['host']);
        }
        if (!empty($connection['port'])) {
            $lines[] = 'port='.(int) $connection['port'];
        }

        return implode("\n", $lines)."\n";
    }

    protected function processAlive(int $pid): bool
    {
        if (is_dir('/proc/self')) {
            return is_dir("/proc/{$pid}");
        }
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0) || posix_get_last_error() === 1;
        }

        return true; // can't tell; keep polling (the page gives up eventually)
    }

    /**
     * Files zip reported as unreadable (left out of the backup).
     *
     * @return list<string>
     */
    protected function unreadableFiles(string $log): array
    {
        if (!File::exists($log)) {
            return [];
        }

        preg_match_all('/could not open for reading: (.+)$/m', File::get($log), $matches);

        return array_map('trim', $matches[1]);
    }

    protected function logTail(string $log): string
    {
        return File::exists($log) ? trim(mb_substr(File::get($log), -800)) : '';
    }

    /**
     * Validate the archive, stage it, then replace the application files.
     *
     * Nothing under base_path() is touched until the whole archive has been
     * validated and extracted to a staging directory, and every target has been
     * checked for write permission. vendor/ is swapped as a whole (so packages
     * removed in the new release don't linger) and swapped back if anything fails.
     */
    protected function extractAndApply(): array
    {
        $zipPath = $this->github->zipPath();

        if (!File::exists($zipPath)) {
            throw new Exception('No downloaded update found. Please download first.');
        }

        if (!extension_loaded('zip')) {
            throw new Exception('The PHP ZIP extension is required but not loaded.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new Exception('Failed to open the update archive.');
        }

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $this->assertSafeEntry((string) $zip->getNameIndex($i));
            }

            $rootPrefix = $this->detectZipRootPrefix($zip);
            $staging = $this->github->getTempPath().'/staging';
            File::deleteDirectory($staging);
            File::makeDirectory($staging, 0755, true, true);

            if (!$zip->extractTo($staging)) {
                throw new Exception('Failed to extract the update archive.');
            }
        } finally {
            $zip->close();
        }

        $source = rtrim($staging.'/'.$rootPrefix, '/');
        $basePath = base_path();

        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $files[] = substr($file->getPathname(), strlen($source) + 1);
            }
        }

        $replaceVendor = is_dir($source.'/vendor/composer');
        $log = [];
        $toCopy = [];
        foreach ($files as $relativePath) {
            if ($replaceVendor && str_starts_with($relativePath, 'vendor/')) {
                continue;
            }
            if ($this->isExcluded($relativePath)) {
                $log[] = ['file' => $relativePath, 'status' => 'skipped'];
                continue;
            }
            $toCopy[] = $relativePath;
        }

        $this->assertWritable($basePath, $toCopy, $replaceVendor);

        $oldVendor = $this->github->getTempPath().'/vendor-old';
        $vendorSwapped = false;

        try {
            if ($replaceVendor) {
                if (is_dir($basePath.'/vendor') && !@rename($basePath.'/vendor', $oldVendor)) {
                    throw new Exception('Could not move the current vendor directory aside.');
                }
                $vendorSwapped = true;
                if (!@rename($source.'/vendor', $basePath.'/vendor')) {
                    throw new Exception('Could not move the new vendor directory into place.');
                }
                $log[] = ['file' => 'vendor/', 'status' => 'updated'];
            }

            foreach ($toCopy as $relativePath) {
                $target = $basePath.'/'.$relativePath;
                if (!File::isDirectory(dirname($target))) {
                    File::makeDirectory(dirname($target), 0755, true, true);
                    $log[] = ['file' => dirname($relativePath).'/', 'status' => 'directory_created'];
                }
                if (!@copy($source.'/'.$relativePath, $target)) {
                    throw new Exception("Failed to write {$relativePath}.");
                }
                $log[] = ['file' => $relativePath, 'status' => 'updated'];
            }
        } catch (Throwable $e) {
            if ($vendorSwapped && is_dir($oldVendor)) {
                if (is_dir($basePath.'/vendor')) {
                    File::deleteDirectory($basePath.'/vendor');
                }
                @rename($oldVendor, $basePath.'/vendor');
            }
            Log::error('Update: applying files failed', ['error' => $e->getMessage()]);

            throw $e;
        }

        Log::info('Update: files extracted', ['total' => count($log)]);

        return $log;
    }

    /**
     * Reject archive entries that could write outside the application directory.
     */
    protected function assertSafeEntry(string $name): void
    {
        if ($name === ''
            || str_starts_with($name, '/')
            || str_contains($name, '\\')
            || str_contains($name, "\0")
            || preg_match('#^[A-Za-z]:#', $name)
            || in_array('..', explode('/', $name), true)) {
            throw new Exception("The update archive contains an unsafe path: {$name}");
        }
    }

    /**
     * Fail before changing anything if a file can't be written, instead of
     * stopping half-way with a mix of old and new code.
     */
    /**
     * @param list<string> $relativePaths
     */
    protected function assertWritable(string $basePath, array $relativePaths, bool $replaceVendor): void
    {
        $problems = [];

        if ($replaceVendor && !is_writable($basePath)) {
            $problems[] = $basePath;
        }

        foreach ($relativePaths as $relativePath) {
            $target = $basePath.'/'.$relativePath;
            if (file_exists($target)) {
                if (!is_writable($target)) {
                    $problems[] = $relativePath;
                }
                continue;
            }
            $dir = dirname($target);
            while (!file_exists($dir) && $dir !== $basePath && $dir !== dirname($dir)) {
                $dir = dirname($dir);
            }
            if (!is_writable($dir)) {
                $problems[] = $relativePath;
            }
            if (count($problems) >= 10) {
                break;
            }
        }

        if ($problems) {
            throw new Exception('The web server cannot write to: '.implode(', ', array_slice($problems, 0, 10)).'. Fix the file permissions and try again; nothing was changed.');
        }
    }

    protected function detectZipRootPrefix(ZipArchive $zip): string
    {
        if ($zip->numFiles === 0) {
            return '';
        }

        $first = $zip->getNameIndex(0);

        return str_contains($first, '/') ? explode('/', $first)[0].'/' : '';
    }

    protected function isExcluded(string $relativePath): bool
    {
        foreach (config('update.excluded_paths', []) as $pattern) {
            if (str_ends_with($pattern, '/')) {
                if (str_starts_with($relativePath, $pattern)) {
                    return true;
                }
            } elseif ($relativePath === $pattern || (!str_contains($pattern, '/') && basename($relativePath) === $pattern)) {
                return true;
            }
        }

        return false;
    }
}
