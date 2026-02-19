<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Platforms\Keywords;

use function array_merge;

/** @deprecated */
class MariaDB117Keywords extends MariaDBKeywords
{
    /**
     * {@inheritDoc}
     *
     * @link https://mariadb.com/docs/server/reference/sql-structure/sql-language-structure/reserved-words
     */
    protected function getKeywords(): array
    {
        $keywords = parent::getKeywords();

        // New Keywords and Reserved Words
        $keywords = array_merge($keywords, ['VECTOR']);

        return $keywords;
    }
}
