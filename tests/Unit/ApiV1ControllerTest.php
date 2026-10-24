<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression coverage for the v1 REST API broken-access-control report:
 * App\Api\v1\ApiController's constructor applies only 'jwt.authOveride' and the
 * shared api_key middleware, neither of which checks the caller's role. A plain
 * 'user' JWT could therefore reach the customer directory, forge the author of
 * an internal note on any ticket, and even mint a new shared api_key -
 * defeating 'api_key_mandatory' for every other integration.
 *
 * Each test issues exactly one API call. A second call to this controller
 * within the same test process observes the FIRST call's Request (a
 * pre-existing, unrelated quirk reproducible even on an untouched endpoint
 * such as searchTicket()) - so, as with the rest of this file, one
 * request per test method keeps the assertions meaningful.
 */
class ApiV1ControllerTest extends TestCase
{
    private function makeUser(string $role, array $overrides = []): User
    {
        $faker = FakerFactory::create();
        // Faker's unique() only tracks names generated within this process, so it
        // can still collide with rows a prior, non-transactional test run left in
        // testing_db (there is no RefreshDatabase/DatabaseTransactions here, matching
        // the rest of this suite). A random suffix guarantees uniqueness regardless.
        $suffix = Str::random(8);
        $user = new User(array_merge([
            'first_name' => $faker->firstName(),
            'last_name'  => $faker->lastName(),
            'email'      => $suffix.'@example.test',
            'user_name'  => 'u_'.$suffix,
            'password'   => Hash::make(Str::random(10)),
            'active'     => 1,
            'role'       => $role,
        ], $overrides));
        $user->save();

        return $user;
    }

    private function jwtFor(User $user): string
    {
        return \JWTAuth::fromUser($user);
    }

    private function authHeader(User $user): array
    {
        return ['Authorization' => 'Bearer '.$this->jwtFor($user)];
    }

    /** Ticket owned by $owner, in $deptId (dept_id isn't mass-assignable). */
    private function makeTicket(User $owner, int $deptId): Tickets
    {
        $ticket = new Tickets([
            'ticket_number' => strtoupper(Str::random(4)).'-'.random_int(1000, 9999),
            'user_id'       => $owner->id,
            'priority_id'   => 2,
            'sla'           => 2,
            'help_topic_id' => 1,
            'status'        => 1,
            'source'        => 1,
        ]);
        $ticket->save();
        $ticket->dept_id = $deptId;
        $ticket->save();

        return $ticket;
    }

    // ── Customer directory disclosure ───────────────────────────────────────

    public function test_customer_search_is_denied_for_an_end_user()
    {
        $attacker = $this->makeUser('user');
        $victim = $this->makeUser('user', ['user_name' => 'victim_'.Str::random(6)]);

        $response = $this->getJson(
            '/api/v1/helpdesk/customers?search='.$victim->user_name,
            $this->authHeader($attacker)
        );

        $response->assertStatus(403);
        $response->assertJsonMissing(['result' => true]);
    }

    public function test_customer_directory_is_denied_for_an_end_user()
    {
        $attacker = $this->makeUser('user');

        $response = $this->getJson('/api/v1/helpdesk/customers-custom', $this->authHeader($attacker));

        $response->assertStatus(403);
    }

    public function test_customer_lookup_by_id_is_denied_for_an_end_user()
    {
        $attacker = $this->makeUser('user');
        $victim = $this->makeUser('user');

        $response = $this->getJson(
            '/api/v1/helpdesk/customer?user_id='.$victim->id,
            $this->authHeader($attacker)
        );

        $response->assertStatus(403);
    }

