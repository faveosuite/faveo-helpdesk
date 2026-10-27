<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The ticket page (priority field and the edit dialog's priority dropdown) showed the priority's
 * description instead of its name, so a priority named "checked" described as "checking" read "checking".
 */
class PriorityNameDisplayTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ticket_page_shows_priority_name_not_description()
    {
        $s = Str::random(6);
        $priority = Ticket_Priority::create(['priority' => 'Name'.$s, 'priority_desc' => 'Desc'.$s, 'status' => 1,
            'priority_color'                            => '#000000', 'priority_urgency' => 1, 'ispublic' => 1]);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password'                      => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
        $_SERVER['REQUEST_URI'] = '/newticket';
        $this->actingAs($admin)->post('newticket/post', [
            'email'     => 'c'.$s.'@example.test', 'first_name' => 'Customer', 'last_name' => 'Person',
            'helptopic' => 1, 'sla' => 1, 'priority' => $priority->priority_id, 'subject' => 'Priority '.$s, 'body' => 'Priority display body',
        ]);
        $ticket = Tickets::find(Ticket_Thread::where('title', '=', 'Priority '.$s)->value('ticket_id'));
        $this->assertNotNull($ticket, 'ticket was not created: '.json_encode(session()->all()));

        $_SERVER['REQUEST_URI'] = '/thread/'.$ticket->id;
        $html = $this->actingAs($admin)->get('thread/'.$ticket->id)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/>\s*Name'.$s.'\s*<\/option>/', $html, 'edit dialog should list the priority by name');
        $this->assertDoesNotMatchRegularExpression('/>\s*Desc'.$s.'\s*</', $html, 'description must only appear as a tooltip');
    }
}
