<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractEntity;

/**
 * The GIF files that can be found on the Internet come in a wide variety
 * of forms. Some strictly adhere to the original specification, others do
 * not and differ in the actual sequence of blocks or their number.
 *
 * For this reason, this libary has this (kind of "virtual") FrameBlock,
 * which can contain all possible blocks in different order that occur in
 * a GIF animation.
 *
 * - Image Description
 * - Local Color Table
 * - Image Data Block
 * - Plain Text Extension
 * - Application Extension
 * - Comment Extension
 *
 * The TableBasedImage block, which is a chain of ImageDescriptor, (Local
 * Color Table) and ImageData, is used as a marker for terminating a
 * FrameBlock.
 *
 * So far I have only seen GIF files that follow this scheme. However, there are
 * examples which have one (or more) comment extensions added before the end. So
 * there can be additional "global comments" that are not part of the FrameBlock
 * and are appended to the GifDataStream afterwards.
 */
class FrameBlock extends AbstractEntity
{
    protected ?GraphicControlExtension $graphicControlExtension = null;

    protected ?ColorTable $colorTable = null;

    protected ?PlainTextExtension $plainTextExtension = null;

    /**
     * @var array<ApplicationExtension> $applicationExtensions
     */
    protected array $applicationExtensions = [];

    /**
     * @var array<CommentExtension> $commentExtensions
     */
    protected array $commentExtensions = [];

    public function __construct(
        protected ImageDescriptor $imageDescriptor = new ImageDescriptor(),
        protected ImageData $imageData = new ImageData()
    ) {
        //
    }

    public function addEntity(AbstractEntity $entity): self
    {
        return match (true) {
            $entity instanceof TableBasedImage => $this->setTableBasedImage($entity),
            $entity instanceof GraphicControlExtension => $this->setGraphicControlExtension($entity),
            $entity instanceof ImageDescriptor => $this->setImageDescriptor($entity),
            $entity instanceof ColorTable => $this->setColorTable($entity),
            $entity instanceof ImageData => $this->setImageData($entity),
            $entity instanceof PlainTextExtension => $this->setPlainTextExtension($entity),
            $entity instanceof NetscapeApplicationExtension,
            $entity instanceof ApplicationExtension => $this->addApplicationExtension($entity),
            $entity instanceof CommentExtension => $this->addCommentExtension($entity),
            default => $this,
        };
    }

    /**
     * Return application extensions of current frame block
     *
     * @return array<ApplicationExtension>
     */
    public function getApplicationExtensions(): array
    {
        return $this->applicationExtensions;
    }

    /**
     * Return comment extensions of current frame block
     *
     * @return array<CommentExtension>
     */
    public function getCommentExtensions(): array
    {
        return $this->commentExtensions;
    }

    /**
     * Set the graphic control extension
     */
    public function setGraphicControlExtension(GraphicControlExtension $extension): self
    {
        $this->graphicControlExtension = $extension;

        return $this;
    }

    /**
     * Get the graphic control extension of the current frame block
     */
    public function getGraphicControlExtension(): ?GraphicControlExtension
    {
        return $this->graphicControlExtension;
    }

    /**
     * Set the image descriptor
     */
    public function setImageDescriptor(ImageDescriptor $descriptor): self
    {
        $this->imageDescriptor = $descriptor;
        return $this;
    }

    /**
     * Get the image descriptor of the frame block
     */
    public function getImageDescriptor(): ImageDescriptor
    {
        return $this->imageDescriptor;
    }

    /**
     * Set the color table of the current frame block
     */
    public function setColorTable(ColorTable $table): self
    {
        $this->colorTable = $table;

        return $this;
    }

    /**
     * Get color table
     */
    public function getColorTable(): ?ColorTable
    {
        return $this->colorTable;
    }

    /**
     * Determine if frame block has color table
     */
    public function hasColorTable(): bool
    {
        return !is_null($this->colorTable);
    }

    /**
     * Set image data of frame block
     */
    public function setImageData(ImageData $data): self
    {
        $this->imageData = $data;

        return $this;
    }

    /**
     * Get image data of current frame block
     */
    public function getImageData(): ImageData
    {
        return $this->imageData;
    }

    /**
     * Set plain text extension
     */
    public function setPlainTextExtension(PlainTextExtension $extension): self
    {
        $this->plainTextExtension = $extension;

        return $this;
    }

    /**
     * Get plain text extension
     */
    public function getPlainTextExtension(): ?PlainTextExtension
    {
        return $this->plainTextExtension;
    }

    /**
     * Add given application extension to the current frame block
     */
    public function addApplicationExtension(ApplicationExtension $extension): self
    {
        $this->applicationExtensions[] = $extension;

        return $this;
    }

    /**
     * Remove all application extensions from the current frame block.
     */
    public function clearApplicationExtensions(): self
    {
        $this->applicationExtensions = [];

        return $this;
    }

    /**
     * Add given comment extension to the current frame block
     */
    public function addCommentExtension(CommentExtension $extension): self
    {
        $this->commentExtensions[] = $extension;

        return $this;
    }

    /**
     * Return netscape extension of the frame block if available
     */
    public function getNetscapeExtension(): ?NetscapeApplicationExtension
    {
        $extensions = array_filter(
            $this->applicationExtensions,
            fn(ApplicationExtension $extension): bool => $extension instanceof NetscapeApplicationExtension,
        );

        return count($extensions) ? reset($extensions) : null;
    }

    /**
     * Set the table based image of the current frame block
     */
    public function setTableBasedImage(TableBasedImage $tableBasedImage): self
    {
        $this->setImageDescriptor($tableBasedImage->getImageDescriptor());

        if ($colorTable = $tableBasedImage->getColorTable()) {
            $this->setColorTable($colorTable);
        }

        $this->setImageData($tableBasedImage->getImageData());

        return $this;
    }
}
