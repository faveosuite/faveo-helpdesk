<?php

/*
 * Regression tests for "Unauthenticated RCE via command injection in the upgrade
 * backup endpoint" (CWE-306 / CWE-78), reported against v2.0.4.RC.1.
 *
 * UpgradeController::backup() calls exec() unqualified from its own namespace, so
 * defining App\Http\Controllers\Update\exec() here intercepts it: the command is
 * recorded instead of run (no mysqldump, no zipping the whole application in the
 * background, and nothing an injection could execute).
 */

namespace App\Http\Controllers\Update {
    function exec($command, &$output = null, &$result_code = null)
    {
        \Tests\Unit\UpgradeRoutesAuthorizationTest::$execCalls[] = $command;
        $output = [];
        $result_code = 0;

        return '';
    }
}

namespace Tests\Unit {
    use App\Model\helpdesk\Settings\BackupPath;
    use App\User;
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;
    use PHPUnit\Framework\Attributes\DataProvider;
    use Tests\TestCase;

    class UpgradeRoutesAuthorizationTest extends TestCase
    {
        /** @var string[] commands UpgradeController passed to exec() */
        public static array $execCalls = [];

        private string $marker;

        protected function setUp(): void
        {
            parent::setUp();
            self::$execCalls = [];
            $this->marker = sys_get_temp_dir().'/faveo_rce_marker_'.Str::random(12);
        }

        protected function tearDown(): void
        {
            @unlink($this->marker);
            parent::tearDown();
        }

        private function makeUser(string $role): User
        {
            $suffix = Str::random(10);
            $user = new User([
                'first_name'   => 'Upg',
                'last_name'    => $suffix,
                'email'        => 'upg-'.$suffix.'@example.test',
                'user_name'    => 'upg_'.$suffix,
                'password'     => Hash::make(Str::random(12)),
                'active'       => 1,
                'role'         => $role,
                'agent_tzone'  => 81,
                'primary_dpt'  => $role === 'user' ? null : 1,
                'assign_group' => $role === 'user' ? null : 1,
            ]);
            $user->save();

            return $user;
        }

        /** Every update endpoint; all are privileged. */
        public static function updateEndpoints(): array
        {
            return [
                'GET database-update'   => ['get', 'database-update'],
                'GET database-upgrade'  => ['get', 'database-upgrade'],
                'GET file-update'       => ['get', 'file-update'],
                'GET file-upgrade'      => ['get', 'file-upgrade'],
                'POST upgrade/backup'   => ['post', 'upgrade/backup'],
                'POST upgrade/download' => ['post', 'upgrade/download'],
                'POST upgrade/apply'    => ['post', 'upgrade/apply'],
                'POST upgrade/database' => ['post', 'upgrade/database'],
                'GET upgrade/check'     => ['get', 'upgrade/check'],
            ];
        }

        private function injectionPayload(): array
        {
            // The report's payload shape, pointed at a marker file we can check for.
            return ['path' => '/tmp/bk$(touch '.$this->marker.')', 'autoUpdate' => false];
        }

        private function assertNothingExecuted(): void
        {
            $this->assertSame([], self::$execCalls, 'a shell command was built for an unauthorized request');
            $this->assertFileDoesNotExist($this->marker);
        }

        #[DataProvider('updateEndpoints')]
        public function test_guest_is_sent_to_login(string $method, string $uri)
        {
            $before = BackupPath::value('backup_path');

            $response = $this->{$method}($uri, $this->injectionPayload());

            $response->assertRedirect();
            $this->assertStringContainsString('auth/login', (string) $response->headers->get('Location'));
            $this->assertNothingExecuted();
            $this->assertSame($before, BackupPath::value('backup_path'), 'guest request changed the stored backup path');
        }

        #[DataProvider('updateEndpoints')]
        public function test_agent_is_refused(string $method, string $uri)
        {
            $this->actingAs($this->makeUser('agent'));

            $response = $this->{$method}($uri, $this->injectionPayload());

            $response->assertRedirect();
            $this->assertStringNotContainsString($uri, (string) $response->headers->get('Location'));
            $response->assertSessionHas('fails');
            $this->assertNothingExecuted();
        }

        #[DataProvider('updateEndpoints')]
        public function test_customer_is_refused(string $method, string $uri)
        {
            $this->actingAs($this->makeUser('user'));

            $response = $this->{$method}($uri, $this->injectionPayload());

            $response->assertRedirect();
            $response->assertSessionHas('fails');
            $this->assertNothingExecuted();
        }

        /**
         * Even an admin cannot choose the backup destination: the request 'path'
         * is ignored, so the injection payload never reaches the shell, and every
         * argument in the command is single-quoted.
         */
        public function test_admin_backup_ignores_request_path_and_escapes_the_command()
        {
            $this->actingAs($this->makeUser('admin'));

            $this->postJson('upgrade/backup', $this->injectionPayload())->assertStatus(200);

            $this->assertCount(1, self::$execCalls);
            $command = self::$execCalls[0];
            $this->assertStringNotContainsString('$(', $command);
            $this->assertStringNotContainsString($this->marker, $command);
            $this->assertStringNotContainsString('/tmp/bk', $command);
            $this->assertStringContainsString("'".storage_path('backups').DIRECTORY_SEPARATOR, $command, 'backup must go to storage/backups, single-quoted');
            $this->assertStringContainsString("zip -r '", $command);
            $this->assertFileDoesNotExist($this->marker);
            $this->assertFalse(File::isDirectory('/tmp/bk$(touch '.$this->marker.')'), 'request path was still used to create a directory');
            $this->assertSame(storage_path('backups'), BackupPath::value('backup_path'));
        }
    }
}
