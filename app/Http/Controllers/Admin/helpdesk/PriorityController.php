<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controllers
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\PriorityRequest;
// models
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use DB;
use Exception;
// classes
use Illuminate\Http\Request;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * TicketController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class PriorityController extends Controller
{
    public function __construct(PhpMailController $PhpMailController, NotificationController $NotificationController)
    {
        $this->PhpMailController = $PhpMailController;
        $this->NotificationController = $NotificationController;
        $this->middleware('roles');
    }

    /**
     * Show the Inbox ticket list page.
     *
     * @return type response
     */
    public function priorityIndex()
    {
        $user_status = CommonSettings::where('option_name', '=', 'user_priority')->first();
        // dd( $user_status);

        return view('themes.default1.admin.helpdesk.manage.ticket_priority.index', compact('user_status'));
    }

    /**
     * Show the Inbox ticket list page.
     *
     * @return type response
     */
    public function userPriorityIndex(Request $request)
    {
        try {
            $user_status = $request->user_settings_priority;

            CommonSettings::where('option_name', '=', 'user_priority')->update(['status' => $user_status]);

            return 'Your Status Updated';
        } catch (Exception $e) {
            return Redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * @return type
     */
    public function priorityIndex1()
    {
        try {
            $ticket = new Ticket_Priority();
            $tickets = $ticket->select('priority_id', 'priority', 'priority_desc', 'priority_color', 'status', 'is_default', 'ispublic')->get();

            return DataTables::of($tickets)
                            ->addColumn('priority_color', function ($model) {
                                return "<button class='btn' style = 'background-color:$model->priority_color'></button>";
                            })
                            ->addColumn('status', function ($model) {
                                if ($model->status == 1) {
                                    return "<a style='color:green'>active</a>";
                                } elseif ($model->status == 0) {
                                    Ticket_Priority::where('priority_id', '=', '$priority_id')
                                    ->update(['priority_id' => '']);

                                    return "<a style='color:red'>inactive</a>";
                                }
                            })
                            ->addColumn('action', function ($model) {
                                if ($model->is_default > 0) {
                                    return '<button class="btn btn-primary btn-xs" disabled> Edit </button>&nbsp;<button class="btn btn-danger btn-xs" disabled> Delete </button>';
                                } else {
                                    return '<a href='.url('ticket/priority/'.$model->priority_id.'/edit')." class='btn btn-primary btn-xs'>Edit</a>&nbsp;<a class='btn btn-danger btn-xs' onclick='confirmDelete(".$model->priority_id.")' href='javascript:;'>Delete </a>";
                                }
                            })
                            ->rawColumns(['priority_color', 'status', 'action'])
                            ->make(true);
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * @return type
     */
    public function priorityCreate()
    {
        return view('themes.default1.admin.helpdesk.manage.ticket_priority.create');
    }

    public function priorityCreate1(PriorityRequest $request)
    {
        $tk_priority = new Ticket_Priority();
        $tk_priority->priority = $request->priority;
        $tk_priority->status = $request->status;
        $tk_priority->priority_desc = $request->priority_desc;
        $tk_priority->priority_color = $request->priority_color;
        $tk_priority->ispublic = $request->ispublic;
        $tk_priority->save();

        return \Redirect::route('priority.index')->with('success', Lang::get('lang.priority_successfully_created'));
    }

    /**
     * @param type $priority_id
     *
     * @return type
     */
    public function priorityEdit($priority_id)
    {
        $tk_priority = Ticket_Priority::wherepriority_id($priority_id)->first();

        return view('themes.default1.admin.helpdesk.manage.ticket_priority.edit', compact('tk_priority'));
    }

    /**
     * @param PriorityRequest $request
     *
     * @return type
     */
    public function priorityEdit1(PriorityRequest $request)
    {
        $priority_id = $request->priority_id;
        $tk_priority = Ticket_Priority::findOrFail($priority_id);
        $tk_priority->priority = $request->priority;
        $tk_priority->status = $request->status;
        $tk_priority->priority_desc = $request->priority_desc;
        $tk_priority->priority_color = $request->priority_color;
        $tk_priority->ispublic = $request->ispublic;
        $tk_priority->save();
        if ($request->input('default_priority') == 'on') {
            Ticket_Priority::where('is_default', '>', 0)
                    ->update(['is_default' => 0]);
            Ticket_Priority::where('priority_id', '=', $priority_id)
                    ->update(['is_default' => 1]);
        }

        return \Redirect::route('priority.index')->with('success', Lang::get('lang.priority_successfully_updated'));
    }

    /**
     * @param type $priority_id
     *
     * @return type
     */
    public function destroy($priority_id)
    {
        $default_priority = Ticket_Priority::where('is_default', '>', '0')->first();
        // dd($default_priority->is_default);
        $topic = DB::table('help_topic')->where('priority', '=', $priority_id)->update(['priority' => $default_priority->is_default]);
        // if ($topic > 0) {
        //     if ($topic > 1) {
        //         $text_topic = 'Emails';
        //     } else {
        //         $text_topic = 'Email';
        //     }
        //     $topic = '<li>'.Lang::get('lang.associated_help_topic_have_been_moved_to_default_sla').'</li>';
        // } else {
        //     $topic = '';
        // }
        // dd('llll');
        $tk_priority = Ticket_Priority::findOrFail($priority_id);

        $tk_priority->delete();

        return \Redirect::route('priority.index')->with('success', Lang::get('lang.delete_successfully'));
    }
}
