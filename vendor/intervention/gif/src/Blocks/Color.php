<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class Color extends AbstractEntity
{
    /**
     * Create new instance
     */
    public function __construct(
        protected int $r = 0,
        protected int $g = 0,
        protected int $b = 0
    ) {
        //
    }

    /**
     * Get red value
     */
    public function getRed(): int
    {
        return $this->r;
    }

    /**
     * Set red value
     */
    public function setRed(int $value): self
    {
        $this->r = $value;

        return $this;
    }

    /**
     * Get green value
     */
    public function getGreen(): int
    {
        return $this->g;
    }

    /**
     * Set green value
     */
    public function setGreen(int $value): self
    {
        $this->g = $value;

        return $this;
    }

    /**
     * Get blue value
     */
    public function getBlue(): int
    {
        return $this->b;
    }

    /**
     * Set blue value
     */
    public function setBlue(int $value): self
    {
        $this->b = $value;

        return $this;
    }

    /**
     * Return hash value of current color
     */
    public function getHash(): string
    {
        return md5($this->r . $this->g . $this->b);
    }
}
