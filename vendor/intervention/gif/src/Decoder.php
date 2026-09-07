<?php

declare(strict_types=1);

namespace Intervention\Gif;

use Intervention\Gif\Exceptions\DecoderException;
use Intervention\Gif\Exceptions\RuntimeException;
use Intervention\Gif\Traits\CanHandleFiles;

class Decoder
{
    use CanHandleFiles;

    /**
     * Decode given input
     *
     * @throws DecoderException
     */
    public static function decode(mixed $input): GifDataStream
    {
        try {
            $handle = match (true) {
                self::isFilePath($input) => self::getHandleFromFilePath($input),
                is_string($input) => self::getHandleFromData($input),
                self::isFileHandle($input) => $input,
                default => throw new DecoderException(
                    'Decoder input must be either file path, file pointer resource or binary data.'
                )
            };
        } catch (RuntimeException $e) {
            throw new DecoderException($e->getMessage());
        }

        rewind($handle);

        return GifDataStream::decode($handle);
    }

    /**
     * Determine if input is file pointer resource
     */
    private static function isFileHandle(mixed $input): bool
    {
        return is_resource($input) && get_resource_type($input) === 'stream';
    }
}
