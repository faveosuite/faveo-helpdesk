<?php

namespace Flow;

class Request implements RequestInterface
{
    /**
     * Constructor
     */
    public function __construct(protected ?array $params = null, protected ?array $file = null)
    {
        if (null === $params) {
            $this->params = $_REQUEST;
        }

        if (null === $file && isset($_FILES['file'])) {
            $this->file = $_FILES['file'];
        }
    }

    /**
     * Get parameter value
     *
     *
     * @return string|int|null
     */
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Get uploaded file name
     *
     */
    public function getFileName(): ?string
    {
        return $this->getParam('flowFilename');
    }

    /**
     * Get total file size in bytes
     */
    public function getTotalSize(): ?int
    {
        return $this->getParam('flowTotalSize');
    }

    /**
     * Get file unique identifier
     */
    public function getIdentifier(): ?string
    {
        return $this->getParam('flowIdentifier');
    }

    /**
     * Get file relative path
     */
    public function getRelativePath(): ?string
    {
        return $this->getParam('flowRelativePath');
    }

    /**
     * Get total chunks number
     */
    public function getTotalChunks(): ?int
    {
        return $this->getParam('flowTotalChunks');
    }

    /**
     * Get default chunk size
     */
    public function getDefaultChunkSize(): ?int
    {
        return $this->getParam('flowChunkSize');
    }

    /**
     * Get current uploaded chunk number, starts with 1
     */
    public function getCurrentChunkNumber(): ?int
    {
        return $this->getParam('flowChunkNumber');
    }

    /**
     * Get current uploaded chunk size
     */
    public function getCurrentChunkSize(): ?int
    {
        return $this->getParam('flowCurrentChunkSize');
    }

    /**
     * Return $_FILES request
     */
    public function getFile(): ?array
    {
        return $this->file;
    }

    /**
     * Checks if request is formed by fusty flow
     */
    public function isFustyFlowRequest(): bool
    {
        return false;
    }
}
