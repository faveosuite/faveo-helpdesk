<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\GraphicControlExtension;

class GraphicControlExtensionEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(GraphicControlExtension $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     */
    public function encode(): string
    {
        return implode('', [
            GraphicControlExtension::MARKER,
            GraphicControlExtension::LABEL,
            GraphicControlExtension::BLOCKSIZE,
            $this->encodePackedField(),
            $this->encodeDelay(),
            $this->encodeTransparentColorIndex(),
            GraphicControlExtension::TERMINATOR,
        ]);
    }

    /**
     * Encode delay time
     */
    protected function encodeDelay(): string
    {
        return pack('v*', $this->source->getDelay());
    }

    /**
     * Encode transparent color index
     */
    protected function encodeTransparentColorIndex(): string
    {
        return pack('C', $this->source->getTransparentColorIndex());
    }

    /**
     * Encode packed field
     */
    protected function encodePackedField(): string
    {
        return pack('C', bindec(implode('', [
            str_pad('0', 3, '0', STR_PAD_LEFT),
            str_pad(decbin($this->source->getDisposalMethod()->value), 3, '0', STR_PAD_LEFT),
            (int) $this->source->getUserInput(),
            (int) $this->source->getTransparentColorExistance(),
        ])));
    }
}
