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
            $date = date('Y-m-d H:i:s');
            $this->validate($request, ['email' => 'required']);
            event('reset.password', []);
            $user = User::where('email', '=', $request->all('email'))->orWhere('mobile', '=', $request->all('email'))->first();
            if (isset($user)) {
                $user1 = $user->email;
                //gen new code and pass
                $code = Str::random(60);
                $password_reset_table = \DB::table('password_resets')->where('email', '=', $user->email)->first();
                if (isset($password_reset_table)) {
                    $password_reset_table = \DB::table('password_resets')->where('email', '=', $user->email)->update(['token' => $code, 'created_at' => $date]);
                    // $password_reset_table->token = $code;
                    // $password_reset_table->update(['token' => $code]);
                } else {
                    $create_password_reset = \DB::table('password_resets')->insert(['email' => $user->email, 'token' => $code, 'created_at' => $date]);
                }
                $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $user->user_name, 'email' => $user->email], $message = ['subject' => 'Your Password Reset Link', 'scenario' => 'reset-password'], $template_variables = ['user' => $user->first_name, 'email_address' => $user->email, 'password_reset_link' => url('password/reset/'.$code)], true);
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
        // dd($credentials);
        $email = $credentials['email'];
        $password = $credentials['password'];
        $token = $credentials['token'];
        $response = 'fails';
        $password_tokens = \DB::table('password_resets')->where('email', '=', $email)->first();
        if ($password_tokens) {
            if ($password_tokens->token == $token) {
                $users = new User();
                $user = $users->where('email', $email)->first();
                if ($user) {
                    $user->password = \Hash::make($password);
                    $user->save();
                    $response = 'success';
                } else {
                    $response = 'fails';
                }
            }
        }
        if ($response == 'success') {
            return redirect('/auth/login')->with('status', Lang::get('lang.password-reset-successfully'));
        } else {
            return redirect('/home')->with('fails', Lang::get('lang.password-can-not-reset'));
        }
    }
}
