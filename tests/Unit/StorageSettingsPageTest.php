<?php

namespace Tests\Unit;

use App\Model\helpdesk\Settings\CommonSettings;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The admin "Storage" settings page used to live at /storage. `storage:link` creates
 * public/storage, and Apache serves that directory instead of routing to Laravel
 * (public/.htaccess: RewriteCond !-d), so on a real install the page answered
 * "403 Forbidden". Like the paid edition, the page now lives at a URL that no
 * directory in public/ can shadow; the route names are unchanged.
 */
class StorageSettingsPageTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $role): User
    {
        $suffix = Str::random(10);
        $user = new User([
            'first_name'   => 'Storage',
            'last_name'    => $suffix,
            'email'        => 'storage-'.$suffix.'@example.test',
            'user_name'    => 'storage_'.$suffix,
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

    public function test_page_url_cannot_be_shadowed_by_the_public_storage_link()
    {
        $this->assertStringEndsWith('/storage-settings', route('storage'));
        $this->assertStringEndsWith('/storage-settings', route('post.storage'));
        $this->assertSame(storage_path('app/public'), config('filesystems.links')[public_path('storage')], 'storage:link must stay as before (same as the paid edition)');
    }

    public function test_admin_opens_the_page_from_the_menu_and_saves_settings()
    {
        $this->actingAs($this->makeUser('admin'));

        // the admin menu links to the new URL
        $this->get(route('storage'))
            ->assertOk()
            ->assertSee('href="'.route('storage').'"', false)
            ->assertSee('action="'.route('post.storage').'"', false);

        $this->from(route('storage'))
            ->post(route('post.storage'), ['default' => 'local', 'root' => storage_path('app')])
            ->assertRedirect(route('storage'))
            ->assertSessionHas('success');

        $saved = CommonSettings::where('option_name', 'storage')->pluck('option_value', 'optional_field')->all();
        $this->assertSame(['default' => 'local', 'root' => storage_path('app')], $saved);
    }

    public function test_old_storage_url_redirects_to_the_new_page()
    {
        $this->get('/storage')->assertStatus(301)->assertRedirect(route('storage'));
    }

    public function test_agents_and_guests_are_still_refused()
    {
        $this->get(route('storage'))->assertRedirect();
        $this->post(route('post.storage'), ['default' => 'database'])->assertRedirect();

        $this->actingAs($this->makeUser('agent'));
        $this->get(route('storage'))->assertRedirect();
        $this->post(route('post.storage'), ['default' => 'database'])->assertRedirect();

        $this->assertSame(0, CommonSettings::where('option_name', 'storage')->where('option_value', 'database')->count());
    }
}
