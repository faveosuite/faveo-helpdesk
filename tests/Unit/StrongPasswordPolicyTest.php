<?php

namespace Tests\Unit;

use App\Rules\StrongPassword;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Password policy (same as the installer's admin account): 9-16 characters with a
 * lowercase letter, an uppercase letter, a number and a special character.
 * Enforced on the admin/agent "Change password" modal and on the register page.
 */
class StrongPasswordPolicyTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
    }

    private function makeUser(string $role): User
    {
        $suffix = Str::random(10);
        $user = new User([
            'first_name'   => 'Pwd',
            'last_name'    => $suffix,
            'email'        => 'pwd-'.$suffix.'@example.test',
            'user_name'    => 'pwd_'.$suffix,
            'password'     => Hash::make('Old-Pass#123'),
            'active'       => 1,
            'role'         => $role,
            'agent_tzone'  => 81,
            'assign_group' => 1,
            'primary_dpt'  => 1,
        ]);
        $user->save();

        return $user;
    }

    public static function passwords(): array
    {
        return [
            'valid'                => ['Abcdef1@x', []],
            'valid at 16'          => ['Abcdefgh1@xyzxyz', []],
            'too short'            => ['Ab1@xyz', ['length']],
            'too long'             => ['Abcdefgh1@xyzxyzq', ['length']],
            'no lowercase'         => ['ABCDEF1@X', ['lowercase']],
            'no uppercase'         => ['abcdef1@x', ['uppercase']],
            'no number'            => ['Abcdefg@x', ['number']],
            'no special'           => ['Abcdefg1x', ['special']],
            'brace is not special' => ['Abcdefg1{', ['special']],
            'empty'                => ['', ['length', 'lowercase', 'uppercase', 'number', 'special']],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('passwords')]
    public function test_policy(string $password, array $expectedFailures)
    {
        $this->assertSame($expectedFailures, StrongPassword::failures($password));
        // as the forms use it: 'required' covers the empty value
        $this->assertSame($expectedFailures === [], Validator::make(['p' => $password], ['p' => ['required', new StrongPassword()]])->passes());
    }

    public function test_generated_passwords_always_meet_the_policy_and_are_safe_in_html_mail()
    {
        for ($i = 0; $i < 500; $i++) {
            $password = StrongPassword::generate(12);
            $this->assertSame([], StrongPassword::failures($password), $password);
            $this->assertSame(12, strlen($password));
            $this->assertDoesNotMatchRegularExpression('/[<>&]/', $password);
        }
    }

    public function test_generator_endpoint_returns_a_valid_password()
    {
        $this->actingAs($this->makeUser('admin'));

        $password = $this->get(route('user.changepassword'))->assertOk()->getContent();

        $this->assertSame([], StrongPassword::failures($password));
    }

    public function test_change_password_rejects_a_weak_password_and_does_not_keep_it_in_the_session()
    {
        $this->actingAs($this->makeUser('admin'));
        $target = $this->makeUser('user');
        $profile = route('user.show', $target->id);

        $this->from($profile)
            ->post(route('user.post.changepassword', $target->id), ['change_password' => 'weak'])
            ->assertRedirect($profile)
            ->assertSessionHasErrors('change_password');

        $this->assertNull(session()->getOldInput('change_password'), 'typed password was flashed to the session');
        $this->assertTrue(Hash::check('Old-Pass#123', $target->fresh()->password), 'password changed despite failing the policy');
    }

    public function test_change_password_accepts_a_strong_password()
    {
        $this->actingAs($this->makeUser('admin'));
        $target = $this->makeUser('user');

        $this->post(route('user.post.changepassword', $target->id), ['change_password' => 'New-Pass#456'])
            ->assertRedirect(url('user'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('New-Pass#456', $target->fresh()->password));
    }

    public function test_register_rejects_a_weak_password()
    {
        $email = 'reg-'.Str::random(8).'@example.test';

        $this->from(url('auth/register'))
            ->post(route('post.register'), ['full_name' => 'Reg User', 'email' => $email, 'password' => 'abc123', 'password_confirmation' => 'abc123'])
            ->assertSessionHasErrors('password');

        $this->assertFalse(User::where('email', $email)->exists());
    }

    public function test_register_accepts_a_strong_password()
    {
        $email = 'reg-'.Str::random(8).'@example.test';

        $this->post(route('post.register'), ['full_name' => 'Reg User', 'email' => $email, 'password' => 'Reg-Pass#789', 'password_confirmation' => 'Reg-Pass#789'])
            ->assertSessionHasNoErrors();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Reg-Pass#789', $user->password));
    }

    public function test_pages_render_the_checklist()
    {
        $this->get(url('auth/register'))->assertOk()
            ->assertSee('id="reg-password-rules"', false)
            ->assertSee(trans('lang.password_must_include'))
            ->assertSee(StrongPassword::label('length'));

        $this->actingAs($this->makeUser('admin'));
        $target = $this->makeUser('user');
        $this->get(route('user.show', $target->id))->assertOk()
            ->assertSee('id="changepassword1-rules"', false)
            ->assertSee('Between 9-16 characters');
    }
}
