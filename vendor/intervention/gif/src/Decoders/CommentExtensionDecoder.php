<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Blocks\CommentExtension;
use Intervention\Gif\Exceptions\DecoderException;

class CommentExtensionDecoder extends AbstractDecoder
{
    /**
     * Decode current source
     *
     * @throws DecoderException
     */
    public function decode(): CommentExtension
    {
        $this->getNextBytesOrFail(2); // skip marker & label

        $extension = new CommentExtension();
        foreach ($this->decodeComments() as $comment) {
            $extension->addComment($comment);
        }

        return $extension;
    }

    /**
     * Decode comment from current source
     *
     * @throws DecoderException
     * @return array<string>
     */
    protected function decodeComments(): array
    {
        $comments = [];

        do {
            $byte = $this->getNextByteOrFail();
            $size = $this->decodeBlocksize($byte);
            if ($size > 0) {
                $comments[] = $this->getNextBytesOrFail($size);
            }
        } while ($byte !== CommentExtension::TERMINATOR);

        return $comments;
    }

    /**
     * Decode blocksize of following comment
     *
     * @throws DecoderException
     */
    protected function decodeBlocksize(string $byte): int
    {
        $unpacked = @unpack('C', $byte);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Unable to decode comment extension block size.');
        }

        return intval($unpacked[1]);
    }
}
