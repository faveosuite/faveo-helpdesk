<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        $this->middleware('guest');
        $this->PhpMailController = $PhpMailController;
    }

    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        try {
            $date = date('Y-m-d H:i:s');
            $this->validate($request, ['email' => 'required']);
            event('reset.password', []);
            $user = User::where('email', '=', $request->all('email'))->orWhere('mobile', '=', $request->all('email'))->first();
            if (isset($user)) {
                $user1 = $user->email;
                //gen new code and pass
                $code = Str::random(60);
                $password_reset_table = DB::table('password_resets')->where('email', '=', $user->email)->first();
                if (isset($password_reset_table)) {
                    $password_reset_table = DB::table('password_resets')->where('email', '=', $user->email)->update(['token' => $code, 'created_at' => $date]);
                    // $password_reset_table->token = $code;
                    // $password_reset_table->update(['token' => $code]);
                } else {
                    $create_password_reset = DB::table('password_resets')->insert(['email' => $user->email, 'token' => $code, 'created_at' => $date]);
                }
                $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $user->user_name, 'email' => $user->email], $message = ['subject' => 'Your Password Reset Link', 'scenario' => 'reset-password'], $template_variables = ['user' => $user->first_name, 'email_address' => $user->email, 'password_reset_link' => url('password/reset/'.$code.'?email='.$user->email)], true);
                if ($user->mobile != '' && $user->mobile != null) {
                    if ($user->first_name) {
                        $name = $user->first_name;
                    } else {
                        $name = $user->user_name;
                    }
                    $value = [
                        'url'    => url('password/reset/'.$code),
                        'name'   => $name,
                        'mobile' => $user->mobile,
                        'code'   => $user->country_code, ];
                    event('reset.password2', [$value]);
                }

                return redirect()->back()->with('status', Lang::get('lang.we_have_e-mailed_your_password_reset_link'));
            } else {
                return redirect()->back()->with('fails', Lang::get("lang.we_can't_find_a_user_with_that_e-mail_address"));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }
}
