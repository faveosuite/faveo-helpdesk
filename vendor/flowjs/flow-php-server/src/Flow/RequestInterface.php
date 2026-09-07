<?php

namespace Flow;

interface RequestInterface
{
    /**
     * Get uploaded file name
     */
    public function getFileName(): ?string;

    /**
     * Get total file size in bytes
     */
    public function getTotalSize(): ?int;

    /**
     * Get file unique identifier
     */
    public function getIdentifier(): ?string;

    /**
     * Get file relative path
     */
    public function getRelativePath(): ?string;

    /**
     * Get total chunks number
     */
    public function getTotalChunks(): ?int;

    /**
     * Get default chunk size
     */
    public function getDefaultChunkSize(): ?int;

    /**
     * Get current uploaded chunk number, starts with 1
     */
    public function getCurrentChunkNumber(): ?int;

    /**
     * Get current uploaded chunk size
     */
    public function getCurrentChunkSize(): ?int;

    /**
     * Return $_FILES request
     */
    public function getFile(): ?array;

    /**
     * Checks if request is formed by fusty flow
     */
    public function isFustyFlowRequest(): bool;
}
