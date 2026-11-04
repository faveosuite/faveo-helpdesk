<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression tests for the cross-department ticket authorization report
 * (CWE-862 / CWE-639, support ticket #54225).
 *
 * Rule under test (same as TicketController::thread() / authorizeTicketAccess()):
 * an agent may act on a ticket in their own primary department, or one assigned
 * to them; an admin may act on any ticket.
 *
 * Every refusal is checked for side effects too: no thread written, no ticket
 * field changed, and no mail job queued. Mail and Queue are faked, so nothing
 * can leave the test process. Data is synthetic and lives in testing_db.
 */
class CrossDepartmentTicketAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
    }

    // ── fixtures ─────────────────────────────────────────────────────────────

    private function makeUser(string $role, array $overrides = []): User
    {
        $suffix = Str::random(10);
        $user = new User(array_merge([
            'first_name'   => 'Authz',
            'last_name'    => $suffix,
            'email'        => 'authz-'.$suffix.'@example.test',
            'user_name'    => 'authz_'.$suffix,
            'password'     => Hash::make(Str::random(12)),
            'active'       => 1,
            'role'         => $role,
            'agent_tzone'  => 81,
            'assign_group' => 1,
        ], $overrides));
        $user->save();

        return $user;
    }

    /** Agent A: primary department 1. The attacker in the refusal cases. */
    private function agentA(): User
    {
        return $this->makeUser('agent', ['primary_dpt' => 1]);
    }

    /** Agent B: primary department 2. Owns the victim tickets. */
    private function agentB(): User
    {
        return $this->makeUser('agent', ['primary_dpt' => 2]);
    }

    /** A ticket raised by a synthetic customer, in $dept. */
    private function makeTicket(int $dept, ?int $assignedTo = null, int $status = 1): Tickets
    {
        $customer = $this->makeUser('user', ['primary_dpt' => null, 'assign_group' => null]);
        $ticket = new Tickets([
            'ticket_number' => 'AUTHZ-'.random_int(100000, 999999),
            'user_id'       => $customer->id,
            'priority_id'   => 2,
            'sla'           => 2,
            'help_topic_id' => 1,
            'status'        => $status,
            'source'        => 1,
            'is_deleted'    => 0,
        ]);
        $ticket->save();
        // dept_id isn't mass-assignable.
        $ticket->dept_id = $dept;
        $ticket->assigned_to = $assignedTo;
        $ticket->save();

        (new Ticket_Thread([
            'ticket_id' => $ticket->id,
            'user_id'   => $customer->id,
            'poster'    => 'client',
            'title'     => 'Authz subject',
            'body'      => 'Authz body',
        ]))->save();

        return $ticket->fresh();
    }

    /** The ticket fields a refused request must leave untouched. */
    private function snapshot(Tickets $ticket): array
    {
        $fresh = Tickets::find($ticket->id);

        return $fresh ? $fresh->only(['status', 'is_deleted', 'assigned_to', 'isanswered', 'closed']) : ['deleted' => true];
    }

    private function threadCount(Tickets $ticket): int
    {
        return Ticket_Thread::where('ticket_id', $ticket->id)->count();
    }

    private function assertRefusedWithoutSideEffects($response, Tickets $ticket, array $before, int $threadsBefore): void
    {
        $this->assertSame(403, $response->status(), 'unauthorized request was not refused (HTTP '.$response->status().')');
        $this->assertSame($before, $this->snapshot($ticket), 'unauthorized request changed the ticket');
        $this->assertSame($threadsBefore, $this->threadCount($ticket), 'unauthorized request wrote a ticket thread');
        Queue::assertNothingPushed();
    }

    /** Victim tickets in Agent B's department that Agent A must not touch. */
    public static function foreignTickets(): array
    {
        return [
            'dept B, assigned to agent B' => ['agentB', 1],
            'dept B, unassigned'          => [null, 1],
            'dept B, closed, unassigned'  => [null, 3],
        ];
    }

    private function foreignTicket(?string $assignee, int $status): Tickets
    {
        $assignedTo = $assignee === 'agentB' ? $this->agentB()->id : null;

        return $this->makeTicket(2, $assignedTo, $status);
    }

    // ── reply (web) ──────────────────────────────────────────────────────────

    /** The exact fields the agent reply form (#form3) submits. */
    private function replyAsUi(Tickets $ticket, string $body)
    {
        return $this->post(route('ticket.reply', ['id' => $ticket->id]), [
            'ticket_ID'     => $ticket->id,
            'reply_content' => $body,
        ]);
    }

    #[DataProvider('foreignTickets')]
    public function test_reply_to_another_departments_ticket_is_refused(?string $assignee, int $status)
    {
        $victim = $this->foreignTicket($assignee, $status);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->agentA());

        $response = $this->replyAsUi($victim, 'unauthorized reply');

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    /** ticket_id (lowercase) naming an allowed ticket must not unlock ticket_ID. */
    public function test_reply_with_mismatched_ticket_id_fields_is_refused()
    {
        $agent = $this->agentA();
        $own = $this->makeTicket(1);
        $victim = $this->makeTicket(2);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($agent);

        $response = $this->post(route('ticket.reply', ['id' => $own->id]), [
            'ticket_id'     => $own->id,
            'ticket_ID'     => $victim->id,
            'reply_content' => 'unauthorized reply',
        ]);

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    public function test_reply_without_a_ticket_id_is_refused()
    {
        $this->actingAs($this->agentA());

        $response = $this->post(route('ticket.reply', ['id' => 0]), ['reply_content' => 'no ticket']);

        $this->assertSame(403, $response->status());
        Queue::assertNothingPushed();
    }

    public function test_reply_to_own_department_ticket_is_allowed()
    {
        $ticket = $this->makeTicket(1);
        $this->actingAs($this->agentA());

        $this->replyAsUi($ticket, 'authorized reply')->assertStatus(200);

        $this->assertSame(1, Ticket_Thread::where('ticket_id', $ticket->id)->where('body', 'authorized reply')->count());
    }

    public function test_reply_to_ticket_assigned_to_the_agent_in_another_department_is_allowed()
    {
        $agent = $this->agentA();
        $ticket = $this->makeTicket(2, $agent->id);
        $this->actingAs($agent);

        $this->replyAsUi($ticket, 'assigned reply')->assertStatus(200);

        $this->assertSame(1, Ticket_Thread::where('ticket_id', $ticket->id)->where('body', 'assigned reply')->count());
    }

    public function test_admin_may_reply_to_any_department()
    {
        $ticket = $this->makeTicket(2);
        $this->actingAs($this->makeUser('admin', ['primary_dpt' => 1]));

        $this->replyAsUi($ticket, 'admin reply')->assertStatus(200);

        $this->assertSame(1, Ticket_Thread::where('ticket_id', $ticket->id)->where('body', 'admin reply')->count());
    }

    // ── delete ───────────────────────────────────────────────────────────────

    #[DataProvider('foreignTickets')]
    public function test_delete_of_another_departments_ticket_is_refused(?string $assignee, int $status)
    {
        $victim = $this->foreignTicket($assignee, $status);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->agentA());

        $response = $this->post(route('ticket.delete', ['id' => $victim->id]));

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    /** status 5 = already in trash: the next delete is the permanent one. */
    public function test_permanent_delete_of_another_departments_trashed_ticket_is_refused()
    {
        $victim = $this->makeTicket(2, null, 5);
        $this->actingAs($this->agentA());

        $this->post(route('ticket.delete', ['id' => $victim->id]))->assertStatus(403);

        $this->assertNotNull(Tickets::find($victim->id), 'foreign ticket was permanently deleted');
        $this->assertSame(1, $this->threadCount($victim));
    }

    public function test_delete_of_a_missing_ticket_is_refused_not_crashed()
    {
        $this->actingAs($this->agentA());

        $this->post(route('ticket.delete', ['id' => 999999999]))->assertStatus(403);
    }

    // ── internal note / ban / surrender ──────────────────────────────────────

    #[DataProvider('foreignTickets')]
    public function test_internal_note_on_another_departments_ticket_is_refused(?string $assignee, int $status)
    {
        $victim = $this->foreignTicket($assignee, $status);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->agentA());

        $response = $this->patch(route('Internal.note', ['id' => $victim->id]), ['InternalContent' => 'unauthorized note']);

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    #[DataProvider('foreignTickets')]
    public function test_ban_via_another_departments_ticket_is_refused(?string $assignee, int $status)
    {
        $victim = $this->foreignTicket($assignee, $status);
        $this->actingAs($this->agentA());

        $this->get(route('ban.email', ['id' => $victim->id]))->assertStatus(403);

        $this->assertSame(0, (int) User::find($victim->user_id)->ban, 'customer was banned through a foreign ticket');
    }

    public function test_surrender_of_another_departments_ticket_is_refused()
    {
        $victim = $this->foreignTicket('agentB', 1);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->agentA());

        $response = $this->get(route('ticket.surrender', ['id' => $victim->id]));

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    public function test_agent_may_surrender_a_ticket_assigned_to_them()
    {
        $agent = $this->agentA();
        $ticket = $this->makeTicket(2, $agent->id);
        $this->actingAs($agent);

        $this->get(route('ticket.surrender', ['id' => $ticket->id]))->assertStatus(200);

        $this->assertNull(Tickets::find($ticket->id)->assigned_to);
    }

    // ── status changes: open / close / resolve ───────────────────────────────

    public static function statusRoutes(): array
    {
        // [route, status the victim starts in]
        return [
            'reopen a closed ticket' => ['ticket.open', 3],
            'close'                  => ['ticket.close', 1],
            'resolve'                => ['ticket.resolve', 1],
        ];
    }

    #[DataProvider('statusRoutes')]
    public function test_status_change_on_another_departments_ticket_is_refused(string $route, int $startStatus)
    {
        $victim = $this->makeTicket(2, null, $startStatus);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->agentA());

        $response = $this->post(route($route, ['id' => $victim->id]));

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    #[DataProvider('statusRoutes')]
    public function test_status_change_on_own_department_ticket_is_allowed(string $route, int $startStatus)
    {
        $ticket = $this->makeTicket(1, null, $startStatus);
        $this->actingAs($this->agentA());

        $this->post(route($route, ['id' => $ticket->id]))->assertStatus(200);

        $this->assertNotSame($startStatus, (int) Tickets::find($ticket->id)->status);
    }

    // ── bulk actions (ticket list checkboxes) ────────────────────────────────

    public static function bulkActions(): array
    {
        return [
            'Delete'         => ['Delete'],
            'Delete forever' => ['Delete forever'],
            'Close'          => ['Close'],
            'Resolve'        => ['Resolve'],
        ];
    }

    #[DataProvider('bulkActions')]
    public function test_bulk_action_skips_another_departments_ticket(string $action)
    {
        $own = $this->makeTicket(1);
        $victim = $this->makeTicket(2);
        $ownBefore = $this->snapshot($own);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);
        $this->actingAs($this->agentA());

        $this->post(route('select_all'), ['select_all' => [$own->id, $victim->id], 'submit' => $action]);

        $this->assertSame($before, $this->snapshot($victim), "bulk '$action' changed a foreign ticket");
        $this->assertSame($threads, $this->threadCount($victim));
        // Sanity: the same request did act on the ticket the agent may touch.
        $this->assertNotSame($ownBefore, $this->snapshot($own), "bulk '$action' did nothing at all");
    }

    // ── API v1 (one request per test: see ApiV1ControllerTest) ───────────────

    private function apiHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.\JWTAuth::fromUser($user)];
    }

    public function test_api_reply_to_another_departments_ticket_is_refused()
    {
        $victim = $this->makeTicket(2);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $response = $this->postJson('/api/v1/helpdesk/reply', [
            'ticket_id'     => $victim->id,
            'reply_content' => 'unauthorized api reply',
        ], $this->apiHeaders($this->agentA()));

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    public function test_api_delete_of_another_departments_ticket_is_refused()
    {
        $victim = $this->makeTicket(2);
        $before = $this->snapshot($victim);
        $threads = $this->threadCount($victim);

        $response = $this->postJson('/api/v1/helpdesk/delete', ['ticket_id' => (string) $victim->id], $this->apiHeaders($this->agentA()));

        $this->assertRefusedWithoutSideEffects($response, $victim, $before, $threads);
    }

    /**
     * One foreign id smuggled into an otherwise-allowed list must not delete
     * anything. (The 'exists' rule currently rejects a comma list before the
     * access check is reached, so this asserts the outcome, not the status.)
     */
    public function test_api_bulk_delete_containing_a_foreign_ticket_changes_nothing()
    {
        $own = $this->makeTicket(1);
        $victim = $this->makeTicket(2);
        $ownBefore = $this->snapshot($own);
        $victimBefore = $this->snapshot($victim);

        $this->postJson('/api/v1/helpdesk/delete', ['ticket_id' => $own->id.','.$victim->id], $this->apiHeaders($this->agentA()));

        $this->assertSame($victimBefore, $this->snapshot($victim));
        $this->assertSame($ownBefore, $this->snapshot($own));
    }

    public function test_api_assign_of_another_departments_ticket_is_refused()
    {
        $victim = $this->foreignTicket('agentB', 1);
        $before = $this->snapshot($victim);
        $attacker = $this->agentA();

        $response = $this->postJson('/api/v1/helpdesk/assign', [
            'ticket_id' => $victim->id,
            'user'      => $attacker->email,
        ], $this->apiHeaders($attacker));

        $this->assertSame(403, $response->status(), 'unauthorized api assign was not refused');
        $this->assertSame($before, $this->snapshot($victim));
    }

    public function test_api_delete_of_own_department_ticket_is_allowed()
    {
        $ticket = $this->makeTicket(1);

        $response = $this->postJson('/api/v1/helpdesk/delete', ['ticket_id' => (string) $ticket->id], $this->apiHeaders($this->agentA()));

        $response->assertStatus(200);
        $this->assertSame(5, (int) Tickets::find($ticket->id)->status);
    }
}
