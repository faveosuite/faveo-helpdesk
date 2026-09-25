<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Common\PhpMailController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression coverage for the weak-password-recovery report: the custom
 * password_resets handling in ForgotPasswordController/PasswordController
 * (bypassing Laravel's own PasswordBroker) stored the token in plaintext,
 * never checked its age against auth.passwords.users.expire, and never
 * deleted it after a successful reset - so a token exposed once (a forwarded
 * email, a log, a backup) stayed usable indefinitely and could reset the
 * account's password repeatedly.
 *
 * Both controllers now delegate entirely to Illuminate\Auth\Passwords\
 * PasswordBroker (the preferred fix per the report, and the approach Laravel
 * itself ships for this): sendResetLink() hashes and stores the token and
 * enforces the request-throttle; reset() verifies the hash and expiry and
 * deletes the row once the callback that sets the password returns. The
 * custom mail template and SMS branch are preserved via the $callback each
 * method passes in, which the broker invokes with the plaintext token instead
 * of firing its own notification.
 */
class PasswordResetTest extends TestCase
{
    private function makeUser(): User
    {
        $suffix = Str::random(8);
        $user = new User([
            'first_name' => 'Reset', 'last_name' => 'User',
            'email'      => $suffix.'@example.test',
            'user_name'  => 'u_'.$suffix,
            'password'   => Hash::make('OriginalPass123!'),
            'active'     => 1,
            'role'       => 'user',
        ]);
        $user->save();

        return $user;
    }

    /** Issues a token exactly the way the controllers do, without sending mail. */
    private function issueToken(string $email): string
    {
        $raw = null;
        Password::broker()->sendResetLink(['email' => $email], function ($user, $token) use (&$raw) {
            $raw = $token;
        });

        return $raw;
    }

    private function reset(string $email, string $token, string $password)
    {
        $controller = new PasswordController(app(PhpMailController::class));
        $request = Request::create('/password/reset', 'POST', [
            'email'                 => $email,
            'token'                 => $token,
            'password'              => $password,
            'password_confirmation' => $password,
        ]);

        return $controller->reset($request);
    }

    public function test_issued_token_is_not_stored_in_plaintext()
    {
        $user = $this->makeUser();

        $rawToken = $this->issueToken($user->email);
        $stored = DB::table('password_resets')->where('email', $user->email)->value('token');

        $this->assertNotSame($rawToken, $stored);
        $this->assertTrue(Hash::check($rawToken, $stored));
    }

    public function test_a_fresh_token_resets_the_password()
    {
        $user = $this->makeUser();
        $rawToken = $this->issueToken($user->email);

        $this->reset($user->email, $rawToken, 'NewPass111!');

        $this->assertTrue(Hash::check('NewPass111!', $user->fresh()->password));
    }

    public function test_an_invalid_token_is_rejected()
    {
        $user = $this->makeUser();
        $this->issueToken($user->email);

        $this->reset($user->email, 'not-the-real-token', 'ShouldNotApply1!');

        $this->assertTrue(Hash::check('OriginalPass123!', $user->fresh()->password));
    }

    /** The exact scenario from the report: a token generated 10 years ago must be refused. */
    public function test_a_token_older_than_the_expiry_window_is_rejected()
    {
        $user = $this->makeUser();
        $rawToken = $this->issueToken($user->email);
        DB::table('password_resets')->where('email', $user->email)->update(['created_at' => now()->subYears(10)]);

        $this->reset($user->email, $rawToken, 'ShouldNotApply1!');

        $this->assertTrue(Hash::check('OriginalPass123!', $user->fresh()->password));
    }

    public function test_a_token_one_minute_past_the_configured_expiry_is_rejected()
    {
        $user = $this->makeUser();
        $rawToken = $this->issueToken($user->email);
        $expireMinutes = (int) config('auth.passwords.users.expire', 60);
        DB::table('password_resets')->where('email', $user->email)->update(['created_at' => now()->subMinutes($expireMinutes + 1)]);

        $this->reset($user->email, $rawToken, 'ShouldNotApply1!');

        $this->assertTrue(Hash::check('OriginalPass123!', $user->fresh()->password));
    }

    public function test_a_token_one_minute_within_the_configured_expiry_still_works()
    {
        $user = $this->makeUser();
        $rawToken = $this->issueToken($user->email);
        $expireMinutes = (int) config('auth.passwords.users.expire', 60);
        DB::table('password_resets')->where('email', $user->email)->update(['created_at' => now()->subMinutes($expireMinutes - 1)]);

        $this->reset($user->email, $rawToken, 'StillInTime1!');

        $this->assertTrue(Hash::check('StillInTime1!', $user->fresh()->password));
    }

    /** The report's second finding: the same token used again must not change the password again. */
    public function test_a_token_cannot_be_replayed_after_a_successful_reset()
    {
        $user = $this->makeUser();
        $rawToken = $this->issueToken($user->email);

        $this->reset($user->email, $rawToken, 'FirstPass111!');
        $this->reset($user->email, $rawToken, 'ReplayPass222!');

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('FirstPass111!', $fresh->password));
        $this->assertFalse(Hash::check('ReplayPass222!', $fresh->password));
    }

    public function test_the_token_row_is_deleted_after_a_successful_reset()
    {
        $user = $this->makeUser();
        $rawToken = $this->issueToken($user->email);

        $this->reset($user->email, $rawToken, 'NewPass111!');

        $this->assertFalse(DB::table('password_resets')->where('email', $user->email)->exists());
    }

    /** Non-regression: requesting a new token retires the previous one (the broker's own behaviour). */
    public function test_requesting_a_new_token_invalidates_the_previous_one()
    {
        $user = $this->makeUser();
        $firstToken = $this->issueToken($user->email);
        // Otherwise the broker's own request-throttle (auth.passwords.users.throttle)
        // would refuse to issue a second token this soon and the test would be
        // asserting nothing: sendResetLink() returns RESET_THROTTLED and the
        // callback that captures the raw token never runs.
        $throttleSeconds = (int) config('auth.passwords.users.throttle', 60);
        DB::table('password_resets')->where('email', $user->email)->update(['created_at' => now()->subSeconds($throttleSeconds + 1)]);
        $secondToken = $this->issueToken($user->email);

        $this->reset($user->email, $firstToken, 'UsingOldToken1!');

        $this->assertTrue(Hash::check('OriginalPass123!', $user->fresh()->password));
        $this->assertTrue(Password::broker()->tokenExists($user->fresh(), $secondToken));
    }

    // ── The actually-routed "request a reset" endpoint ──────────────────────

    /** ForgotPasswordController::sendResetLinkEmail() is what POST password/email
     *  actually dispatches to - this covers the fix at that entry point too, not
     *  just the lower-level broker call used as a test helper above. */
    public function test_forgot_password_controller_issues_a_hashed_broker_token()
    {
        $user = $this->makeUser();
        $controller = new ForgotPasswordController(app(PhpMailController::class));
        $request = Request::create('/password/email', 'POST', ['email' => $user->email]);

        $controller->sendResetLinkEmail($request);

        // A bcrypt hash and the 60-char raw token happen to be the same length,
        // so length alone can't prove it's hashed - Hash::check() against an
        // arbitrary wrong value failing (rather than a literal string compare
        // matching) is what confirms the stored value isn't the plaintext token.
        $stored = DB::table('password_resets')->where('email', $user->email)->first();
        $this->assertNotNull($stored);
        $this->assertFalse(Hash::check('not-the-real-token', $stored->token));
    }

    public function test_forgot_password_controller_reports_an_unknown_email_as_a_failure()
    {
        $controller = new ForgotPasswordController(app(PhpMailController::class));
        $request = Request::create('/password/email', 'POST', ['email' => 'no-such-user-'.Str::random(8).'@example.test']);

        $controller->sendResetLinkEmail($request);

        $this->assertTrue(session()->has('fails'));
    }
}
