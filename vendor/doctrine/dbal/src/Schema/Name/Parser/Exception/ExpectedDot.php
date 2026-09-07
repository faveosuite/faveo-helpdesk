<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Schema\Name\Parser\Exception;

use Doctrine\DBAL\Schema\Name\Parser\Exception;
use LogicException;

use function sprintf;

/** @internal */
class ExpectedDot extends LogicException implements Exception
{
    public static function new(int $position, string $got): self
    {
        return new self(sprintf('Expected dot at position %d, got "%s".', $position, $got));
    }
}
