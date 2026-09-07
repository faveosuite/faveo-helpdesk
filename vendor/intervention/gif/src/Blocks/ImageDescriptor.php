<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class ImageDescriptor extends AbstractEntity
{
    public const SEPARATOR = "\x2C";

    /**
     * Width of frame
     */
    protected int $width = 0;

    /**
     * Height of frame
     */
    protected int $height = 0;

    /**
     * Left position of frame
     */
    protected int $left = 0;

    /**
     * Top position of frame
     */
    protected int $top = 0;

    /**
     * Determine if frame is interlaced
     */
    protected bool $interlaced = false;

    /**
     * Local color table flag
     */
    protected bool $localColorTableExistance = false;

    /**
     * Sort flag of local color table
     */
    protected bool $localColorTableSorted = false;

    /**
     * Size of local color table
     */
    protected int $localColorTableSize = 0;

    /**
     * Get current width
     */
    public function getWidth(): int
    {
        return intval($this->width);
    }

    /**
     * Get current width
     */
    public function getHeight(): int
    {
        return intval($this->height);
    }

    /**
     * Get current Top
     */
    public function getTop(): int
    {
        return intval($this->top);
    }

    /**
     * Get current Left
     */
    public function getLeft(): int
    {
        return intval($this->left);
    }

    /**
     * Set size of current instance
     */
    public function setSize(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Set position of current instance
     */
    public function setPosition(int $left, int $top): self
    {
        $this->left = $left;
        $this->top = $top;

        return $this;
    }

    /**
     * Determine if frame is interlaced
     */
    public function isInterlaced(): bool
    {
        return $this->interlaced;
    }

    /**
     * Set or unset interlaced value
     */
    public function setInterlaced(bool $value = true): self
    {
        $this->interlaced = $value;

        return $this;
    }

    /**
     * Determine if local color table is present
     */
    public function getLocalColorTableExistance(): bool
    {
        return $this->localColorTableExistance;
    }

    /**
     * Alias for getLocalColorTableExistance
     */
    public function hasLocalColorTable(): bool
    {
        return $this->getLocalColorTableExistance();
    }

    /**
     * Set local color table flag
     */
    public function setLocalColorTableExistance(bool $existance = true): self
    {
        $this->localColorTableExistance = $existance;

        return $this;
    }

    /**
     * Get local color table sorted flag
     */
    public function getLocalColorTableSorted(): bool
    {
        return $this->localColorTableSorted;
    }

    /**
     * Set local color table sorted flag
     */
    public function setLocalColorTableSorted(bool $sorted = true): self
    {
        $this->localColorTableSorted = $sorted;

        return $this;
    }

    /**
     * Get size of local color table
     */
    public function getLocalColorTableSize(): int
    {
        return $this->localColorTableSize;
    }

    /**
     * Get byte size of global color table
     */
    public function getLocalColorTableByteSize(): int
    {
        return 3 * pow(2, $this->getLocalColorTableSize() + 1);
    }

    /**
     * Set size of local color table
     */
    public function setLocalColorTableSize(int $size): self
    {
        $this->localColorTableSize = $size;

        return $this;
    }
}
