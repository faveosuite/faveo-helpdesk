<?php

namespace Tests\Unit;

use App\Providers\ConfigServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Admin > Settings links to Clockwork while debugging is on, but the link was a 404: the admin's
 * debugging choice was applied in ConfigServiceProvider::boot(), after Clockwork had already booted
 * and skipped its routes, and Clockwork only enabled itself on *.localhost / *.test hosts.
 */
class ClockworkDebugSettingTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Cache::forget(ConfigServiceProvider::DEBUGGING_CACHE_KEY);
        parent::tearDown();
    }

    public function test_clockwork_follows_the_saved_debugging_setting()
    {
        foreach (['1' => true, '0' => false] as $saved => $expected) {
            $this->saveDebugging($saved);
            config(['clockwork.enable' => null, 'app.debug' => !$expected]);

            (new ConfigServiceProvider($this->app))->applySavedSettings();

            $this->assertSame($expected, config('app.debug'), "debugging=$saved");
            $this->assertSame($expected, config('clockwork.enable'), "debugging=$saved");
        }
    }

    public function test_explicit_clockwork_enable_in_env_wins()
    {
        $this->saveDebugging('1');
        config(['clockwork.enable' => false]);

        (new ConfigServiceProvider($this->app))->applySavedSettings();

        $this->assertTrue(config('app.debug'));
        $this->assertFalse(config('clockwork.enable'));
    }

    public function test_saved_settings_are_read_without_eloquent()
    {
        // applySavedSettings runs before DatabaseServiceProvider::boot() gives models a connection
        $resolver = \Illuminate\Database\Eloquent\Model::getConnectionResolver();
        \Illuminate\Database\Eloquent\Model::unsetConnectionResolver();
        $this->saveDebugging('1');
        config(['app.debug' => false]);

        try {
            (new ConfigServiceProvider($this->app))->applySavedSettings();
        } finally {
            \Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
        }

        $this->assertTrue(config('app.debug'));
    }

    private function saveDebugging($value)
    {
        DB::table('common_settings')->updateOrInsert(['option_name' => 'debugging', 'optional_field' => 'app_debug'], ['option_value' => $value]);
        Cache::forget(ConfigServiceProvider::DEBUGGING_CACHE_KEY);
    }
}
