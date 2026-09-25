<?php

namespace App\Http\Controllers\Auth;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\User;
// request
use Illuminate\Foundation\Auth\ResetsPasswords;
// model
// classes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Lang;

/**
 * PasswordController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class PasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        $this->PhpMailController = $PhpMailController;
        $this->middleware('guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return Response
     */
    public function getEmail()
    {
        return view('auth.password');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return Response
     */
    public function postEmail(Request $request)
    {
        try {
            $this->validate($request, ['email' => 'required']);
            event('reset.password', []);
            $user = User::where('email', '=', $request->input('email'))->orWhere('mobile', '=', $request->input('email'))->first();
            if (!isset($user)) {
                return redirect()->back()->with('fails', Lang::get("lang.we_can't_find_a_user_with_that_e-mail_address"));
            }

            // See ForgotPasswordController::sendResetLinkEmail() for why this
            // delegates to Laravel's PasswordBroker instead of writing
            // password_resets directly - this method mirrors it, kept in sync
            // since nothing routes to this one but it's reachable the same way.
            $response = Password::broker()->sendResetLink(
                ['email' => $user->email],
                function ($user, $token) {
                    $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $user->user_name, 'email' => $user->email], $message = ['subject' => 'Your Password Reset Link', 'scenario' => 'reset-password'], $template_variables = ['user' => $user->first_name, 'email_address' => $user->email, 'password_reset_link' => url('password/reset/'.$token)], true);
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

            return redirect()->back()->with('fails', Lang::get('lang.password-can-not-reset'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $this->validate(
            $request,
            $this->rules(),
            $this->validationErrorMessages()
        );
        $credentials = $this->credentials($request);

        // Password::broker()->reset() is what used to be the hand-rolled lookup
        // against password_resets below: it hashes/compares the token, rejects
        // one older than auth.passwords.users.expire, and - unlike the old
        // code - deletes the token row itself once $callback returns, so it
        // cannot be replayed. $callback only runs once that has all passed, and
        // is just where the actual password gets set, same as before.
        $response = Password::broker()->reset($credentials, function ($user, $password) {
            $user->password = \Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();
        });

        if ($response === Password::PASSWORD_RESET) {
            return redirect('/auth/login')->with('status', Lang::get('lang.password-reset-successfully'));
        }

        return redirect('/home')->with('fails', Lang::get('lang.password-can-not-reset'));
    }
}
