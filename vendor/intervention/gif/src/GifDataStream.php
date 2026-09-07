<?php

declare(strict_types=1);

namespace Intervention\Gif;

use Intervention\Gif\Blocks\ColorTable;
use Intervention\Gif\Blocks\CommentExtension;
use Intervention\Gif\Blocks\FrameBlock;
use Intervention\Gif\Blocks\Header;
use Intervention\Gif\Blocks\LogicalScreenDescriptor;
use Intervention\Gif\Blocks\NetscapeApplicationExtension;
use Intervention\Gif\Blocks\Trailer;

class GifDataStream extends AbstractEntity
{
    /**
     * Create new instance
     *
     * @param array<FrameBlock> $frames
     * @param array<CommentExtension> $comments
     */
    public function __construct(
        protected Header $header = new Header(),
        protected LogicalScreenDescriptor $logicalScreenDescriptor = new LogicalScreenDescriptor(),
        protected ?ColorTable $globalColorTable = null,
        protected array $frames = [],
        protected array $comments = []
    ) {
        //
    }

    /**
     * Get header
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * Set header
     */
    public function setHeader(Header $header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get logical screen descriptor
     */
    public function getLogicalScreenDescriptor(): LogicalScreenDescriptor
    {
        return $this->logicalScreenDescriptor;
    }

    /**
     * Set logical screen descriptor
     */
    public function setLogicalScreenDescriptor(LogicalScreenDescriptor $descriptor): self
    {
        $this->logicalScreenDescriptor = $descriptor;

        return $this;
    }

    /**
     * Return global color table if available else null
     */
    public function getGlobalColorTable(): ?ColorTable
    {
        return $this->globalColorTable;
    }

    /**
     * Set global color table
     */
    public function setGlobalColorTable(ColorTable $table): self
    {
        $this->globalColorTable = $table;
        $this->logicalScreenDescriptor->setGlobalColorTableExistance(true);
        $this->logicalScreenDescriptor->setGlobalColorTableSize(
            $table->getLogicalSize()
        );

        return $this;
    }

    /**
     * Get main graphic control extension
     */
    public function getMainApplicationExtension(): ?NetscapeApplicationExtension
    {
        foreach ($this->frames as $frame) {
            if ($extension = $frame->getNetscapeExtension()) {
                return $extension;
            }
        }

        return null;
    }

    /**
     * Get array of frames
     *
     * @return array<FrameBlock>
     */
    public function getFrames(): array
    {
        return $this->frames;
    }

    /**
     * Return array of "global" comments
     *
     * @return array<CommentExtension>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * Return first frame
     */
    public function getFirstFrame(): ?FrameBlock
    {
        if (!array_key_exists(0, $this->frames)) {
            return null;
        }

        return $this->frames[0];
    }

    /**
     * Add frame
     */
    public function addFrame(FrameBlock $frame): self
    {
        $this->frames[] = $frame;

        return $this;
    }

    /**
     * Add comment extension
     */
    public function addComment(CommentExtension $comment): self
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Set the current data
     *
     * @param array<FrameBlock> $frames
     */
    public function setFrames(array $frames): self
    {
        $this->frames = $frames;

        return $this;
    }

    /**
     * Get trailer
     */
    public function getTrailer(): Trailer
    {
        return new Trailer();
    }

    /**
     * Determine if gif is animated
     */
    public function isAnimated(): bool
    {
        return count($this->getFrames()) > 1;
    }

    /**
     * Determine if global color table is set
     */
    public function hasGlobalColorTable(): bool
    {
        return !is_null($this->globalColorTable);
    }
}
