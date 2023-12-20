<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\CommentExtension;
use Intervention\Gif\Blocks\FrameBlock;
use Intervention\Gif\Exceptions\EncoderException;
use Intervention\Gif\GifDataStream;

class GifDataStreamEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(GifDataStream $source)
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
        return implode('', [
            $this->source->getHeader()->encode(),
            $this->source->getLogicalScreenDescriptor()->encode(),
            $this->maybeEncodeGlobalColorTable(),
            $this->encodeFrames(),
            $this->encodeComments(),
            $this->source->getTrailer()->encode(),
        ]);
    }

    protected function maybeEncodeGlobalColorTable(): string
    {
        if (!$this->source->hasGlobalColorTable()) {
            return '';
        }

        return $this->source->getGlobalColorTable()->encode();
    }

    /**
     * Encode data blocks of source
     *
     * @throws EncoderException
     */
    protected function encodeFrames(): string
    {
        return implode('', array_map(
            fn(FrameBlock $frame): string => $frame->encode(),
            $this->source->getFrames(),
        ));
    }

    /**
     * Encode comment extension blocks of source
     *
     * @throws EncoderException
     */
    protected function encodeComments(): string
    {
        return implode('', array_map(
            fn(CommentExtension $commentExtension): string => $commentExtension->encode(),
            $this->source->getComments()
        ));
    }
}
