<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

class TableBasedImage extends AbstractEntity
{
    protected ImageDescriptor $imageDescriptor;
    protected ?ColorTable $colorTable = null;
    protected ImageData $imageData;

    public function getImageDescriptor(): ImageDescriptor
    {
        return $this->imageDescriptor;
    }

    public function setImageDescriptor(ImageDescriptor $descriptor): self
    {
        $this->imageDescriptor = $descriptor;

        return $this;
    }

    public function getImageData(): ImageData
    {
        return $this->imageData;
    }

    public function setImageData(ImageData $data): self
    {
        $this->imageData = $data;

        return $this;
    }

    public function getColorTable(): ?ColorTable
    {
        return $this->colorTable;
    }

    public function setColorTable(ColorTable $table): self
    {
        $this->colorTable = $table;

        return $this;
    }
}
