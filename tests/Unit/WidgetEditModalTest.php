<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The Edit buttons on Admin > Widgets and Admin > Social used Bootstrap 4 data-toggle, so
 * under Bootstrap 5 their edit modal never opened.
 */
class WidgetEditModalTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $s = Str::random(8);
        $this->admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password'                            => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
    }

    public function test_widget_and_social_edit_buttons_open_a_bootstrap_5_modal()
    {
        $footer = DB::table('widgets')->where('id', '<', 7)->value('id');
        $social = DB::table('widgets')->where('id', '>', 6)->value('id');
        DB::table('widgets')->where('id', $footer)->update(['title' => '"><b>x</b>', 'value' => '<p>footer</p>']);
        DB::table('widgets')->where('id', $social)->update(['value' => 'https://example.test/?a="b"']);

        foreach (['list-widget' => $footer, 'list-social-buttons' => $social] as $url => $id) {
            $actions = collect($this->actingAs($this->admin)->getJson($url.'?length=-1')->assertOk()->json('data'))->pluck('Actions')->implode('');

            $this->assertStringContainsString('data-bs-toggle="modal" data-bs-target="#edit_widget'.$id.'"', $actions, $url);
            $this->assertStringContainsString('<div class="modal fade" id="edit_widget'.$id.'"', $actions, $url);
            $this->assertStringNotContainsString('data-toggle=', $actions, $url);
            $this->assertStringNotContainsString('data-dismiss=', $actions, $url);
        }

        $widgetActions = collect($this->getJson('list-widget?length=-1')->json('data'))->pluck('Actions')->implode('');
        $this->assertStringContainsString('value="&quot;&gt;&lt;b&gt;x&lt;/b&gt;"', $widgetActions);
        $this->assertStringContainsString('&lt;p&gt;footer&lt;/p&gt;</textarea>', $widgetActions);
    }

    public function test_edit_modal_saves_the_widget()
    {
        $footer = DB::table('widgets')->where('id', '<', 7)->value('id');
        $_SERVER['REQUEST_URI'] = '/edit-widget/'.$footer;

        $this->actingAs($this->admin)->from('widgets')->post('edit-widget/'.$footer, ['title' => 'Contact', 'content' => '<p>Call us</p>'])
            ->assertRedirect('widgets');

        $this->assertSame(['Contact', '<p>Call us</p>'], array_values((array) DB::table('widgets')->where('id', $footer)->first(['title', 'value'])));
    }
}
