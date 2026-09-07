<?php

namespace Unit;

use Flow\Config;
use Flow\Request;

/**
 * Config unit tests
 *
 * @coversDefaultClass \Flow\Config
 *
 * @package Unit
 */
class ConfigTest extends FlowUnitCase
{
    /**
     * @covers ::getTempDir
     * @covers ::getDeleteChunksOnSave
     * @covers ::getHashNameCallback
     * @covers ::getPreprocessCallback
     * @covers ::__construct
     */
    public function testConfig_construct_config()
    {
        $exampleConfig = [
            'tempDir' => '/some/dir',
            'deleteChunksOnSave' => true,
            'hashNameCallback' => ['\SomeNs\SomeClass', 'someMethod'],
            'preprocessCallback' => ['\SomeNs\SomeClass', 'preProcess'],
        ];

        $config = new Config($exampleConfig);

        $this->assertSame($exampleConfig['tempDir'], $config->getTempDir());
        $this->assertSame($exampleConfig['deleteChunksOnSave'], $config->getDeleteChunksOnSave());
        $this->assertSame($exampleConfig['hashNameCallback'], $config->getHashNameCallback());
        $this->assertSame($exampleConfig['preprocessCallback'], $config->getPreprocessCallback());
    }

    /**
     * @covers ::getTempDir
     * @covers ::getDeleteChunksOnSave
     * @covers ::getHashNameCallback
     * @covers ::getPreprocessCallback
     * @covers ::__construct
     */
    public function testConfig_construct_default()
    {
        $config = new Config();

        $this->assertSame('', $config->getTempDir());
        $this->assertSame(true, $config->getDeleteChunksOnSave());
        $this->assertSame(['\Flow\Config', 'hashNameCallback'], $config->getHashNameCallback());
        $this->assertSame(null, $config->getPreprocessCallback());
    }

    /**
     * @covers ::setTempDir
     * @covers ::getTempDir
     */
    public function testConfig_setTempDir()
    {
        $dir = '/some/dir';
        $config = new Config();

        $config->setTempDir($dir);
        $this->assertSame($dir, $config->getTempDir());
    }

    /**
     * @covers ::setHashNameCallback
     * @covers ::getHashNameCallback
     */
    public function testConfig_setHashNameCallback()
    {
        $callback = ['\SomeNs\SomeClass', 'someMethod'];
        $config = new Config();

        $config->setHashNameCallback($callback);
        $this->assertSame($callback, $config->getHashNameCallback());
    }

    /**
     * @covers ::setPreprocessCallback
     * @covers ::getPreprocessCallback
     */
    public function testConfig_setPreprocessCallback()
    {
        $callback = ['\SomeNs\SomeClass', 'someOtherMethod'];
        $config = new Config();

        $config->setPreprocessCallback($callback);
        $this->assertSame($callback, $config->getPreprocessCallback());
    }

    /**
     * @covers ::setDeleteChunksOnSave
     * @covers ::getDeleteChunksOnSave
     */
    public function testConfig_setDeleteChunksOnSave()
    {
        $config = new Config();

        $config->setDeleteChunksOnSave(false);
        $this->assertFalse($config->getDeleteChunksOnSave());
    }

    public function testConfig_hashNameCallback()
    {
        $request = new Request($this->requestArr);

        $expHash = sha1($request->getIdentifier());
        $this->assertSame($expHash, Config::hashNameCallback($request));
    }
}
