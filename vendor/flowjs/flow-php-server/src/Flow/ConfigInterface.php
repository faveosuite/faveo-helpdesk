<?php

namespace Flow;

use MongoDB\GridFS\Bucket;

interface ConfigInterface
{
    /**
     * Get path to temporary directory for chunks storage
     */
    public function getTempDir(): string;

    /**
     * Generate chunk identifier
     */
    public function getHashNameCallback(): callable | array;

    /**
     * Callback to pre-process chunk
     */
    public function setPreprocessCallback(callable | array $callback): static;

    /**
     * Callback to preprocess chunk
     */
    public function getPreprocessCallback(): callable | array | null;

    /**
     * Delete chunks on save
     */
    public function setDeleteChunksOnSave(bool $delete): static;

    /**
     * Delete chunks on save
     */
    public function getDeleteChunksOnSave(): bool;

    /**
     * Only defined for MongoConfig
     */
    public function getGridFs(): ?Bucket;
}
