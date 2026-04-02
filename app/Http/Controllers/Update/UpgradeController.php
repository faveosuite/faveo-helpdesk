<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utility\LibraryController as Utility;
use App\Model\helpdesk\Settings\Backup;
use App\Model\helpdesk\Settings\BackupPath;
use App\Model\Update\BarNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use ZipArchive;

class UpgradeController extends Controller
{
    public function __construct(
        protected GitHubUpdateService $github
    ) {
    }

    /**
     * API: check whether a new release is available on GitHub.
     */
    public function checkUpdate()
    {
        try {
            $release = $this->github->getLatestRelease();

            $backupPath = BackupPath::value('backup_path') ?: storage_path('backups');

            return successResponse('', [
                'current_version'  => $this->getCurrentVersion(),
                'database_version' => $this->getDatabaseVersion(),
                'latest_version'   => $release['version'] ?? null,
                'update_available' => $this->isUpdateAvailable(),
                'database_outdated'=> $this->isDatabaseOutdated(),
                'release_notes'    => $release['body'] ?? null,
                'release_url'      => $release['html_url'] ?? null,
                'backup_path'      => $backupPath,
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
            $updateAvailable = $release && version_compare($latestVersion, $currentVersion, '>');
            $recentReleases = collect($this->github->getRecentReleases())
                ->filter(fn ($r) => version_compare($r['version'], $currentVersion, '>'))
                ->values()
                ->all();

            $backupPath = BackupPath::value('backup_path') ?: storage_path('backups');

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
     * AJAX: take system backup (database + filesystem) before update.
     */
    public function backup(Request $request)
    {
        try {
            $backupPath = $request->input('path', storage_path('backups'));

            if (!is_dir($backupPath)) {
                File::makeDirectory($backupPath, 0777, true, true);
            }

            if (!is_readable($backupPath) || !is_writable($backupPath)) {
                return errorResponse('Backup directory is not readable/writable. Please check permissions.');
            }

            BackupPath::updateOrCreate(['id' => 1], ['backup_path' => $backupPath]);

            $currentVersion = $this->getCurrentVersion();
            $dbType = \Config::get('database.default');
            $dbUser = \Config::get('database.connections.'.$dbType.'.username');
            $dbPass = \Config::get('database.connections.'.$dbType.'.password');
            $database = \Config::get('database.connections.'.$dbType.'.database');
            $host = \Config::get('database.connections.'.$dbType.'.host');

            $timestamp = Carbon::now()->timestamp;
            $datePath = $backupPath.DIRECTORY_SEPARATOR.date('Y/m/d');
            $filesystemZip = $datePath.DIRECTORY_SEPARATOR."filesystem-{$timestamp}";
            $dbZip = $datePath.DIRECTORY_SEPARATOR."db-{$timestamp}";

            if (!is_dir($datePath)) {
                File::makeDirectory($datePath, 0775, true, true);
            }

            $folderPath = base_path();
            $sanitizedPass = str_replace("'", "'\\''", $dbPass);
            $autoUpdate = $request->input('autoUpdate');

            if ($dbPass == '') {
                exec("(mysqldump -h{$host} -u{$dbUser} {$database} | zip {$dbZip} - ; zip -r {$filesystemZip} {$folderPath}) > /dev/null 2>&1 &");
            } else {
                exec("(mysqldump -h{$host} -u{$dbUser} -p'{$sanitizedPass}' {$database} | zip {$dbZip} - ; zip -r {$filesystemZip} {$folderPath}) > /dev/null 2>&1 &");
            }

            Backup::create([
                'filename'  => "Filesystem_{$currentVersion}",
                'db_name'   => "Database_{$currentVersion}",
                'file_path' => $filesystemZip.'.zip',
                'db_path'   => $dbZip.'.zip',
                'version'   => $currentVersion,
            ]);

            if ($autoUpdate) {
                Artisan::call('down');
                $this->github->downloadRelease();
                $this->extractAndApply();
                $this->dismissNotification('new-version');
                $this->cleanup();
                $this->clearBootstrapCache();
                Artisan::call('database:sync');
                Artisan::call('up');

                return successResponse('Backup and update started.');
            }

            return successResponse('Backup started.');
        } catch (Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * AJAX: download the latest release ZIP from GitHub.
     */
    public function download()
    {
        try {
            if ($this->github->hasDownload()) {
                return successResponse('Update archive already downloaded.');
            }

            $this->github->downloadRelease();

            return successResponse('Release downloaded successfully.');
        } catch (Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * AJAX: extract and apply downloaded update files.
     */
    public function install()
    {
        try {
            Artisan::call('down');

            $log = $this->extractAndApply();

            $this->dismissNotification('new-version');
            $this->cleanup();
            $this->clearBootstrapCache();

            Artisan::call('up');

            return successResponse('Files updated successfully.', [
                'version' => $this->getCurrentVersion(),
                'log'     => $log,
            ]);
        } catch (Exception $e) {
            Artisan::call('up');

            return errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Page: show "database update required" notification.
     */
    public function databaseUpdate(): View|RedirectResponse
    {
        try {
            if (!$this->isDatabaseOutdated()) {
                return redirect()->back();
            }

            $url = url('database-upgrade');

            return view('themes.default1.update.database', compact('url'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Action: run database sync (migrations + seeders).
     */
    public function databaseUpgrade(): RedirectResponse
    {
        try {
            if (!$this->isDatabaseOutdated()) {
                return redirect()->back();
            }

            Artisan::call('database:sync');
            $output = trim(Artisan::output());

            return redirect('dashboard')->with('success', 'Database synced successfully. '.$output);
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * AJAX: run database sync after file update.
     */
    public function ajaxDatabaseSync()
    {
        try {
            Artisan::call('database:sync');
            $output = trim(Artisan::output());

            return successResponse('Database updated successfully. '.$output);
        } catch (Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }

    // ------------------------------------------------------------------
    //  Application-level helpers
    // ------------------------------------------------------------------

    protected function clearBootstrapCache(): void
    {
        $files = glob(base_path('bootstrap/cache/*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    protected function getCurrentVersion(): string
    {
        return Utility::getFileVersion() ?: '0';
    }

    protected function getDatabaseVersion(): string
    {
        return Utility::getDatabaseVersion() ?: '0';
    }

    protected function isUpdateAvailable(): bool
    {
        $latest = $this->github->getLatestVersion();

        return $latest && version_compare($latest, $this->getCurrentVersion(), '>');
    }

    protected function isDatabaseOutdated(): bool
    {
        return version_compare($this->getCurrentVersion(), $this->getDatabaseVersion(), '>');
    }

    protected function dismissNotification(string $key): void
    {
        BarNotification::where('key', $key)->delete();
    }

    /**
     * Extract the downloaded ZIP and overwrite application files.
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

        $log = [];
        $basePath = base_path();
        $rootPrefix = $this->detectZipRootPrefix($zip);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            $relativePath = $this->stripPrefix($entryName, $rootPrefix);

            if ($relativePath === null || $relativePath === '' || str_ends_with($entryName, '/')) {
                continue;
            }

            if ($this->isExcluded($relativePath)) {
                $log[] = ['file' => $relativePath, 'status' => 'skipped'];
                continue;
            }

            $targetPath = $basePath.'/'.$relativePath;
            $targetDir = dirname($targetPath);

            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0755, true, true);
                $log[] = ['file' => dirname($relativePath).'/', 'status' => 'directory_created'];
            }

            $contents = $zip->getFromIndex($i);
            if ($contents !== false) {
                File::put($targetPath, $contents);
                $log[] = ['file' => $relativePath, 'status' => 'updated'];
            } else {
                $log[] = ['file' => $relativePath, 'status' => 'failed'];
            }
        }

        $zip->close();

        Log::info('Update: files extracted', ['total' => count($log)]);

        return $log;
    }

    /**
     * Delete the temporary update directory.
     */
    protected function cleanup(): void
    {
        $tempPath = $this->github->getTempPath();

        if (File::isDirectory($tempPath)) {
            File::deleteDirectory($tempPath);
        }

        Log::info('Update: temp files cleaned up');
    }

    protected function detectZipRootPrefix(ZipArchive $zip): string
    {
        if ($zip->numFiles === 0) {
            return '';
        }

        $first = $zip->getNameIndex(0);

        return str_contains($first, '/') ? explode('/', $first)[0].'/' : '';
    }

    protected function stripPrefix(string $path, string $prefix): ?string
    {
        if ($prefix === '') {
            return $path;
        }

        return str_starts_with($path, $prefix) ? substr($path, strlen($prefix)) : null;
    }

    protected function isExcluded(string $relativePath): bool
    {
        foreach (config('update.excluded_paths') as $pattern) {
            if (str_ends_with($pattern, '/')) {
                if (str_starts_with($relativePath, $pattern)) {
                    return true;
                }
            } elseif ($relativePath === $pattern || basename($relativePath) === $pattern) {
                return true;
            }
        }

        return false;
    }
}
