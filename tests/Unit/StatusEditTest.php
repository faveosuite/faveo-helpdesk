<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_Status;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The status edit form always pre-ticked "resolved = yes" and never showed the saved "deleted" or
 * "notify user" answers, so every save turned the status into a closed one and lost the other two.
 */
class StatusEditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_form_shows_saved_answers()
    {
        $status = $this->makeStatus('open', 1);
        $_SERVER['REQUEST_URI'] = '/status/edit/'.$status->id;

        $html =$this->actingAs($this->makeAdmin())->get('status/edit/'.$status->id)->assertOk()->getContent();

        $this->assertRadioChecked($html, 'state', 'open');
        $this->assertRadioNotChecked($html, 'state', 'closed');
        $this->assertRadioChecked($html, 'delete', 'no');
        $this->assertRadioChecked($html, 'email_user', '1');
    }

    public function test_saving_unchanged_open_status_keeps_it_open()
    {
        $status = $this->makeStatus('open', 0);

        $this->update($status, ['state' => 'open', 'delete' => 'no', 'email_user' => '0']);

        $this->assertSame('open', $status->fresh()->state);
    }

    public function test_answers_are_saved()
    {
        $status = $this->makeStatus('open', 0);

        $this->update($status, ['state' => 'closed', 'delete' => 'no', 'email_user' => '1']);
        $this->assertSame('closed', $status->fresh()->state);
        $this->assertSame(1, (int) $status->fresh()->email_user);

        $this->update($status, ['state' => 'open', 'delete' => 'yes', 'email_user' => '0']);
        $this->assertSame('deleted', $status->fresh()->state);
        $this->assertSame(0, (int) $status->fresh()->email_user);

        $this->update($status, ['state' => 'open', 'delete' => 'no', 'email_user' => '0']);
        $this->assertSame('open', $status->fresh()->state);
    }

    public function test_saving_archived_status_keeps_its_state()
    {
        $status = $this->makeStatus('archived', 0);

        $this->update($status, ['state' => 'open', 'delete' => 'no', 'email_user' => '0']);

        $this->assertSame('archived', $status->fresh()->state);
    }

    private function update($status, array $answers)
    {
        $this->actingAs($this->makeAdmin())->patch('status-update/'.$status->id, $answers + [
            'name' => $status->name, 'sort' => 1, 'icon_class' => 'fa-solid fa-bell',
        ])->assertSessionHas('success');
    }

    private function makeStatus($state, $emailUser)
    {
        $status = new Ticket_Status();
        $status->name = 'Status '.Str::random(6);
        $status->state = $state;
        $status->mode = 3;
        $status->flags = 0;
        $status->sort = 1;
        $status->message = 'Status changed by';
        $status->email_user = $emailUser;
        $status->icon_class = 'fa-solid fa-bell';
        $status->properties = '';
        $status->save();

        return $status;
    }

    private function makeAdmin()
    {
        $s = Str::random(8);

        return User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
    }

    private function radioTag($html, $name, $value)
    {
        preg_match('/<input[^>]*type="radio"[^>]*name="'.$name.'"[^>]*value="'.$value.'"[^>]*>/', $html, $m);
        $this->assertNotEmpty($m, "radio $name=$value not found");

        return $m[0];
    }

    private function assertRadioChecked($html, $name, $value)
    {
        $this->assertStringContainsString('checked', $this->radioTag($html, $name, $value), "$name=$value should be selected");
    }

    private function assertRadioNotChecked($html, $name, $value)
    {
        $this->assertStringNotContainsString('checked', $this->radioTag($html, $name, $value), "$name=$value should not be selected");
    }
}
