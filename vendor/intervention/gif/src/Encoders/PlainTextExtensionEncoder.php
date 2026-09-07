<?php

declare(strict_types=1);

namespace Intervention\Gif\Encoders;

use Intervention\Gif\Blocks\PlainTextExtension;

class PlainTextExtensionEncoder extends AbstractEncoder
{
    /**
     * Create new instance
     */
    public function __construct(PlainTextExtension $source)
    {
        $this->source = $source;
    }

    /**
     * Encode current source
     */
    public function encode(): string
    {
        if (!$this->source->hasText()) {
            return '';
        }

        return implode('', [
            PlainTextExtension::MARKER,
            PlainTextExtension::LABEL,
            $this->encodeHead(),
            $this->encodeTexts(),
            PlainTextExtension::TERMINATOR,
        ]);
    }

    /**
     * Encode head block
     */
    protected function encodeHead(): string
    {
        return "\x0c\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
    }

    /**
     * Encode text chunks
     */
    protected function encodeTexts(): string
    {
        return implode('', array_map(
            fn(string $text): string => pack('C', strlen($text)) . $text,
            $this->source->getText(),
        ));
    }
}
