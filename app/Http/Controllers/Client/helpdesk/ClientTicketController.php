<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Agent\helpdesk\TicketWorkflowController;
use App\Http\Controllers\Controller;
// requests
use App\Model\helpdesk\Ticket\Ticket_Thread;
// models
use App\Model\helpdesk\Ticket\Tickets;
use App\User;
use Auth;
// classes
use Illuminate\Http\Request;
use Lang;

/**
 * TicketController2.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class ClientTicketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return type response
     */
    public function __construct(TicketWorkflowController $TicketWorkflowController)
    {
        $this->TicketWorkflowController = $TicketWorkflowController;
        // $this->middleware('auth');
        // $this->middleware('role.user');
        $this->middleware('board');
    }

    /**
     * Get Checked ticket.
     *
     * @param type Tickets $ticket
     * @param type User    $user
     *
     * @return type response
     */
    public function getCheckTicket(Tickets $ticket, User $user)
    {
        return view('themes.default1.client.helpdesk.guest-user.newticket', compact('ticket'));
    }

    /**
     * reply.
     *
     * @param type $value
     *
     * @return type view
     */
    public function reply($id, Request $request)
    {
        $tickets = Tickets::where('id', '=', $id)->first();

        if (!$tickets || !$this->canAuthenticatedUserProceedToReply($tickets)) {
            return \Redirect::back()->with('fails', Lang::get('lang.unauthorized_access'));
        }

        $thread = Ticket_Thread::where('ticket_id', '=', $tickets->id)->first();

        $subject = $thread->title.'[#'.$tickets->ticket_number.']';
        $body = $request->input('comment');

        $user_cred = User::where('id', '=', $tickets->user_id)->first();

        $fromaddress = $user_cred->email;
        $fromname = $user_cred->user_name;
        $phone = '';
        $phonecode = '';
        $mobile_number = '';

        $helptopic = $tickets->help_topic_id;
        $sla = $tickets->sla;
        $priority = $tickets->priority_id;
        $source = $tickets->source;
        $collaborator = '';
        $dept = $tickets->dept_id;
        $assign = $tickets->assigned_to;
        $form_data = null;
        $team_assign = null;
        $ticket_status = null;
        $auto_response = 0;

        $this->TicketWorkflowController->workflow($fromaddress, $fromname, $subject, $body, $phone, $phonecode, $mobile_number, $helptopic, $sla, $priority, $source, $collaborator, $dept, $assign, $team_assign, $ticket_status, $form_data, $auto_response);

        return \Redirect::back()->with('success1', Lang::get('lang.successfully_replied'));
    }

    /**
     * Check if the authenticated user is the owner of the ticket.
     *
     * @param Tickets $ticket
     *
     * @return bool
     */
    private function canAuthenticatedUserProceedToReply($ticket)
    {
        return Tickets::where(function ($query) use ($ticket) {
            $query->where('id', '=', $ticket->id)
                  ->where('user_id', '=', Auth::id());
        })->exists();
    }
}
