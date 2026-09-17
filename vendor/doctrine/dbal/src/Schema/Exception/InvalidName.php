<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use InvalidArgumentException;

final class InvalidName extends InvalidArgumentException implements SchemaException
{
    public static function fromEmpty(): self
    {
        return new self('Name cannot be empty.');
    }
}
