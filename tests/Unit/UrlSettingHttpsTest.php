<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\helpdesk\UrlSettingController;
use App\Providers\AppServiceProvider;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin > URL settings (WWW / HTTPS). The old code appended its redirect rules after a "#custom"
 * marker at the END of public/.htaccess, i.e. after Laravel's front-controller rule, so with HTTPS
 * on, http://site/login redirected to https://site/index.php (the page was lost); the www rules
 * hardcoded http:// (HTTPS was downgraded) and the non-www rule dropped the install folder.
 * The rules now live in one managed block right after "RewriteEngine On".
 */
class UrlSettingHttpsTest extends TestCase
{
    use DatabaseTransactions;

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        // Work on a copy of the shipped public/.htaccess; the real file is never touched.
        $this->file = tempnam(sys_get_temp_dir(), 'htaccess');
        copy(public_path('.htaccess'), $this->file);
        $file = $this->file;
        $this->app->bind(UrlSettingController::class, function () use ($file) {
            return new class($file) extends UrlSettingController {
                public function __construct(private string $path)
                {
                    parent::__construct();
                }

                public function htaccessPath(): string
                {
                    return $this->path;
                }
            };
        });
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    private function controller(): UrlSettingController
    {
        return $this->app->make(UrlSettingController::class);
    }

    private function original(): string
    {
        return file_get_contents(public_path('.htaccess'));
    }

    private function admin(): User
    {
        $suffix = Str::random(10);
        $user = new User([
            'first_name'   => 'Url',
            'last_name'    => $suffix,
            'email'        => 'url-'.$suffix.'@example.test',
            'user_name'    => 'url_'.$suffix,
            'password'     => Hash::make(Str::random(12)),
            'active'       => 1,
            'role'         => 'admin',
            'agent_tzone'  => 81,
            'assign_group' => 1,
            'primary_dpt'  => 1,
        ]);
        $user->save();

        return $user;
    }

    public function test_rules_go_before_the_front_controller_and_keep_the_rest_of_the_file()
    {
        $c = $this->controller();
        $out = $c->buildHtaccess($this->original(), $c->buildRules(false, true));

        $block = strpos($out, UrlSettingController::BLOCK_BEGIN);
        $this->assertNotFalse($block);
        $this->assertGreaterThan(strpos($out, 'RewriteEngine On'), $block);
        $this->assertLessThan(strpos($out, 'RewriteRule ^ index.php [L]'), $block, 'redirects must run before the front controller, or every page becomes /index.php');
        $this->assertStringContainsString('RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301,NE]', $out);
        $this->assertStringContainsString('RewriteCond %{HTTP:X-Forwarded-Proto} !=https', $out, 'no redirect loop behind an SSL proxy');
        $this->assertStringNotContainsString('#custom', $out);

        // Everything outside the block is unchanged.
        $withoutBlock = preg_replace('/\n    # BEGIN Faveo URL settings.*?# END Faveo URL settings\n/s', '', $out);
        $this->assertSame($this->original(), $withoutBlock);
    }

    public function test_redirects_never_send_https_visitors_back_to_http()
    {
        $c = $this->controller();
        foreach ([true, false] as $www) {
            $rules = $c->buildRules($www, true);
            $this->assertStringNotContainsString('http://', $rules);
            $this->assertStringNotContainsString('$1', $rules, 'paths must come from %{REQUEST_URI} so the install folder is kept');
        }
        // With HTTPS off the www redirect keeps whatever scheme the visitor used.
        $this->assertStringContainsString('%{ENV:FAVEO_SCHEME}://%1%{REQUEST_URI}', $c->buildRules(false, false));
        $this->assertStringNotContainsString('HTTPS', $c->buildRules(false, false));
    }

    public function test_saving_again_replaces_the_block_instead_of_adding_another()
    {
        $c = $this->controller();
        $once = $c->buildHtaccess($this->original(), $c->buildRules(false, true));
        $twice = $c->buildHtaccess($once, $c->buildRules(false, true));
        $this->assertSame($once, $twice);
        $this->assertSame(1, substr_count($twice, UrlSettingController::BLOCK_BEGIN));

        $off = $c->buildHtaccess($twice, $c->buildRules(false, false));
        $this->assertStringNotContainsString('%{HTTPS}', $off, 'turning HTTPS off removes the redirect');
        $this->assertSame($c->buildHtaccess($this->original(), $c->buildRules(false, false)), $off);
    }

