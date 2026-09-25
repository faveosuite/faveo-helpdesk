<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * "Ban email" opened with Active already selected; the admin has to choose the ban status.
 */
class BanlistCreateDefaultTest extends TestCase
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

    public function test_no_ban_status_is_selected_when_the_page_opens()
    {
        $html = $this->page();

        foreach (['1', '0'] as $value) {
            $this->assertStringNotContainsString('checked', $this->radio($html, $value), "ban=$value");
        }
    }

    public function test_submitting_without_a_ban_status_is_rejected()
    {
        $email = 'ban_'.Str::random(8).'@example.test';
        $_SERVER['REQUEST_URI'] = '/banlist';

        $this->actingAs($this->admin)->from('banlist/create')->post('banlist', ['email' => $email])
            ->assertRedirect('banlist/create')
            ->assertSessionHasErrors('ban');

        $this->assertNull(User::where('email', $email)->first());
    }

    public function test_the_chosen_status_is_kept_after_a_failed_submit()
    {
        $this->actingAs($this->admin)->from('banlist/create')->post('banlist', ['email' => 'not-an-email', 'ban' => '0']);

        $html = $this->page();

        $this->assertStringContainsString('checked', $this->radio($html, '0'));
        $this->assertStringNotContainsString('checked', $this->radio($html, '1'));
    }

    private function page()
    {
        $_SERVER['REQUEST_URI'] = '/banlist/create';

        return $this->actingAs($this->admin)->get('banlist/create')->assertOk()->getContent();
    }

    private function radio($html, $value)
    {
        preg_match('/<input[^>]*type="radio"[^>]*name="ban"[^>]*value="'.$value.'"[^>]*>/', $html, $m);
        $this->assertNotEmpty($m, "radio ban=$value not found");

        return $m[0];
    }
}
