<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Settings\System;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class SyncFaveoToLatestVersion extends Controller
{
    public function sync()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        set_time_limit(0);

        $latestVersion = $this->getPhpComaptibleVersion(Config::get('app.version'));
        $olderVersion = $this->getOlderVersion();

        if (version_compare($latestVersion, $olderVersion) == 1) {
            $this->updateToLatestVersion($olderVersion);
        }
        Artisan::call('storage:link');
        $system = System::first();
        if ($system) {
            $system->update(['version' => Config::get('app.version')]);
        }
    }

    private function updateToLatestVersion($olderVersion)
    {
        Artisan::call('migrate', ['--force' => true]);

        $seederPath = base_path('database'.DIRECTORY_SEPARATOR.'seeders');

        if (file_exists($seederPath)) {
            $seederVersions = scandir($seederPath);

            natsort($seederVersions);
            $formattedOlderVersion = $olderVersion;
            foreach ($seederVersions as $version) {
                if (version_compare($this->getPhpComaptibleVersion($version), $formattedOlderVersion) == 1) {
                    Artisan::call('db:seed', ['--class' => "Database\Seeders\\$version\DatabaseSeeder", '--force' => true]);
                }
            }
        }
    }

    private function getOlderVersion()
    {
        if (!isInstall()) {
            return '0.0.0';
        }

        $version = System::value('version') ?: '0.0.0';

        return $this->getPhpComaptibleVersion($version);
    }

    private function getPhpComaptibleVersion($version)
    {
        return preg_replace('#v\.|v#', '', str_replace('_', '.', $version));
    }
}
