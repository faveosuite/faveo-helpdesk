<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_Status;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The status icon picker listed bare glyph codes (some twice), so options had no readable name for
 * the search box, and its Select2 box did not line up with the inputs beside it.
 */
class StatusIconPickerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_icon_options_are_unique_named_and_keep_the_saved_icon()
    {
        $status = Ticket_Status::first();
        foreach (['fa-solid fa-bell', 'fa-solid fa-not-in-the-list'] as $saved) {
            $status->icon_class = $saved;
            $status->save();

            preg_match_all('#<option value="([^"]+)"\s*(selected)?\s*>([^<]*)</option>#', $this->page($status->id), $m, PREG_SET_ORDER);
            $values = array_column($m, 1);

            $this->assertNotEmpty($values);
            $this->assertSame(array_unique($values), $values, 'duplicate icon options');
            $this->assertSame([$saved], array_column(array_filter($m, fn ($o) => $o[2] !== ''), 1), 'saved icon is selected');
            foreach ($m as $option) {
                $this->assertMatchesRegularExpression('/^[A-Z][a-z ]+/', $option[3], $option[1].' has no readable name');
            }
        }
        $this->assertSame('Bell', collect($m)->firstWhere(1, 'fa-solid fa-bell')[3]);
    }

    private function page($id)
    {
        $s = Str::random(8);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password'                      => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
        $_SERVER['REQUEST_URI'] = '/status/edit/'.$id;

        return $this->actingAs($admin)->get('status/edit/'.$id)->assertOk()->getContent();
    }
}
