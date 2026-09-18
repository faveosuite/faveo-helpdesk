<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class Header extends AbstractEntity
{
    /**
     * Header signature
     */
    public const SIGNATURE = 'GIF';

    /**
     * Current GIF version
     */
    protected string $version = '89a';

    /**
     * Set GIF version
     */
    public function setVersion(string $value): self
    {
        $this->version = $value;

        return $this;
    }

    /**
     * Return current version
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
