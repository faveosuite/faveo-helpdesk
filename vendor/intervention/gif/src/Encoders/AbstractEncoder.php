<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

abstract class AbstractEncoder
{
    /**
     * Encode current source
     */
    abstract public function encode(): string;

    /**
     * Create new instance
     */
    public function __construct(protected mixed $source)
    {
        //
    }
}
