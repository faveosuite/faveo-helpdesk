<?php

namespace Tests\Unit;

use App\Exceptions\Handler;
use App\Http\Controllers\Update\GitHubUpdateService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * The branded error pages extend the client layout, which queries the database. Before
 * the installer has written DB credentials (or with the database down) rendering them
 * threw from inside the exception handler: every missing asset the installer requested
 * logged "Access denied for user 'forge'" plus a final "Uncaught PDOException".
 */
class ErrorPagesWithoutDatabaseTest extends TestCase
{
    private array $savedInstallFlag = [];

    protected function tearDown(): void
    {
        foreach ($this->savedInstallFlag as $store => $value) {
            if ($store === 'putenv') {
                putenv($value === false ? 'DB_INSTALL' : "DB_INSTALL=$value");
            } elseif ($store === '_ENV') {
                $_ENV['DB_INSTALL'] = $value;
            } else {
                $_SERVER['DB_INSTALL'] = $value;
            }
        }
        $this->savedInstallFlag = [];

        parent::tearDown();
    }

    /** isInstall() reads env('DB_INSTALL') live; flip it to "not installed yet". */
    private function pretendNotInstalled(): void
    {
        $this->savedInstallFlag = [
            '_ENV'    => $_ENV['DB_INSTALL'] ?? null,
            '_SERVER' => $_SERVER['DB_INSTALL'] ?? null,
            'putenv'  => getenv('DB_INSTALL'),
        ];
        $_ENV['DB_INSTALL'] = $_SERVER['DB_INSTALL'] = '0';
        putenv('DB_INSTALL=0');

        $this->assertFalse(isInstall());
    }

    /** Point the default connection at a server that refuses connections. */
    private function makeDatabaseUnreachable(): void
    {
        config([
            'database.connections.unreachable' => [
                'driver'   => 'mysql',
                'host'     => '127.0.0.1',
                'port'     => 1,
                'database' => 'forge',
                'username' => 'forge',
                'password' => '',
            ],
            'database.default' => 'unreachable',
        ]);
        DB::purge('unreachable');
    }

    public function test_missing_asset_before_install_is_a_plain_404_without_touching_the_database()
    {
        $this->pretendNotInstalled();
        $this->makeDatabaseUnreachable();
        Exceptions::fake();

        $this->get('/lb-faveo/css/images/spinner.gif')
            ->assertStatus(404)
            ->assertSee(trans('lang.we_are_sorry_but_the_page_you_are_looking_for_can_not_be_found'), false);

        Exceptions::assertNothingReported();
    }

    public function test_404_route_before_install_renders_without_the_database()
    {
        $this->pretendNotInstalled();
        $this->makeDatabaseUnreachable();
        Exceptions::fake();

        $this->get('/404')->assertStatus(404);
        $this->get('/500')->assertStatus(500);
        $this->get('/error-in-database-connection')->assertStatus(503);

        Exceptions::assertNothingReported();
    }

    public function test_database_connection_failure_renders_the_db_page_even_when_wrapped()
    {
        config(['app.debug' => false]);
        $this->makeDatabaseUnreachable();

        $refused = new QueryException('mysql', 'select 1', [], new \PDOException('SQLSTATE[HY000] [1045] Access denied', 1045));
        // Thrown from a Blade view, the QueryException arrives wrapped (ViewException).
        $wrapped = new \ErrorException('view failed', 0, E_ERROR, __FILE__, __LINE__, $refused);

        foreach ([$refused, $wrapped] as $e) {
            $response = app(Handler::class)->render(Request::create('/tickets'), $e);

            $this->assertSame(503, $response->getStatusCode());
            $this->assertStringContainsString(trans('lang.error_establishing_connection_to_database'), $response->getContent());
        }

        $api = app(Handler::class)->render(Request::create('/api/v1/tickets'), $refused);
        $this->assertSame(503, $api->getStatusCode());
    }

    public function test_server_error_page_falls_back_when_the_branded_page_cannot_be_built()
    {
        config(['app.debug' => false]);
        $this->makeDatabaseUnreachable();

        $response = app(Handler::class)->render(Request::create('/tickets'), new \RuntimeException('boom'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString(trans('lang.sorry_something_went_wrong'), $response->getContent());
    }

    public function test_github_rate_limit_is_logged_as_info_and_other_failures_as_warning()
    {
        Http::fake([
            'api.github.com/*' => Http::sequence()
                ->push(['message' => 'API rate limit exceeded for 1.2.3.4.'], 403, ['X-RateLimit-Remaining' => '0'])
                ->push(['message' => 'Not Found'], 404),
        ]);
        Log::spy();

        $service = app(GitHubUpdateService::class);
        $this->assertNull($service->getLatestRelease(true));
        $this->assertNull($service->getLatestRelease(true));

        Log::shouldHaveReceived('log')->with('info', 'GitHub Update: failed to fetch latest release', \Mockery::any())->once();
        Log::shouldHaveReceived('log')->with('warning', 'GitHub Update: failed to fetch latest release', \Mockery::any())->once();
    }
}
