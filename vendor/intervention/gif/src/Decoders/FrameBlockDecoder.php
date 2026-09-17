<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\AbstractExtension;
use Intervention\Gif\Blocks\ApplicationExtension;
use Intervention\Gif\Blocks\CommentExtension;
use Intervention\Gif\Blocks\FrameBlock;
use Intervention\Gif\Blocks\GraphicControlExtension;
use Intervention\Gif\Blocks\ImageDescriptor;
use Intervention\Gif\Blocks\NetscapeApplicationExtension;
use Intervention\Gif\Blocks\PlainTextExtension;
use Intervention\Gif\Blocks\TableBasedImage;
use Intervention\Gif\Exceptions\DecoderException;

class FrameBlockDecoder extends AbstractDecoder
{
    /**
     * Decode FrameBlock
     *
     * @throws DecoderException
     */
    public function decode(): FrameBlock
    {
        $frame = new FrameBlock();

        do {
            $block = match ($this->viewNextBytesOrFail(2)) {
                AbstractExtension::MARKER . GraphicControlExtension::LABEL
                => GraphicControlExtension::decode($this->handle),
                AbstractExtension::MARKER . NetscapeApplicationExtension::LABEL
                => NetscapeApplicationExtension::decode($this->handle),
                AbstractExtension::MARKER . ApplicationExtension::LABEL
                => ApplicationExtension::decode($this->handle),
                AbstractExtension::MARKER . PlainTextExtension::LABEL
                => PlainTextExtension::decode($this->handle),
                AbstractExtension::MARKER . CommentExtension::LABEL
                => CommentExtension::decode($this->handle),
                default => match ($this->viewNextByteOrFail()) {
                    ImageDescriptor::SEPARATOR => TableBasedImage::decode($this->handle),
                    default => throw new DecoderException('Unable to decode Data Block'),
                }
            };

            $frame->addEntity($block);
        } while (!($block instanceof TableBasedImage));

        return $frame;
    }
}
