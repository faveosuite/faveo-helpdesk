<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Creating a ticket with an email that matches no user but a mobile that belongs to one used to
 * fail with a generic message that never said which user owned the number.
 */
class NewTicketMobileMessageTest extends TestCase
{
    public function test_mobile_clash_message_names_the_owning_user()
    {
        $s = Str::random(8);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password'                      => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1]);
        $mobile = (string) random_int(7000000000, 7999999999);
        $owner = User::create(['first_name' => 'Owner', 'last_name' => 'Person', 'email' => 'o'.$s.'@example.test', 'user_name' => 'o_'.$s,
            'password'                      => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'user', 'mobile' => $mobile, 'country_code' => 91, 'phone_number' => '']);
        $_SERVER['REQUEST_URI'] = '/newticket';

        $response = $this->actingAs($admin)->post('newticket/post', [
            'email'   => 'different'.$s.'@example.test', 'first_name' => 'Someone', 'last_name' => 'Else',
            'code'    => '91', 'mobile' => $mobile, 'helptopic' => 1, 'sla' => 1, 'priority' => 1,
            'subject' => 'Clash subject', 'body' => 'Clash body text here',
        ]);

        $fails = session('fails');
        $this->assertNotNull($fails, 'expected a failure message; got: '.json_encode(session()->all()));
        $this->assertStringContainsString('o'.$s.'@example.test', $fails);
        $this->assertStringContainsString('Owner Person', $fails);
        $this->assertSame(0, User::where('email', 'different'.$s.'@example.test')->count());
    }

    private function submit(array $override)
    {
        $s = Str::random(8);
        $admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password'                      => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1]);
        $_SERVER['REQUEST_URI'] = '/newticket';

        return $this->actingAs($admin)->post('newticket/post', $override + [
            'email'     => 'n'.$s.'@example.test', 'first_name' => 'Someone', 'last_name' => 'Else',
            'helptopic' => 1, 'sla' => 1, 'priority' => 1, 'subject' => 'A subject', 'body' => 'Some body text here',
        ]);
    }

    public function test_non_numeric_mobile_is_rejected()
    {
        $this->submit(['code' => '91', 'mobile' => 'abc12345'])->assertSessionHasErrors('mobile');
    }

    public function test_non_numeric_phone_is_rejected()
    {
        $this->submit(['code' => '91', 'phone' => '12-34x'])->assertSessionHasErrors('phone');
    }

    public function test_numeric_mobile_and_phone_pass_validation()
    {
        $this->submit(['code' => '91', 'mobile' => (string) random_int(7000000000, 7999999999), 'phone' => '0442345678'])
            ->assertSessionDoesntHaveErrors(['mobile', 'phone']);
    }
}
