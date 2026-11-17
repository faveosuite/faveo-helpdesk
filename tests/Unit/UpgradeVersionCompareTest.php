<?php

namespace Tests\Unit;

use App\Http\Controllers\Update\GitHubUpdateService;
use App\Http\Controllers\Update\UpgradeController;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/*
 * Regression: an RC install (v2.0.4.RC.1) was never offered the final v2.0.4,
 * because the installed version was read as "2.0.4..1" (letters stripped),
 * which version_compare() ranks above "2.0.4".
 */
class UpgradeVersionCompareTest extends TestCase
{
    public static function releases(): array
    {
        return [
            'RC to its final release' => ['v2.0.4.RC.1', '2.0.4', true],
            'RC to next release'      => ['v2.0.4.RC.1', '2.0.5', true],
            'final to next release'   => ['v2.0.4', '2.0.5', true],
            'same final version'      => ['v2.0.4', '2.0.4', false],
            'same RC version'         => ['v2.0.4.RC.1', '2.0.4.RC.1', false],
            'final to its own RC'     => ['v2.0.4', '2.0.4.RC.1', false],
            'underscore tag'          => ['v2_0_4', '2.0.5', true],
        ];
    }

    #[DataProvider('releases')]
    public function test_update_available(string $installed, string $latest, bool $expected): void
    {
        config(['app.version' => $installed]);

        $github = $this->createMock(GitHubUpdateService::class);
        $github->method('getLatestVersion')->willReturn($latest);

        $this->assertSame($expected, $this->call_protected(new UpgradeController($github), 'isUpdateAvailable'));
    }

    public function test_installed_version_keeps_rc_marker(): void
    {
        config(['app.version' => 'v2.0.4.RC.1']);
        $controller = new UpgradeController($this->createMock(GitHubUpdateService::class));

        $this->assertSame('2.0.4.RC.1', $this->call_protected($controller, 'getCurrentVersion'));
    }

    public static function unsafeEntries(): array
    {
        return [
            'parent traversal' => ['faveo-abc/../../etc/cron.d/x'],
            'absolute path'    => ['/etc/passwd'],
            'backslash'        => ['faveo-abc\\..\\x.php'],
            'windows drive'    => ['C:/x.php'],
        ];
    }

    #[DataProvider('unsafeEntries')]
    public function test_archive_entries_outside_the_app_are_rejected(string $entry): void
    {
        $controller = new UpgradeController($this->createMock(GitHubUpdateService::class));

        $this->expectExceptionMessage('unsafe path');
        $this->call_protected($controller, 'assertSafeEntry', $entry);
    }

    public function test_normal_archive_entry_is_accepted(): void
    {
        $controller = new UpgradeController($this->createMock(GitHubUpdateService::class));

        $this->assertNull($this->call_protected($controller, 'assertSafeEntry', 'faveo-abc/app/Http/Kernel.php'));
    }

    public function test_site_specific_files_are_never_overwritten(): void
    {
        $controller = new UpgradeController($this->createMock(GitHubUpdateService::class));

        foreach (['.env', 'storage/app/private.key', 'storage/backups/2026/x.zip', 'UPDATES/latest-release.zip'] as $path) {
            $this->assertTrue($this->call_protected($controller, 'isExcluded', $path), $path);
        }
        foreach (['storage/faveoconfig.ini', 'app/private.key', 'config/app.php'] as $path) {
            $this->assertFalse($this->call_protected($controller, 'isExcluded', $path), $path);
        }
    }

    public function test_mysql_option_file_quotes_the_password(): void
    {
        $controller = new UpgradeController($this->createMock(GitHubUpdateService::class));

        $file = $this->call_protected($controller, 'mysqlOptionFile', [
            'username' => 'faveo', 'password' => 'p"a\\ss;$(id)', 'host' => 'localhost', 'port' => '3306',
        ]);

        $this->assertStringContainsString('password="p\\"a\\\\ss;$(id)"', $file);
        $this->assertStringContainsString('port=3306', $file);
    }

    private function call_protected(object $object, string $method, mixed ...$args): mixed
    {
        return (new \ReflectionMethod($object, $method))->invoke($object, ...$args);
    }
}
