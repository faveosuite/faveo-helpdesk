<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class ColorTable extends AbstractEntity
{
    /**
     * Create new instance
     *
     * @param array<Color> $colors
     * @return void
     */
    public function __construct(protected array $colors = [])
    {
        //
    }

    /**
     * Return array of current colors
     *
     * @return array<Color>
     */
    public function getColors(): array
    {
        return array_values($this->colors);
    }

    /**
     * Add color to table
     */
    public function addRgb(int $r, int $g, int $b): self
    {
        $this->addColor(new Color($r, $g, $b));

        return $this;
    }

    /**
     * Add color to table
     */
    public function addColor(Color $color): self
    {
        $this->colors[] = $color;

        return $this;
    }

    /**
     * Reset colors to array of color objects
     *
     * @param array<Color> $colors
     */
    public function setColors(array $colors): self
    {
        $this->empty();
        foreach ($colors as $color) {
            $this->addColor($color);
        }

        return $this;
    }

    /**
     * Count colors of current instance
     */
    public function countColors(): int
    {
        return count($this->colors);
    }

    /**
     * Determine if any colors are present on the current table
     */
    public function hasColors(): bool
    {
        return $this->countColors() >= 1;
    }

    /**
     * Empty color table
     */
    public function empty(): self
    {
        $this->colors = [];

        return $this;
    }

    /**
     * Get size of color table in logical screen descriptor
     */
    public function getLogicalSize(): int
    {
        return match ($this->countColors()) {
            4 => 1,
            8 => 2,
            16 => 3,
            32 => 4,
            64 => 5,
            128 => 6,
            256 => 7,
            default => 0,
        };
    }

    /**
     * Calculate the number of bytes contained by the current table
     */
    public function getByteSize(): int
    {
        if (!$this->hasColors()) {
            return 0;
        }

        return 3 * pow(2, $this->getLogicalSize() + 1);
    }
}
