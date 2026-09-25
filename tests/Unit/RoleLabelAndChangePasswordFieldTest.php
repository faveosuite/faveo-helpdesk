<?php

namespace Tests\Unit;

use App\Model\helpdesk\Agent\Teams;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * - Team members list showed the raw stored role ("agent"); it shows the label ("Agent").
 * - User profile showed "user"; it shows "User".
 * - The admin "Change password" field was type="text", so the password was visible
 *   while typing; it is hidden by default with an eye toggle.
 */
class RoleLabelAndChangePasswordFieldTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $role): User
    {
        $suffix = Str::random(10);
        $user = new User([
            'first_name'   => 'Role',
            'last_name'    => $suffix,
            'email'        => 'role-'.$suffix.'@example.test',
            'user_name'    => 'role_'.$suffix,
            'password'     => Hash::make(Str::random(12)),
            'active'       => 1,
            'role'         => $role,
            'agent_tzone'  => 81,
            'assign_group' => 1,
            'primary_dpt'  => 1,
        ]);
        $user->save();

        return $user;
    }

    public function test_team_members_list_shows_translated_role_labels()
    {
        $this->actingAs($this->makeUser('admin'));
        $team = new Teams(['name' => 'Role label team '.Str::random(5), 'status' => 1]);
        $team->save();
        foreach ([$this->makeUser('agent'), $this->makeUser('admin')] as $member) {
            DB::table('team_assign_agent')->insert(['team_id' => $team->id, 'agent_id' => $member->id]);
        }

        $rows = $this->getJson(route('teams.getshow.list', $team->id))->assertOk()->json('data');

        $roles = collect($rows)->pluck('role')->map(fn ($html) => trim(strip_tags($html)))->sort()->values()->all();
        $this->assertSame(['Admin', 'Agent'], $roles);
    }

    public function test_user_profile_shows_role_label_and_a_hidden_password_field_with_eye_toggle()
    {
        $this->actingAs($this->makeUser('admin'));
        $customer = $this->makeUser('user');

        $html = $this->get(route('user.show', $customer->id))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<a class="float-end text-success">\s*User</a>#', $html);
        $this->assertStringContainsString('<input type="password" class="form-control" name="change_password" id="changepassword1"', $html);
        $this->assertStringContainsString('id="changepassword1-toggle"', $html);
        $this->assertStringNotContainsString('<input type="text" class="form-control" name="change_password"', $html);
    }
}
