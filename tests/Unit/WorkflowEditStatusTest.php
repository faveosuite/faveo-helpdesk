<?php

namespace Tests\Unit;

use App\Model\helpdesk\Workflow\WorkflowAction;
use App\Model\helpdesk\Workflow\WorkflowName;
use App\Model\helpdesk\Workflow\WorkflowRules;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The workflow list said "Active" while the edit page showed "Inactive": the browser restored an
 * unsaved radio choice on reload/back. The edit form must select the saved status and opt out of that.
 */
class WorkflowEditStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_edit_page_selects_saved_status()
    {
        foreach ([1 => ['1', '0'], 0 => ['0', '1']] as $saved => [$checked, $unchecked]) {
            $html = $this->editPage($saved);

            $this->assertStringContainsString('checked', $this->statusRadio($html, $checked), "status $saved: radio $checked should be selected");
            $this->assertStringNotContainsString('checked', $this->statusRadio($html, $unchecked), "status $saved: radio $unchecked should not be selected");
        }
    }

    public function test_list_shows_status_as_active_or_inactive_badge()
    {
        $this->editPage(1);
        $inactive = WorkflowName::create(['name' => 'wf off '.Str::random(6), 'status' => 0, 'order' => 1, 'target' => 'A-3', 'internal_note' => '']);
        $active = WorkflowName::where('status', '=', 1)->latest('id')->first();

        $rows = collect($this->getJson('workflow-list')->assertOk()->json('data'))->keyBy('name');

        $this->assertSame('<button class="btn btn-success btn-xs">Active</button>', $rows[$active->name]['status']);
        $this->assertSame('<button class="btn btn-danger btn-xs">Inactive</button>', $rows[$inactive->name]['status']);
    }

    public function test_create_page_defaults_to_active_and_store_saves_choice()
    {
        $this->editPage(1);
        $admin = User::where('role', '=', 'admin')->latest('id')->first();
        $_SERVER['REQUEST_URI'] = '/workflow/create';
        $html = $this->actingAs($admin)->get('workflow/create')->assertOk()->getContent();
        $this->assertStringContainsString('checked', $this->statusRadio($html, '1'), 'new workflow should default to Active');
        $this->assertStringNotContainsString('checked', $this->statusRadio($html, '0'));

        foreach (['1', '0'] as $status) {
            $name = 'wf new '.Str::random(6);
            $this->actingAs($admin)->post('workflow/store', [
                'name' => $name, 'status' => $status, 'execution_order' => 1, 'target_channel' => 'A-3', 'internal_note' => '',
                'rule'   => [['a' => 'subject', 'b' => 'equal', 'c' => 'x']],
                'action' => [['a' => 'status', 'b' => '3']],
            ])->assertSessionHas('success');
            $this->assertSame((int) $status, (int) WorkflowName::where('name', '=', $name)->value('status'));
        }
    }

    public function test_edit_form_disables_browser_form_restore()
    {
        $this->assertMatchesRegularExpression('/<form[^>]*workflow\/update[^>]*autocomplete="off"/', $this->editPage(1));
    }

    private function editPage($status)
    {
        $s = Str::random(6);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
        $workflow = WorkflowName::create(['name' => 'wf '.$s, 'status' => $status, 'order' => 1, 'target' => 'A-3', 'internal_note' => '']);
        WorkflowRules::create(['workflow_id' => $workflow->id, 'matching_criteria' => '', 'matching_scenario' => 'subject', 'matching_relation' => 'equal', 'matching_value' => 'x']);
        WorkflowAction::create(['workflow_id' => $workflow->id, 'condition' => 'status', 'action' => '3']);
        $_SERVER['REQUEST_URI'] = '/workflow/edit/'.$workflow->id;

        return $this->actingAs($admin)->get('workflow/edit/'.$workflow->id)->assertOk()->getContent();
    }

    private function statusRadio($html, $value)
    {
        preg_match('/<input[^>]*name="status"[^>]*value="'.$value.'"[^>]*>/', $html, $m);
        $this->assertNotEmpty($m, "status radio $value not found");

        return $m[0];
    }
}
