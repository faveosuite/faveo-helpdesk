<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\CommentExtension;

class CommentExtensionEncoder extends AbstractEncoder
{
    /**
     * Create new decoder instance
     */
    public function __construct(CommentExtension $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     */
    public function encode(): string
    {
        return implode('', [
            CommentExtension::MARKER,
            CommentExtension::LABEL,
            $this->encodeComments(),
            CommentExtension::TERMINATOR,
        ]);
    }

    /**
     * Encode comment blocks
     */
    protected function encodeComments(): string
    {
        return implode('', array_map(function (string $comment): string {
            return pack('C', strlen($comment)) . $comment;
        }, $this->source->getComments()));
    }
}
