<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Edit pages hard-coded the "1" radio as checked and compared the model's int to a string value,
 * so an inactive/private/draft record always opened as active/public/published, and saving the
 * form unchanged flipped it. Every edit page must select the value that is actually saved.
 */
class EditFormSavedStatusTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('forms')]
    public function test_edit_page_selects_saved_value($url, $table, $key, $field, $find)
    {
        $admin = $this->makeAdmin();
        $id = match ($find) {
            'admin' => $admin->id,
            'user'  => DB::table('users')->where('role', '=', 'user')->value('id'),
            default => DB::table($table)->value($key),
        };
        $this->assertNotNull($id, "no $table row to edit");

        foreach (['0', '1'] as $saved) {
            DB::table($table)->where($key, $id)->update([$field => $saved]);
            $_SERVER['REQUEST_URI'] = '/'.sprintf($url, $id);
            $html = $this->actingAs($admin)->get(sprintf($url, $id))->assertOk()->getContent();

            $other = $saved === '1' ? '0' : '1';
            $this->assertStringContainsString('checked', $this->radio($html, $field, $saved), "$url: saved $field=$saved should be selected");
            $this->assertStringNotContainsString('checked', $this->radio($html, $field, $other), "$url: $field=$other should not be selected");
        }
    }

    public static function forms()
    {
        return [
            'agent status'          => ['agents/%s/edit', 'users', 'id', 'active', 'admin'],
            'group status'          => ['groups/%s/edit', 'groups', 'id', 'group_status', null],
            'user status'           => ['user/%s/edit', 'users', 'id', 'active', 'user'],
            'user ban'              => ['user/%s/edit', 'users', 'id', 'ban', 'user'],
            'help topic type'       => ['helptopic/%s/edit', 'help_topic', 'id', 'type', null],
            'kb article published'  => ['article/%s/edit', 'kb_article', 'id', 'type', null],
            'kb article visibility' => ['article/%s/edit', 'kb_article', 'id', 'status', null],
            'kb category status'    => ['category/%s/edit', 'kb_category', 'id', 'status', null],
            'close workflow email'  => ['close-workflow', 'workflow_close', 'id', 'send_email', null],
            'rating modification'   => ['editratings/%s', 'ratings', 'id', 'allow_modification', null],
        ];
    }

    private function makeAdmin()
    {
        $s = Str::random(8);

        return User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
    }

    private function radio($html, $name, $value)
    {
        preg_match('/<input[^>]*type="radio"[^>]*name="'.$name.'"[^>]*value="'.$value.'"[^>]*>/', $html, $m);
        $this->assertNotEmpty($m, "radio $name=$value not found");

        return $m[0];
    }
}
