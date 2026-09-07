<?php

namespace Unit;

class FlowUnitCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Test request
     */
    protected array $requestArr;

    /**
     * $_FILES
     */
    protected array $filesArr;

    protected function setUp(): void
    {
        $this->requestArr = [
            'flowChunkNumber' => 1,
            'flowChunkSize' => 1048576,
            'flowCurrentChunkSize' => 10,
            'flowTotalSize' => 100,
            'flowIdentifier' => '13632-prettifyjs',
            'flowFilename' => 'prettify.js',
            'flowRelativePath' => 'home/prettify.js',
            'flowTotalChunks' => 42
        ];

        $this->filesArr = [
            'file' => [
                'name' => 'someFile.gif',
                'type' => 'image/gif',
                'size' => '10',
                'tmp_name' => '/tmp/abc1234',
                'error' => UPLOAD_ERR_OK
            ]
        ];
    }

    protected function tearDown(): void
    {
        $_REQUEST = [];
        $_FILES = [];
    }
}
