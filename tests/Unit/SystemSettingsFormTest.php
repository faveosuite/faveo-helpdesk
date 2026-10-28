<?php

namespace Tests\Unit;

use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Settings\System;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The System settings radios were regrouped into inline labels; each group must still post the
 * same field names and show the saved choice.
 */
class SystemSettingsFormTest extends TestCase
{
    use DatabaseTransactions;

    public function test_each_radio_group_shows_the_saved_choice()
    {
        $expected = [
            'status'                 => (string) System::first()->status === '0' ? '0' : '1',
            'user_set_ticket_status' => (string) CommonSettings::where('option_name', 'user_set_ticket_status')->value('status'),
            'send_otp'               => (string) CommonSettings::where('option_name', 'send_otp')->value('status'),
            'email_mandatory'        => (string) CommonSettings::where('option_name', 'email_mandatory')->value('status'),
        ];

        $html = $this->page();

        foreach ($expected as $name => $value) {
            preg_match_all('#<label class="fw-normal[^"]*"><input[^>]*type="radio"[^>]*name="'.$name.'"[^>]*value="([^"]+)"([^>]*)>#', $html, $m, PREG_SET_ORDER);
            $this->assertCount(2, $m, "$name radios");
            $checked = array_column(array_filter($m, fn ($r) => str_contains($r[2], 'checked')), 1);
            $this->assertSame([$value], $checked, "$name shows the saved choice");
        }
        $this->assertMatchesRegularExpression('#<label class="fw-normal"><input type="checkbox" name="enable_rtl"#', $html);
    }

    private function page()
    {
        $s = Str::random(8);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
        $_SERVER['REQUEST_URI'] = '/getsystem';

        return $this->actingAs($admin)->get('getsystem')->assertOk()->getContent();
    }
}
