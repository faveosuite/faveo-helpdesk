<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Schema\Name\Parser\Exception;

use Doctrine\DBAL\Schema\Name\Parser\Exception;
use LogicException;

/** @internal */
class ExpectedNextIdentifier extends LogicException implements Exception
{
    public static function new(): self
    {
        return new self('Unexpected end of input. Next identifier expected.');
    }
}
