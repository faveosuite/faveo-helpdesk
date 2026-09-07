<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class ImageData extends AbstractEntity
{
    /**
     * LZW min. code size
     */
    protected int $lzw_min_code_size;

    /**
     * Sub blocks
     *
     * @var array<DataSubBlock>
     */
    protected array $blocks = [];

    /**
     * Get LZW min. code size
     */
    public function getLzwMinCodeSize(): int
    {
        return $this->lzw_min_code_size;
    }

    /**
     * Set lzw min. code size
     */
    public function setLzwMinCodeSize(int $size): self
    {
        $this->lzw_min_code_size = $size;

        return $this;
    }

    /**
     * Get current data sub blocks
     *
     * @return array<DataSubBlock>
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * Addd sub block
     */
    public function addBlock(DataSubBlock $block): self
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     * Determine if data sub blocks are present
     */
    public function hasBlocks(): bool
    {
        return count($this->blocks) >= 1;
    }
}