    /** Non-regression: an agent must keep using the directory exactly as before. */
    public function test_customer_search_still_allows_an_agent()
    {
        $agent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 1]);
        $victim = $this->makeUser('user', ['user_name' => 'victim_'.Str::random(6)]);

        $response = $this->getJson(
            '/api/v1/helpdesk/customers?search='.$victim->user_name,
            $this->authHeader($agent)
        );

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $victim->email]);
    }

    // ── Forged internal-note injection ──────────────────────────────────────

    public function test_internal_note_is_denied_for_an_end_user()
    {
        $attacker = $this->makeUser('user');
        $victim = $this->makeUser('user');
        $admin = $this->makeUser('admin');
        $ticket = $this->makeTicket($victim, 1);

        $response = $this->postJson('/api/v1/helpdesk/internal-note', [
            'user_id'   => $admin->id,
            'ticket_id' => $ticket->id,
            'body'      => 'forged note',
        ], $this->authHeader($attacker));

        $response->assertStatus(403);
        $this->assertSame(0, Ticket_Thread::where('ticket_id', $ticket->id)->where('is_internal', 1)->count());
    }

    /** An agent outside the ticket's department, and not assigned to it, is refused. */
    public function test_internal_note_is_denied_for_an_agent_outside_the_ticket_department()
    {
        $victim = $this->makeUser('user');
        $ticket = $this->makeTicket($victim, 1);
        $outsideAgent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 2]);

        $response = $this->postJson('/api/v1/helpdesk/internal-note', [
            'ticket_id' => $ticket->id,
            'body'      => 'should not be stored',
        ], $this->authHeader($outsideAgent));

        $response->assertStatus(403);
        $this->assertSame(0, Ticket_Thread::where('ticket_id', $ticket->id)->where('is_internal', 1)->count());
    }

    /**
     * The reported forgery: a caller supplying another user's id as 'user_id'
     * must not have the note attributed to that user. The author is always the
     * authenticated caller, even when they are legitimately allowed to post.
     */
    public function test_internal_note_author_is_the_authenticated_user_not_the_request_parameter()
    {
        $victim = $this->makeUser('user');
        $ticket = $this->makeTicket($victim, 1);
        $admin = $this->makeUser('admin');
        $impersonated = $this->makeUser('user');

        $response = $this->postJson('/api/v1/helpdesk/internal-note', [
            'user_id'   => $impersonated->id,
            'ticket_id' => $ticket->id,
            'body'      => 'legitimate admin note',
        ], $this->authHeader($admin));

        $response->assertStatus(200);
        $thread = Ticket_Thread::where('ticket_id', $ticket->id)->where('is_internal', 1)->first();
        $this->assertNotNull($thread);
        $this->assertSame($admin->id, $thread->user_id);
        $this->assertNotSame($impersonated->id, $thread->user_id);
    }

    /** Non-regression: an agent in the ticket's own department may still note it. */
    public function test_internal_note_still_allows_an_agent_in_the_ticket_department()
    {
        $victim = $this->makeUser('user');
        $ticket = $this->makeTicket($victim, 1);
        $agent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 1]);

        $response = $this->postJson('/api/v1/helpdesk/internal-note', [
            'ticket_id' => $ticket->id,
            'body'      => 'dept agent note',
        ], $this->authHeader($agent));

        $response->assertStatus(200);
        $thread = Ticket_Thread::where('ticket_id', $ticket->id)->where('is_internal', 1)->first();
        $this->assertNotNull($thread);
        $this->assertSame($agent->id, $thread->user_id);
    }

    // ── Shared API-key rotation ──────────────────────────────────────────────

    public function test_api_key_rotation_is_denied_for_an_end_user()
    {
        DB::table('settings_system')->where('id', 1)->update(['api_enable' => 1, 'api_key' => 'UNCHANGED-KEY']);
        $attacker = $this->makeUser('user');

        $response = $this->getJson('/api/v1/helpdesk/api_key', $this->authHeader($attacker));

        $response->assertStatus(403);
        $this->assertSame('UNCHANGED-KEY', DB::table('settings_system')->where('id', 1)->value('api_key'));
    }

    public function test_api_key_rotation_is_denied_for_a_non_admin_agent()
    {
        DB::table('settings_system')->where('id', 1)->update(['api_enable' => 1, 'api_key' => 'UNCHANGED-KEY']);
        $agent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 1]);

        $response = $this->getJson('/api/v1/helpdesk/api_key', $this->authHeader($agent));

        $response->assertStatus(403);
        $this->assertSame('UNCHANGED-KEY', DB::table('settings_system')->where('id', 1)->value('api_key'));
    }

    /** Non-regression: an admin may still rotate the shared key. */
    public function test_api_key_rotation_still_allows_an_admin()
    {
        DB::table('settings_system')->where('id', 1)->update(['api_enable' => 1, 'api_key' => 'OLD-KEY']);
        $admin = $this->makeUser('admin');

        $response = $this->getJson('/api/v1/helpdesk/api_key', $this->authHeader($admin));

        $response->assertStatus(200);
        $this->assertNotSame('OLD-KEY', DB::table('settings_system')->where('id', 1)->value('api_key'));
    }

    // ── Staff-only by default ────────────────────────────────────────────────

    /**
     * Every v1 endpoint outside ApiController::CUSTOMER_METHODS. The gate runs
     * before validation, so the ids in these requests need not exist.
     */
    public static function staffOnlyEndpoints(): array
    {
        return [
            'tickets'                  => ['GET', '/api/v1/helpdesk/tickets'],
            'ticket'                   => ['GET', '/api/v1/helpdesk/ticket?ticket_id=1'],
            'ticket-thread'            => ['GET', '/api/v1/helpdesk/ticket-thread?id=1'],
            'ticket-search'            => ['GET', '/api/v1/helpdesk/ticket-search?search=a'],
            'open'                     => ['GET', '/api/v1/helpdesk/open'],
            'unassigned'               => ['GET', '/api/v1/helpdesk/unassigned'],
            'closed'                   => ['GET', '/api/v1/helpdesk/closed'],
            'inbox'                    => ['GET', '/api/v1/helpdesk/inbox'],
            'trash'                    => ['GET', '/api/v1/helpdesk/trash'],
            'my-tickets-agent'         => ['GET', '/api/v1/helpdesk/my-tickets-agent?user_id=1'],
            'agents'                   => ['GET', '/api/v1/helpdesk/agents'],
            'teams'                    => ['GET', '/api/v1/helpdesk/teams'],
            'dependency'               => ['GET', '/api/v1/helpdesk/dependency'],
            'collaborator/search'      => ['GET', '/api/v1/helpdesk/collaborator/search?term=a'],
            'create'                   => ['POST', '/api/v1/helpdesk/create'],
            'reply'                    => ['POST', '/api/v1/helpdesk/reply'],
            'edit'                     => ['POST', '/api/v1/helpdesk/edit'],
            'delete'                   => ['POST', '/api/v1/helpdesk/delete'],
            'assign'                   => ['POST', '/api/v1/helpdesk/assign'],
            'register'                 => ['POST', '/api/v1/helpdesk/register'],
            'collaborator/create'      => ['POST', '/api/v1/helpdesk/collaborator/create'],
            'collaborator/get-ticket'  => ['POST', '/api/v1/helpdesk/collaborator/get-ticket'],
            'collaborator/remove'      => ['POST', '/api/v1/helpdesk/collaborator/remove'],
        ];
    }

    #[DataProvider('staffOnlyEndpoints')]
    public function test_staff_only_endpoint_is_denied_for_an_end_user(string $method, string $uri)
    {
        $attacker = $this->makeUser('user');

        $response = $this->json($method, $uri, [], $this->authHeader($attacker));

        $response->assertStatus(403);
    }

    /** Non-regression: the lookup lists stay open to customers. */
    public function test_help_topics_stay_available_to_an_end_user()
    {
        $customer = $this->makeUser('user');

        $response = $this->getJson('/api/v1/helpdesk/help-topic', $this->authHeader($customer));

        $response->assertStatus(200);
    }

    public function test_customer_may_list_their_own_tickets()
    {
        $customer = $this->makeUser('user');

        $response = $this->getJson('/api/v1/helpdesk/my-tickets-user?user_id='.$customer->id, $this->authHeader($customer));

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $customer->email]);
    }

    public function test_customer_may_not_list_another_customers_tickets()
    {
        $attacker = $this->makeUser('user');
        $victim = $this->makeUser('user');

        $response = $this->getJson('/api/v1/helpdesk/my-tickets-user?user_id='.$victim->id, $this->authHeader($attacker));

        $response->assertStatus(403);
    }

    // ── Per-ticket access for agents ─────────────────────────────────────────

    /** The reported internal-note rule, applied to reading: threads carry internal notes. */
    public function test_ticket_thread_is_denied_for_an_agent_outside_the_ticket_department()
    {
        $ticket = $this->makeTicket($this->makeUser('user'), 1);
        $outsideAgent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 2]);

        $response = $this->getJson('/api/v1/helpdesk/ticket-thread?id='.$ticket->id, $this->authHeader($outsideAgent));

        $response->assertStatus(403);
    }

    public function test_ticket_thread_still_allows_an_agent_in_the_ticket_department()
    {
        $ticket = $this->makeTicket($this->makeUser('user'), 1);
        $agent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 1]);

        $response = $this->getJson('/api/v1/helpdesk/ticket-thread?id='.$ticket->id, $this->authHeader($agent));

        $response->assertStatus(200);
    }

    public function test_ticket_details_are_denied_for_an_agent_outside_the_ticket_department()
    {
        $ticket = $this->makeTicket($this->makeUser('user'), 1);
        $outsideAgent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 2]);

        $response = $this->getJson('/api/v1/helpdesk/ticket?ticket_id='.$ticket->id, $this->authHeader($outsideAgent));

        $response->assertStatus(403);
    }

    public function test_ticket_list_leaves_out_other_departments_for_an_agent()
    {
        $otherDeptTicket = $this->makeTicket($this->makeUser('user'), 2);
        $agent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 1]);

        $response = $this->getJson('/api/v1/helpdesk/tickets', $this->authHeader($agent));

        $response->assertStatus(200);
        $this->assertNotContains($otherDeptTicket->id, array_column($response->json('data'), 'id'));
    }

    public function test_agent_may_not_list_another_agents_assignments()
    {
        $agent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 1]);
        $otherAgent = $this->makeUser('agent', ['agent_tzone' => 81, 'primary_dpt' => 1]);

        $response = $this->getJson('/api/v1/helpdesk/my-tickets-agent?user_id='.$otherAgent->id, $this->authHeader($agent));

        $response->assertStatus(403);
    }

    /** userremove() deletes by collaborator id alone; the id must belong to the checked ticket. */
    public function test_collaborator_on_another_ticket_cannot_be_removed()
    {
        $admin = $this->makeUser('admin');
        $ticketA = $this->makeTicket($this->makeUser('user'), 1);
        $ticketB = $this->makeTicket($this->makeUser('user'), 1);
        $collaboratorId = DB::table('ticket_collaborator')->insertGetId([
            'isactive'  => 1,
            'ticket_id' => $ticketB->id,
            'user_id'   => $this->makeUser('user')->id,
            'role'      => 'ccc',
        ]);

        $response = $this->postJson('/api/v1/helpdesk/collaborator/remove', [
            'ticketid' => $ticketA->id,
            'email'    => 'any@example.test',
            'data1'    => $collaboratorId,
        ], $this->authHeader($admin));

        $response->assertStatus(403);
        $this->assertTrue(DB::table('ticket_collaborator')->where('id', $collaboratorId)->exists());
    }
}
