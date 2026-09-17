<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\AbstractEntity;
use Intervention\Gif\Blocks\DataSubBlock;
use Intervention\Gif\Exceptions\EncoderException;
use Intervention\Gif\Blocks\ImageData;

class ImageDataEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(ImageData $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     *
     * @throws EncoderException
     */
    public function encode(): string
    {
        if (!$this->source->hasBlocks()) {
            throw new EncoderException("No data blocks in ImageData.");
        }

        return implode('', [
            pack('C', $this->source->getLzwMinCodeSize()),
            implode('', array_map(
                fn(DataSubBlock $block): string => $block->encode(),
                $this->source->getBlocks(),
            )),
            AbstractEntity::TERMINATOR,
        ]);
    }
}
