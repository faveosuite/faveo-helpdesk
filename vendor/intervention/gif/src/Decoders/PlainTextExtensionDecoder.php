<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\PlainTextExtension;
use Intervention\Gif\Exceptions\DecoderException;

class PlainTextExtensionDecoder extends AbstractDecoder
{
    /**
     * Decode current source
     *
     * @throws DecoderException
     */
    public function decode(): PlainTextExtension
    {
        $extension = new PlainTextExtension();

        // skip marker & label
        $this->getNextBytesOrFail(2);

        // skip info block
        $this->getNextBytesOrFail($this->getInfoBlockSize());

        // text blocks
        $extension->setText($this->decodeTextBlocks());

        return $extension;
    }

    /**
     * Get number of bytes in header block
     *
     * @throws DecoderException
     */
    protected function getInfoBlockSize(): int
    {
        $unpacked = unpack('C', $this->getNextByteOrFail());
        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Unable to decode info block size.');
        }

        return $unpacked[1];
    }

    /**
     * Decode text sub blocks
     *
     * @throws DecoderException
     * @return array<string>
     */
    protected function decodeTextBlocks(): array
    {
        $blocks = [];

        do {
            $char = $this->getNextByteOrFail();
            $unpacked = unpack('C', $char);
            if ($unpacked === false || !array_key_exists(1, $unpacked)) {
                throw new DecoderException('Unable to decode text block.');
            }

            $size = (int) $unpacked[1];

            if ($size > 0) {
                $blocks[] = $this->getNextBytesOrFail($size);
            }
        } while ($char !== PlainTextExtension::TERMINATOR);

        return $blocks;
    }
}
