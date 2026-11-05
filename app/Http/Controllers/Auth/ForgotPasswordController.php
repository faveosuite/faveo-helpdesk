<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Password;

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
        $this->validate($request, ['email' => 'required']);

        try {
            event('reset.password', []);
            $user = User::where('email', '=', $request->input('email'))->orWhere('mobile', '=', $request->input('email'))->first();
            if (!isset($user)) {
                return redirect()->back()->with('fails', Lang::get("lang.we_can't_find_a_user_with_that_e-mail_address"));
            }

            // Token generation, hashing, expiry (auth.passwords.users.expire) and
            // single-use invalidation are all handled by Laravel's own
            // PasswordBroker - the same one PasswordController::reset() validates
            // against - instead of the hand-rolled, plaintext/never-expiring
            // password_resets handling this used to do directly. The $callback
            // below is what keeps this app's own mail template and the SMS
            // branch: sendResetLink() calls it with the plaintext $token instead
            // of firing Laravel's default notification.
            $response = Password::broker()->sendResetLink(
                ['email' => $user->email],
                function ($user, $token) {
                    $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $user->user_name, 'email' => $user->email], $message = ['subject' => 'Your Password Reset Link', 'scenario' => 'reset-password'], $template_variables = ['user' => $user->first_name, 'email_address' => $user->email, 'password_reset_link' => url('password/reset/'.$token.'?email='.$user->email)], true);
                    if ($user->mobile != '' && $user->mobile != null) {
                        $name = $user->first_name ?: $user->user_name;
                        $value = [
                            'url'    => url('password/reset/'.$token),
                            'name'   => $name,
                            'mobile' => $user->mobile,
                            'code'   => $user->country_code, ];
                        event('reset.password2', [$value]);
                    }
                }
            );

            if ($response === Password::RESET_LINK_SENT) {
                return redirect()->back()->with('status', Lang::get('lang.we_have_e-mailed_your_password_reset_link'));
            }

            // Most likely Password::RESET_THROTTLED: a link was already issued to
            // this address within auth.passwords.users.throttle seconds.
            return redirect()->back()->with('fails', Lang::get('lang.password-can-not-reset'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }
}
