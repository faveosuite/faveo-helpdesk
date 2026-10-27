<?php

namespace Tests\Unit;

use App\Http\Controllers\Agent\helpdesk\TicketController;
use App\Http\Controllers\Agent\helpdesk\TicketWorkflowController;
use App\Http\Requests\helpdesk\CreateTicketRequest;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Utility\CountryCode;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Workflow\WorkflowAction;
use App\Model\helpdesk\Workflow\WorkflowName;
use App\Model\helpdesk\Workflow\WorkflowRules;
use App\User;
use Database\Seeders\v_2_0_4_RC_1\DatabaseSeeder as ApiSourceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tickets created from the agent panel used to skip the workflow engine entirely, so no
 * workflow (not even one targeted at "Any") was ever applied to them.
 */
class AgentPanelWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // testing-setup only runs the v_2_0_0 seeder, the API source comes from the v_2_0_4_RC_1 one
        $this->seed(ApiSourceSeeder::class);
    }

    public function test_api_calls_workflow_changes_status_of_api_ticket()
    {
        $subject = 'Api wf '.Str::random(8);
        $this->makeWorkflow('A-4', $subject, 'status', 3);

        $s = Str::random(8);
        $this->actingAs($this->makeAdmin($s));
        $response = app(TicketController::class)->post_newticket(new CreateTicketRequest([
            'email' => 'c'.$s.'@example.test', 'first_name' => 'Customer', 'last_name' => 'Person',
            'helptopic' => 1, 'sla' => 1, 'priority' => 1, 'subject' => $subject, 'body' => 'Workflow test body text',
        ]), new CountryCode(), true);

        $this->assertArrayHasKey('ticket_id', $response, json_encode($response));
        $ticket = Tickets::find($response['ticket_id']);
        $this->assertSame(3, (int) $ticket->status);
        $this->assertSame((int) Ticket_source::where('name', '=', 'api')->value('id'), (int) $ticket->source);
    }

    public function test_agent_panel_workflow_changes_status_of_agent_ticket()
    {
        $subject = 'Agent wf '.Str::random(8);
        $this->makeWorkflow('A-3', $subject, 'status', 3);

        $this->postTicket($subject);

        $this->assertSame(3, (int) $this->ticketWithSubject($subject)->status);
    }

    public function test_any_workflow_applies_to_agent_ticket()
    {
        $subject = 'Any wf '.Str::random(8);
        $this->makeWorkflow('A-0', $subject, 'status', 2);

        $this->postTicket($subject);

        $this->assertSame(2, (int) $this->ticketWithSubject($subject)->status);
    }

    public function test_web_forms_workflow_does_not_apply_to_agent_ticket()
    {
        $subject = 'Web wf '.Str::random(8);
        $this->makeWorkflow('A-1', $subject, 'status', 3);

        $this->postTicket($subject);

        $this->assertSame(1, (int) $this->ticketWithSubject($subject)->status);
    }

    public function test_reject_workflow_blocks_agent_ticket_with_message()
    {
        $subject = 'Reject wf '.Str::random(8);
        $this->makeWorkflow('A-3', $subject, 'reject', '');

        $this->postTicket($subject);

        $this->assertNull($this->ticketWithSubject($subject));
        $this->assertSame(trans('lang.ticket-rejected-by-workflow'), session('fails'));
    }

    public function test_sources_map_to_target_channels()
    {
        $controller = app(TicketWorkflowController::class);
        foreach (['web' => 'A-1', 'email' => 'A-2', 'agent' => 'A-3', 'api' => 'A-4'] as $name => $channel) {
            $source = Ticket_source::where('name', '=', $name)->first();
            $this->assertNotNull($source, "ticket source '$name' is missing");
            $this->assertSame($channel, $controller->channelForSource($source->id));
        }
    }

    private function makeWorkflow($target, $subject, $condition, $action)
    {
        $workflow = WorkflowName::create(['name' => 'wf '.Str::random(8), 'status' => 1, 'order' => 1, 'target' => $target, 'internal_note' => '']);
        WorkflowRules::create(['workflow_id' => $workflow->id, 'matching_criteria' => '', 'matching_scenario' => 'subject', 'matching_relation' => 'equal', 'matching_value' => $subject]);
        WorkflowAction::create(['workflow_id' => $workflow->id, 'condition' => $condition, 'action' => $action]);
    }

    private function makeAdmin($s)
    {
        return User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1]);
    }

    private function postTicket($subject)
    {
        $s = Str::random(8);
        $_SERVER['REQUEST_URI'] = '/newticket';

        return $this->actingAs($this->makeAdmin($s))->post('newticket/post', [
            'email' => 'c'.$s.'@example.test', 'first_name' => 'Customer', 'last_name' => 'Person',
            'helptopic' => 1, 'sla' => 1, 'priority' => 1, 'subject' => $subject, 'body' => 'Workflow test body text',
        ]);
    }

    private function ticketWithSubject($subject)
    {
        $thread = Ticket_Thread::where('title', '=', $subject)->first();

        return $thread ? Tickets::find($thread->ticket_id) : null;
    }
}
