<?php

declare(strict_types=1);

namespace Intervention\Gif\Blocks;

use Intervention\Gif\AbstractExtension;
use Intervention\Gif\DisposalMethod;

class GraphicControlExtension extends AbstractExtension
{
    public const LABEL = "\xF9";
    public const BLOCKSIZE = "\x04";

    /**
     * Existance flag of transparent color
     */
    protected bool $transparentColorExistance = false;

    /**
     * Transparent color index
     */
    protected int $transparentColorIndex = 0;

    /**
     * User input flag
     */
    protected bool $userInput = false;

    /**
     * Create new instance
     */
    public function __construct(
        protected int $delay = 0,
        protected DisposalMethod $disposalMethod = DisposalMethod::UNDEFINED,
    ) {
        //
    }

    /**
     * Set delay time (1/100 second)
     */
    public function setDelay(int $value): self
    {
        $this->delay = $value;

        return $this;
    }

    /**
     * Return delay time (1/100 second)
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * Set disposal method
     */
    public function setDisposalMethod(DisposalMethod $method): self
    {
        $this->disposalMethod = $method;

        return $this;
    }

    /**
     * Get disposal method
     */
    public function getDisposalMethod(): DisposalMethod
    {
        return $this->disposalMethod;
    }

    /**
     * Get transparent color index
     */
    public function getTransparentColorIndex(): int
    {
        return $this->transparentColorIndex;
    }

    /**
     * Set transparent color index
     */
    public function setTransparentColorIndex(int $index): self
    {
        $this->transparentColorIndex = $index;

        return $this;
    }

    /**
     * Get current transparent color existance
     */
    public function getTransparentColorExistance(): bool
    {
        return $this->transparentColorExistance;
    }

    /**
     * Set existance flag of transparent color
     */
    public function setTransparentColorExistance(bool $existance = true): self
    {
        $this->transparentColorExistance = $existance;

        return $this;
    }

    /**
     * Get user input flag
     */
    public function getUserInput(): bool
    {
        return $this->userInput;
    }

    /**
     * Set user input flag
     */
    public function setUserInput(bool $value = true): self
    {
        $this->userInput = $value;

        return $this;
    }
}
