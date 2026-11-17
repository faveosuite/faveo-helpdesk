<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Settings\System;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SyncFaveoToLatestVersion extends Controller
{
    /** @var string */
    private $log = '';

    /**
     * @throws \Exception when a migration or seeder fails (the version is then
     *                    left unchanged so the next sync retries)
     */
    public function sync()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        set_time_limit(0);

        $latestVersion = $this->getPhpComaptibleVersion(Config::get('app.version'));
        $olderVersion = $this->getOlderVersion();

        // Always migrate: migrations are tracked individually, so this is a no-op
        // when nothing is pending, and a release that ships a migration without
        // bumping APP_VERSION still gets it.
        $this->artisan('migrate', ['--force' => true]);

        if (version_compare($latestVersion, $olderVersion) == 1) {
            $this->runSeeders($olderVersion);
        }

        Artisan::call('storage:link');
        $system = System::first();
        if ($system) {
            $system->update(['version' => Config::get('app.version')]);
        }

        return $this->log;
    }

    /**
     * Run every versioned seeder newer than $olderVersion, oldest first.
     *
     * Sorted by version, not by name: natsort() puts "v_2_0_4" before
     * "v_2_0_4_RC_1", but the RC is the earlier release and its seeder must run
     * first when an update crosses both.
     */
    private function runSeeders(string $olderVersion): void
    {
        $seederPath = base_path('database'.DIRECTORY_SEPARATOR.'seeders');

        if (!is_dir($seederPath)) {
            return;
        }

        $seederVersions = array_values(array_filter(
            scandir($seederPath) ?: [],
            fn ($dir) => $dir[0] !== '.' && is_dir($seederPath.DIRECTORY_SEPARATOR.$dir)
        ));

        usort($seederVersions, fn ($a, $b) => version_compare(
            $this->getPhpComaptibleVersion($a),
            $this->getPhpComaptibleVersion($b)
        ));

        foreach ($seederVersions as $version) {
            if (version_compare($this->getPhpComaptibleVersion($version), $olderVersion) == 1) {
                $this->artisan('db:seed', ['--class' => "Database\\Seeders\\$version\\DatabaseSeeder", '--force' => true]);
            }
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function artisan(string $command, array $parameters): void
    {
        $exitCode = Artisan::call($command, $parameters);
        $output = trim(Artisan::output());
        $this->log .= ($output !== '' ? $output."\n" : '');

        if ($exitCode !== 0) {
            throw new \RuntimeException("{$command} failed (exit code {$exitCode}). {$output}");
        }
    }

    private function getOlderVersion()
    {
        if (!isInstall()) {
            return '0.0.0';
        }

        $version = trim((string) System::value('version'));

        if ($version === '') {
            // Installs made before the installer fix saved an empty version. Reading
            // that as 0.0.0 would re-run every seeder on live data. The updater
            // records the real version before replacing files; anything else
            // reaching here is assumed to be on the version on disk.
            Log::warning('database:sync: settings_system.version is empty; assuming '.Config::get('app.version').' and running migrations only.');

            return $this->getPhpComaptibleVersion(Config::get('app.version'));
        }

        return $this->getPhpComaptibleVersion($version);
    }

    private function getPhpComaptibleVersion($version)
    {
        return preg_replace('#v\.|v#i', '', str_replace('_', '.', (string) $version));
    }
}
