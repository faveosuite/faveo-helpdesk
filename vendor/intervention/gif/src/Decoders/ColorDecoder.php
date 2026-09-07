<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\Color;
use Intervention\Gif\Exceptions\DecoderException;

class ColorDecoder extends AbstractDecoder
{
    /**
     * Decode current source to Color
     *
     * @throws DecoderException
     */
    public function decode(): Color
    {
        $color = new Color();

        $color->setRed($this->decodeColorValue($this->getNextByteOrFail()));
        $color->setGreen($this->decodeColorValue($this->getNextByteOrFail()));
        $color->setBlue($this->decodeColorValue($this->getNextByteOrFail()));

        return $color;
    }

    /**
     * Decode red value from source
     *
     * @throws DecoderException
     */
    protected function decodeColorValue(string $byte): int
    {
        $unpacked = unpack('C', $byte);
        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Unable to decode color value.');
        }

        return $unpacked[1];
    }
}
