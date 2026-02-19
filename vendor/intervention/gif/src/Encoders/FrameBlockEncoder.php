<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\ApplicationExtension;
use Intervention\Gif\Blocks\CommentExtension;
use Intervention\Gif\Blocks\FrameBlock;
use Intervention\Gif\Exceptions\EncoderException;

class FrameBlockEncoder extends AbstractEncoder
{
    /**
     * Create new decoder instance
     */
    public function __construct(FrameBlock $source)
    {
        $this->source = $source;
    }

    /**
     * @throws EncoderException
     */
    public function encode(): string
    {
        $graphicControlExtension = $this->source->getGraphicControlExtension();
        $colorTable = $this->source->getColorTable();
        $plainTextExtension = $this->source->getPlainTextExtension();

        return implode('', [
            implode('', array_map(
                fn(ApplicationExtension $extension): string => $extension->encode(),
                $this->source->getApplicationExtensions(),
            )),
            implode('', array_map(
                fn(CommentExtension $extension): string => $extension->encode(),
                $this->source->getCommentExtensions(),
            )),
            $plainTextExtension ? $plainTextExtension->encode() : '',
            $graphicControlExtension ? $graphicControlExtension->encode() : '',
            $this->source->getImageDescriptor()->encode(),
            $colorTable ? $colorTable->encode() : '',
            $this->source->getImageData()->encode(),
        ]);
    }
}
