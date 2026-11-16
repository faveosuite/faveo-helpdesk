<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\TicketToken;
use App\Model\helpdesk\Ticket\Tickets;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression tests for the client-portal reply BOLA report
 * (CWE-862 / CWE-639): "POST /post/reply/{id} lets any logged-in customer
 * reply to another customer's ticket, and the reply is recorded as the
 * ticket owner".
 *
 * Rule under test: a customer may only add to a ticket they own. That holds
 * for every customer-reachable way of appending a thread to an existing
 * ticket:
 *   - POST /post/reply/{id}            (logged-in reply, the reported endpoint)
 *   - POST /post-ticket-reply/{id}     (guest reply, needs the emailed ticket token)
 *   - POST /postedform                 (public create-ticket form; a "[#number]"
 *                                       in the Subject used to thread into that ticket)
 *   - POST /ticket/{close,resolve,open}/{id}, /rating/{id}, /rating2/{id}
 *
 * Every refusal is also checked for side effects: no thread written and no
 * ticket field changed. Mail and Queue are faked; data is synthetic.
 */
class ClientTicketReplyAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
    }

    // ── fixtures ─────────────────────────────────────────────────────────────

    private function makeCustomer(): User
    {
        $suffix = Str::random(10);
        $user = new User([
            'first_name' => 'Bola',
            'last_name'  => $suffix,
            'email'      => 'bola-'.$suffix.'@example.test',
            'user_name'  => 'bola_'.$suffix,
            'password'   => Hash::make(Str::random(12)),
            'active'     => 1,
            'role'       => 'user',
        ]);
        $user->save();

        return $user;
    }

    private function makeTicket(User $owner, int $status = 1): Tickets
    {
        $ticket = new Tickets([
            'ticket_number' => 'BOLA-'.random_int(100000, 999999),
            'user_id'       => $owner->id,
            'priority_id'   => 2,
            'sla'           => 2,
            'help_topic_id' => 1,
            'status'        => $status,
            'source'        => 1,
            'is_deleted'    => 0,
        ]);
        $ticket->save();
        $ticket->dept_id = 1;
        $ticket->save();

        (new Ticket_Thread([
            'ticket_id' => $ticket->id,
            'user_id'   => $owner->id,
            'poster'    => 'client',
            'title'     => 'Bola subject',
            'body'      => 'Bola original body',
        ]))->save();

        return $ticket->fresh();
    }

    private function snapshot(Tickets $ticket): array
    {
        return Tickets::find($ticket->id)->only(['status', 'closed', 'reopened', 'isanswered', 'is_deleted']);
    }

    private function threadCount(Tickets $ticket): int
    {
        return Ticket_Thread::where('ticket_id', $ticket->id)->count();
    }

    private function assertNoSideEffects(Tickets $ticket, array $before, int $threadsBefore): void
    {
        $this->assertSame($threadsBefore, $this->threadCount($ticket), 'a thread was written to a ticket the requester does not own');
        $this->assertSame($before, $this->snapshot($ticket), 'a ticket the requester does not own was changed');
        $this->assertSame(0, Ticket_Thread::where('ticket_id', $ticket->id)->where('body', 'like', '%BOLA ATTACK%')->count());
    }

    // ── POST /post/reply/{id} (the reported endpoint) ───────────────────────

    /** Exact request from the report: attacker (role user) replies to the victim's ticket. */
    public function test_customer_cannot_reply_to_another_customers_ticket()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->makeCustomer());

        $response = $this->post(route('client.reply', ['id' => $victim->id]), [
            'comment' => "BOLA ATTACK: injected into another user's ticket",
        ]);

        $response->assertStatus(403);
        $this->assertNoSideEffects($victim, $before, $threads);
    }

    public function test_customer_cannot_reply_to_another_customers_closed_ticket()
    {
        $victim = $this->makeTicket($this->makeCustomer(), 3);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->makeCustomer());

        $this->post(route('client.reply', ['id' => $victim->id]), ['comment' => 'BOLA ATTACK closed'])->assertStatus(403);

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    public function test_reply_to_a_missing_ticket_is_refused_not_crashed()
    {
        $this->actingAs($this->makeCustomer());

        $this->post(route('client.reply', ['id' => 999999999]), ['comment' => 'BOLA ATTACK'])->assertStatus(403);
    }

    public function test_guest_cannot_use_the_logged_in_reply_endpoint()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $threads = $this->threadCount($victim);

        $this->post(route('client.reply', ['id' => $victim->id]), ['comment' => 'BOLA ATTACK guest'])
            ->assertRedirect();

        $this->assertSame($threads, $this->threadCount($victim));
    }

    /** Regression: the owner can still reply, and the thread is attributed to them. */
    public function test_owner_can_reply_and_reply_is_attributed_to_the_owner()
    {
        $owner = $this->makeCustomer();
        $ticket = $this->makeTicket($owner);
        $threads = $this->threadCount($ticket);
        $this->actingAs($owner);

        $this->post(route('client.reply', ['id' => $ticket->id]), ['comment' => 'legitimate owner reply'])
            ->assertRedirect()
            ->assertSessionHas('success1');

        $this->assertSame($threads + 1, $this->threadCount($ticket));
        $thread = Ticket_Thread::where('ticket_id', $ticket->id)->where('body', 'like', '%legitimate owner reply%')->first();
        $this->assertNotNull($thread);
        $this->assertSame((int) $owner->id, (int) $thread->user_id);
    }

    /** Agents reply from the agent panel; the customer-portal endpoint is for ticket owners only. */
    public function test_agent_cannot_use_the_customer_reply_endpoint_for_a_customers_ticket()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $agent = $this->makeCustomer();
        $agent->role = 'agent';
        $agent->save();
        $this->actingAs($agent);

        $this->post(route('client.reply', ['id' => $victim->id]), ['comment' => 'BOLA ATTACK agent'])->assertStatus(403);

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    // ── POST /post-ticket-reply/{id} (guest reply, token-gated) ─────────────

    public function test_guest_reply_without_a_valid_ticket_token_is_refused()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $this->post('post-ticket-reply/'.$victim->id, ['comment' => 'BOLA ATTACK guest', 'tid_token' => 'guessed'])
            ->assertSessionHas('fails1');

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    public function test_other_logged_in_customer_cannot_use_the_guest_reply_endpoint()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->makeCustomer());

        $this->post('post-ticket-reply/'.$victim->id, ['comment' => 'BOLA ATTACK other'])
            ->assertSessionHas('fails1');

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    /**
     * Issue a guest ticket token the way UnAuthController::PostCheckTicket() does
     * (plain token mailed, bcrypt hash stored). $ageHours backdates it.
     */
    private function issueGuestToken(Tickets $ticket, int $ageHours = 0): string
    {
        $plain = Str::random(10);
        TicketToken::where('ticket_id', $ticket->id)->delete();
        $token = new TicketToken();
        $token->ticket_id = $ticket->id;
        $token->token = Hash::make($plain);
        $token->save();
        if ($ageHours > 0) {
            // Bypass Eloquent timestamps so updated_at really is in the past.
            DB::table('ticket_token')->where('id', $token->id)->update(['updated_at' => now()->subHours($ageHours)]);
        }

        return $plain;
    }

    /** Exact request from the "unauthenticated /post-ticket-reply" report: no session, no token. */
    public function test_unauthenticated_reply_without_any_token_is_refused()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $this->post('post-ticket-reply/'.$victim->id, ['comment' => 'BOLA ATTACK INJECTED BY UNAUTHENTICATED ATTACKER'])
            ->assertSessionHas('fails1');

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    public function test_guest_token_for_one_ticket_cannot_reply_to_another()
    {
        $own = $this->makeTicket($this->makeCustomer());
        $victim = $this->makeTicket($this->makeCustomer());
        $plain = $this->issueGuestToken($own);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $this->post('post-ticket-reply/'.$victim->id, ['comment' => 'BOLA ATTACK cross token', 'tid_token' => $plain])
            ->assertSessionHas('fails1');

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    /** The emailed link expires (ticket_token_time_duration); so must the right to write with it. */
    public function test_guest_reply_with_an_expired_token_is_refused()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $plain = $this->issueGuestToken($victim, 24 * 365);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $this->post('post-ticket-reply/'.$victim->id, ['comment' => 'BOLA ATTACK expired token', 'tid_token' => $plain])
            ->assertSessionHas('fails1');

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    public function test_guest_status_change_and_close_with_an_expired_token_are_refused()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $plain = $this->issueGuestToken($victim, 24 * 365);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $this->post(route('show.change.status', ['status' => 3, 'id' => $victim->id]), ['tid_token' => $plain]);
        $this->post(route('show.close', ['id' => $victim->id]), ['tid_token' => $plain]);

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    /** Regression: the legitimate guest flow (valid, fresh emailed token) still works. */
    public function test_guest_reply_with_a_valid_token_is_accepted_and_attributed_to_the_owner()
    {
        $owner = $this->makeCustomer();
        $ticket = $this->makeTicket($owner);
        $plain = $this->issueGuestToken($ticket);
        $threads = $this->threadCount($ticket);

        $this->post('post-ticket-reply/'.$ticket->id, ['comment' => 'guest owner reply', 'tid_token' => $plain])
            ->assertSessionHas('success1');

        $this->assertSame($threads + 1, $this->threadCount($ticket));
        $thread = Ticket_Thread::where('ticket_id', $ticket->id)->where('body', 'like', '%guest owner reply%')->first();
        $this->assertNotNull($thread);
        $this->assertSame((int) $owner->id, (int) $thread->user_id);
    }

    // ── POST /postedform (public create-ticket form) ────────────────────────

    private function submitNewTicketForm(string $email, string $subject, string $details)
    {
        return $this->post(route('client.form.post'), [
            'Name'      => 'Form User',
            'Email'     => $email,
            'Subject'   => $subject,
            'Details'   => $details,
            'helptopic' => 1,
        ]);
    }

    /** A "[#number]" in a new ticket's subject must not thread into someone else's ticket. */
    public function test_new_ticket_form_with_a_foreign_ticket_number_in_the_subject_does_not_reply_to_it()
    {
        $victim = $this->makeTicket($this->makeCustomer());
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $attacker = $this->makeCustomer();
        $this->actingAs($attacker);

        $this->submitNewTicketForm($attacker->email, 'Hello [#'.$victim->ticket_number.']', 'BOLA ATTACK via subject');

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    /** Same, unauthenticated, giving the victim's own e-mail address: no impersonated reply. */
    public function test_guest_new_ticket_form_with_victim_email_and_ticket_number_does_not_reply_to_it()
    {
        $owner = $this->makeCustomer();
        $victim = $this->makeTicket($owner, 3);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $this->submitNewTicketForm($owner->email, 'Re: Bola subject [#'.$victim->ticket_number.']', 'BOLA ATTACK impersonation');

        $this->assertNoSideEffects($victim, $before, $threads);
    }

    /** Regression: the form still creates a new ticket (and does not lose the submission). */
    public function test_new_ticket_form_still_creates_a_new_ticket()
    {
        $user = $this->makeCustomer();
        $victim = $this->makeTicket($this->makeCustomer());
        $this->actingAs($user);
        $countBefore = Tickets::where('user_id', $user->id)->count();

        $this->submitNewTicketForm($user->email, 'Printer broken [#'.$victim->ticket_number.']', 'my own new issue')
            ->assertSessionHas('success');

        $this->assertSame($countBefore + 1, Tickets::where('user_id', $user->id)->count());
        $thread = Ticket_Thread::where('body', 'like', '%my own new issue%')->whereIn('ticket_id', Tickets::where('user_id', $user->id)->pluck('id'))->first();
        $this->assertNotNull($thread);
        // The marker must not survive into the stored title: notification subjects are built as
        // "<title>[#<new number>]", and check_ticket() follows the FIRST "[#" it finds, so a kept
        // "[#victim]" would route the customer's later e-mail replies into the victim's ticket.
        $this->assertStringNotContainsString('[#', (string) $thread->title);
        $this->assertStringContainsString($victim->ticket_number, (string) $thread->title, 'subject text should be kept, only the marker neutralised');
    }

    // ── sibling actions in the same route group ─────────────────────────────

    public static function siblingActions(): array
    {
        return [
            'close'   => ['ticket.close'],
            'resolve' => ['ticket.resolve'],
            'open'    => ['ticket.open'],
            'rating'  => ['ticket.rating'],
            'rating2' => ['ticket.rating2'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('siblingActions')]
    public function test_customer_cannot_change_another_customers_ticket_via_sibling_action(string $route)
    {
        $victim = $this->makeTicket($this->makeCustomer(), $route === 'ticket.open' ? 3 : 1);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->makeCustomer());

        $response = $this->post(route($route, ['id' => $victim->id]), ['rating' => 5, 'thread_id' => 0]);

        // close/resolve/open refuse a customer by redirecting to the 'unauth' page; rating* abort(403).
        $refused = $response->status() === 403 || ($response->isRedirect() && $response->headers->get('Location') === route('unauth'));
        $this->assertTrue($refused, 'request was not refused (HTTP '.$response->status().')');
        $this->assertNoSideEffects($victim, $before, $threads);
    }
}
