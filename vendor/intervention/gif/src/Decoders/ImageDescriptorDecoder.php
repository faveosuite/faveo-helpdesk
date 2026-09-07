<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\ImageDescriptor;
use Intervention\Gif\Exceptions\DecoderException;

class ImageDescriptorDecoder extends AbstractPackedBitDecoder
{
    /**
     * Decode given string to current instance
     *
     * @throws DecoderException
     */
    public function decode(): ImageDescriptor
    {
        $descriptor = new ImageDescriptor();

        $this->getNextByteOrFail(); // skip separator

        $descriptor->setPosition(
            $this->decodeMultiByte($this->getNextBytesOrFail(2)),
            $this->decodeMultiByte($this->getNextBytesOrFail(2))
        );

        $descriptor->setSize(
            $this->decodeMultiByte($this->getNextBytesOrFail(2)),
            $this->decodeMultiByte($this->getNextBytesOrFail(2))
        );

        $packedField = $this->getNextByteOrFail();

        $descriptor->setLocalColorTableExistance(
            $this->decodeLocalColorTableExistance($packedField)
        );

        $descriptor->setLocalColorTableSorted(
            $this->decodeLocalColorTableSorted($packedField)
        );

        $descriptor->setLocalColorTableSize(
            $this->decodeLocalColorTableSize($packedField)
        );

        $descriptor->setInterlaced(
            $this->decodeInterlaced($packedField)
        );

        return $descriptor;
    }

    /**
     * Decode local color table existance
     *
     * @throws DecoderException
     */
    protected function decodeLocalColorTableExistance(string $byte): bool
    {
        return $this->hasPackedBit($byte, 0);
    }

    /**
     * Decode local color table sort method
     *
     * @throws DecoderException
     */
    protected function decodeLocalColorTableSorted(string $byte): bool
    {
        return $this->hasPackedBit($byte, 2);
    }

    /**
     * Decode local color table size
     *
     * @throws DecoderException
     */
    protected function decodeLocalColorTableSize(string $byte): int
    {
        return (int) bindec($this->getPackedBits($byte, 5, 3));
    }

    /**
     * Decode interlaced flag
     *
     * @throws DecoderException
     */
    protected function decodeInterlaced(string $byte): bool
    {
        return $this->hasPackedBit($byte, 1);
    }
}
