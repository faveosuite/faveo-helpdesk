<?php

namespace Unit;

use Flow\Request;

/**
 * Request unit tests
 *
 * @coversDefaultClass \Flow\Request
 *
 * @package Unit
 */
class RequestTest extends FlowUnitCase
{
    /**
     * @covers ::__construct
     */
    public function testRequest_construct_withREQUEST()
    {
        $_REQUEST = $this->requestArr;

        $request = new Request();
        $this->assertSame('prettify.js', $request->getFileName());
        $this->assertSame(100, $request->getTotalSize());
        $this->assertSame('13632-prettifyjs', $request->getIdentifier());
        $this->assertSame('home/prettify.js', $request->getRelativePath());
        $this->assertSame(42, $request->getTotalChunks());
        $this->assertSame(1048576, $request->getDefaultChunkSize());
        $this->assertSame(1, $request->getCurrentChunkNumber());
        $this->assertSame(10, $request->getCurrentChunkSize());
        $this->assertSame(null, $request->getFile());
        $this->assertFalse($request->isFustyFlowRequest());
    }

    /**
     * @covers ::__construct
     * @covers ::getParam
     * @covers ::getFileName
     * @covers ::getTotalSize
     * @covers ::getIdentifier
     * @covers ::getRelativePath
     * @covers ::getTotalChunks
     * @covers ::getDefaultChunkSize
     * @covers ::getCurrentChunkNumber
     * @covers ::getCurrentChunkSize
     * @covers ::getFile
     * @covers ::isFustyFlowRequest
     */
    public function testRequest_construct_withCustomRequest()
    {
        $request = new Request($this->requestArr);

        $this->assertSame('prettify.js', $request->getFileName());
        $this->assertSame(100, $request->getTotalSize());
        $this->assertSame('13632-prettifyjs', $request->getIdentifier());
        $this->assertSame('home/prettify.js', $request->getRelativePath());
        $this->assertSame(42, $request->getTotalChunks());
        $this->assertSame(1048576, $request->getDefaultChunkSize());
        $this->assertSame(1, $request->getCurrentChunkNumber());
        $this->assertSame(10, $request->getCurrentChunkSize());
        $this->assertSame(null, $request->getFile());
        $this->assertFalse($request->isFustyFlowRequest());
    }

    /**
     * @covers ::__construct
     */
    public function testRequest_construct_withFILES()
    {
        $_FILES = $this->filesArr;

        $request = new Request();
        $this->assertSame($this->filesArr['file'], $request->getFile());
    }

    /**
     * @covers ::__construct
     */
    public function testRequest_construct_withCustFiles()
    {
        $request = new Request(null, $this->filesArr['file']);
        $this->assertSame($this->filesArr['file'], $request->getFile());
    }
}
