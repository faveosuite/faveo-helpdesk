<?php

namespace Tests\Unit;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Clockwork boots before ConfigServiceProvider, so it must see the admin's saved debugging choice
 * when it decides whether to register its routes. Runs without a wrapping transaction so a freshly
 * booted application (its own DB connection) reads the saved row; the row is restored afterwards.
 */
class ClockworkRoutesTest extends TestCase
{
    public function test_clockwork_routes_follow_the_saved_debugging_setting()
    {
        $key = ['option_name' => 'debugging', 'optional_field' => 'app_debug'];
        $before = DB::table('common_settings')->where($key)->value('option_value');

        try {
            foreach (['1' => true, '0' => false] as $saved => $registered) {
                DB::table('common_settings')->updateOrInsert($key, ['option_value' => $saved]);

                $app = require base_path('bootstrap/app.php');
                $app->make(Kernel::class)->bootstrap();
                $uris = collect($app['router']->getRoutes()->getRoutes())->map->uri();

                $this->assertSame($registered, $app['config']->get('app.debug'), "debugging=$saved");
                $this->assertSame($registered, $uris->contains('clockwork/app'), "debugging=$saved");
            }
        } finally {
            if ($before === null) {
                DB::table('common_settings')->where($key)->delete();
            } else {
                DB::table('common_settings')->where($key)->update(['option_value' => $before]);
            }
        }
    }
}
