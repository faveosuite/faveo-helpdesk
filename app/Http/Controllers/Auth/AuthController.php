<?php

namespace App\Http\Controllers\Auth;

// controllers
use App\Http\Controllers\Admin\helpdesk\SocialMedia\SocialMediaController;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\LoginRequest;
use App\Http\Requests\helpdesk\OtpVerifyRequest;
use App\Http\Requests\helpdesk\RegisterRequest;
use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Settings\Plugin;
use App\Model\helpdesk\Settings\Security;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
// classes
use App\Model\helpdesk\Utility\Otp;
use App\User;
use Auth;
use DateTime;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lang;
use Socialite;

/**
 * ---------------------------------------------------
 * AuthController
 * ---------------------------------------------------
 * This controller handles the registration of new users, as well as the
 * authentication of existing users. By default, this controller uses
 * a simple trait to add these behaviors. Why don't you explore it?
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class AuthController extends Controller
{
    protected $maxLoginAttempts = 5;
    protected $lockoutTime = 60;

    use \Illuminate\Foundation\Auth\ThrottlesLogins;
    /* to redirect after login */

    // if auth is agent
    protected $redirectTo = '/dashboard';

    // if auth is user
    protected $redirectToUser = '/profile';

    /* Direct After Logout */
    protected $redirectAfterLogout = '/';

    protected $loginPath = '/auth/login';

    protected $social;

    /**
     * Create a new authentication controller instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard     $auth
     * @param \Illuminate\Contracts\Auth\Registrar $registrar
     *
     * @return void
     */
    public function __construct()
    {
        $this->PhpMailController = new PhpMailController();
        $social = new SocialMediaController();
        $social->configService();
        $this->middleware('guest', ['except' => ['getLogout', 'verifyOTP', 'redirectToProvider']]);
    }

    public function loginUsername()
    {
        return 'email';
    }

    public function redirectToProvider($provider, $redirect = '')
    {
        if ($redirect !== '') {
            $this->setSession($provider, $redirect);
        }
        //dd(\Config::get('services'));
        $s = Socialite::driver($provider)->redirect();

        //dd('dscd');
        return $s;
    }

    public function handleProviderCallback($provider)
    {
        try {
            //notice we are not doing any validation, you should do it
            $this->changeRedirect();

            $user = Socialite::driver($provider)->user();
            if ($user) {
                // stroing data to our use table and logging them in
                $username = $user->getEmail();
                $first_name = $user->getName();
                if ($user->nickname) {
                    $username = $user->nickname;
                }
                if (!$first_name) {
                    $first_name = $username;
                }
                $data = [
                    'first_name' => $first_name,
                    'email'      => $user->getEmail(),
                    'user_name'  => $username,
                    'role'       => 'user',
                    'active'     => 1,
                ];
                $user = User::where('email', $data['email'])->first();
                if (!$user) {
                    $user = User::where('user_name', $data['user_name'])->first();
                }
                if (!$user) {
                    $user = User::firstOrCreate($data);
                }
                Auth::login($user);
            }

            //after login redirecting to home page
            return redirect('/');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get the form for registration.
     *
     * @return type Response
     */
    public function getRegister(CommonSettings $settings)
    {
        // Event for login
        $settings = $settings->select('status')->where('option_name', '=', 'send_otp')->first();
        $email_mandatory = $settings->select('status')->where('option_name', '=', 'email_mandatory')->first();
        //dd($settings->status);
        event(new \App\Events\FormRegisterEvent());
        if (Auth::user()) {
            if (Auth::user()->role == 'admin' || Auth::user()->role == 'agent') {
                return \Redirect::route('dashboard');
            } elseif (Auth::user()->role == 'user') {
                // return view('auth.register');
            }
        } else {
            return view('auth.register', compact('settings', 'email_mandatory'));
        }
    }

    /**
     * Post registration form.
     *
     * @param type User            $user
     * @param type RegisterRequest $request
     *
     * @return type Response
     */
    public function postRegister(User $user, RegisterRequest $request, $api = false)
    {
        //dd($request->all());
        try {
            $request_array = $request->input();
            $password = Hash::make($request->input('password'));
            $user->password = $password;
            $name = $request->input('full_name');
            $user->first_name = $name;
            if ($request_array['email'] == '') {
                $user->email = null;
            } else {
                $user->email = $request->input('email');
            }
            if (!checkArray('mobile', $request_array)) {
                $user->mobile = null;
            } else {
                $user->mobile = $request->input('mobile');
            }
            if (!checkArray('code', $request_array)) {
                $user->country_code = 0;
            } else {
                $user->country_code = $request->input('code');
            }
            if (checkArray('username', $request_array)) {
                $user->user_name = checkArray('username', $request_array);
            } else {
                $user->user_name = $request->input('email');
            }
            $user->role = 'user';
            $code = Str::random(60);
            $user->remember_token = $code;
            $user->save();
            $message12 = '';
            $settings = CommonSettings::select('status')->where('option_name', '=', 'send_otp')->first();
            $sms = Plugin::select('status')->where('name', '=', 'SMS')->first();
            // Event for login
            event(new \App\Events\LoginEvent($request));
            if ($request->input('email') !== '') {
                $var = $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $name, 'email' => $request->input('email')], $message = ['subject' => null, 'scenario' => 'registration'], $template_variables = ['user' => $name, 'email_address' => $request->input('email'), 'password_reset_link' => url('account/activate/'.$code)]);
            }
            if ($settings->status == 1 || $settings->status == '1') {
                if (count($sms) > 0) {
                    if ($sms->status == 1 || $sms->status == '1') {
                        $message12 = Lang::get('lang.activate_your_account_click_on_Link_that_send_to_your_mail_and_moble');
                    } else {
                        $message12 = Lang::get('lang.activate_your_account_click_on_Link_that_send_to_your_mail_sms_plugin_inactive_or_not_setup');
                    }
                } else {
                    if ($request->input('email') !== '') {
                        $message12 = Lang::get('lang.activate_your_account_click_on_Link_that_send_to_your_mail');
                    } else {
                        $message12 = Lang::get('lang.account-created-contact-admin-as-we-were-not-able-to-send-opt');
                    }
                }
            } else {
                $message12 = Lang::get('lang.activate_your_account_click_on_Link_that_send_to_your_mail');
            }
            if ($api == true) {
                return ['message' => $message12, 'user' => $user->toArray()];
            }

            return redirect('home')->with('success', $message12);
        } catch (\Exception $e) {
            if ($api == true) {
                throw new \Exception($e->getMessage());
            }

            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Function to activate account.
     *
     * @param type $token
     *
     * @return type redirect
     */
    public function accountActivate($token)
    {
        $user = User::where('remember_token', '=', $token)->first();
        if ($user) {
            $user->active = 1;
            $user->remember_token = null;
            $user->save();
            $this->openTicketAfterVerification($user->id);

            return redirect('/auth/login')->with('status', 'Acount activated. Login to start');
        } else {
            return redirect('/auth/login')->with('fails', 'Invalid Token');
        }
    }

    /**
     * Get mail function.
     *
     * @param type $token
     * @param type User $user
     *
     * @return type Response
     */
    public function getMail($token, User $user)
    {
        $user = $user->where('remember_token', $token)->where('active', 0)->first();
        if ($user) {
            $user->active = 1;
            $user->save();

            return redirect('auth/login');
        } else {
            return redirect('auth/login');
        }
    }

    /**
     * Get login page.
     *
     * @return type Response
     */
    public function getLogin()
    {
        $directory = base_path();
        if (file_exists($directory.DIRECTORY_SEPARATOR.'.env')) {
            if (Auth::user()) {
                if (Auth::user()->role == 'admin' || Auth::user()->role == 'agent') {
                    return \Redirect::route('dashboard');
                } elseif (Auth::user()->role == 'user') {
                    return \Redirect::route('home');
                }
            } else {
                return view('auth.login');
            }
        } else {
            return Redirect::route('licence');
        }
    }

    /**
     * Post of login page.
     *
     * @param type LoginRequest $request
     *
     * @return type Response
     */
    public function postLogin(LoginRequest $request)
    {
        try {
            // dd($request->input());
            event('auth.login.event', []); //added 5/5/2016

            $throttles = $this->isUsingThrottlesLoginsTrait();

            if ($throttles && $this->hasTooManyLoginAttempts($request)) {
                return $this->sendLockoutResponse($request);
            }
            $credentials = $this->getCredentials($request);

            if (Auth::attempt($credentials, $request->has('remember'))) {
                return $this->handleUserWasAuthenticated($request, $throttles);
            }

            if ($throttles) {
                $this->incrementLoginAttempts($request);
            }

            $check_active = User::where('email', '=', $request->input('email'))->orwhere('user_name', '=', $request->input('email'))->first();
            if (!$check_active) { //check if user exists or not
                //if user deos not exist then return back with error that user is not registered
                return redirect()->back()
                                ->withInput($request->only('email', 'remember'))
                                ->withErrors([
                                    'email'       => $this->getFailedLoginMessage(),
                                    'password'    => $this->getFailedLoginMessage(),
                                ])->with(['error' => Lang::get('lang.not-registered'),
                                    'referer'     => $referer, ]);
            }

            //if user exists
            $settings = CommonSettings::select('status')->where('option_name', '=', 'send_otp')->first();

            if ($settings->status == '1' || $settings->status == 1) { // check for otp verification setting
                // setting is enabled
                $sms = Plugin::select('status')->where('name', '=', 'SMS')->first();
                if ($sms) { //check sms plugin installed or not
                    // plugin is installed
                    if ($sms->status == 1 || $sms->status === '1') { //check plugin is active or not
                        // plugin is active
                        if (!$check_active->active) { //check account is active or not
                            // account is not active show verify otp window
                            if ($check_active->mobile) { //check user has mobile or not
                                // user has mobile number return verify OTP screen
                                return \Redirect::route('otp-verification')
                                                ->withInput($request->input())
                                                ->with(['values' => $request->input(),
                                                    'referer'    => $referer,
                                                    'name'       => $check_active->first_name,
                                                    'number'     => $check_active->mobile,
                                                    'code'       => $check_active->country_code, ]);
                            } else {
                                goto a; //attenmpt login  (be careful while using goto statements)
                            }
                        } else {
                            goto a; //attenmpt login  (be careful while using goto statements)
                        }
                    } else {
                        goto a; //attenmpt login  (be careful while using goto statements)
                    }
                } else {
                    goto a; //attenmpt login  (be careful while using goto statements)
                }
            } else {
                // setting is disabled
                a: if (!$check_active->active) { //check account is active or not
                    // if accoutn is not active return back with error message that account is inactive
                    return redirect()->back()
                                    ->withInput($request->only('email', 'remember'))
                                    ->withErrors([
                                        'email'       => $this->getFailedLoginMessage(),
                                        'password'    => $this->getFailedLoginMessage(),
                                    ])->with(['error' => Lang::get('lang.this_account_is_currently_inactive'),
                                        'referer'     => $referer, ]);
                } else {
                    // try login
                    $loginAttempts = 1;
                    // If session has login attempts, retrieve attempts counter and attempts time
                    if (\Session::has('loginAttempts')) {
                        $loginAttempts = \Session::get('loginAttempts');
                        $loginAttemptTime = \Session::get('loginAttemptTime');
                        $this->addLoginAttempt($value, $usernameinput);
                        // $credentials = $request->only('email', 'password');
                        $usernameinput = $request->input('email');
                        $password = $request->input('password');
                        $field = filter_var($usernameinput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';
                        // If attempts > 3 and time < 10 minutes
                        if ($loginAttempts > $security->backlist_threshold && (time() - $loginAttemptTime <= ($security->lockout_period * 60))) {
                            return redirect()->back()->withErrors('email', 'incorrect email')->with('error', $security->lockout_message);
                        }
                        // If time > 10 minutes, reset attempts counter and time in session
                        if (time() - $loginAttemptTime > ($security->lockout_period * 60)) {
                            \Session::put('loginAttempts', 1);
                            \Session::put('loginAttemptTime', time());
                        }
                    } else { // If no login attempts stored, init login attempts and time
                        \Session::put('loginAttempts', $loginAttempts);
                        \Session::put('loginAttemptTime', time());
                        $this->clearLoginAttempts($value, $usernameinput);
                    }
                    // If auth ok, redirect to restricted area
                    \Session::put('loginAttempts', $loginAttempts + 1);
                    if (Auth::Attempt([$field => $usernameinput, 'password' => $password], $request->has('remember'))) {
                        if (Auth::user()->role == 'user') {
                            if ($request->input('referer')) {
                                return \Redirect::route($request->input('referer'));
                            }

                            // return \Redirect::route('/');
                            return redirect()->intended($this->redirectPath());
                        }

                        return redirect()->intended($this->redirectPath());
                    }
                }
            }

            return redirect()->back()
                            ->withInput($request->only('email', 'remember'))
                            ->withErrors([
                                'email'       => $this->getFailedLoginMessage(),
                                'password'    => $this->getFailedLoginMessage(),
                            ])->with(['error' => Lang::get('lang.invalid'),
                                'referer'     => $referer, ]);
            // Increment login attempts
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Add login attempt.
     *
     * @param type IPaddress $value
     *
     * @return type Response
     */
    public function addLoginAttempt($value, $field)
    {
        $result = DB::table('login_attempts')->where('IP', '=', $value)->first();
        $data = $result;
        $security = Security::whereId('1')->first();
        $apt = $security->backlist_threshold;
        if ($data) {
            $attempts = $data->Attempts + 1;
            if ($attempts == $apt) {
                //                $result = DB::select('UPDATE login_attempts SET Attempts='.$attempts.", LastLogin=NOW() WHERE IP = '$value' OR User = '$field'");
                $result = DB::table('login_attempts')->where('IP', '=', $value)->orWhere('User', '=', $field)->update(['Attempts' => $attempts, 'LastLogin' => date('Y-m-d H:i:s')]);
            } else {
                $result = DB::table('login_attempts')->where('IP', '=', $value)->orWhere('User', '=', $field)->update(['Attempts' => $attempts]);
                // $result = DB::select("UPDATE login_attempts SET Attempts=".$attempts." WHERE IP = '$value' OR User = '$field'");
            }
        } else {
            //            $result = DB::select("INSERT INTO login_attempts (Attempts,User,IP,LastLogin) values (1,'$field','$value', NOW())");
            $result = DB::table('login_attempts')->update(['Attempts' => 1, 'User' => $field, 'IP' => $value, 'LastLogin' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Clear login attempt.
     *
     * @param type IPaddress $value
     *
     * @return type Response
     */
    public function clearLoginAttempts($value, $field)
    {
        $data = DB::table('login_attempts')->where('IP', '=', $value)->orWhere('User', '=', $field)->update(['attempts' => '0']);

        return $data;
    }

    /**
     * Confiem IP.
     *
     * @param type IPaddress $value
     *
     * @return type Response
     */
    public function confirmIPAddress($value, $field)
    {
        $security = Security::whereId('1')->first();
        $time = $security->lockout_period;
        $max_attempts = $security->backlist_threshold;
        $table = 'login_attempts';
        $result = DB::table($table)
            ->select('Attempts', DB::raw('(CASE when LastLogin is not NULL and DATE_ADD(LastLogin, INTERVAL ? MINUTE) > NOW() then 1 else 0 end) as Denied'))
            ->where(function ($query) use ($value, $field) {
                $query->where('IP', '=', $value)
                    ->orWhere('User', '=', $field);
            })
            ->setBindings([$time, $value, $field])
            ->get();
        $data = $result;
        //Verify that at least one login attempt is in database
        if (count($data) == 0) {
            return 0;
        }
        if ($data[0]->Attempts >= $max_attempts) {
            if ($data[0]->Denied == 1) {
                return 1;
            } else {
                $this->clearLoginAttempts($value, $field);

                return 0;
            }
        }

        return 0;
    }

    /**
     * Get Failed login message.
     *
     * @return type string
     */
    protected function getFailedLoginMessage()
    {
        return Lang::get('lang.this_field_do_not_match_our_records');
    }

    /**
     * @category function to show verify OTP page
     *
     * @param null
     *
     * @return response|view
     */
    public function getVerifyOTP()
    {
        if (\Session::has('values')) {
            return view('auth.otp-verify');
        } else {
            return redirect('auth/login');
        }
    }

    /**
     * @category function to verify OTP
     *
     * @param $request
     *
     * @return int|string
     */
    public function verifyOTP(LoginRequest $request)
    {
        $user = User::select('id', 'mobile', 'user_name')->where('email', '=', $request->input('email'))
                        ->orWhere('user_name', '=', $request->input('email'))->first();
        $otp_length = strlen($request->input('otp'));
        if (!\Schema::hasTable('user_verification')) {
            $message = Lang::get('lang.opt-can-not-be-verified');
        } else {
            $otp = Otp::select('otp', 'updated_at')->where('user_id', '=', $user->id)
                    ->first();
            if ($otp != null) {
                if ($otp_length == 6 && !preg_match('/[a-z]/i', $request->input('otp'))) {
                    $otp2 = Hash::make($request->input('otp'));
                    $date1 = date_format($otp->updated_at, 'Y-m-d h:i:sa');
                    $date2 = date('Y-m-d h:i:sa');
                    $time1 = new DateTime($date2);
                    $time2 = new DateTime($date1);
                    $interval = $time1->diff($time2);
                    if ($interval->i > 30 || $interval->h > 0) {
                        $message = Lang::get('lang.otp-expired');
                    } else {
                        if (Hash::check($request->input('otp'), $otp->otp)) {
                            Otp::where('user_id', '=', $user->id)
                                    ->update(['otp' => '']);
                            User::where('id', '=', $user->id)
                                    ->update(['active' => 1]);
                            $this->openTicketAfterVerification($user->id);

                            return $this->postLogin($request);
                        } else {
                            $message = Lang::get('lang.otp-not-matched');
                        }
                    }
                } else {
                    $message = Lang::get('lang.otp-invalid');
                }
            } else {
                $message = Lang::get('lang.otp-not-matched');
            }
        }

        return \Redirect::route('otp-verification')
                        ->withInput($request->input())
                        ->with(['values' => $request->input(),
                            'number'     => $user->mobile,
                            'name'       => $user->user_name,
                            'fails'      => $message, ]);
    }

    public function resendOTP(OtpVerifyRequest $request)
    {
        if (!\Schema::hasTable('user_verification') || !\Schema::hasTable('sms')) {
            $message = Lang::get('lang.opt-can-not-be-verified');

            return $message;
        } else {
            $sms = DB::table('sms')->get();
            if (count($sms) > 0) {
                event(new \App\Events\LoginEvent($request));

                return 1;
            } else {
                $message = Lang::get('lang.opt-can-not-be-verified');

                return $message;
            }
        }
    }

    /**
     * @category function to change ticket status when user verifies his account
     *
     * @param int $id => user_id
     *
     * @return null
     *
     * @author manish.verma@ladybirdweb.com
     */
    public function openTicketAfterVerification($id)
    {
        // dd($id);
        $ticket = Tickets::select('id')
                ->where(['user_id' => $id, 'status' => 6])
                ->get();
        Tickets::where(['user_id' => $id, 'status' => 6])
                ->update(['status' => 1]);
        if ($ticket != null) {
            foreach ($ticket as $value) {
                $ticket_id = $value->id;
                Ticket_Thread::where('ticket_id', '=', $ticket_id)
                        ->update(['updated_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function changeRedirect()
    {
        $provider = \Session::get('provider');
        $url = \Session::get($provider.'redirect');
        \Config::set("services.$provider.redirect", $url);
    }

    public function setSession($provider, $redirect)
    {
        $url = url($redirect);
        \Session::set('provider', $provider);
        \Session::set($provider.'redirect', $url);
        $this->changeRedirect();
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout(Request $request)
    {
        event('user.logout', []);
        $login = new LoginController();

        return $login->logout($request);
    }

    public function redirectPath()
    {
        $auth = Auth::user();
        if ($auth && $auth->role != 'user') {
            return 'dashboard';
        } else {
            return '/';
        }
    }
}
