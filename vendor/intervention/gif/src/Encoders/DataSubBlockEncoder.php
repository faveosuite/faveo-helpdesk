<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\DataSubBlock;

class DataSubBlockEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(DataSubBlock $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     */
    public function encode(): string
    {
        return pack('C', $this->source->getSize()) . $this->source->getValue();
    }
}
