<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\Color;

class ColorEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(Color $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     */
    public function encode(): string
    {
        return implode('', [
            $this->encodeColorValue($this->source->getRed()),
            $this->encodeColorValue($this->source->getGreen()),
            $this->encodeColorValue($this->source->getBlue()),
        ]);
    }

    /**
     * Encode color value
     */
    protected function encodeColorValue(int $value): string
    {
        return pack('C', $value);
    }
}
