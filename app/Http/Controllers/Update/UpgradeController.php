<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utility\LibraryController as Utility;
use App\Model\Update\BarNotification;
use Exception;
use Illuminate\Http\JsonResponse;
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
    public function checkUpdate(): JsonResponse
    {
        try {
            $release = $this->github->getLatestRelease();

            return response()->json([
                'current_version'  => $this->getCurrentVersion(),
                'database_version' => $this->getDatabaseVersion(),
                'latest_version'   => $release['version'] ?? null,
                'update_available' => $this->isUpdateAvailable(),
                'database_outdated'=> $this->isDatabaseOutdated(),
                'release_notes'    => $release['body'] ?? null,
                'release_url'      => $release['html_url'] ?? null,
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
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

            return view('themes.default1.update.update', compact(
                'currentVersion',
                'latestVersion',
                'updateAvailable',
                'recentReleases',
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
     * AJAX: download the latest release ZIP from GitHub.
     */
    public function download(): JsonResponse
    {
        try {
            if ($this->github->hasDownload()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Update archive already downloaded.',
                ]);
            }

            $this->github->downloadRelease();

            return response()->json([
                'status'  => 'success',
                'message' => 'Release downloaded successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: extract and apply downloaded update files.
     */
    public function install(): JsonResponse
    {
        try {
            $log = $this->extractAndApply();

            $this->dismissNotification('new-version');
            $this->cleanup();

            return response()->json([
                'status'  => 'success',
                'message' => 'Files updated successfully.',
                'version' => $this->getCurrentVersion(),
                'log'     => $log,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
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

    // ------------------------------------------------------------------
    //  Application-level helpers
    // ------------------------------------------------------------------

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

        $limit = (int) ini_get('memory_limit');
        $required = config('update.min_memory_mb');
        if ($limit !== -1 && $limit < $required) {
            throw new Exception("Insufficient memory ({$limit}M). At least {$required}M is required.");
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
