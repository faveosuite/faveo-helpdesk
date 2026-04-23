<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
// requests
use App\Model\helpdesk\Email\Emails;
// models
use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Settings\Followup;
use App\Model\helpdesk\Ticket\Ticket_Status;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Ticket\TicketToken;
use App\User;
use Hash;
// classes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lang;
use Session;

/**
 * GuestController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class UnAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        $this->middleware('board');
        $this->PhpMailController = $PhpMailController;
    }

    /**
     * Post Check ticket.
     *
     * @param type CheckTicket   $request
     * @param type User          $user
     * @param type Tickets       $ticket
     * @param type Ticket_Thread $thread
     *
     * @return type Response
     */
    public function PostCheckTicket(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'email_address' => 'required|email',
                'ticket_number' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput()
                                ->with('check', '1');
            }
            $email = $request->input('email_address');
            $ticket_number = $request->input('ticket_number');
            // get user details
            $user_details = User::where('email', '=', $email)->first();
            if ($user_details == null) {
                return \Redirect::route('form')->with('fails', Lang::get('lang.sorry_that_email_is not_available_in_this_system'));
            }
            // get ticket details
            $ticket = Tickets::where('ticket_number', '=', $ticket_number)->first();
            if ($ticket == null) {
                return \Redirect::route('form')->with('fails', Lang::get('lang.there_is_no_such_ticket_number'));
            }
            if ($ticket->user_id == $user_details->id) {
                if ($user_details->role == 'user') {
                    $username = $user_details->first_name;
                } else {
                    $username = $user_details->first_name.' '.$user_details->last_name;
                }
                // check for preentered ticket token
                $ticket_token = TicketToken::where('ticket_id', '=', $ticket->id)->first();
                if ($ticket_token) {
                    $token = $this->generate_random_ticket_token();
                    $hashed_token = \Hash::make($token);
                    $ticket_token->token = $hashed_token;
                    $ticket_token->save();
                } else {
                    $ticket_token = new TicketToken();
                    $ticket_token->ticket_id = $ticket->id;
                    $token = $this->generate_random_ticket_token();
                    $hashed_token = \Hash::make($token);
                    $ticket_token->token = $hashed_token;
                    $ticket_token->save();
                }

                try {
                    $this->PhpMailController->sendmail(
                        $from = $this->PhpMailController->mailfrom('1', '0'),
                        $to = ['name' => $username, 'email' => $user_details->email],
                        $message = ['subject' => 'Ticket link Request ['.$ticket_number.']', 'scenario' => 'check-ticket'],
                        $template_variables = ['user' => $username, 'ticket_link_with_number' => url('show-ticket/'.$ticket->id.'/'.$token)]
                    );
                } catch (\Exception $e) {
                }

                return redirect()->back()
                                ->with('success', Lang::get('lang.we_have_sent_you_a_link_by_email_please_click_on_that_link_to_view_ticket'));
            } else {
                return \Redirect::route('form')->with('fails', Lang::get("lang.email_didn't_match_with_ticket_number"));
            }
        } catch (\Exception $e) {
            return \Redirect::route('form')->with('fails', $e->getMessage());
        }
    }

    /**
     * generate random string token for ticket.
     *
     * @param type $length
     *
     * @return string
     */
    public function generate_random_ticket_token($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * function to check the ticket without loggin In.
     *
     * @param type $ticket_id
     * @param type $token
     *
     * @return type view
     */
    public function showTicketCode($ticket_id, $token)
    {
        try {
            $check_token = TicketToken::where('ticket_id', '=', $ticket_id)->first();
            if (Hash::check($token, $check_token->token) == true) {
                $token_time = CommonSettings::where('option_name', '=', 'ticket_token_time_duration')->first();
                $time = $token_time->option_value;
                $new_time = date_add($check_token->updated_at, date_interval_create_from_date_string($time.' Hours'));
                if (date('Y-m-d H:i:s') > $new_time) {
                    return redirect()->route('form')->with('fails', Lang::get('lang.sorry_your_ticket_token_has_expired_please_try_to_resend_the_ticket_link_request'));
                }
                $tickets = Tickets::where('id', '=', $ticket_id)->first();

                return view('themes.default1.client.helpdesk.unauth.showticket', compact('tickets', 'token'));
            } else {
                return redirect()->route('form')->with('fails', Lang::get('lang.sorry_you_are_not_allowed_token_expired'));
            }
        } catch (Exception $ex) {
            return redirect()->route('form')->with('fails', $e->getMessage());
        }
    }

    /**
     * Store ratings of the user.
     *
     * @return type Redirect
     */
    public function rating($id, Request $request, \App\Model\helpdesk\Ratings\RatingRef $rating_ref)
    {
        foreach ($request->except(['_token']) as $key => $value) {
            if (strpos($key, '_') !== false) {
                $ratName = str_replace('_', ' ', $key);
            } else {
                $ratName = $key;
            }
            $ratID = \App\Model\helpdesk\Ratings\Rating::where('name', '=', $ratName)->first();
            $ratingrefs = $rating_ref->where('rating_id', '=', $ratID->id)->where('ticket_id', '=', $id)->first();
            if ($ratingrefs !== null) {
                $ratingrefs->rating_id = $ratID->id;
                $ratingrefs->ticket_id = $id;

                $ratingrefs->thread_id = '0';
                $ratingrefs->rating_value = $value;
                $ratingrefs->save();
            } else {
                $rating_ref->rating_id = $ratID->id;
                $rating_ref->ticket_id = $id;

                $rating_ref->thread_id = '0';
                $rating_ref->rating_value = $value;
                $rating_ref->save();
            }
        }

        return redirect()->back()->with('Success', Lang::get('lang.thank_you_for_your_rating'));
    }

    /**
     * Store Client rating about reply of agent quality.
     *
     * @return type Redirect
     */
    public function ratingReply($id, Request $request, \App\Model\helpdesk\Ratings\RatingRef $rating_ref)
    {
        foreach ($request->all() as $key => $value) {
            $key1 = explode(',', $key);
            if (strpos($key1[0], '_') !== false) {
                $ratName = str_replace('_', ' ', $key1[0]);
            } else {
                $ratName = $key1[0];
            }
            $ratID = \App\Model\helpdesk\Ratings\Rating::where('name', '=', $ratName)->first();
            $ratingrefs = $rating_ref->where('rating_id', '=', $ratID->id)->where('thread_id', '=', $key1[1])->first();
            if ($ratingrefs !== null) {
                $ratingrefs->rating_id = $ratID->id;
                $ratingrefs->ticket_id = $id;
                $ratingrefs->thread_id = $key1[1];
                $ratingrefs->rating_value = $value;
                $ratingrefs->save();
            } else {
                $rating_ref->rating_id = $ratID->id;
                $rating_ref->ticket_id = $id;
                $rating_ref->thread_id = $key1[1];
                $rating_ref->rating_value = $value;
                $rating_ref->save();
            }
        }

        return redirect()->back()->with('Success', Lang::get('lang.thank_you_for_your_rating'));
    }

    /**
     * function to change the status of the ticket.
     *
     * @param type $status
     * @param type $id
     *
     * @return string
     */
    public function changeStatus($status, $id)
    {
        $tickets = Tickets::where('id', '=', $id)->first();
        $tickets->status = $status;
        $ticket_status = Ticket_Status::where('id', '=', $status)->first();
        if ($ticket_status->state == 'closed') {
            $tickets->closed = $ticket_status->id;
            $tickets->closed_at = date('Y-m-d H:i:s');
        }
        $tickets->save();
        $ticket_thread = Ticket_Thread::where('ticket_id', '=', $ticket_status->id)->first();
        $ticket_subject = $ticket_thread->title;

        $user = User::where('id', '=', $tickets->user_id)->first();

        $thread = new Ticket_Thread();
        $thread->ticket_id = $tickets->id;
        $thread->user_id = $tickets->user_id;
        $thread->is_internal = 1;
        $thread->body = $ticket_status->message.' '.$user->user_name;
        $thread->save();

        $email = $user->email;
        $user_name = $user->user_name;

        $ticket_number = $tickets->ticket_number;

        $sending_emails = Emails::where('department', '=', $ticket_status->dept_id)->first();
        if ($sending_emails == null) {
            $from_email = $this->system_mail();
        } else {
            $from_email = $sending_emails->id;
        }

        try {
            $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('0', $tickets->dept_id), $to = ['name' => $user_name, 'email' => $email], $message = ['subject' => $ticket_subject.'[#'.$ticket_number.']', 'scenario' => 'close-ticket'], $template_variables = ['ticket_number' => $ticket_number]);
        } catch (\Exception $e) {
            return 0;
        }

        return Lang::get('lang.your_ticket_has_been').' '.$ticket_status->state;
    }

    //Auto-close tickets
    public function autoCloseTickets()
    {
        $workflow = \App\Model\helpdesk\Workflow\WorkflowClose::whereId(1)->first();

        if ($workflow->condition == 1) {
            $overdues = Tickets::where('status', '=', 1)->where('isanswered', '=', 1)->orderBy('id', 'DESC')->get();
            if (count($overdues) == 0) {
                $tickets = null;
            } else {
                $i = 0;
                foreach ($overdues as $overdue) {
                    //                $sla_plan = Sla_plan::where('id', '=', $overdue->sla)->first();

                    $ovadate = $overdue->created_at;
                    $new_date = date_add($ovadate, date_interval_create_from_date_string($workflow->days.' days')).'<br/><br/>';
                    if (date('Y-m-d H:i:s') > $new_date) {
                        $i++;
                        $overdue->status = 3;
                        $overdue->closed = 1;
                        $overdue->closed_at = date('Y-m-d H:i:s');
                        $overdue->save();
//        if($workflow->send_email == 1) {
//             $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('0', $overdue->dept_id), $to = ['name' => $user_name, 'email' => $email], $message = ['subject' => $ticket_subject.'[#'.$ticket_number.']', 'scenario' => 'close-ticket'], $template_variables = ['ticket_number' => $ticket_number]);
//        }
                    }
                }
                // dd(count($value));
//            if ($i > 0) {
//                $tickets = new collection($value);
//            } else {
//                $tickets = null;
//            }
            }
        } else {
        }
    }

    /**
     *@category function to change system's language
     *
     * @param string $lang //desired language's iso code
     *
     * @return response
     */
    public static function changeLanguage($lang)
    {
//        if(Cache::has('language'))
//         {
//           return Cache::get('language');
//         } else return 'false';
//          Cache::put('language',$);
        $path = base_path('lang');  // Path to check available language packages
        if (array_key_exists($lang, \Config::get('languages')) && in_array($lang, scandir($path))) {
            // dd(array_key_exists($lang, Config::get('languages')));
            // app()->setLocale($lang);

            \Cache::forever('language', $lang);
            // dd(Cache::get('language'));
            // dd()
        } else {
            return false;
        }

        return true;

        /*  $path = base_path('lang');  // Path to check available language packages
          if (array_key_exists($lang, \Config::get('languages')) && in_array($lang, scandir($path))) {
              if (Auth::check()) {
                  $id = Auth::user()->id;
                  $user = User::find($id);
                  $user->user_language = $lang;
                  $user->save();
              } else {
                  Session::put('language', $lang);
              }
          }

          return redirect()->back();*/
    }

    // Follow up tickets
    public function followup()
    {
        $followup = Followup::whereId('1')->first();
        $condition = $followup->condition;
        // dd($condition);

        switch ($condition) {
            case 'everyMinute':
                $followup_set = ' + 1 minute';
                break;
            case 'everyFiveMinutes':
                $followup_set = ' + 5 minute';
                break;
            case 'everyTenMinutes':
                $followup_set = ' + 10 minute';
                break;
            case 'everyThirtyMinutes':
                $followup_set = ' + 30 minute';
                break;
            case 'hourly':
                $followup_set = ' + 1 hours';
                break;
            case 'daily':
                $followup_set = ' + 1 day';
                break;
            case 'weekly':
                $followup_set = ' + 7 day';
                break;
            case 'monthly':
                $followup_set = ' + 30 day';
                break;
            case 'yearly':
                $followup_set = ' + 365 day';
                break;
        }

        if ($followup->status = 1) {
            $tickets = Tickets::where('id', '>=', 1)->where('status', '!=', 5)->get();
            // dd( $tickets);
            // $tickets=Tickets::where('id', '>=', 1)->where('status', '!=', 5)->pluck('id');
            // dd( $tickets);
            // $id=1;
            foreach ($tickets as $ticket) {
                // $id=1;
                // $id++;
                // $ticket=Tickets::where('status', '!=', 5)->get();

                // dd($ticket);
                // if($ticket != null){
                // dd('here');
                $ck = date('Y-m-d H:i:s', strtotime($ticket->updated_at.$followup_set));
                // dd($ck);
                $current_time = date('Y-m-d H:i:s');
                if ($current_time > $ck) {
                    $ticket->follow_up = 1;
                    $ticket->save();
                    //  Tickets::where('id', '=',$id)
                    // ->update(['follow_up' => 1]);

                    // }
                }
                //       if($id=2)
                // {dd($ticket);}
            }
        }
    }

    /**
     * Function to chnage user language preference.
     *
     * @param string $lang //desired language's iso code
     *
     * @category function to change system's language
     *
     * @return response
     */
    public static function changeUserLanguage($lang)
    {
        $path = base_path('lang');  // Path to check available language packages
        if (array_key_exists($lang, \Config::get('languages')) && in_array($lang, scandir($path))) {
            if (Auth::check()) {
                $id = Auth::user()->id;
                $user = User::find($id);
                $user->user_language = $lang;
                $user->save();
            } else {
                Session::put('language', $lang);
            }
        }

        return redirect()->back();
    }

    public function close($id, Tickets $ticket)
    {
        $tickets = Tickets::where('id', '=', $id)->first();
        $tickets->status = 3;
        $ticket_status = Ticket_Status::where('id', '=', 3)->first();
        if ($ticket_status->state == 'closed') {
            $tickets->closed = $ticket_status->id;
            $tickets->closed_at = date('Y-m-d H:i:s');
        }
        $tickets->save();
        $ticket_thread = Ticket_Thread::where('ticket_id', '=', $ticket_status->id)->first();
        $ticket_subject = $ticket_thread->title;

        $user = User::where('id', '=', $tickets->user_id)->first();

        $thread = new Ticket_Thread();
        $thread->ticket_id = $tickets->id;
        $thread->user_id = $tickets->user_id;
        $thread->is_internal = 1;
        $thread->body = $ticket_status->message.' '.$user->user_name;
        $thread->save();

        $email = $user->email;
        $user_name = $user->user_name;
        $ticket_number = $tickets->ticket_number;

        $sending_emails = Emails::where('department', '=', $ticket_status->dept_id)->first();
        if ($sending_emails == null) {
            $from_email = $this->system_mail();
        } else {
            $from_email = $sending_emails->id;
        }

        try {
            $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('0', $tickets->dept_id), $to = ['name' => $user_name, 'email' => $email], $message = ['subject' => $ticket_subject.'[#'.$ticket_number.']', 'scenario' => 'close-ticket'], $template_variables = ['ticket_number' => $ticket_number]);
        } catch (\Exception $e) {
            return 0;
        }

        return Lang::get('lang.your_ticket_has_been').' '.$ticket_status->state;
    }

    public function open($id, Tickets $ticket)
    {
        $ticket_status = $ticket->where('id', '=', $id)->first();
        $ticket_status->status = 1;
        $ticket_status->reopened_at = date('Y-m-d H:i:s');
        $ticket_status->save();
        $ticket_status_message = Ticket_Status::where('id', '=', $ticket_status->status)->first();
        $thread = new Ticket_Thread();
        $user = User::where('id', '=', $ticket->user_id)->first();
        $thread->ticket_id = $ticket_status->id;
        $thread->user_id = $ticket->user_id;
        $thread->is_internal = 1;
        $thread->body = $ticket_status->message.' '.$user->user_name;
        $thread->save();

        return 'your ticket'.$ticket_status->ticket_number.' has been opened';
    }

    public function resolve($id, Tickets $ticket)
    {
        $ticket_status = $ticket->where('id', '=', $id)->first();

        $ticket_status->status = 2;
        $ticket_status->closed = 1;
        $ticket_status->closed_at = date('Y-m-d H:i:s');
        $ticket_status->save();
        $ticket_status_message = Ticket_Status::where('id', '=', $ticket_status->status)->first();
        $thread = new Ticket_Thread();
        $user = User::where('id', '=', $ticket->user_id)->first();
        $thread->ticket_id = $ticket_status->id;
        $thread->user_id = $ticket->user_id;
        $thread->is_internal = 1;

        $thread->save();

        return Lang::get('lang.your_ticket_has_been').' '.$ticket_status->state;
    }
}
