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
use Tests\Concerns\CreatesPassportTokens;
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
    use CreatesPassportTokens;

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

    // ── read-only disclosure (PDF print) ─────────────────────────────────────

    #[DataProvider('foreignTickets')]
    public function test_print_of_another_departments_ticket_is_refused(?string $assignee, int $status)
    {
        $victim = $this->foreignTicket($assignee, $status);
        $this->actingAs($this->agentA());

        $this->get(route('ticket.print', ['id' => $victim->id]))->assertStatus(403);
    }

    public function test_print_of_a_missing_ticket_is_refused()
    {
        $this->actingAs($this->agentA());

        $this->get(route('ticket.print', ['id' => 999999999]))->assertStatus(403);
    }

    #[DataProvider('foreignTickets')]
    public function test_thread_pdf_of_another_departments_ticket_is_refused(?string $assignee, int $status)
    {
        $victim = $this->foreignTicket($assignee, $status);
        $threadId = Ticket_Thread::where('ticket_id', $victim->id)->value('id');
        $this->actingAs($this->agentA());

        // pdfThread() catches the 403 abort in its try/catch and redirects back
        // with a flash error, so the refusal shows up as a redirect, not a 403.
        $response = $this->get(route('thread.pdf', ['threadid' => $threadId]));

        $response->assertRedirect();
        $response->assertSessionHas('fails', 'Unauthorized');
        $this->assertStringNotContainsString('Authz body', (string) $response->getContent());
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

    // ── merge ────────────────────────────────────────────────────────────────

    /** Merge state of a ticket: status plus where its threads live. */
    private function mergeSnapshot(Tickets $ticket): array
    {
        return [$this->snapshot($ticket), $this->threadCount($ticket)];
    }

    /** Ticket page flow: a foreign ticket in the URL. */
    public function test_merge_with_foreign_ticket_in_path_is_refused()
    {
        $own = $this->makeTicket(1);
        $victim = $this->makeTicket(2);
        $before = $this->mergeSnapshot($victim);
        $this->actingAs($this->agentA());

        $response = $this->patch(route('merge.tickets', ['id' => $victim->id]), ['p_id' => $own->id, 't_id' => [$own->id]]);

        $this->assertSame(403, $response->status());
        $this->assertSame($before, $this->mergeSnapshot($victim));
    }

    /** Own ticket in the URL, foreign ticket smuggled in as a child via t_id[]. */
    public function test_merge_with_foreign_child_in_body_is_refused()
    {
        $own = $this->makeTicket(1);
        $victim = $this->makeTicket(2);
        $before = $this->mergeSnapshot($victim);
        $ownBefore = $this->mergeSnapshot($own);
        $this->actingAs($this->agentA());

        $response = $this->patch(route('merge.tickets', ['id' => $own->id]), ['p_id' => $own->id, 't_id' => [$victim->id]]);

        $this->assertSame(403, $response->status());
        $this->assertSame($before, $this->mergeSnapshot($victim), 'foreign ticket was merged away');
        $this->assertSame($ownBefore, $this->mergeSnapshot($own));
    }

    /** Own ticket as the child, foreign ticket as the parent via p_id. */
    public function test_merge_into_foreign_parent_is_refused()
    {
        $own = $this->makeTicket(1);
        $victim = $this->makeTicket(2);
        $before = $this->mergeSnapshot($victim);
        $ownBefore = $this->mergeSnapshot($own);
        $this->actingAs($this->agentA());

        $response = $this->patch(route('merge.tickets', ['id' => $own->id]), ['p_id' => $victim->id, 't_id' => [$own->id]]);

        $this->assertSame(403, $response->status());
        $this->assertSame($before, $this->mergeSnapshot($victim), 'threads were written into a foreign ticket');
        $this->assertSame($ownBefore, $this->mergeSnapshot($own));
    }

    /** Ticket list flow: '<own>,<victim>' in the URL must not pass on the first id alone. */
    public function test_merge_with_foreign_ticket_in_comma_list_is_refused()
    {
        $own = $this->makeTicket(1);
        $victim = $this->makeTicket(2);
        $before = $this->mergeSnapshot($victim);
        $this->actingAs($this->agentA());

        $response = $this->patch(route('merge.tickets', ['id' => $own->id.','.$victim->id]), ['p_id' => $own->id]);

        $this->assertSame(403, $response->status());
        $this->assertSame($before, $this->mergeSnapshot($victim));
    }

    public function test_merge_within_own_department_is_allowed()
    {
        $parent = $this->makeTicket(1);
        $child = $this->makeTicket(1);
        $this->actingAs($this->agentA());

        $response = $this->patch(route('merge.tickets', ['id' => $parent->id]), ['p_id' => $parent->id, 't_id' => [$child->id]]);

        $this->assertSame(200, $response->status());
        $this->assertSame(3, (int) Tickets::find($child->id)->status, 'child ticket was not merged');
    }

    public function test_merge_candidate_lookups_for_foreign_ticket_are_refused()
    {
        $victim = $this->makeTicket(2);
        $this->actingAs($this->agentA());

        $this->get(route('get.merge.tickets', ['id' => $victim->id]))->assertStatus(403);
        $this->get(route('check.merge.tickets', ['id' => $victim->id]))->assertStatus(403);
        $this->get(route('get.merge.tickets', ['id' => 0, 'data1' => [$victim->id]]))->assertStatus(403);
        $this->get(route('check.merge.tickets', ['id' => 0, 'data1' => [$victim->id, $victim->id]]))->assertStatus(403);
        $this->get(route('get.parent.ticket', ['id' => $victim->id, 'data1' => [$victim->id]]))->assertStatus(403);
    }

    /** The same customer's ticket in another department must not be listed. */
    public function test_merge_candidates_exclude_other_departments()
    {
        $own = $this->makeTicket(1);
        $sameCustomerOwnDept = $this->makeTicket(1);
        $sameCustomerOtherDept = $this->makeTicket(2);
        foreach ([$sameCustomerOwnDept, $sameCustomerOtherDept] as $t) {
            $t->user_id = $own->user_id;
            $t->save();
        }
        $this->actingAs($this->agentA());

        $html = $this->echoedBy(fn () => $this->get(route('get.merge.tickets', ['id' => $own->id]))->assertStatus(200));

        $this->assertStringContainsString("value='{$sameCustomerOwnDept->id}'", $html);
        $this->assertStringNotContainsString("value='{$sameCustomerOtherDept->id}'", $html);
    }

    public function test_merge_candidate_titles_are_escaped()
    {
        $own = $this->makeTicket(1);
        $other = $this->makeTicket(1);
        $other->user_id = $own->user_id;
        $other->save();
        Ticket_Thread::where('ticket_id', $other->id)->update(['title' => '<script>x</script>']);
        $this->actingAs($this->agentA());

        $html = $this->echoedBy(fn () => $this->get(route('get.merge.tickets', ['id' => $own->id])));

        $this->assertStringContainsString("value='{$other->id}'", $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    /** The merge pickers echo their <option> list instead of returning it. */
    private function echoedBy(callable $request): string
    {
        ob_start();
        try {
            $request();
        } finally {
            $out = ob_get_clean();
        }

        return $out;
    }

    // ── edit / due date / lock ───────────────────────────────────────────────

    public function test_edit_of_another_departments_ticket_is_refused()
    {
        $victim = $this->makeTicket(2);
        $before = Tickets::find($victim->id)->only(['sla', 'help_topic_id', 'source', 'priority_id', 'dept_id']);
        $this->actingAs($this->agentA());

        $response = $this->patch(route('ticket.post.edit', ['id' => $victim->id]), [
            'subject'         => 'hijacked',
            'sla_paln'        => 1,
            'help_topic'      => 1,
            'ticket_source'   => 2,
            'ticket_priority' => 1,
        ]);

        $this->assertSame(403, $response->status());
        $this->assertSame($before, Tickets::find($victim->id)->only(['sla', 'help_topic_id', 'source', 'priority_id', 'dept_id']));
        $this->assertSame('Authz subject', Ticket_Thread::where('ticket_id', $victim->id)->first()->title);
    }

    public function test_due_date_change_on_another_departments_ticket_is_refused()
    {
        $victim = $this->makeTicket(2);
        $before = Tickets::find($victim->id)->duedate;
        $this->actingAs($this->agentA());

        $this->post(route('ticket.duedate', ['id' => $victim->id]), ['duedate' => '01/01/2030'])->assertStatus(403);

        $this->assertSame($before, Tickets::find($victim->id)->duedate);
    }

    public function test_lock_of_another_departments_ticket_is_refused()
    {
        $victim = $this->makeTicket(2);
        $before = Tickets::find($victim->id)->only(['lock_by', 'lock_at']);
        $this->actingAs($this->agentA());

        // Two routes share the name 'lock'; address check/lock/{id} by URL.
        $this->get('check/lock/'.$victim->id)->assertStatus(403);

        $this->assertSame($before, Tickets::find($victim->id)->only(['lock_by', 'lock_at']));
    }

    // ── API v1 (one request per test: see ApiV1ControllerTest) ───────────────

    private function apiHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.$this->passportTokenFor($user)];
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
     * access check is reached, so this asserts the outcome, not the status.).
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
