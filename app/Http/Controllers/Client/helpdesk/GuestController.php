<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\OtpVerifyRequest;
use App\Http\Requests\helpdesk\ProfilePassword;
use App\Http\Requests\helpdesk\ProfileRequest;
use App\Http\Requests\helpdesk\TicketRequest;
// models
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\CountryCode;
use App\Model\helpdesk\Utility\Otp;
use App\User;
use Auth;
// classes
use DateTime;
use DB;
use Exception;
use GeoIP;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Lang;
use Socialite;

/**
 * GuestController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class GuestController extends Controller
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
        // checking authentication
        $this->middleware('auth');
    }

    /**
     * Get profile.
     *
     * @return type Response
     */
    public function getProfile(CountryCode $code)
    {
        $user = Auth::user();
        $location = GeoIP::getLocation();
        $phonecode = $code->where('iso', '=', $location->iso_code)->first();
        $settings = CommonSettings::select('status')->where('option_name', '=', 'send_otp')->first();
        $status = $settings->status;

        return view('themes.default1.client.helpdesk.profile', compact('user'))
                        ->with(['phonecode' => $phonecode->phonecode,
                            'verify'        => $status, ]);
    }

    /**
     * Save profile data.
     *
     * @param type $id
     * @param type ProfileRequest $request
     *
     * @return type Response
     */
    public function postProfile(ProfileRequest $request)
    {
        try {
            // geet authenticated user details
            $user = Auth::user();
            if ($request->get('country_code') == '' && ($request->get('phone_number') != '' || $request->get('mobile') != '')) {
                return redirect()->back()->with(['fails' => Lang::get('lang.country-code-required-error'), 'country_code_error' => 1])->withInput();
            } else {
                $code = CountryCode::select('phonecode')->where('phonecode', '=', $request->get('country_code'))->get();
                if (!count($code)) {
                    return redirect()->back()->with(['fails' => Lang::get('lang.incorrect-country-code-error'), 'country_code_error' => 1])->withInput();
                }
                $user->country_code = $request->country_code;
            }
            $user->fill($request->except('profile_pic', 'mobile', 'active', 'role', 'is_delete', 'ban'));
            $user->gender = $request->input('gender');
            $user->save();
            if (Input::file('profile_pic')) {
                // fetching picture name
                $name = Input::file('profile_pic')->getClientOriginalName();
                // fetching upload destination path
                $destinationPath = 'uploads/profilepic';
                // adding a random value to profile picture filename
                $fileName = rand(0000, 9999).'.'.str_replace(' ', '_', $name);
                // moving the picture to a destination folder
                Input::file('profile_pic')->move($destinationPath, $fileName);
                // saving filename to database
                $user->profile_pic = $fileName;
            }
            if ($request->get('mobile')) {
                $user->mobile = $request->get('mobile');
            } else {
                $user->mobile = null;
            }
            if ($user->save()) {
                return redirect()->back()->with('success', Lang::get('lang.Profile-Updated-sucessfully'));
            } else {
                return redirect()->back()->route('profile')->with('fails', Lang::get('lang.Profile-Updated-sucessfully'));
            }
        } catch (Exception $e) {
            return redirect()->back()->route('profile')->with('fails', $e->getMessage());
        }
    }

    /**
     *@category fucntion to check if mobile number is unqique or not
     *
     * @param string $mobile
     *
     * @return bool true(if mobile exists in users table)/false (if mobile does not exist in user table)
     */
    public function checkMobile($mobile)
    {
        if ($mobile) {
            $check = User::where('mobile', '=', $mobile)
                ->where('id', '<>', \Auth::user()->id)
                ->first();
            if (count($check) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Ticket page.
     *
     * @param type Help_topic $topic
     *
     * @return type Response
     */
    public function getTicket(Help_topic $topic)
    {
        $topics = $topic->get();

        return view('themes.default1.client.helpdesk.tickets.form', compact('topics'));
    }

    /**
     * getform.
     *
     * @param type Help_topic $topic
     *
     * @return type
     */
    public function getForm(Help_topic $topic)
    {
        if (\Config::get('database.install') == '%0%') {
            return \Redirect::route('licence');
        }
        if (System::first()->status == 1) {
            $topics = $topic->get();

            return view('themes.default1.client.helpdesk.form', compact('topics'));
        } else {
            return \Redirect::route('home');
        }
    }

    /**
     * Get my ticket.
     *
     * @param type Tickets       $tickets
     * @param type Ticket_Thread $thread
     * @param type User          $user
     *
     * @return type Response
     */
    public function getMyticket(CommonSettings $common_settings)
    {
        $user_ticket_status = $common_settings->select('status')
            ->where('option_name', '=', 'user_set_ticket_status')
            ->first();

        return view('themes.default1.client.helpdesk.mytickets', compact('user_ticket_status'));
    }

    /**
     * Get ticket-thread.
     *
     * @param type Ticket_Thread $thread
     * @param type Tickets       $tickets
     * @param type User          $user
     *
     * @return type Response
     */
    public function thread(Ticket_Thread $thread, Tickets $tickets, User $user)
    {
        $user_id = Auth::user()->id;
        //dd($user_id);
        /* get the ticket's id == ticket_id of thread  */
        $tickets = $tickets->where('user_id', '=', $user_id)->first();
        //dd($ticket);
        $thread = $thread->where('ticket_id', $tickets->id)->first();

        //dd($thread);
        // $tickets = $tickets->whereId($id)->first();
        return view('themes.default1.client.guest-user.view_ticket', compact('thread', 'tickets'));
    }

    /**
     * ticket Edit.
     *
     * @return
     */
    public function ticketEdit()
    {
    }

    /**
     * Post porfile password.
     *
     * @param type $id
     * @param type ProfilePassword $request
     *
     * @return type Response
     */
    public function postProfilePassword(ProfilePassword $request)
    {
        $user = Auth::user();
        //echo $user->password;
        if (Hash::check($request->input('old_password'), $user->getAuthPassword())) {
            $user->password = Hash::make($request->input('new_password'));

            try {
                $user->save();

                return redirect()->back()->with('success2', Lang::get('lang.password_updated_sucessfully'));
            } catch (Exception $e) {
                return redirect()->back()->with('fails2', $e->getMessage());
            }
        } else {
            return redirect()->back()->with('fails2', Lang::get('lang.password_was_not_updated_incorrect_old_password'));
        }
    }

    /**
     * Ticekt reply.
     *
     * @param type Ticket_Thread $thread
     * @param type TicketRequest $request
     *
     * @return type Response
     */
    public function reply(Ticket_Thread $thread, TicketRequest $request)
    {
        $thread->ticket_id = $request->input('ticket_ID');
        $thread->title = $request->input('To');
        $thread->user_id = Auth::user()->id;
        $thread->body = $request->input('reply_content');
        $thread->poster = 'user';
        $thread->save();
        $ticket_id = $request->input('ticket_ID');
        $tickets = Tickets::where('id', '=', $ticket_id)->first();
        $thread = Ticket_Thread::where('ticket_id', '=', $ticket_id)->first();

        return Redirect('thread/'.$ticket_id);
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
        $validator = \Validator::make($request->all(), [
            'email'         => 'required|email',
            'ticket_number' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput()
                            ->with('check', '1');
        }
        $Email = $request->input('email');
        $Ticket_number = $request->input('ticket_number');
        $ticket = Tickets::where('ticket_number', '=', $Ticket_number)->first();
        if ($ticket == null) {
            return \Redirect::route('form')->with('fails', Lang::get('lang.there_is_no_such_ticket_number'));
        } else {
            $userId = $ticket->user_id;
            $user = User::where('id', '=', $userId)->first();
            if ($user->role == 'user') {
                $username = $user->first_name;
            } else {
                $username = $user->first_name.' '.$user->last_name;
            }
            if ($user->email != $Email) {
                return \Redirect::route('form')->with('fails', Lang::get("lang.email_didn't_match_with_ticket_number"));
            } else {
                $code = $ticket->id;
                $code = \Crypt::encrypt($code);

                $company = $this->company();

                $this->PhpMailController->sendmail(
                    $from = $this->PhpMailController->mailfrom('1', '0'),
                    $to = ['name' => $username, 'email' => $user->email],
                    $message = ['subject' => 'Ticket link Request ['.$Ticket_number.']', 'scenario' => 'check-ticket'],
                    $template_variables = ['user' => $username, 'ticket_link_with_number' => \URL::route('check_ticket', $code)]
                );

                return \Redirect::back()
                                ->with('success', Lang::get('lang.we_have_sent_you_a_link_by_email_please_click_on_that_link_to_view_ticket'));
            }
        }
    }

    /**
     * get ticket email.
     *
     * @param type $id
     *
     * @return type
     */
    public function get_ticket_email($id, CommonSettings $common_settings)
    {
        $common_setting = $common_settings->select('status')
                ->where('option_name', '=', 'user_set_ticket_status')
                ->first();

        return view('themes.default1.client.helpdesk.ckeckticket', compact('id', 'common_setting'));
    }

    /**
     * get ticket status.
     *
     * @param type Tickets $ticket
     *
     * @return type
     */
    public function getTicketStat(Tickets $ticket)
    {
        return view('themes.default1.client.helpdesk.ckeckticket', compact('ticket'));
    }

    /**
     * get company.
     *
     * @return type
     */
    public function company()
    {
        $company = Company::Where('id', '=', '1')->first();
        if ($company->company_name == null) {
            $company = 'Support Center';
        } else {
            $company = $company->company_name;
        }

        return $company;
    }

    public function resendOTP(OtpVerifyRequest $request)
    {
        if (\Schema::hasTable('sms')) {
            $sms = DB::table('sms')->get();
            if (count($sms) > 0) {
                event(new \App\Events\LoginEvent($request));

                return 1;
            }
        } else {
            return 'Plugin has not been setup successfully.';
        }
    }

    public function verifyOTP()
    {
        // dd(Input::all());
        // $user = User::select('id', 'mobile', 'user_name')->where('email', '=', $request->input('email'))->first();
        $otp = Otp::select('otp', 'updated_at')->where('user_id', '=', Input::get('u_id'))
                                ->first();
        if ($otp != null) {
            $otp_length = strlen(Input::get('otp'));
            if ($otp_length == 6 && !preg_match('/[a-z]/i', Input::get('otp'))) {
                $otp2 = Hash::make(Input::get('otp'));
                $date1 = date_format($otp->updated_at, 'Y-m-d h:i:sa');
                $date2 = date('Y-m-d h:i:sa');
                $time1 = new DateTime($date2);
                $time2 = new DateTime($date1);
                $interval = $time1->diff($time2);
                if ($interval->i > 10 || $interval->h > 0) {
                    $message = Lang::get('lang.otp-expired');

                    return $message;
                } else {
                    if (Hash::check(Input::get('otp'), $otp->otp)) {
                        Otp::where('user_id', '=', Input::get('u_id'))
                            ->update(['otp' => '']);

                        // User::where('id', '=', $user->id)
                        //     ->update(['active' => 1]);
                        // $this->openTicketAfterVerification($user->id);
                        return 1;
                    } else {
                        $message = Lang::get('lang.otp-not-matched');

                        return $message;
                    }
                }
            } else {
                $message = Lang::get('lang.otp-invalid');

                return $message;
            }
        } else {
            $message = Lang::get('lang.otp-not-matched');

            return $message;
        }
    }

    public function sync()
    {
        try {
            $provider = $this->getProvider();
            $this->changeRedirect();
            $users = Socialite::driver($provider)->user();
            $this->forgetSession();
            $user['provider'] = $provider;
            $user['social_id'] = $users->id;
            $user['name'] = $users->name;
            $user['email'] = $users->email;
            $user['username'] = $users->nickname;
            $user['avatar'] = $users->avatar;

            return redirect('client-profile')->with('success', 'Additional informations fetched');
        } catch (Exception $ex) {
            dd($ex);

            return redirect('client-profile')->with('fails', $ex->getMessage());
        }
    }

    public function getProvider()
    {
        $provider = \Session::get('provider');

        return $provider;
    }

    public function changeRedirect()
    {
        $provider = \Session::get('provider');
        $url = \Session::get($provider.'redirect');
        \Config::set("services.$provider.redirect", $url);
    }

    public function forgetSession()
    {
        $provider = $this->getProvider();
        \Session::forget('provider');
        \Session::forget($provider.'redirect');
    }

    public function checkArray($key, $array)
    {
        $value = '';
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
        }

        return $value;
    }

    public function updateUser($user = [])
    {
        $userid = \Auth::user()->id;
        $useremail = \Auth::user()->email;
        $email = $this->checkArray('email', $user); //$user['email'];
        if ($email !== '' && $email !== $useremail) {
            throw new Exception('Sorry! your current email and '.ucfirst($user['provider']).' email is different so system can not sync');
        }
        $this->update($userid, $user);
    }

    public function update($userid, $user, $provider)
    {
        $email = $this->checkArray('email', $user);
        $this->deleteUser($userid, $user, $provider);
        $this->insertAdditional($userid, $provider, $user);
        $this->changeEmail($email);
    }

    public function deleteUser($userid, $user, $provider)
    {
        $info = new \App\UserAdditionalInfo();
        $infos = $info->where('owner', $userid)->where('service', $provider)->get();
        if ($infos->count() > 0 && count($user) > 0) {
            foreach ($infos as $key => $detail) {
                //if ($user[$key] !== $detail->$key) {
                $detail->delete();
                //}
            }
        }
    }

    public function insertAdditional($id, $provider, $user = [])
    {
        $info = new \App\UserAdditionalInfo();
        if (count($user) > 0) {
            foreach ($user as $key => $value) {
                $info->create([
                    'owner'   => $id,
                    'service' => $provider,
                    'key'     => $key,
                    'value'   => $value,
                ]);
            }
        }
    }

    public function changeEmail($email)
    {
        $user = \Auth::user();
        if ($user && $email && !$user->email) {
            $user->email = $email;
            $user->save();
        }
    }
}
