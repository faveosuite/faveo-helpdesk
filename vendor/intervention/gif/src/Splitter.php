<?php

declare(strict_types=1);

namespace Intervention\Gif;

use ArrayIterator;
use GdImage;
use Intervention\Gif\Exceptions\EncoderException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<GifDataStream>
 */
class Splitter implements IteratorAggregate
{
    /**
     * Single frames resolved to GifDataStream
     *
     * @var array<GifDataStream>
     */
    protected array $frames = [];

    /**
     * Delays of each frame
     *
     * @var array<int>
     */
    protected array $delays = [];

    /**
     * Create new instance
     */
    public function __construct(protected GifDataStream $stream)
    {
        //
    }

    /**
     * Static constructor method
     */
    public static function create(GifDataStream $stream): self
    {
        return new self($stream);
    }

    /**
     * Iterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->frames);
    }

    /**
     * Get frames
     *
     * @return array<GifDataStream>
     */
    public function getFrames(): array
    {
        return $this->frames;
    }

    /**
     * Get delays
     *
     * @return array<int>
     */
    public function getDelays(): array
    {
        return $this->delays;
    }

    /**
     * Set stream of instance
     */
    public function setStream(GifDataStream $stream): self
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * Split current stream into array of seperate streams for each frame
     */
    public function split(): self
    {
        $this->frames = [];

        foreach ($this->stream->getFrames() as $frame) {
            // create separate stream for each frame
            $gif = Builder::canvas(
                $this->stream->getLogicalScreenDescriptor()->getWidth(),
                $this->stream->getLogicalScreenDescriptor()->getHeight()
            )->getGifDataStream();

            // check if working stream has global color table
            if ($this->stream->hasGlobalColorTable()) {
                $gif->setGlobalColorTable($this->stream->getGlobalColorTable());
                $gif->getLogicalScreenDescriptor()->setGlobalColorTableExistance(true);

                $gif->getLogicalScreenDescriptor()->setGlobalColorTableSorted(
                    $this->stream->getLogicalScreenDescriptor()->getGlobalColorTableSorted()
                );

                $gif->getLogicalScreenDescriptor()->setGlobalColorTableSize(
                    $this->stream->getLogicalScreenDescriptor()->getGlobalColorTableSize()
                );

                $gif->getLogicalScreenDescriptor()->setBackgroundColorIndex(
                    $this->stream->getLogicalScreenDescriptor()->getBackgroundColorIndex()
                );

                $gif->getLogicalScreenDescriptor()->setPixelAspectRatio(
                    $this->stream->getLogicalScreenDescriptor()->getPixelAspectRatio()
                );

                $gif->getLogicalScreenDescriptor()->setBitsPerPixel(
                    $this->stream->getLogicalScreenDescriptor()->getBitsPerPixel()
                );
            }

            // copy original frame
            $gif->addFrame($frame);

            $this->frames[] = $gif;
            $this->delays[] = match (is_object($frame->getGraphicControlExtension())) {
                true => $frame->getGraphicControlExtension()->getDelay(),
                default => 0,
            };
        }

        return $this;
    }

    /**
     * Return array of GD library resources for each frame
     *
     * @throws EncoderException
     * @return array<GdImage>
     */
    public function toResources(): array
    {
        $resources = [];

        foreach ($this->frames as $frame) {
            $resource = imagecreatefromstring($frame->encode());
            if ($resource === false) {
                throw new EncoderException('Unable to extract animation frames.');
            }

            imagepalettetotruecolor($resource);
            imagesavealpha($resource, true);
            $resources[] = $resource;
        }

        return $resources;
    }

    /**
     * Return array of coalesced GD library resources for each frame
     *
     * @throws EncoderException
     * @return array<GdImage>
     */
    public function coalesceToResources(): array
    {
        $resources = $this->toResources();

        // static gif files don't need to be coalesced
        if (count($resources) === 1) {
            return $resources;
        }

        $width = imagesx($resources[0]);
        $height = imagesy($resources[0]);
        $transparent = imagecolortransparent($resources[0]);

        foreach ($resources as $key => $resource) {
            // get meta data
            $gif = $this->frames[$key];
            $descriptor = $gif->getFirstFrame()->getImageDescriptor();
            $offset_x = $descriptor->getLeft();
            $offset_y = $descriptor->getTop();
            $w = $descriptor->getWidth();
            $h = $descriptor->getHeight();

            if (in_array($this->getDisposalMethod($gif), [DisposalMethod::NONE, DisposalMethod::PREVIOUS])) {
                if ($key >= 1) {
                    // create normalized gd image
                    $canvas = imagecreatetruecolor($width, $height);
                    if (imagecolortransparent($resource) != -1) {
                        $transparent = imagecolortransparent($resource);
                    } else {
                        $transparent = imagecolorallocatealpha($resource, 255, 0, 255, 127);
                    }

                    if (!is_int($transparent)) {
                        throw new EncoderException('Animation frames cannot be converted into resources.');
                    }

                    // fill with transparent
                    imagefill($canvas, 0, 0, $transparent);
                    imagecolortransparent($canvas, $transparent);
                    imagealphablending($canvas, true);

                    // insert last as base
                    imagecopy(
                        $canvas,
                        $resources[$key - 1],
                        0,
                        0,
                        0,
                        0,
                        $width,
                        $height
                    );

                    // insert resource
                    imagecopy(
                        $canvas,
                        $resource,
                        $offset_x,
                        $offset_y,
                        0,
                        0,
                        $w,
                        $h
                    );
                } else {
                    imagealphablending($resource, true);
                    $canvas = $resource;
                }
            } else {
                // create normalized gd image
                $canvas = imagecreatetruecolor($width, $height);
                if (imagecolortransparent($resource) != -1) {
                    $transparent = imagecolortransparent($resource);
                } else {
                    $transparent = imagecolorallocatealpha($resource, 255, 0, 255, 127);
                }

                if (!is_int($transparent)) {
                    throw new EncoderException('Animation frames cannot be converted into resources.');
                }

                // fill with transparent
                imagefill($canvas, 0, 0, $transparent);
                imagecolortransparent($canvas, $transparent);
                imagealphablending($canvas, true);

                // insert frame resource
                imagecopy(
                    $canvas,
                    $resource,
                    $offset_x,
                    $offset_y,
                    0,
                    0,
                    $w,
                    $h
                );
            }

            $resources[$key] = $canvas;
        }

        return $resources;
    }

    /**
     * Find and return disposal method of given gif data stream
     */
    private function getDisposalMethod(GifDataStream $gif): DisposalMethod
    {
        return $gif->getFirstFrame()->getGraphicControlExtension()->getDisposalMethod();
    }
}
