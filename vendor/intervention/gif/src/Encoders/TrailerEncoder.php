<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\Trailer;

class TrailerEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(Trailer $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     */
    public function encode(): string
    {
        return Trailer::MARKER;
    }
}
