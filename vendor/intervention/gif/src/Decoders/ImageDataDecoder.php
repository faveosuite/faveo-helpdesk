<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Blocks\ImageData;
use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\FormatException;

class ImageDataDecoder extends AbstractDecoder
{
    /**
     * Decode current source
     *
     * @throws DecoderException
     * @throws FormatException
     */
    public function decode(): ImageData
    {
        $data = new ImageData();

        // LZW min. code size
        $char = $this->getNextByteOrFail();
        $unpacked = unpack('C', $char);
        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Unable to decode lzw min. code size.');
        }

        $data->setLzwMinCodeSize(intval($unpacked[1]));

        do {
            // decode sub blocks
            $char = $this->getNextByteOrFail();
            $unpacked = unpack('C', $char);
            if ($unpacked === false || !array_key_exists(1, $unpacked)) {
                throw new DecoderException('Unable to decode image data sub block.');
            }

            $size = intval($unpacked[1]);

            if ($size > 0) {
                $data->addBlock(new DataSubBlock($this->getNextBytesOrFail($size)));
            }
        } while ($char !== AbstractEntity::TERMINATOR);

        return $data;
    }
}
