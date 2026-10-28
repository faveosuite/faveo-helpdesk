<?php

namespace Tests\Unit;

use App\Http\Controllers\Agent\helpdesk\TicketController;
use App\Http\Controllers\Agent\helpdesk\TicketWorkflowController;
use App\Http\Requests\helpdesk\CreateTicketRequest;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\CountryCode;
use App\User;
use Database\Seeders\v_2_0_4_RC_1\DatabaseSeeder as ApiSourceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Banning an email only set users.ban = 1; nothing read it, so a banned address could still log in
 * and open tickets by web form, email or API. A ban now blocks those channels for end users, while
 * agents may still raise a ticket for them from the agent panel.
 */
class BannedEmailTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApiSourceSeeder::class);
    }

    public function test_only_banned_end_users_count_as_banned()
    {
        $this->assertTrue(User::isBannedEmail($this->user(1)->email));
        $this->assertFalse(User::isBannedEmail($this->user(0)->email));
        $this->assertFalse(User::isBannedEmail($this->user(1, 'agent')->email), 'agents are never locked out');
        $this->assertFalse(User::isBannedEmail($this->user(1, 'admin')->email), 'admins are never locked out');
        $this->assertFalse(User::isBannedEmail(null));
        $this->assertFalse(User::isBannedEmail('nobody-'.Str::random(8).'@example.test'));

        // Ban lists > Ban email for an address that is not a user yet stores an email-only row
        $email = 'new-'.Str::random(8).'@example.test';
        $this->actingAs($this->user(0, 'admin'))->post('banlist', ['email' => $email, 'ban' => '1'])->assertSessionHas('success');
        $this->assertTrue(User::isBannedEmail($email));
    }

    public function test_workflow_refuses_banned_senders_except_from_the_agent_panel()
    {
        $banned = $this->user(1);
        $workflow = app(TicketWorkflowController::class);

        foreach (['web' => true, 'email' => true, 'api' => true, 'agent' => false] as $source => $refused) {
            $subject = "Banned $source ".Str::random(8);
            $result = $workflow->workflow($banned->email, 'Banned Person', $subject, 'Body text', '', '', '', 1, 1, 1,
                Ticket_source::where('name', '=', $source)->value('id'), [], 1, null, null, null, [], 0);

            $this->assertSame($refused, !empty($result['banned']), $source);
            $this->assertSame(!$refused, $this->ticketWithSubject($subject) !== null, $source);
        }
    }

    public function test_workflow_still_creates_tickets_for_other_senders()
    {
        $subject = 'Not banned '.Str::random(8);
        $result = app(TicketWorkflowController::class)->workflow($this->user(0)->email, 'Normal Person', $subject, 'Body text', '', '', '', 1, 1, 1,
            Ticket_source::where('name', '=', 'email')->value('id'), [], 1, null, null, null, [], 0);

        $this->assertTrue((bool) $result[1]);
        $this->assertNotNull($this->ticketWithSubject($subject));
    }

    public function test_client_form_refuses_a_banned_email()
    {
        $subject = 'Form banned '.Str::random(8);
        $_SERVER['REQUEST_URI'] = '/postedform';

        $this->from('create-ticket')->post('postedform', [
            'Name' => 'Banned Person', 'Email' => $this->user(1)->email, 'helptopic' => 1, 'priority' => 1,
            'Subject' => $subject, 'Details' => 'Ticket body from a banned email',
        ])->assertRedirect('create-ticket');

        $this->assertSame(trans('lang.email-is-banned'), session('fails'));
        $this->assertNull($this->ticketWithSubject($subject));
    }

    public function test_api_refuses_a_banned_email_and_the_agent_panel_allows_it()
    {
        $banned = $this->user(1);
        $this->actingAs($this->user(0, 'admin'));
        $fields = ['email' => $banned->email, 'first_name' => 'Banned', 'last_name' => 'Person', 'helptopic' => 1, 'sla' => 1, 'priority' => 1, 'body' => 'Ticket body text'];

        $subject = 'Api banned '.Str::random(8);
        $response = app(TicketController::class)->post_newticket(new CreateTicketRequest($fields + ['subject' => $subject]), new CountryCode(), true);
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(trans('lang.email-is-banned'), $response->getData(true)['error']);
        $this->assertNull($this->ticketWithSubject($subject));

        $subject = 'Agent banned '.Str::random(8);
        $_SERVER['REQUEST_URI'] = '/newticket';
        $this->post('newticket/post', $fields + ['subject' => $subject]);
        $this->assertNotNull($this->ticketWithSubject($subject), 'an agent can still raise a ticket for a banned email');
    }

    public function test_banned_user_cannot_log_in_but_others_can()
    {
        $password = 'Pass-'.Str::random(10);
        $banned = $this->user(1, 'user', $password);
        $_SERVER['REQUEST_URI'] = '/auth/login';

        // the login throttling reads the client IP straight from $_SERVER
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->from('auth/login')->post('auth/login', ['email' => $banned->email, 'password' => $password])->assertRedirect('auth/login');
        $this->assertFalse(Auth::check());
        $this->assertSame(trans('lang.email-is-banned'), session('error'));

        $this->flushSession();
        $allowed = $this->user(0, 'user', $password);
        $this->post('auth/login', ['email' => $allowed->email, 'password' => $password]);
        $this->assertSame($allowed->id, Auth::id());
    }

    public function test_banned_user_cannot_reply_from_the_client_panel()
    {
        $owner = $this->user(0);
        $subject = 'Reply banned '.Str::random(8);
        app(TicketWorkflowController::class)->workflow($owner->email, 'Ticket Owner', $subject, 'First message', '', '', '', 1, 1, 1,
            Ticket_source::where('name', '=', 'web')->value('id'), [], 1, null, null, null, [], 0);
        $ticket = $this->ticketWithSubject($subject);
        $threads = Ticket_Thread::where('ticket_id', '=', $ticket->id)->count();

        $owner->ban = 1;
        $owner->save();
        $_SERVER['REQUEST_URI'] = '/post/reply/'.$ticket->id;
        $this->actingAs($owner)->from('mytickets')->post('post/reply/'.$ticket->id, ['comment' => 'A reply from a banned user']);

        $this->assertSame(trans('lang.email-is-banned'), session('fails1'));
        $this->assertSame($threads, Ticket_Thread::where('ticket_id', '=', $ticket->id)->count());
    }

    private function user($ban, $role = 'user', $password = null)
    {
        $s = Str::random(8);

        return User::create(['first_name' => 'U', 'last_name' => 'Ser', 'email' => 'u'.$s.'@example.test', 'user_name' => 'u_'.$s,
            'password' => Hash::make($password ?: Str::random(12)), 'active' => 1, 'role' => $role, 'ban' => $ban,
            'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
    }

    private function ticketWithSubject($subject)
    {
        $thread = Ticket_Thread::where('title', '=', $subject)->first();

        return $thread ? Tickets::find($thread->ticket_id) : null;
    }
}
