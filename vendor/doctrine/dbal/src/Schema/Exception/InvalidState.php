<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use LogicException;

use function sprintf;

final class InvalidState extends LogicException implements SchemaException
{
    public static function objectNameNotInitialized(): self
    {
        return new self('Object name has not been initialized.');
    }

    public static function indexHasInvalidType(string $indexName): self
    {
        return new self(sprintf('Index "%s" has invalid type.', $indexName));
    }

    public static function indexHasInvalidPredicate(string $indexName): self
    {
        return new self(sprintf('Index "%s" has invalid predicate.', $indexName));
    }

    public static function indexHasInvalidColumns(string $indexName): self
    {
        return new self(sprintf('Index "%s" has invalid columns.', $indexName));
    }

    public static function foreignKeyConstraintHasInvalidReferencedTableName(string $constraintName): self
    {
        return new self(sprintf(
            'Foreign key constraint "%s" has invalid referenced table name.',
            $constraintName,
        ));
    }

    public static function foreignKeyConstraintHasInvalidReferencingColumnNames(string $constraintName): self
    {
        return new self(sprintf(
            'Foreign key constraint "%s" has one or more invalid referencing column names.',
            $constraintName,
        ));
    }

    public static function foreignKeyConstraintHasInvalidReferencedColumnNames(string $constraintName): self
    {
        return new self(sprintf(
            'Foreign key constraint "%s" has one or more invalid referenced column name.',
            $constraintName,
        ));
    }

    public static function foreignKeyConstraintHasInvalidMatchType(string $constraintName): self
    {
        return new self(sprintf('Foreign key constraint "%s" has invalid match type.', $constraintName));
    }

    public static function foreignKeyConstraintHasInvalidOnUpdateAction(string $constraintName): self
    {
        return new self(sprintf('Foreign key constraint "%s" has invalid ON UPDATE action.', $constraintName));
    }

    public static function foreignKeyConstraintHasInvalidOnDeleteAction(string $constraintName): self
    {
        return new self(sprintf('Foreign key constraint "%s" has invalid ON DELETE action.', $constraintName));
    }

    public static function foreignKeyConstraintHasInvalidDeferrability(string $constraintName): self
    {
        return new self(sprintf('Foreign key constraint "%s" has invalid deferrability.', $constraintName));
    }

    public static function uniqueConstraintHasInvalidColumnNames(string $constraintName): self
    {
        return new self(sprintf('Unique constraint "%s" has one or more invalid column names.', $constraintName));
    }

    public static function uniqueConstraintHasEmptyColumnNames(string $constraintName): self
    {
        return new self(sprintf('Unique constraint "%s" has no column names.', $constraintName));
    }

    public static function tableHasInvalidPrimaryKeyConstraint(string $tableName): self
    {
        return new self(sprintf('Table "%s" has invalid primary key constraint.', $tableName));
    }

    public static function tableDiffContainsUnnamedDroppedForeignKeyConstraints(): self
    {
        return new self('Table diff contains unnamed dropped foreign key constraints');
    }
}
