<?php

namespace Tests\Unit;

use App\Model\helpdesk\Agent\Teams;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The team edit form hard-coded the Active radio as checked, so every team - including
 * inactive ones - showed "Active" when opened for editing, and saving without touching the
 * radio silently re-activated it.
 */
class TeamEditStatusTest extends TestCase
{
    private function actAsAdmin(): void
    {
        $suffix = Str::random(8);
        $admin = new User([
            'first_name' => 'T', 'last_name' => 'Admin', 'email' => $suffix.'@example.test',
            'user_name' => 'u_'.$suffix, 'password' => Hash::make(Str::random(10)),
            'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1,
        ]);
        $admin->save();
        $this->actingAs($admin);
    }

    /** The admin layout reads $_SERVER['REQUEST_URI'], which the test client doesn't populate. */
    private function editPage(Teams $team)
    {
        $_SERVER['REQUEST_URI'] = '/teams/'.$team->id.'/edit';

        return $this->get('teams/'.$team->id.'/edit');
    }

    private function makeTeam(int $status): Teams
    {
        $team = new Teams();
        $team->name = 'Team '.Str::random(6);
        $team->status = $status;
        $team->save();

        return $team;
    }

    public function test_edit_form_shows_an_active_team_as_active()
    {
        $this->actAsAdmin();
        $team = $this->makeTeam(1);

        $html = $this->editPage($team)->assertStatus(200)->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*value="1"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*value="0"[^>]*checked/', $html);
    }

    public function test_edit_form_shows_an_inactive_team_as_inactive()
    {
        $this->actAsAdmin();
        $team = $this->makeTeam(0);

        $html = $this->editPage($team)->assertStatus(200)->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*value="0"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*value="1"[^>]*checked/', $html);
    }
}
