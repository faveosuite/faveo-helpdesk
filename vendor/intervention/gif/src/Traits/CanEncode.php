<?php

declare(strict_types=1);

namespace Intervention\Gif\Traits;

use Intervention\Gif\Encoders\AbstractEncoder;
use Intervention\Gif\Exceptions\EncoderException;

trait CanEncode
{
    /**
     * Encode current entity
     *
     * @throws EncoderException
     */
    public function encode(): string
    {
        return $this->getEncoder()->encode();
    }

    /**
     * Get encoder object for current entity
     *
     * @throws EncoderException
     */
    protected function getEncoder(): AbstractEncoder
    {
        $classname = sprintf('Intervention\Gif\Encoders\%sEncoder', $this->getShortClassname());

        if (!class_exists($classname)) {
            throw new EncoderException("Encoder for '" . $this::class . "' not found.");
        }

        $encoder = new $classname($this);

        if (!($encoder instanceof AbstractEncoder)) {
            throw new EncoderException("Encoder for '" . $this::class . "' not found.");
        }

        return $encoder;
    }
}
