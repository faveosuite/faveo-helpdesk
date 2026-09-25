<?php

namespace Tests\Unit;

use App\Http\Controllers\Update\GitHubUpdateService;
use App\Http\Controllers\Update\UpgradeController;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

/*
 * Runs the real file-replacement step against a throw-away "application"
 * directory (the container's base path is pointed at it), never the checkout.
 */
class UpgradeApplyFilesTest extends TestCase
{
    private string $root;

    private string $originalBase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = sys_get_temp_dir().'/faveo-apply-'.uniqid();
        $this->originalBase = $this->app->basePath();

        // The installed application.
        $this->putFile('app/Old.php', 'old');
        $this->putFile('app/Keep.php', 'untouched');
        $this->putFile('.env', 'APP_KEY=site-secret');
        $this->putFile('storage/app/private.key', 'site-key');
        $this->putFile('vendor/composer/installed.php', 'old-vendor');
        $this->putFile('vendor/removed/package.php', 'stale');

        $this->app->setBasePath($this->root);
    }

    protected function tearDown(): void
    {
        $this->app->setBasePath($this->originalBase);
        File::deleteDirectory($this->root);
        parent::tearDown();
    }

    public function test_files_are_replaced_vendor_is_swapped_and_site_files_are_kept(): void
    {
        $controller = $this->controllerFor($this->release([
            'app/Old.php'                   => 'new',
            'app/Added.php'                 => 'added',
            '.env'                          => 'APP_KEY=from-release',
            'storage/app/private.key'       => 'release-key',
            'vendor/composer/installed.php' => 'new-vendor',
        ]));

        $this->invoke($controller, 'extractAndApply');

        $this->assertSame('new', $this->readFile('app/Old.php'));
        $this->assertSame('added', $this->readFile('app/Added.php'));
        $this->assertSame('untouched', $this->readFile('app/Keep.php'));
        $this->assertSame('APP_KEY=site-secret', $this->readFile('.env'));
        $this->assertSame('site-key', $this->readFile('storage/app/private.key'));
        $this->assertSame('new-vendor', $this->readFile('vendor/composer/installed.php'));
        $this->assertFileDoesNotExist($this->root.'/vendor/removed/package.php', 'packages dropped from the release must not linger');
    }

    public function test_unwritable_target_aborts_before_anything_changes(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('root can write to read-only files');
        }

        chmod($this->root.'/app/Keep.php', 0444);
        $controller = $this->controllerFor($this->release([
            'app/Old.php'                   => 'new',
            'app/Keep.php'                  => 'new',
            'vendor/composer/installed.php' => 'new-vendor',
        ]));

        try {
            $this->invoke($controller, 'extractAndApply');
            $this->fail('expected the update to stop');
        } catch (\Exception $e) {
            $this->assertStringContainsString('cannot write to', $e->getMessage());
        }

        $this->assertSame('old', $this->readFile('app/Old.php'));
        $this->assertSame('old-vendor', $this->readFile('vendor/composer/installed.php'));
    }

    private function controllerFor(string $zipPath): UpgradeController
    {
        $github = $this->createMock(GitHubUpdateService::class);
        $github->method('zipPath')->willReturn($zipPath);
        $github->method('getTempPath')->willReturn($this->root.'/UPDATES');

        return new UpgradeController($github);
    }

    /** Build a GitHub-style zipball: everything under one top-level folder. */
    private function release(array $files): string
    {
        File::makeDirectory($this->root.'/UPDATES', 0755, true, true);
        $path = $this->root.'/UPDATES/latest-release.zip';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($files as $name => $contents) {
            $zip->addFromString('faveosuite-faveo-helpdesk-abc123/'.$name, $contents);
        }
        $zip->close();

        return $path;
    }

    private function putFile(string $relative, string $contents): void
    {
        File::ensureDirectoryExists(dirname($this->root.'/'.$relative));
        File::put($this->root.'/'.$relative, $contents);
    }

    private function readFile(string $relative): string
    {
        return File::get($this->root.'/'.$relative);
    }

    private function invoke(object $object, string $method): mixed
    {
        return (new \ReflectionMethod($object, $method))->invoke($object);
    }
}
