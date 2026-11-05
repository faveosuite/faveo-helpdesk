<?php

namespace Database\Seeders\v_2_0_4_RC_1;

use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Ticket\Ticket_source;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

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
