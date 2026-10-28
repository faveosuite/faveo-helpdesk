<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * On a user's profile the Closed / Deleted ticket tabs never reloaded the table: the tab script
 * called the DataTables 1 legacy fnDestroy(), which DataTables 2 no longer has, so it threw and the
 * open tickets stayed on screen. The Status row used Bootstrap 3 glyphicons, which are not loaded.
 */
class UserProfileTicketTabsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_table_scripts_do_not_use_the_removed_datatables_1_api()
    {
        foreach (glob(resource_path('views/vendor/Chumper/*.blade.php')) as $script) {
            $this->assertStringNotContainsString('fnDestroy', file_get_contents($script), basename($script));
        }
    }

    public function test_profile_status_uses_font_awesome_icons()
    {
        $s = Str::random(8);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);

        foreach ([1 => ['text-success', 'fa-circle-check'], 0 => ['text-danger', 'fa-ban']] as $active => [$colour, $icon]) {
            $user = User::create(['first_name' => 'U', 'last_name' => 'Ser', 'email' => Str::random(8).'@example.test', 'user_name' => 'u_'.Str::random(8),
                'password' => Hash::make(Str::random(10)), 'active' => $active, 'role' => 'user']);
            $_SERVER['REQUEST_URI'] = '/user/'.$user->id;

            $html = $this->actingAs($admin)->get('user/'.$user->id)->assertOk()->getContent();

            $this->assertStringNotContainsString('glyphicon', $html);
            $this->assertMatchesRegularExpression('#<span class="'.$colour.'"[^>]*>\s*<i class="fa-[a-z]+ '.$icon.'"></i>\s*<i class="fa-solid fa-user"></i>#', $html, "active=$active");
        }
    }
}