    public function test_old_custom_block_is_cleaned_up_but_admin_lines_are_kept()
    {
        $legacy = rtrim($this->original())."#custom\n"
            ."RewriteCond %{HTTP_HOST} ^www\\.(.*)$ [NC]\n"
            ."RewriteRule ^(.*)$ http://%1/$1 [R=301,L]\n"
            ."RewriteCond %{HTTPS} off\n"
            ."RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n"
            ."Header set X-Admin-Added yes\n";

        $c = $this->controller();
        $out = $c->buildHtaccess($legacy, $c->buildRules(false, true));

        $this->assertStringNotContainsString('#custom', $out);
        $this->assertStringNotContainsString('RewriteCond %{HTTPS} off', $out);
        $this->assertStringNotContainsString('http://%1/$1', $out);
        $this->assertStringContainsString('#</FilesMatch>', $out, 'the last line of the shipped file survives');
        $this->assertStringContainsString("Header set X-Admin-Added yes\n", $out, 'lines the admin added are kept');
    }

    public function test_file_without_rewrite_engine_is_left_untouched()
    {
        file_put_contents($this->file, "# hand written\n");
        $this->expectExceptionMessage('RewriteEngine On');
        try {
            $this->controller()->writeHtaccess('x');
        } finally {
            $this->assertSame("# hand written\n", file_get_contents($this->file));
        }
    }

    public function test_settings_page_shows_what_is_saved()
    {
        $c = $this->controller();
        $this->assertSame([], $c->savedSettings($this->original()));
        $this->assertSame(['www' => false, 'https' => true], $c->savedSettings($c->buildHtaccess($this->original(), $c->buildRules(false, true))));
        $this->assertSame(['www' => true, 'https' => false], $c->savedSettings($c->buildHtaccess($this->original(), $c->buildRules(true, false))));
        $this->assertFalse($c->checkWwwInUrl('https://wwwidget.com/x'));
        $this->assertTrue($c->checkWwwInUrl('https://www.example.com/x'));
        $this->assertFalse($c->checkHttpsInUrl('http://example.com/https'));
    }

    public function test_https_can_only_be_enabled_from_an_https_page()
    {
        $this->actingAs($this->admin());

        // Over plain HTTP: refused, file unchanged (no lock-out on a site without a certificate).
        $this->from('http://localhost/url/settings')
            ->patch('http://localhost/url/settings', ['www' => 'no', 'ssl' => 'yes'])
            ->assertSessionHas('fails');
        $this->assertSame($this->original(), file_get_contents($this->file));

        // Over HTTPS: saved.
        $this->from('https://localhost/url/settings')
            ->patch('https://localhost/url/settings', ['www' => 'no', 'ssl' => 'yes'])
            ->assertSessionHas('success');
        $this->assertSame(['www' => false, 'https' => true], $this->controller()->savedSettings(file_get_contents($this->file)));

        // Turning HTTPS off works from anywhere.
        $this->from('http://localhost/url/settings')
            ->patch('http://localhost/url/settings', ['www' => 'no', 'ssl' => 'no'])
            ->assertSessionHas('success');
        $this->assertSame(['www' => false, 'https' => false], $this->controller()->savedSettings(file_get_contents($this->file)));
    }

    public function test_www_can_only_be_enabled_from_the_www_address_and_input_is_validated()
    {
        $this->actingAs($this->admin());

        $this->from('https://localhost/url/settings')
            ->patch('https://localhost/url/settings', ['www' => 'yes', 'ssl' => 'yes'])
            ->assertSessionHas('fails');
        $this->assertSame($this->original(), file_get_contents($this->file));

        $this->from('https://localhost/url/settings')
            ->patch('https://localhost/url/settings', ['www' => 'no'])
            ->assertSessionHasErrors('ssl');
        $this->assertSame($this->original(), file_get_contents($this->file));
    }

    public function test_https_links_are_forced_except_on_a_plain_http_site()
    {
        $provider = new AppServiceProvider($this->app);

        config(['app.url' => 'https://helpdesk.example.com']);
        $this->assertTrue($provider->shouldForceHttps(), 'https APP_URL: unchanged, still forced');

        config(['app.url' => 'http://helpdesk.example.com']);
        $this->assertFalse($provider->shouldForceHttps(), 'http APP_URL from the console (queue mails): plain http links');
    }
}
