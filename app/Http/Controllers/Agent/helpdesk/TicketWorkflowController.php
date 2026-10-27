<?php

namespace App\Http\Controllers\Agent\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// models
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Agent\Teams;
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Ticket\Ticket_Status;
use App\Model\helpdesk\Workflow\WorkflowAction;
use App\Model\helpdesk\Workflow\WorkflowName;
use App\Model\helpdesk\Workflow\WorkflowRules;
use App\User;

/**
 * TicketWorkflowController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class TicketWorkflowController extends Controller
{
    /**
     * constructor
     * Create a new controller instance.
     *
     * @param type TicketController $TicketController
     */
    public function __construct(TicketController $TicketController)
    {
        $this->TicketController = $TicketController;
    }

    /**
     * This is the core function from where the workflow is applied.
     *
     * @return type response
     */
    public function workflow($fromaddress, $fromname, $subject, $body, $phone, $phonecode, $mobile_number, $helptopic, $sla, $priority, $source, $collaborator, $dept, $assign, $team_assign, $ticket_status, $form_data, $auto_response, $duedate = null)
    {
        // the agent panel joins first and last name with '%$%', match rules against the readable name
        $contact_details = ['email' => $fromaddress, 'email_name' => str_replace('%$%', ' ', $fromname), 'subject' => $subject, 'message' => $body];
        $ticket_settings_details = ['help_topic' => $helptopic, 'sla' => $sla, 'priority' => $priority, 'source' => $source, 'dept' => $dept, 'assign' => $assign, 'team' => $team_assign, 'status' => $ticket_status, 'reject' => false];
        // workflows targeted at "Any" run first, then the ones targeted at the channel the ticket came from, each in ascending execution order
        $targets = ['A-0'];
        if ($channel = $this->channelForSource($source)) {
            $targets[] = $channel;
        }
        foreach ($targets as $target) {
            $workflows = WorkflowName::where('target', '=', $target)->where('status', '=', '1')->orderBy('order', 'asc')->get();
            foreach ($workflows as $workflow) {
                $worklfow_rules = WorkflowRules::where('workflow_id', '=', $workflow->id)->get();
                foreach ($worklfow_rules as $worklfow_rule) {
                    // matching_scenario is one of email, email_name, subject or message
                    if (!array_key_exists($worklfow_rule->matching_scenario, $contact_details)) {
                        continue;
                    }
                    if ($this->checkRuleCondition($contact_details[$worklfow_rule->matching_scenario], $worklfow_rule->matching_relation, $worklfow_rule->matching_value) == true) {
                        $ticket_settings_details = $this->applyActionCondition($workflow->id, $ticket_settings_details);
                    }
                }
            }
        }

        //dd($form_data);
        if ($ticket_settings_details['reject'] == true) {
            return ['0' => false, '1' => false];
        } else {
            $create_ticket = $this->TicketController->create_user($contact_details['email'], $fromname, $contact_details['subject'], $contact_details['message'], $phone, $phonecode, $mobile_number, $ticket_settings_details['help_topic'], $ticket_settings_details['sla'], $ticket_settings_details['priority'], $source, $collaborator, $ticket_settings_details['dept'], $ticket_settings_details['assign'], $form_data, $auto_response, $ticket_settings_details['status'], $duedate);

            return $create_ticket;
        }
    }

    /**
     * Get the target channel (A-1 web forms, A-2 emails, A-3 agent panel, A-4 API) for a ticket source.
     *
     * @param int $source
     *
     * @return string|null
     */
    public function channelForSource($source)
    {
        $channels = ['web' => 'A-1', 'email' => 'A-2', 'agent' => 'A-3', 'api' => 'A-4'];
        $name = strtolower((string) Ticket_source::where('id', '=', $source)->value('name'));

        return $channels[$name] ?? null;
    }

    /**
     * function to check the rules applied to the ticket workflow.
     *
     * @param type $to_check
     * @param type $condition
     * @param type $statement
     *
     * @return type boolean
     */
    public function checkRuleCondition($to_check, $condition, $statement)
    {
        // relations without a check (match / not_match) never match
        $return = false;
        if ($condition == 'equal') {
            $return = $this->checkEqual($statement, $to_check);
        } elseif ($condition == 'not_equal') {
            $return = $this->checkNotEqual($statement, $to_check);
        } elseif ($condition == 'contains') {
            $return = $this->checkContains($statement, $to_check);
        } elseif ($condition == 'dn_contain') {
            $return = $this->checkDoNotContain($statement, $to_check);
        } elseif ($condition == 'starts') {
            $return = $this->checkStarts($statement, $to_check);
        } elseif ($condition == 'ends') {
            $return = $this->checkEnds($statement, $to_check);
        }

//        elseif($condition == 'match') {
//
//        } elseif($condition == 'not_match') {
//
//        }
        return $return;
    }

    /**
     * function to check if the equal functions are applied.
     *
     * @param type $statement
     * @param type $to_check
     *
     * @return bool
     */
    public function checkEqual($statement, $to_check)
    {
        if ($statement == $to_check) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function to check if the not-equal functions are applied.
     *
     * @param type $statement
     * @param type $to_check
     *
     * @return bool
     */
    public function checkNotEqual($statement, $to_check)
    {
        if ($statement != $to_check) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function to check if the contains functions are applied.
     *
     * @param type $statement
     * @param type $to_check
     *
     * @return bool
     */
    public function checkContains($statement, $to_check)
    {
        if (strpos($to_check, $statement) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function to check if the do not contain functions are applied.
     *
     * @param type $statement
     * @param type $to_check
     *
     * @return bool
     */
    public function checkDoNotContain($statement, $to_check)
    {
        if (strpos($to_check, $statement) == false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function to check if the start functions are applied.
     *
     * @param type $statement
     * @param type $to_check
     *
     * @return bool
     */
    public function checkStarts($statement, $to_check)
    {
        if (substr($to_check, 0, strlen($statement)) == $statement) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function to check if the ends functions are applied.
     *
     * @param type $statement
     * @param type $to_check
     *
     * @return bool
     */
    public function checkEnds($statement, $to_check)
    {
        $to_check = strip_tags($to_check);
        if (substr($to_check, -strlen($statement)) == $statement) {
            return true;
        } else {
            return false;
        }
    }

//    function startsWith($to_check, $statement) {
//        // search backwards starting from haystack length characters from the end
//        return $statement === "" || strrpos($to_check, $statement, -strlen($to_check)) !== false;
//    }

//    function endsWith($to_check, $statement) {
//        // search forward starting from end minus needle length characters
//        return $statement === "" || (($temp = strlen($to_check) - strlen($statement)) >= 0 && strpos($to_check, $statement, $temp) !== false);
//    }

    /**
     * function to apply the action to a ticket.
     *
     * @param type $workflow_id
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function applyActionCondition($workflow_id, $ticket_settings_details)
    {
        $workflow_actions = WorkflowAction::where('workflow_id', '=', $workflow_id)->get();
        foreach ($workflow_actions as $workflow_action) {
            if ($workflow_action->condition == 'reject') {
                $ticket_settings_details = $this->rejectTicket($ticket_settings_details);
            } elseif ($workflow_action->condition == 'department') {
                $ticket_settings_details = $this->changeDepartment($workflow_action, $ticket_settings_details);
            } elseif ($workflow_action->condition == 'priority') {
                $ticket_settings_details = $this->changePriority($workflow_action, $ticket_settings_details);
            } elseif ($workflow_action->condition == 'sla') {
                $ticket_settings_details = $this->changeSla($workflow_action, $ticket_settings_details);
            } elseif ($workflow_action->condition == 'team') {
                $ticket_settings_details = $this->changeTeam($workflow_action, $ticket_settings_details);
            } elseif ($workflow_action->condition == 'agent') {
                $ticket_settings_details = $this->changeAgent($workflow_action, $ticket_settings_details);
            } elseif ($workflow_action->condition == 'helptopic') {
                $ticket_settings_details = $this->changeHelptopic($workflow_action, $ticket_settings_details);
            } elseif ($workflow_action->condition == 'status') {
                $ticket_settings_details = $this->changeStatus($workflow_action, $ticket_settings_details);
            }
        }

        return $ticket_settings_details;
    }

    /**
     * function to reject ticket.
     *
     * @param array $ticket_settings_details
     *
     * @return type array
     */
    public function rejectTicket($ticket_settings_details)
    {
        $ticket_settings_details['reject'] = true;

        return $ticket_settings_details;
    }

    /**
     * function to change the department of a ticket.
     *
     * @param type $workflow_action
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function changeDepartment($workflow_action, $ticket_settings_details)
    {
        $dept = Department::where('id', '=', $workflow_action->action)->first();
        if ($dept == null) {
            return $ticket_settings_details;
        } else {
            $ticket_settings_details['dept'] = $dept->id;

            return $ticket_settings_details;
        }
    }

    /**
     * function to change the priority of a ticket.
     *
     * @param type $workflow_action
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function changePriority($workflow_action, $ticket_settings_details)
    {
        $priority = Ticket_Priority::where('priority_id', '=', $workflow_action->action)->first();
        if ($priority == null) {
            return $ticket_settings_details;
        } else {
            $ticket_settings_details['priority'] = $priority->priority_id;

            return $ticket_settings_details;
        }
    }

    /**
     * function to change the SLA of a ticket.
     *
     * @param type $workflow_action
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function changeSla($workflow_action, $ticket_settings_details)
    {
        $sla_plan = Sla_plan::where('id', '=', $workflow_action->action)->first();
        if ($sla_plan == null) {
            return $ticket_settings_details;
        } else {
            $ticket_settings_details['sla'] = $sla_plan->id;

            return $ticket_settings_details;
        }
    }

    /**
     * function to assign tean to a ticket.
     *
     * @param type $workflow_action
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function changeTeam($workflow_action, $ticket_settings_details)
    {
        $team = Teams::where('id', '=', $workflow_action->action)->first();
        if ($team == null) {
            return $ticket_settings_details;
        } else {
            $ticket_settings_details['team'] = $team->id;

            return $ticket_settings_details;
        }
    }

    /**
     * function to assing a ticket to an agent.
     *
     * @param type $workflow_action
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function changeAgent($workflow_action, $ticket_settings_details)
    {
        $agent = User::where('id', '=', $workflow_action->action)->where('role', '!=', 'user')->first();
        if ($agent == null) {
            return $ticket_settings_details;
        } else {
            $ticket_settings_details['assign'] = $agent->id;

            return $ticket_settings_details;
        }
    }

    /**
     * function to change the helptopic of a ticket.
     *
     * @param type $workflow_action
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function changeHelptopic($workflow_action, $ticket_settings_details)
    {
        $help_topic = Help_topic::where('id', '=', $workflow_action->action)->first();
        if ($help_topic == null) {
            return $ticket_settings_details;
        } else {
            $ticket_settings_details['help_topic'] = $help_topic->id;

            return $ticket_settings_details;
        }
    }

    /**
     * function to change the status of a ticket.
     *
     * @param type $workflow_action
     * @param type $ticket_settings_details
     *
     * @return type array
     */
    public function changeStatus($workflow_action, $ticket_settings_details)
    {
        $status = Ticket_Status::where('id', '=', $workflow_action->action)->first();
        if ($status == null) {
            return $ticket_settings_details;
        } else {
            $ticket_settings_details['status'] = $status->id;

            return $ticket_settings_details;
        }
    }
}
