<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\FormatException;

class DataSubBlockDecoder extends AbstractDecoder
{
    /**
     * Decode current sourc
     *
     * @throws FormatException
     * @throws DecoderException
     */
    public function decode(): DataSubBlock
    {
        $char = $this->getNextByteOrFail();
        $unpacked = unpack('C', $char);
        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Unable to decode data sub block.');
        }

        $size = (int) $unpacked[1];

        return new DataSubBlock($this->getNextBytesOrFail($size));
    }
}
