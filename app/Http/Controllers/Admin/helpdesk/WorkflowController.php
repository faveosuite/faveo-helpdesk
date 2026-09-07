<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controller
use App\Http\Controllers\Agent\helpdesk\TicketController;
use App\Http\Controllers\Controller;
// request
use App\Http\Requests\helpdesk\WorkflowCreateRequest;
use App\Http\Requests\helpdesk\WorkflowUpdateRequest;
use App\Model\helpdesk\Agent\Department;
// model
use App\Model\helpdesk\Agent\Teams;
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Ticket\Ticket_Status;
use App\Model\helpdesk\Workflow\WorkflowAction;
use App\Model\helpdesk\Workflow\WorkflowName;
use App\Model\helpdesk\Workflow\WorkflowRules;
use App\User;
use Exception;
//classes
use Illuminate\Http\Request;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * WorkflowController
 * In this controller in the CRUD function for all the workflow applied in faveo.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class WorkflowController extends Controller
{
    /**
     * Create a new controller instance.
     * constructor to check
     * 1. authentication
     * 2. user roles
     * 3. roles must be agent.
     *
     * @return void
     */
    public function __construct()
    {
        // checking authentication
        $this->middleware('auth');
        // checking admin roles
        $this->middleware('roles');
    }

    /**
     * Display a listing of all the workflow.
     *
     * @return type
     */
    public function index()
    {
        try {
            return view('themes.default1.admin.helpdesk.manage.workflow.index');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * List of all the workflow in the system.
     *
     * @return type
     */
    public function workFlowList()
    {
        return DataTables::of(WorkflowName::All())
                        ->addColumn('name', function ($model) {
                            return $model->name;
                        })
                        /* add column status */
                        ->addColumn('status', function ($model) {
                            if ($model->status == 1) {
                                return 'Active';
                            } elseif ($model->status == 0) {
                                return 'Disabled';
                            }
                        })
                        /* add column order */
                        ->addColumn('order', function ($model) {
                            return $model->order;
                        })
                        /* add column rules */
                        ->addColumn('rules', function ($model) {
                            $rules = WorkflowRules::where('workflow_id', '=', $model->id)->count();

                            return $rules;
                        })
                        /* add column target */
                        ->addColumn('target', function ($model) {
                            $target = $model->target;
                            $target1 = explode('-', $target);
                            if ($target1[0] == 'A') {
                                if ($target1[1] == 0) {
                                    return 'Any';
                                } elseif ($target1[1] == 1) {
                                    return 'Web Forms';
                                } elseif ($target1[1] == 2) {
                                    return 'Email';
                                } elseif ($target1[1] == 4) {
                                    return 'API';
                                }
                            } elseif ($target1[0] == 'E') {
                                $emails = Emails::where('id', '=', $target1[1])->first();

                                return $emails->email_address;
                            }
                        })
                        /* add column created */
                        ->addColumn('Created', function ($model) {
                            return TicketController::usertimezone($model->created_at);
                        })
                        /* add column updated */
                        ->addColumn('Updated', function ($model) {
                            return TicketController::usertimezone($model->updated_at);
                        })
                        /* add column action */
                        ->addColumn('Actions', function ($model) {
                            return "<a class='btn btn-primary btn-xs' href='".route('workflow.edit', $model->id)."'><i class='fas fa-edit'></i> Edit</a>  <a class='btn btn-danger btn-xs' href='".route('workflow.delete', $model->id)."'><i class='fas fa-trash'></i> Delete</a>";
                        })
                        ->rawColumns(['Actions'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new workflow.
     *
     * @return type Response
     */
    public function create(Emails $emails)
    {
        $email_data = [];
        foreach ($emails->pluck('email_address', 'id') as $key => $email) {
            $email_data["E-$key"] = $email;
        }
        $emails = $email_data;

        try {
            return view('themes.default1.admin.helpdesk.manage.workflow.create', compact('emails'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Store a new workflow in to the system.
     *
     * @param \App\Http\Requests\helpdesk\WorkflowCreateRequest $request
     *
     * @return type view
     */
    public function store(WorkflowCreateRequest $request)
    {
        try {
            // store a new workflow credentials in to the system
            $workflow_name = new WorkflowName();
            $workflow_name->name = $request->name;
            $workflow_name->status = $request->status;
            $workflow_name->order = $request->execution_order;
            $workflow_name->target = $request->target_channel;
            $workflow_name->internal_note = $request->internal_note;
            $workflow_name->save();

            $rules = $request->rule;
            $actions = $request->action;
            // store workflow rules into the system
            foreach ($rules as $rule) {
                $workflow_rule = new WorkflowRules();
                $workflow_rule->workflow_id = $workflow_name->id;
                $workflow_rule->matching_scenario = $rule['a'];
                $workflow_rule->matching_relation = $rule['b'];
                $workflow_rule->matching_value = $rule['c'];
                $workflow_rule->save();
            }
            // store a new workflow action into the system
            foreach ($actions as $action) {
                $workflow_action = new WorkflowAction();
                $workflow_action->workflow_id = $workflow_name->id;
                $workflow_action->condition = $action['a'];
                $workflow_action->action = $action['b'];
                $workflow_action->save();
            }

            return redirect('workflow')->with('success', Lang::get('lang.workflow_created_successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Editing the details of the banned users.
     *
     * @param type $id
     * @param User $ban
     *
     * @return type Response
     */
    public function edit($id, WorkflowName $work_flow_name, Emails $emails, WorkflowRules $workflow_rule, WorkflowAction $workflow_action)
    {
        try {
            $emails = $emails->get();
            $workflow = $work_flow_name->whereId($id)->first();
            $workflow_rules = $workflow_rule->whereWorkflow_id($id)->get();
            $workflow_actions = $workflow_action->whereWorkflow_id($id)->get();

            return view('themes.default1.admin.helpdesk.manage.workflow.edit', compact('id', 'workflow', 'emails', 'workflow_rules', 'workflow_actions'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update ticket workflow.
     *
     * @param type                                              $id
     * @param \App\Http\Requests\helpdesk\WorkflowUpdateRequest $request
     *
     * @return type view
     */
    public function update($id, WorkflowUpdateRequest $request)
    {
        try {
            // store a new workflow credentials in to the system
            $workflow_name = WorkflowName::whereId($id)->first();
            $workflow_name->name = $request->name;
            $workflow_name->status = $request->status;
            $workflow_name->order = $request->execution_order;
            $workflow_name->target = $request->target_channel;
            $workflow_name->internal_note = $request->internal_note;
            $workflow_name->save();

            $rules = $request->rule;
            $actions = $request->action;
            // removing old foreign values to insert an updated one
            WorkflowAction::where('workflow_id', '=', $id)->delete();
            WorkflowRules::where('workflow_id', '=', $id)->delete();
            // update workflow rules into the system
            foreach ($rules as $rule) {
                $workflow_rule = new WorkflowRules();
                $workflow_rule->workflow_id = $workflow_name->id;
                $workflow_rule->matching_scenario = $rule['a'];
                $workflow_rule->matching_relation = $rule['b'];
                $workflow_rule->matching_value = $rule['c'];
                $workflow_rule->save();
            }
            // update workflow action into the system
            foreach ($actions as $action) {
                $workflow_action = new WorkflowAction();
                $workflow_action->workflow_id = $workflow_name->id;
                $workflow_action->condition = $action['a'];
                $workflow_action->action = $action['b'];
                $workflow_action->save();
            }

            return redirect('workflow')->with('success', Lang::get('lang.workflow_updated_successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * function to delete workflow.
     *
     * @param type $id
     */
    public function destroy($id)
    {
        try {
            // remove all the contents of workflow
            $workflow_action = WorkflowAction::where('workflow_id', '=', $id)->delete();
            $workflow_rules = WorkflowRules::where('workflow_id', '=', $id)->delete();
            $workflow = WorkflowName::whereId($id)->delete();

            return redirect('workflow')->with('success', Lang::get('lang.workflow_deleted_successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * function to select action.
     *
     * @param type                     $id
     * @param \Illuminate\Http\Request $request
     *
     * @return type void
     */
    public function selectAction($id, Request $request)
    {
        if ($request->option == 'reject') {
            return $this->rejectTicket($id);
        } elseif ($request->option == 'department') {
            return $this->department($id);
        } elseif ($request->option == 'priority') {
            return $this->priority($id);
        } elseif ($request->option == 'sla') {
            return $this->slaPlan($id);
        } elseif ($request->option == 'team') {
            return $this->assignTeam($id);
        } elseif ($request->option == 'agent') {
            return $this->assignAgent($id);
        } elseif ($request->option == 'helptopic') {
            return $this->helptopic($id);
        } elseif ($request->option == 'status') {
            return $this->ticketStatus($id);
        }
    }

    /**
     * function to reject ticket.
     *
     * @return string
     */
    public function rejectTicket($id)
    {
        $var = '<input type="hidden" name="action['.$id.'][b]" class="form-control" value="reject"><span text-red>Reject</span> ';

        return $var;
    }

    /**
     * function to return deprtment select option.
     *
     * @return type string
     */
    public function department($id)
    {
        $departments = Department::all();
        $var = "<select name='action[".$id."][b]' class='form-control' required>";
        foreach ($departments as $department) {
            $var .= "<option value='".$department->id."'>".$department->name.'</option>';
        }
        $var .= '</select>';

        return $var;
    }

    /**
     * function to return the priority select option.
     *
     * @return type string
     */
    public function priority($id)
    {
        $priorities = Ticket_Priority::where('status', '=', 1)->get();
        $var = "<select name='action[".$id."][b]' class='form-control' required>";
        foreach ($priorities as $priority) {
            $var .= "<option value='".$priority->priority_id."'>".$priority->priority_desc.'</option>';
        }
        $var .= '</select>';

        return $var;
    }

    /**
     * function to return the slaplan select option.
     *
     * @return type string
     */
    public function slaPlan($id)
    {
        $sla_plans = Sla_plan::where('status', '=', 1)->get();
        $var = "<select name='action[".$id."][b]' class='form-control' required>";
        foreach ($sla_plans as $sla_plan) {
            $var .= "<option value='".$sla_plan->id."'>".$sla_plan->grace_period.'</option>';
        }
        $var .= '</select>';

        return $var;
    }

    /**
     * function to get system team select option.
     *
     * @return type string
     */
    public function assignTeam($id)
    {
        $teams = Teams::where('status', '=', 1)->get();
        $var = "<select name='action[".$id."][b]' class='form-control' required>";
        foreach ($teams as $team) {
            $var .= "<option value='".$team->id."'>".$team->name.'</option>';
        }
        $var .= '</select>';

        return $var;
    }

    /**
     * function to get system agents select option.
     *
     * @return type string
     */
    public function assignAgent($id)
    {
        $users = User::where('role', '!=', 'user')->where('active', '=', 1)->get();
        $var = "<select name='action[".$id."][b]' class='form-control' required>";
        foreach ($users as $user) {
            $var .= "<option value='".$user->id."'>".$user->first_name.' '.$user->last_name.'</option>';
        }
        $var .= '</select>';

        return $var;
    }

    /**
     * function to get the helptopic select option.
     *
     * @return type string
     */
    public function helptopic($id)
    {
        $help_topics = Help_topic::where('status', '=', 1)->get();
        $var = "<select name='action[".$id."][b]' class='form-control' required>";
        foreach ($help_topics as $help_topic) {
            $var .= "<option value='".$help_topic->id."'>".$help_topic->topic.'</option>';
        }
        $var .= '</select>';

        return $var;
    }

    /**
     * function to get the select option to choose the ticket status.
     *
     * @return type string
     */
    public function ticketStatus($id)
    {
        $ticket_status = Ticket_Status::all();
        $var = "<select name='action[".$id."][b]' class='form-control' required>";
        foreach ($ticket_status as $status) {
            $var .= "<option value='".$status->id."'>".$status->name.'</option>';
        }
        $var .= '</select>';

        return $var;
    }
}
