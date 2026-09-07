<?php

declare(strict_types=1);

namespace Intervention\Gif\Decoders;

use Intervention\Gif\Exceptions\DecoderException;

abstract class AbstractDecoder
{
    /**
     * Decode current source
     */
    abstract public function decode(): mixed;

    /**
     * Create new instance
     */
    public function __construct(protected mixed $handle, protected ?int $length = null)
    {
        //
    }

    /**
     * Set source to decode
     */
    public function setHandle(mixed $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * Read given number of bytes and move file pointer
     *
     * @throws DecoderException
     */
    protected function getNextBytesOrFail(int $length): string
    {
        if ($length < 1) {
            throw new DecoderException('The length passed must be at least one byte.');
        }

        $bytes = fread($this->handle, $length);
        if ($bytes === false || strlen($bytes) !== $length) {
            throw new DecoderException('Unexpected end of file.');
        }

        return $bytes;
    }

    /**
     * Read given number of bytes and move pointer back to previous position
     *
     * @throws DecoderException
     */
    protected function viewNextBytesOrFail(int $length): string
    {
        $bytes = $this->getNextBytesOrFail($length);
        $this->movePointer($length * -1);

        return $bytes;
    }

    /**
     * Read next byte and move pointer back to previous position
     *
     * @throws DecoderException
     */
    protected function viewNextByteOrFail(): string
    {
        return $this->viewNextBytesOrFail(1);
    }

    /**
     * Read all remaining bytes from file handler
     */
    protected function getRemainingBytes(): string
    {
        $all = '';
        do {
            $byte = fread($this->handle, 1);
            $all .= $byte;
        } while (!feof($this->handle));

        return $all;
    }

    /**
     * Get next byte in stream and move file pointer
     *
     * @throws DecoderException
     */
    protected function getNextByteOrFail(): string
    {
        return $this->getNextBytesOrFail(1);
    }

    /**
     * Move file pointer on handle by given offset
     */
    protected function movePointer(int $offset): self
    {
        fseek($this->handle, $offset, SEEK_CUR);

        return $this;
    }

    /**
     * Decode multi byte value
     *
     * @throws DecoderException
     */
    protected function decodeMultiByte(string $bytes): int
    {
        $unpacked = unpack('v*', $bytes);

        if ($unpacked === false || !array_key_exists(1, $unpacked)) {
            throw new DecoderException('Unable to decode given bytes.');
        }

        return $unpacked[1];
    }

    /**
     * Set length
     */
    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * Get current handle position
     *
     * @throws DecoderException
     */
    public function getPosition(): int
    {
        $position = ftell($this->handle);

        if ($position === false) {
            throw new DecoderException('Unable to read current position from handle.');
        }

        return $position;
    }
}
