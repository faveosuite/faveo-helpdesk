<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_tooltip()
    {
        $faker = FakerFactory::create();

        //Create User -> Agent

        $str = Str::random(10);
        $password = Hash::make($str);
        $email = $faker->unique()->email();
        $user = new User([
            'first_name'   => $faker->firstName(),
            'last_name'    => $faker->lastName(),
            'email'        => $email,
            'user_name'    => $faker->unique()->userName(),
            'password'     => $password,
            'active'       => 1,
            'role'         => 'agent',
            'agent_tzone'  => 81,
            'primary_dpt'  => 1,
            'assign_group' => 1,
        ]);
        $user->save();

        // Check if data is inserted
        $this->assertDatabaseHas('users', ['email'=>$email]);

        // Authenticate as the created user
        $this->actingAs($user);

        $this->assertAuthenticated();

        // Define the dashboard route name
        $dashboardRouteName = 'dashboard';

        // Generate the dashboard route URL
        $dashboardUrl = route($dashboardRouteName);

        // Simulate a GET request to the dashboard route
        $dashboardResponse = $this->get($dashboardUrl);

        // Assert that the response status code is 200 (OK)
        $dashboardResponse->assertStatus(200);

        // Create a ticket for testing.

        $ticket = new Tickets(
            [
                'ticket_number' => 'TEST-0000-000'.$faker->randomDigit(),
                'user_id'       => $user->id,
                'priority_id'   => 2,
                'sla'           => 2,
                'help_topic_id' => 1,
                'status'        => 1,
                'source'        => 1,
            ]
        );
        $ticket->save();
        $ticket->dept_id = 1;
        $ticket->save();

        //Create Ticket_thread for Testing

        $ticket_thread = new Ticket_Thread(
            [
                'ticket_id' => $ticket->id,
                'user_id'   => $user->id,
                'poster'    => 'client',
                'title'     => 'TestCase2',
                'body'      => 'Testing2',
            ]
        );

        $ticket_thread->save();

        // Make a GET request to the getTooltip
        $response = $this->get(route('ticket.tooltip', ['ticket_id' => $ticket->id]));

        // Assert that the response status is 200 (OK).
        $response->assertStatus(200);
    }

    //Testing Reply Alert and Last Activity filed
    public function test_reply()
    {
        $faker = FakerFactory::create();

        // Build this test's own agent and ticket. It used to pick up
        // User::latest() / Tickets::latest(), i.e. whatever the previously-run test
        // happened to leave behind, which made the result depend on test order.
        $user    = $this->actingAsAgent();
        $tickets = $this->makeTicket($user);

        $this->assertAuthenticated();

        // Define the route URL with the Ticket ID

        $url = route('ticket.thread', ['id' => $tickets->id]);

        $response2 = $this->get($url);

        // Assert that the response status is 200 (OK).
        $response2->assertStatus(200);

        // Create fake data for the reply

        $replyData = [
            'ticket_ID'     => $tickets->id,
            'reply_content' => $faker->paragraph,
            'created_at'    => date_default_timezone_set('UTC'),
            'updated_at'    => date_default_timezone_set('UTC'),
        ];

        // Make a POST request to the route with the reply data
        $response3 = $this->post(route('ticket.reply', ['id' => $tickets->id]), $replyData);
        $response3->assertStatus(200);
        $response3->assertSee(Lang::get('lang.you_have_successfully_replied_to_your_ticket'));
    }

    public function test_user_change_the_status()
    {
        $faker = FakerFactory::create();

        //Create User -> User

        $str = Str::random(10);
        $password = Hash::make($str);
        $email = $faker->unique()->email();
        $user = new User([
            'first_name'   => $faker->firstName(),
            'last_name'    => $faker->lastName(),
            'email'        => $email,
            'user_name'    => $faker->unique()->userName(),
            'password'     => $password,
            'active'       => 1,
            // 'select_all' is routed behind the 'role.agent' middleware
            // (routes/web.php:302), so a 'user' role is redirected before the
            // controller runs and no 'success' flash is ever set.
            'role'         => 'agent',
        ]);
        $user->save();

        // Authenticate as the created user

        $this->actingAs($user);

        $ticket = new Tickets(
            [
                'ticket_number' => 'AAAA-0000-0001',
                'user_id'       => $user->id,
                'priority_id'   => 2,
                'sla'           => 2,
                'help_topic_id' => 1,
                'status'        => 1,
                'source'        => 1,
            ]
        );

        $ticket->save();
        $ticket->dept_id = 1;
        $ticket->save();

        $ticket_thread = new Ticket_Thread(
            [
                'ticket_id' => $ticket->id,
                'user_id'   => $user->id,
                'poster'    => 'client',
                'title'     => 'TestCase',
                'body'      => 'Testing',
            ]
        );
        $ticket_thread->save();

        $mytickets = $this->get(route('ticket2'));
        $mytickets->assertStatus(200);

        $response = $this->post(route('select_all'), [
            'select_all' => [$ticket->id],
            'submit'     => 'Open',

        ]);

        // Assert that the response status code indicates success
        $response->assertStatus(302); // Adjust this as needed

        // Assert that the ticket's status has been updated to open

        $response->assertSessionHas('success', Lang::get('lang.tickets_have_been_opened'));
        $response = $this->post(route('select_all'), [
            'select_all' => [$ticket->id],
            'submit'     => 'Close',
        ]);
        $response->assertStatus(302); // Adjust this as needed
        $this->assertEquals(3, $ticket->fresh()->status); // Adjust this as needed
        $response->assertSessionHas('success', Lang::get('lang.tickets_have_been_closed'));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Create and authenticate an agent. */
    private function actingAsAgent(array $overrides = []): User
    {
        $faker = FakerFactory::create();
        $user = new User(array_merge([
            'first_name'  => $faker->firstName(),
            'last_name'   => $faker->lastName(),
            'email'       => $faker->unique()->email(),
            'user_name'   => $faker->unique()->userName(),
            'password'    => Hash::make(Str::random(10)),
            'active'      => 1,
            'role'        => 'agent',
            'agent_tzone' => 81,
            // authorizeTicketAccess() does Department::where('id', primary_dpt)->first()
            // and then dereferences the result without a null check, so an agent with
            // no primary department triggers a fatal error on delete/ban/resolve.
            'primary_dpt' => 1,
            // The agent layout looks the user's group up and reads
            // can_create_ticket off it; agents always belong to a group in a
            // real install, so the fixture mirrors that.
            'assign_group' => 1,
        ], $overrides));
        $user->save();
        $this->actingAs($user);

        return $user;
    }

    /** Create a ticket owned by $user, with one thread. */
    private function makeTicket(User $user, array $overrides = []): Tickets
    {
        $ticket = new Tickets(array_merge([
            'ticket_number' => strtoupper(Str::random(4)).'-'.random_int(1000, 9999),
            'user_id'       => $user->id,
            'priority_id'   => 2,
            'sla'           => 2,
            'help_topic_id' => 1,
            'status'        => 1,
            'source'        => 1,
        ], $overrides));
        $ticket->save();

        // dept_id is not in the model's $fillable, so it has to be assigned after
        // the mass-assignment save. authorizeTicketAccess() compares it against the
        // agent's primary department, so without it every agent action is refused.
        $ticket->dept_id = $overrides['dept_id'] ?? 1;
        $ticket->save();

        (new Ticket_Thread([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'poster'    => 'client',
            'title'     => 'Test subject',
            'body'      => 'Test body',
        ]))->save();

        return $ticket;
    }


    /**
     * Ticket creation sends a confirmation mail, and PhpMailController throws
     * 'system-email-not-configured' unless an emails row with sending_status = 1
     * exists and settings_email.sys_email points at it. A fresh testing_db has
     * neither, so every creation path dies before a ticket is written.
     */
    private function configureOutgoingMail(): int
    {
        Mail::fake();

        $existing = DB::table('emails')->where('sending_status', 1)->first();
        if ($existing) {
            DB::table('settings_email')->where('id', 1)->update(['sys_email' => $existing->id]);

            return (int) $existing->id;
        }

        $id = DB::table('emails')->insertGetId([
            'email_address'        => 'support@example.com',
            'email_name'           => 'Support',
            'password'             => '',
            'fetching_host'        => '',
            'fetching_port'        => 0,
            'fetching_protocol'    => '',
            'fetching_encryption'  => '',
            'mailbox_protocol'     => '',
            'imap_config'          => '',
            'folder'               => '',
            'sending_host'         => 'localhost',
            'sending_port'         => 25,
            'sending_protocol'     => 'smtp',
            'sending_encryption'   => '',
            'smtp_validate'        => 0,
            'smtp_authentication'  => 0,
            'internal_notes'       => 0,
            'auto_response'        => 0,
            'fetching_status'      => 0,
            'move_to_folder'       => 0,
            'delete_email'         => 0,
            'do_nothing'           => 0,
            'sending_status'       => 1,
            'authentication'       => 0,
            'header_spoofing'      => 0,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        DB::table('settings_email')->where('id', 1)->update(['sys_email' => $id]);

        return $id;
    }

    // ── Ticket list views ────────────────────────────────────────────────────

    public static function ticketListRouteProvider(): array
    {
        return [
            'inbox'      => ['inbox.ticket'],
            'open'       => ['open.ticket'],
            'answered'   => ['answered.ticket'],
            'myticket'   => ['myticket.ticket'],
            'overdue'    => ['overdue.ticket'],
            'closed'     => ['closed.ticket'],
            'assigned'   => ['assigned.ticket'],
            'duetoday'   => ['ticket.duetoday'],
            'trash'      => ['get-trash'],
            'unassigned' => ['unassigned'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('ticketListRouteProvider')]
    public function test_ticket_list_views_render(string $routeName)
    {
        $agent = $this->actingAsAgent();
        $this->makeTicket($agent);

        $this->get(route($routeName))->assertStatus(200);
    }

    /**
     * `tickets-view` sits behind the TicketViewURL middleware, which redirects any
     * request without show[]/departments[] query parameters. Supplying them is the
     * only way to actually reach getTicketsView().
     */
    public function test_tickets_view_renders_with_required_query_parameters()
    {
        $agent = $this->actingAsAgent();
        $this->makeTicket($agent);

        $this->get(route('tickets-view'))->assertStatus(302);

        $this->get(route('tickets-view', ['show' => ['inbox'], 'departments' => ['All']]))
            ->assertStatus(200);
    }

    public function test_followup_ticket_list_redirects_to_the_unified_ticket_page()
    {
        $this->actingAsAgent();

        // followupTicketList() has no view of its own; follow-ups live on the
        // unified ticket page behind show[]=followup.
        $this->get(route('followup.ticket'))
            ->assertStatus(302)
            ->assertRedirect('tickets?show%5B%5D=followup&departments%5B%5D=All')
            ->assertSessionMissing('fails');
    }

    /**
     * The ticket "datatable" routes — /ticket/get-inbox, /ticket/get-open,
     * /ticket/get-answered, /ticket/get-myticket, /ticket/get-assigned,
     * /ticket/get-closed, /ticket/trash, /ticket/unassigned, /ticket/get-overdue,
     * /duetoday/list/ticket, /ticket/get-approval and /ticket/get-followup — are
     * all registered against TicketController methods that do not exist
     * (get_inbox, get_open, …). Every one of them raises a BadMethodCallException.
     *
     * They are deliberately NOT covered by tests: there is nothing to assert
     * beyond "this route is dead". Either the methods should be restored or the
     * routes removed; see the note in the review notes.
     */

    // ── select_all: the branches the original test never reached ─────────────

    public function test_select_all_resolves_tickets()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);

        $this->post(route('select_all'), ['select_all' => [$ticket->id], 'submit' => 'Resolve'])
            ->assertStatus(302);

        $this->assertEquals(2, $ticket->fresh()->status, 'Resolve should set status to 2');
    }

    public function test_select_all_moves_tickets_to_trash()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);

        $this->post(route('select_all'), ['select_all' => [$ticket->id], 'submit' => 'Delete'])
            ->assertStatus(302)
            ->assertSessionHas('success', Lang::get('lang.moved_to_trash'));

        $this->assertEquals(5, $ticket->fresh()->status, 'Delete should move the ticket to trash (status 5)');
    }

    public function test_select_all_deletes_tickets_permanently()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);
        $id     = $ticket->id;

        $this->post(route('select_all'), ['select_all' => [$id], 'submit' => 'Delete forever'])
            ->assertStatus(302);

        $this->assertDatabaseMissing('tickets', ['id' => $id]);
        $this->assertDatabaseMissing('ticket_thread', ['ticket_id' => $id]);
    }

    public function test_select_all_without_a_selection_reports_failure()
    {
        $this->actingAsAgent();

        $this->post(route('select_all'), ['submit' => 'Open'])
            ->assertStatus(302)
            ->assertSessionHas('fails', 'None Selected!');
    }

    // ── Single-ticket status actions ─────────────────────────────────────────

    public function test_close_route_sets_status_to_closed()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);

        $this->post(route('ticket.close', ['id' => $ticket->id]))->assertStatus(200);
        $this->assertEquals(3, $ticket->fresh()->status);
    }

    public function test_resolve_route_sets_status_to_resolved()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);

        $this->post(route('ticket.resolve', ['id' => $ticket->id]))->assertStatus(200);
        $this->assertEquals(2, $ticket->fresh()->status);
    }

    public function test_open_route_reopens_a_closed_ticket()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent, ['status' => 3]);

        $this->post(route('ticket.open', ['id' => $ticket->id]))->assertStatus(200);
        $this->assertEquals(1, $ticket->fresh()->status);
    }

    public function test_delete_route_trashes_then_removes_the_ticket()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);
        $id     = $ticket->id;

        // First call trashes it (status 5, is_deleted 1)...
        $this->post(route('ticket.delete', ['id' => $id]))->assertStatus(200);
        $this->assertEquals(5, $ticket->fresh()->status);
        $this->assertEquals(1, $ticket->fresh()->is_deleted);

        // ...a second call on an already-trashed ticket deletes it for good.
        $this->post(route('ticket.delete', ['id' => $id]))->assertStatus(200);
        $this->assertDatabaseMissing('tickets', ['id' => $id]);
    }

    public function test_status_actions_are_refused_for_a_ticket_outside_the_agents_department()
    {
        $agent  = $this->actingAsAgent(['primary_dpt' => 1]);
        $ticket = $this->makeTicket($agent, ['dept_id' => 2]);

        $this->post(route('ticket.delete', ['id' => $ticket->id]))->assertStatus(403);
    }

    // ── Thread, print, surrender, lock ───────────────────────────────────────

    public function test_thread_page_renders_for_an_agent()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);

        $this->get(route('ticket.thread', ['id' => $ticket->id]))->assertStatus(200);
    }

    /**
     * ticket_print() ends with PdfFacade::load(...)->show(...), which streams the
     * document through PHP's output buffer instead of returning it as a response
     * body. That is fine in a browser, but under PHPUnit it prints the raw PDF
     * into the console output, so the request is wrapped in an output buffer here
     * and the captured bytes are asserted on instead.
     */
    public function test_ticket_print_streams_a_pdf()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);

        ob_start();
        try {
            $response = $this->get(route('ticket.print', ['id' => $ticket->id]));
        } finally {
            $pdf = ob_get_clean();
        }

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $pdf, 'ticket_print should emit a PDF document');
        $this->assertStringContainsString($ticket->ticket_number, $pdf);
    }

    public function test_ticket_print_is_refused_for_a_ticket_outside_the_agents_department()
    {
        $owner  = $this->actingAsAgent();
        $ticket = $this->makeTicket($owner, ['dept_id' => 2]);

        $this->actingAsAgent(['primary_dpt' => 1]);

        ob_start();
        try {
            $response = $this->get(route('ticket.print', ['id' => $ticket->id]));
        } finally {
            ob_get_clean();
        }

        $response->assertStatus(403);
    }

    public function test_surrender_releases_the_ticket_assignment()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);
        $ticket->assigned_to = $agent->id;
        $ticket->save();

        // surrender() returns the literal 1, so the response is a 200 body of "1",
        // not a redirect.
        $this->get(route('ticket.surrender', ['id' => $ticket->id]))->assertStatus(200);

        $this->assertNull($ticket->fresh()->assigned_to, 'surrender() should clear assigned_to');
        $this->assertDatabaseHas('ticket_thread', [
            'ticket_id'   => $ticket->id,
            'is_internal' => 1,
        ]);
    }

    public function test_check_lock_returns_a_response_for_an_unlocked_ticket()
    {
        $agent  = $this->actingAsAgent();
        $ticket = $this->makeTicket($agent);

        // NOTE: the route name 'lock' is registered twice (GET check/lock/{id} and
        // POST lock), so route('lock') resolves to the POST one. Use the URL.
        $this->get('/check/lock/'.$ticket->id)->assertStatus(200);
    }

    // ── Ticket creation ──────────────────────────────────────────────────────

    public function test_new_ticket_form_renders()
    {
        $this->actingAsAgent();

        $this->get(route('newticket'))->assertStatus(200);
    }

    public function test_post_newticket_creates_a_ticket_and_its_requester()
    {
        $this->actingAsAgent();
        $this->configureOutgoingMail();
        $faker = FakerFactory::create();
        $email = $faker->unique()->email();

        $response = $this->post(route('post.newticket'), [
            'email'      => $email,
            'first_name' => $faker->firstName(),
            'last_name'  => $faker->lastName(),
            'helptopic'  => 1,
            'sla'        => 1,
            'priority'   => 2,
            'dept'       => 1,
            'subject'    => 'Printer will not print',
            'body'       => 'The office printer refuses to print anything at all.',
            // A phone is mandatory in practice: users.phone_number is NOT NULL and
            // create_user() passes the submitted value straight through. See
            // test_post_newticket_fails_when_no_phone_number_is_supplied().
            'code'       => 91,
            'phone'      => '9876543210',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $this->assertNull(session('fails'), 'post_newticket failed: '.strip_tags((string) session('fails')));

        // The requester is created from the submitted email...
        $this->assertDatabaseHas('users', ['email' => $email]);

        // ...and a ticket plus its opening thread exist for them.
        $user   = User::where('email', $email)->first();
        $ticket = Tickets::where('user_id', $user->id)->latest()->first();

        $this->assertNotNull($ticket, 'post_newticket should create a ticket for the requester');
        $this->assertDatabaseHas('ticket_thread', [
            'ticket_id' => $ticket->id,
            'title'     => 'Printer will not print',
        ]);
    }

    public static function invalidTicketPayloadProvider(): array
    {
        // A subject that no other test uses, so the assertDatabaseMissing() below
        // cannot be tripped by a ticket some earlier test legitimately created.
        $valid = [
            'email'      => 'requester@example.com',
            'first_name' => 'Alex',
            'helptopic'  => 1,
            'sla'        => 1,
            'priority'   => 2,
            'subject'    => 'Rejected payload probe '.uniqid(),
            'body'       => 'The office printer refuses to print anything at all.',
        ];

        return [
            'missing email'     => [array_merge($valid, ['email' => '']), 'email'],
            'malformed email'   => [array_merge($valid, ['email' => 'not-an-email']), 'email'],
            'missing firstname' => [array_merge($valid, ['first_name' => '']), 'first_name'],
            'short firstname'   => [array_merge($valid, ['first_name' => 'Al']), 'first_name'],
            'missing helptopic' => [array_merge($valid, ['helptopic' => '']), 'helptopic'],
            'missing sla'       => [array_merge($valid, ['sla' => '']), 'sla'],
            'missing priority'  => [array_merge($valid, ['priority' => '']), 'priority'],
            'short subject'     => [array_merge($valid, ['subject' => 'abc']), 'subject'],
            'short body'        => [array_merge($valid, ['body' => 'too short']), 'body'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTicketPayloadProvider')]
    public function test_post_newticket_rejects_invalid_payloads(array $payload, string $expectedField)
    {
        $this->actingAsAgent();

        $this->post(route('post.newticket'), $payload)
            ->assertStatus(302)
            ->assertSessionHasErrors($expectedField);

        $this->assertDatabaseMissing('ticket_thread', ['title' => $payload['subject']]);
    }

    /**
     * The new-ticket form does not require a phone number. users.phone_number is
     * NOT NULL, so create_user() now coalesces a missing phone to an empty string
     * rather than letting a null reach the insert.
     */
    public function test_post_newticket_succeeds_without_a_phone_number()
    {
        $this->actingAsAgent();
        $this->configureOutgoingMail();
        $faker = FakerFactory::create();
        $email = $faker->unique()->email();

        $response = $this->post(route('post.newticket'), [
            'email'      => $email,
            'first_name' => $faker->firstName(),
            'last_name'  => $faker->lastName(),
            'helptopic'  => 1,
            'sla'        => 1,
            'priority'   => 2,
            'subject'    => 'No phone supplied',
            'body'       => 'This request deliberately omits the phone number.',
        ]);

        $response->assertStatus(302);
        $this->assertNull(session('fails'), 'Creation without a phone should not fail: '.strip_tags((string) session('fails')));

        $this->assertDatabaseHas('users', ['email' => $email, 'phone_number' => '']);

        $user = User::where('email', $email)->first();
        $this->assertNotNull(Tickets::where('user_id', $user->id)->first());
    }

    /**
     * authorizeTicketAccess() looks the agent's primary department up and compares
     * it with the ticket's. When the agent has no primary department the lookup
     * returns null, and dereferencing it used to raise a fatal error — a 500 on a
     * request that should simply be refused. The access decision must still be
     * "no", but cleanly.
     */
    public function test_agent_without_a_primary_department_is_refused_not_crashed()
    {
        $owner  = $this->actingAsAgent();
        $ticket = $this->makeTicket($owner, ['dept_id' => 1]);

        $this->actingAsAgent(['primary_dpt' => null]);

        $this->post(route('ticket.delete', ['id' => $ticket->id]))->assertStatus(403);

        // The ticket must be untouched by the refused request.
        $this->assertEquals(1, $ticket->fresh()->status);
        $this->assertEquals(0, $ticket->fresh()->is_deleted);
    }

    /**
     * The same agent, once the ticket is assigned to them directly, is allowed
     * through even without a primary department — the second half of the
     * authorizeTicketAccess() condition.
     */
    public function test_agent_without_a_department_may_act_on_a_ticket_assigned_to_them()
    {
        $owner  = $this->actingAsAgent();
        $ticket = $this->makeTicket($owner, ['dept_id' => 1]);

        $agent = $this->actingAsAgent(['primary_dpt' => null]);
        $ticket->assigned_to = $agent->id;
        $ticket->save();

        $this->post(route('ticket.delete', ['id' => $ticket->id]))->assertStatus(200);
        $this->assertEquals(5, $ticket->fresh()->status);
    }

    // ── Department ticket views ──────────────────────────────────────────────
    // These take the department NAME in the URL, and an agent may only view their
    // own primary department.

    public static function departmentViewRouteProvider(): array
    {
        return [
            'open'       => ['dept.open.ticket'],
            'closed'     => ['dept.closed.ticket'],
            'inprogress' => ['dept.inprogress.ticket'],
        ];
    }

    public static function departmentRedirectProvider(): array
    {
        return [
            'open'       => ['dept.open.ticket', 'tickets/Support/open'],
            'closed'     => ['dept.closed.ticket', 'tickets/Support/closed'],
            'inprogress' => ['dept.inprogress.ticket', 'tickets/Support/assigned'],
        ];
    }

    /**
     * The legacy '{dept}/open|closed|assigned' routes cannot render the shared
     * dept-ticket view: it reads the department and status from URL segments 1 and
     * 2, which only exist on /tickets/{dept}/{status}. They redirect there instead.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('departmentRedirectProvider')]
    public function test_department_views_redirect_to_the_canonical_url(string $routeName, string $target)
    {
        $this->actingAsAgent(['primary_dpt' => 1]);

        $this->get(route($routeName, ['dept' => 'Support']))
            ->assertStatus(302)
            ->assertRedirect($target)
            ->assertSessionMissing('fails');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('departmentViewRouteProvider')]
    public function test_department_views_are_refused_for_another_department(string $routeName)
    {
        $this->actingAsAgent(['primary_dpt' => 1]);

        $this->get(route($routeName, ['dept' => 'Sales']))
            ->assertStatus(302)
            ->assertSessionHas('fails', 'Unauthorised!');
    }

    public function test_dept_ticket_view_renders_for_the_agents_own_department()
    {
        $this->actingAsAgent(['primary_dpt' => 1]);

        $this->get(route('dept.ticket', ['dept' => 'Support', 'status' => 'open']))
            ->assertStatus(200);
    }

    public function test_dept_ticket_view_is_refused_for_another_department()
    {
        $this->actingAsAgent(['primary_dpt' => 1]);

        $this->get(route('dept.ticket', ['dept' => 'Sales', 'status' => 'open']))
            ->assertStatus(302)
            ->assertSessionHas('fails', Lang::get('lang.unauthorized_access'));
    }

    public function test_autofill_view_renders_for_a_search_term()
    {
        $this->actingAsAgent();

        // getautocomplete.blade.php reads Request::get('term'); without it the view
        // raises "Undefined array key \"term\"".
        $this->get(route('post.newticket.autofill', ['term' => 'ali']))->assertStatus(200);
    }

    /**
     * authorizeTicketAccess() lets admins through regardless of department. This
     * covers the branch that the agent-focused tests never reach.
     */
    public function test_admin_may_act_on_a_ticket_in_any_department()
    {
        $owner  = $this->actingAsAgent(['primary_dpt' => 1]);
        $ticket = $this->makeTicket($owner, ['dept_id' => 2]);

        $this->actingAsAgent(['role' => 'admin', 'primary_dpt' => 1]);

        $this->post(route('ticket.delete', ['id' => $ticket->id]))->assertStatus(200);
        $this->assertEquals(5, $ticket->fresh()->status);
    }
}
