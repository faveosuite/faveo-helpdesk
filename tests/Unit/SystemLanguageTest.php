<?php

namespace Tests\Unit;

use App\Http\Controllers\Client\helpdesk\UnAuthController;
use App\Http\Middleware\LanguageMiddleware;
use App\Model\helpdesk\Settings\System;
use Database\Seeders\v_2_0_4_RC_1\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The system language is stored in settings_system.content; the 'language'
 * cache key is only a copy. Clearing the cache must not lose it.
 */
class SystemLanguageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('language');
        $this->setStoredLanguage('');
    }

    private function setStoredLanguage($lang)
    {
        DB::table('settings_system')->where('id', 1)->update(['content' => $lang]);
    }

    private function storedLanguage()
    {
        return System::where('id', 1)->value('content');
    }

    private function localeForGuestRequest()
    {
        (new LanguageMiddleware())->handle(Request::create('/'), function () {
        });

        return App::getLocale();
    }

    public function test_change_language_stores_it_in_database_and_cache()
    {
        $this->assertTrue(UnAuthController::changeLanguage('fr'));

        $this->assertSame('fr', $this->storedLanguage());
        $this->assertSame('fr', Cache::get('language'));
        $this->assertSame('fr', $this->localeForGuestRequest());
    }

    public function test_invalid_language_is_rejected_and_nothing_is_stored()
    {
        $this->setStoredLanguage('de');

        $this->assertFalse(UnAuthController::changeLanguage('xx'));
        $this->assertFalse(UnAuthController::changeLanguage('../en'));

        $this->assertSame('de', $this->storedLanguage());
    }

    public function test_system_language_survives_a_cache_clear()
    {
        UnAuthController::changeLanguage('fr');
        Cache::forget('language');

        $this->assertSame('fr', $this->localeForGuestRequest());
        $this->assertSame('fr', Cache::get('language'), 'cache is re-warmed from the database');
    }

    public function test_falls_back_when_no_language_is_stored()
    {
        $this->assertSame('', systemLanguage());
        // With no user/session/system language, the request rests on the
        // configured default locale (app.locale), matching the enterprise edition.
        $this->assertSame(config('app.locale'), $this->localeForGuestRequest());
    }

    public function test_upgrade_backfills_language_from_cache()
    {
        Cache::forever('language', 'fr');

        (new DatabaseSeeder())->run();

        $this->assertSame('fr', $this->storedLanguage());
    }

    public function test_upgrade_backfills_fallback_when_cache_was_already_lost()
    {
        (new DatabaseSeeder())->run();

        $this->assertSame(config('app.fallback_locale'), $this->storedLanguage());
    }

    public function test_upgrade_does_not_overwrite_a_stored_language()
    {
        $this->setStoredLanguage('ar');
        Cache::forever('language', 'fr');

        (new DatabaseSeeder())->run();

        $this->assertSame('ar', $this->storedLanguage());
    }
}
