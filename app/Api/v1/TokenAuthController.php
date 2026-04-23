<?php

namespace App\Api\v1;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * -----------------------------------------------------------------------------
 * Token authenticate Controller
 * -----------------------------------------------------------------------------.
 *
 *
 * @author Vijay Sebastian <vijay.sebastian@ladybirdweb.com>
 * @copyright (c) 2016, Ladybird Web Solution
 *
 * @name Faveo HELPDESK
 *
 * @version v1
 */
class TokenAuthController extends Controller
{
    public $PhpMailController;

    public function __construct()
    {
        $this->middleware('api');
        $this->middleware('jwt.authOveride')->only('getAuthenticatedUser');
        $PhpMailController = new PhpMailController();
        $this->PhpMailController = $PhpMailController;
    }

    /**
     * Authenticating user with username and password and retuen token.
     *
     * @param Request $request
     *
     * @return type json
     */
    public function authenticate(Request $request)
    {
        $usernameinput = $request->input('username');
        $password = $request->input('password');
        $field = filter_var($usernameinput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';

        //$credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt([$field => $usernameinput, 'password' => $password, 'active' => 1])) {
                return response()->json(['error' => 'invalid_credentials', 'status_code' => 401]);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token', 500]);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }

        $user_id = \Auth::user();

        // if no errors are encountered we can return a JWT
        return response()->json(compact('token', 'user_id'));
    }

    /**
     * Get the user details from token.
     *
     * @return type json
     */
    public function getAuthenticatedUser()
    {
        //dd(JWTAuth::parseToken()->authenticate());
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found', 404]);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired', $e->getStatusCode()]);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid', $e->getStatusCode()]);
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent', $e->getStatusCode()]);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }

        //dd($user);
        return response()->json(compact('user'));
    }

    /**
     * Register a user with username and password.
     *
     * @param Request $request
     *
     * @return type json
     */
//    public function register(Request $request)
//    {
//        try {
//            $v = \Validator::make($request->all(), [
//                        'email'    => 'required|email|unique:users',
//                        'password' => 'required',
//            ]);
//            if ($v->fails()) {
//                $error = $v->errors();
//
//                return response()->json(compact('error'));
//            }
//            $newuser = $request->all();
//            $password = Hash::make($request->input('password'));
//
//            $newuser['password'] = $password;
//
//            return User::create($newuser);
//        } catch (\Exception $e) {
//            $error = $e->getMessage();
//
//            return response()->json(compact('error'));
//        }
//    }

    /**
     * verify the url is existing or not.
     *
     * @return type json
     */
    public function checkUrl(Request $request)
    {
        try {
            $v = \Validator::make($request->all(), [
                'url' => 'required|url',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }

            $url = $this->request->input('url');
            $url = $url.'/api/v1/helpdesk/check-url';
        } catch (Exception $ex) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $v = \Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }

            $date = date('Y-m-d H:i:s');
            $user = User::where('email', '=', $request->only('email'))->first();
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

                $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $user->user_name, 'email' => $user->email], $message = ['subject' => 'Your Password Reset Link', 'scenario' => 'reset-password'], $template_variables = ['user' => $user->user_name, 'email_address' => $user->email, 'password_reset_link' => url('password/reset/'.$code)]);
                $result = 'We have e-mailed your password reset link!';

                return response()->json(compact('result'));
            }
        } catch (Exception $ex) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }
}
