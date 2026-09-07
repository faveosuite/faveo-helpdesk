<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractExtension;

class CommentExtension extends AbstractExtension
{
    public const LABEL = "\xFE";

    /**
     * Comment blocks
     *
     * @var array<string>
     */
    protected array $comments = [];

    /**
     * Get all or one comment
     *
     * @return array<string>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * Get one comment by key
     */
    public function getComment(int $key): mixed
    {
        return $this->comments[$key] ?? null;
    }

    /**
     * Set comment text
     */
    public function addComment(string $value): self
    {
        $this->comments[] = $value;

        return $this;
    }
}
