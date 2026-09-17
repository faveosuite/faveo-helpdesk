<?php

declare(strict_types=1);

namespace Intervention\Gif;

use Intervention\Gif\Exceptions\EncoderException;
use Intervention\Gif\Traits\CanDecode;
use Intervention\Gif\Traits\CanEncode;
use ReflectionClass;
use Stringable;

abstract class AbstractEntity implements Stringable
{
    use CanEncode;
    use CanDecode;

    public const TERMINATOR = "\x00";

    /**
     * Get short classname of current instance
     */
    public static function getShortClassname(): string
    {
        return (new ReflectionClass(static::class))->getShortName();
    }

    /**
     * Cast object to string
     *
     * @throws EncoderException
     */
    public function __toString(): string
    {
        return $this->encode();
    }
}
