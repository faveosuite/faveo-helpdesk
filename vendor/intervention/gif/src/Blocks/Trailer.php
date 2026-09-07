<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class Trailer extends AbstractEntity
{
    public const MARKER = "\x3b";
}
