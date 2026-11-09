<?php

namespace Database\Seeders\v_2_0_4_RC_1;

use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Ticket\Ticket_source;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // API tickets get their own source so "API Calls" workflows can target them
        Ticket_source::firstOrCreate(['name' => 'API'], ['value' => 'API', 'css_class' => 'fa fa-code']);

        $this->persistSystemLanguage();
        $this->provisionPassport();
    }

    /**
     * Provision Laravel Passport, which replaces JWT (tymon/jwt-auth) for the
     * API in this release.
     *
     * The oauth_* tables are created by the migrations run just before this
     * seeder. What is not a migration - and so has to be provisioned here - is
     * the OAuth signing keys and the personal access client that
     * /api/v1/authenticate uses to mint bearer tokens. Both steps are
     * idempotent, so re-running the seeder is safe:
     *  - keys are generated only when absent (never --force, which would rotate
     *    them and invalidate every token already issued to API clients);
     *  - the personal access client is created only when none exists yet.
     *
     * Fresh installs already have both (the installer runs passport:keys and
     * creates the client), so the guards below make this a no-op there.
     */
    private function provisionPassport()
    {
        if (!Schema::hasTable('oauth_clients')) {
            return;
        }

        if (!file_exists(storage_path('oauth-private.key')) || !file_exists(storage_path('oauth-public.key'))) {
            Artisan::call('passport:keys', ['--force' => false]);
        }

        $hasPersonalAccessClient = DB::table('oauth_clients')
            ->where('grant_types', 'like', '%personal_access%')
            ->exists();

        if (!$hasPersonalAccessClient) {
            Artisan::call('passport:client', [
                '--personal'       => true,
                '--name'           => 'Faveo Helpdesk',
                '--no-interaction' => true,
            ]);
        }
    }

    /**
     * Before this release the system language lived only in the 'language'
     * cache key, so a cache clear reset it to English. It is now stored in
     * settings_system.content. Copy the language an existing install is using
     * today into that column, so upgrading does not change what users see.
     */
    private function persistSystemLanguage()
    {
        $system = System::where('id', 1)->first();
        if (!$system || isValidLanguage($system->content)) {
            return; // nothing to backfill, or already stored
        }

        $lang = Cache::get('language');
        if (!isValidLanguage($lang)) {
            // cache already lost: the install is showing the fallback language today
            $lang = Config::get('app.fallback_locale', 'en');
        }

        $system->content = $lang;
        $system->save();
        Cache::forever('language', $lang);
    }
}
