<?php

namespace Unit;

use Flow\FileOpenException;
use Flow\Uploader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * Uploader unit tests
 *
 * @coversDefaultClass \Flow\Uploader
 *
 * @package Unit
 */
class UploaderTest extends FlowUnitCase
{
    /**
     * Virtual file system
     *
     * @var vfsStreamDirectory
     */
    protected $vfs;

    protected function setUp(): void
    {
        vfsStreamWrapper::register();
        $this->vfs = new vfsStreamDirectory('chunks');
        vfsStreamWrapper::setRoot($this->vfs);
    }

    /**
     * @covers ::pruneChunks
     */
    public function testUploader_pruneChunks()
    {
        //// Setup test

        $newDir = vfsStream::newDirectory('1');
        $newDir->lastModified(time() - 31);
        $newDir->lastModified(time());

        $fileFirst = vfsStream::newFile('file31');
        $fileFirst->lastModified(time() - 31);
        $fileSecond = vfsStream::newFile('random_file');
        $fileSecond->lastModified(time() - 30);
        $upDir = vfsStream::newFile('..');

        $this->vfs->addChild($newDir);
        $this->vfs->addChild($fileFirst);
        $this->vfs->addChild($fileSecond);
        $this->vfs->addChild($upDir);

        //// Actual test

        Uploader::pruneChunks($this->vfs->url(), 30);
        $this->assertTrue(file_exists($newDir->url()));
        $this->assertFalse(file_exists($fileFirst->url()));
        $this->assertTrue(file_exists($fileSecond->url()));
    }

    /**
     * @covers ::pruneChunks
     */
    public function testUploader_exception()
    {
        try {
            @Uploader::pruneChunks('not/existing/dir', 30);
            $this->fail();
        } catch (FileOpenException $e) {
            $this->assertSame('failed to open folder: not/existing/dir', $e->getMessage());
        }
    }
}
