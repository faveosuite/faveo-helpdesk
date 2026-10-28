<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The "Testing incoming & outgoing mail server" popup on Add / Edit email put its close button inline
 * next to the spinner, and was shown by hand (classes plus a hand-made backdrop), so the close button,
 * which goes through Bootstrap, could not close it.
 */
class EmailTestPopupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_popup_close_sits_top_right_and_bootstrap_opens_and_closes_it()
    {
        $id = DB::table('emails')->value('id');
        $pages = ['emails/create'];
        if ($id) {
            $pages[] = 'emails/'.$id.'/edit';
        }

        foreach ($pages as $url) {
            $html = $this->page($url);

            $this->assertMatchesRegularExpression('#<div class="modal fade" id="loadingpopup"[^>]*data-bs-backdrop="static"#', $html, $url);
            $this->assertStringContainsString('<button type="button" class="btn-close position-absolute top-0 end-0 m-3" id="close" data-bs-dismiss="modal"', $html, $url);
            $this->assertStringContainsString("bootstrap.Modal.getOrCreateInstance(document.getElementById('loadingpopup')).show();", $html, $url);
            $this->assertStringNotContainsString('modal-backdrop', $html, $url);
            $this->assertSame(1, substr_count($html, 'id="head"'), "$url: one result container");
            $this->assertStringContainsString("$('#form').on('submit'", $html, "$url: submit handler still present");
        }
    }

    private function page($url)
    {
        $s = Str::random(8);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
        $_SERVER['REQUEST_URI'] = '/'.$url;

        return $this->actingAs($admin)->get($url)->assertOk()->getContent();
    }
}
