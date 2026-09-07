<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\ApplicationExtension;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Blocks\NetscapeApplicationExtension;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\FormatException;

class ApplicationExtensionDecoder extends AbstractDecoder
{
    /**
     * Decode current source
     *
     * @throws FormatException
     * @throws DecoderException
     */
    public function decode(): ApplicationExtension
    {
        $result = new ApplicationExtension();

        $this->getNextByteOrFail(); // marker
        $this->getNextByteOrFail(); // label
        $blocksize = $this->decodeBlockSize($this->getNextByteOrFail());
        $application = $this->getNextBytesOrFail($blocksize);

        if ($application === NetscapeApplicationExtension::IDENTIFIER . NetscapeApplicationExtension::AUTH_CODE) {
            $result = new NetscapeApplicationExtension();

            // skip length
            $this->getNextByteOrFail();

            $result->setBlocks([
                new DataSubBlock(
                    $this->getNextBytesOrFail(3)
                )
            ]);

            // skip terminator
            $this->getNextByteOrFail();

            return $result;
        }

        $result->setApplication($application);

        // decode data sub blocks
        $blocksize = $this->decodeBlockSize($this->getNextByteOrFail());
        while ($blocksize > 0) {
            $result->addBlock(new DataSubBlock($this->getNextBytesOrFail($blocksize)));
            $blocksize = $this->decodeBlockSize($this->getNextByteOrFail());
        }

        return $result;
    }

    /**
     * Decode block size of ApplicationExtension from given byte
     *
     * @throws DecoderException
     */
    protected function decodeBlockSize(string $byte): int
    {
        $unpacked = @unpack('C', $byte);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Unable to decode application extension block size.');
        }

        return intval($unpacked[1]);
    }
}
