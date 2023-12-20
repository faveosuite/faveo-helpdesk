<?php

namespace Unit;

use Flow\Config;
use Flow\File;
use Flow\FileLockException;
use Flow\FileOpenException;
use Flow\Request;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * File unit tests
 *
 * @coversDefaultClass \Flow\File
 *
 * @package Unit
 */
class FileTest extends FlowUnitCase
{
    /**
     * Config
     *
     * @var Config
     */
    protected $config;

    /**
     * Virtual file system
     *
     * @var vfsStreamDirectory
     */
    protected $vfs;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup virtual file system
        vfsStreamWrapper::register();
        $this->vfs = new vfsStreamDirectory('chunks');
        vfsStreamWrapper::setRoot($this->vfs);

        // Setup Config
        $this->config = new Config();
        $this->config->setTempDir($this->vfs->url());
    }

    /**
     * @covers ::__construct
     * @covers ::getIdentifier
     */
    public function testFile_construct_withRequest()
    {
        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $expIdentifier = sha1($this->requestArr['flowIdentifier']);
        $this->assertSame($expIdentifier, $file->getIdentifier());
    }

    /**
     * @covers ::__construct
     * @covers ::getIdentifier
     */
    public function testFile_construct_noRequest()
    {
        $_REQUEST = $this->requestArr;

        $file = new File($this->config);

        $expIdentifier = sha1($this->requestArr['flowIdentifier']);
        $this->assertSame($expIdentifier, $file->getIdentifier());
    }

    /**
     * @covers ::getChunkPath
     */
    public function testFile_construct_getChunkPath()
    {
        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $expPath = $this->vfs->url().DIRECTORY_SEPARATOR.sha1($this->requestArr['flowIdentifier']).'_1';
        $this->assertSame($expPath, $file->getChunkPath(1));
    }

    /**
     * @covers ::checkChunk
     */
    public function testFile_construct_checkChunk()
    {
        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $this->assertFalse($file->checkChunk());

        $chunkName = sha1($request->getIdentifier()).'_'.$request->getCurrentChunkNumber();
        $firstChunk = vfsStream::newFile($chunkName);
        $this->vfs->addChild($firstChunk);

        $this->assertTrue($file->checkChunk());
    }

    /**
     * @covers ::validateChunk
     */
    public function testFile_validateChunk()
    {
        // No $_FILES
        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $this->assertFalse($file->validateChunk());

        // No 'file' key $_FILES
        $fileInfo = [];
        $request = new Request($this->requestArr, $fileInfo);
        $file = new File($this->config, $request);

        $this->assertFalse($file->validateChunk());

        // Upload OK
        $fileInfo = [
            'size' => 10,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => ''
        ];
        $request = new Request($this->requestArr, $fileInfo);
        $file = new File($this->config, $request);
        $this->assertTrue($file->validateChunk());

        // Chunk size doesn't match
        $fileInfo = [
            'size' => 9,
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => ''
        ];
        $request = new Request($this->requestArr, $fileInfo);
        $file = new File($this->config, $request);
        $this->assertFalse($file->validateChunk());

        // Upload error
        $fileInfo = [
            'size' => 10,
            'error' => UPLOAD_ERR_EXTENSION,
            'tmp_name' => ''
        ];
        $request = new Request($this->requestArr, $fileInfo);
        $file = new File($this->config, $request);
        $this->assertFalse($file->validateChunk());
    }

    /**
     * @covers ::validateFile
     */
    public function testFile_validateFile()
    {
        $this->requestArr['flowTotalSize'] = 10;
        $this->requestArr['flowTotalChunks'] = 3;

        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);
        $chunkPrefix = sha1($request->getIdentifier()).'_';

        // No chunks uploaded yet
        $this->assertFalse($file->validateFile());

        // First chunk
        $firstChunk = vfsStream::newFile($chunkPrefix.'1');
        $firstChunk->setContent('123');
        $this->vfs->addChild($firstChunk);

        // Uploaded not yet complete
        $this->assertFalse($file->validateFile());

        // Second chunk
        $secondChunk = vfsStream::newFile($chunkPrefix.'2');
        $secondChunk->setContent('456');
        $this->vfs->addChild($secondChunk);

        // Uploaded not yet complete
        $this->assertFalse($file->validateFile());

        // Third chunk
        $lastChunk = vfsStream::newFile($chunkPrefix.'3');
        $lastChunk->setContent('7890');
        $this->vfs->addChild($lastChunk);

        // All chunks uploaded
        $this->assertTrue($file->validateFile());

        //// Test false values

        // File size doesn't match
        $lastChunk->setContent('789');
        $this->assertFalse($file->validateFile());

        // Correct file size and expect true
        $this->requestArr['flowTotalSize'] = 9;
        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);
        $this->assertTrue($file->validateFile());
    }

    /**
     * @covers ::deleteChunks
     */
    public function testFile_deleteChunks()
    {
        //// Setup test

        $this->requestArr['flowTotalChunks'] = 4;

        $fileInfo = [];
        $request = new Request($this->requestArr, $fileInfo);
        $file = new File($this->config, $request);
        $chunkPrefix = sha1($request->getIdentifier()).'_';

        $firstChunk = vfsStream::newFile($chunkPrefix. 1);
        $this->vfs->addChild($firstChunk);

        $secondChunk = vfsStream::newFile($chunkPrefix. 3);
        $this->vfs->addChild($secondChunk);

        $thirdChunk = vfsStream::newFile('other');
        $this->vfs->addChild($thirdChunk);

        //// Actual test

        $this->assertTrue(file_exists($firstChunk->url()));
        $this->assertTrue(file_exists($secondChunk->url()));
        $this->assertTrue(file_exists($thirdChunk->url()));

        $file->deleteChunks();
        $this->assertFalse(file_exists($firstChunk->url()));
        $this->assertFalse(file_exists($secondChunk->url()));
        $this->assertTrue(file_exists($thirdChunk->url()));
    }

    /**
     * @covers ::saveChunk
     */
    public function testFile_saveChunk()
    {
        //// Setup test

        // Setup temporary file
        $tmpDir = new vfsStreamDirectory('tmp');
        $tmpFile = vfsStream::newFile('tmpFile');
        $tmpFile->setContent('1234567890');
        $tmpDir->addChild($tmpFile);
        $this->vfs->addChild($tmpDir);
        $this->filesArr['file']['tmp_name'] = $tmpFile->url();

        // Mock File to use rename instead of move_uploaded_file
        $request = new Request($this->requestArr, $this->filesArr['file']);
        $file = $this->createMock('Flow\File'); //, ['_move_uploaded_file'], [$this->config, $request]);
        $file->expects($this->once())
            ->method('_move_uploaded_file')
            ->will($this->returnCallback(static function ($filename, $destination) {
                return rename($filename, $destination);
            }));

        // Expected destination file
        $expDstFile = $this->vfs->url().DIRECTORY_SEPARATOR.sha1($request->getIdentifier()).'_1';

        //// Accrual test

        $this->assertFalse(file_exists($expDstFile));
        $this->assertTrue(file_exists($tmpFile->url()));

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertTrue($file->saveChunk());

        $this->assertTrue(file_exists($expDstFile));
        //$this->assertFalse(file_exists($tmpFile->url()));

        $this->assertSame('1234567890', file_get_contents($expDstFile));
    }

    /**
     * @covers ::save
     */
    public function testFile_save()
    {
        //// Setup test

        $this->requestArr['flowTotalChunks'] = 3;
        $this->requestArr['flowTotalSize'] = 10;

        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $chunkPrefix = sha1($request->getIdentifier()).'_';

        $chunk = vfsStream::newFile($chunkPrefix.'1', 0777);
        $chunk->setContent('0123');
        $this->vfs->addChild($chunk);

        $chunk = vfsStream::newFile($chunkPrefix.'2', 0777);
        $chunk->setContent('456');
        $this->vfs->addChild($chunk);

        $chunk = vfsStream::newFile($chunkPrefix.'3', 0777);
        $chunk->setContent('789');
        $this->vfs->addChild($chunk);

        $filePath = $this->vfs->url().DIRECTORY_SEPARATOR.'file';

        //// Actual test

        $this->assertTrue($file->save($filePath));
        $this->assertTrue(file_exists($filePath));
        $this->assertEquals($request->getTotalSize(), filesize($filePath));
    }

    /**
     * @covers ::save
     */
    public function testFile_save_lock()
    {
        //// Setup test

        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $dstFile = $this->vfs->url().DIRECTORY_SEPARATOR.'file';

        // Lock file
        $fh = fopen($dstFile, 'wb');
        $this->assertTrue(flock($fh, LOCK_EX));

        //// Actual test

        try {
            // practically on a normal file system exception would not be thrown, this happens
            // because vfsStreamWrapper does not support locking with block
            $file->save($dstFile);
            $this->fail();
        } catch (FileLockException $e) {
            $this->assertEquals('failed to lock file: '.$dstFile, $e->getMessage());
        }
    }

    /**
     * @covers ::save
     */
    public function testFile_save_FileOpenException()
    {
        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        try {
            @$file->save('not/existing/path');
            $this->fail();
        } catch (FileOpenException $e) {
            $this->assertEquals('failed to open destination file: not/existing/path', $e->getMessage());
        }
    }

    /**
     * @covers ::save
     */
    public function testFile_save_chunk_FileOpenException()
    {
        //// Setup test

        $this->requestArr['flowTotalChunks'] = 3;
        $this->requestArr['flowTotalSize'] = 10;

        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $chunkPrefix = sha1($request->getIdentifier()).'_';

        $chunk = vfsStream::newFile($chunkPrefix.'1', 0777);
        $chunk->setContent('0123');
        $this->vfs->addChild($chunk);

        $chunk = vfsStream::newFile($chunkPrefix.'2', 0777);
        $chunk->setContent('456');
        $this->vfs->addChild($chunk);

        $missingChunk = $this->vfs->url().DIRECTORY_SEPARATOR.$chunkPrefix.'3';
        $filePath = $this->vfs->url().DIRECTORY_SEPARATOR.'file';

        //// Actual test

        try {
            @$file->save($filePath);
        } catch (FileOpenException $e) {
            $this->assertEquals('failed to open chunk: '.$missingChunk, $e->getMessage());
        }
    }

    /**
     * @covers ::save
     */
    public function testFile_save_preProcess()
    {
        //// Setup test

        $this->requestArr['flowTotalChunks'] = 1;
        $this->requestArr['flowTotalSize'] = 10;
        $processCalled = false;

        $process = static function ($chunk) use (&$processCalled) {
            $processCalled = true;
        };

        $this->config->setPreprocessCallback($process);

        $request = new Request($this->requestArr);
        $file = new File($this->config, $request);

        $chunkPrefix = sha1($request->getIdentifier()).'_';

        $chunk = vfsStream::newFile($chunkPrefix.'1', 0777);
        $chunk->setContent('1234567890');
        $this->vfs->addChild($chunk);

        $filePath = $this->vfs->url().DIRECTORY_SEPARATOR.'file';

        //// Actual test

        $this->assertTrue($file->save($filePath));
        $this->assertTrue(file_exists($filePath));
        $this->assertEquals($request->getTotalSize(), filesize($filePath));
        $this->assertTrue($processCalled);
    }
}
