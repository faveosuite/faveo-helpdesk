<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Schema\Index;

use Doctrine\DBAL\Schema\Exception\InvalidIndexDefinition;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;

final readonly class IndexedColumn
{
    /**
     * @internal
     *
     * @param ?positive-int $length
     */
    public function __construct(private UnqualifiedName $columnName, private ?int $length)
    {
        if ($length !== null && $length <= 0) {
            throw InvalidIndexDefinition::fromNonPositiveColumnLength($columnName, $length);
        }
    }

    public function getColumnName(): UnqualifiedName
    {
        return $this->columnName;
    }

    /** @return ?positive-int */
    public function getLength(): ?int
    {
        return $this->length;
    }
}
