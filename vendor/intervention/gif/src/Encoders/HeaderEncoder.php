<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\Header;

class HeaderEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(Header $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     */
    public function encode(): string
    {
        return Header::SIGNATURE . $this->source->getVersion();
    }
}
