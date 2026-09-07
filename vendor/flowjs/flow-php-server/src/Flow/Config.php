<?php

namespace Flow;

use MongoDB\GridFS\Bucket;

class Config implements ConfigInterface
{
    public function __construct(private array $config = [])
    {
    }

    /**
     * Set path to temporary directory for chunks storage
     */
    public function setTempDir(string $path): static
    {
        $this->config['tempDir'] = $path;

        return $this;
    }

    /**
     * Get path to temporary directory for chunks storage
     */
    public function getTempDir(): string
    {
        return $this->config['tempDir'] ?? '';
    }

    /**
     * Set chunk identifier
     */
    public function setHashNameCallback(callable | array $callback): static
    {
        $this->config['hashNameCallback'] = $callback;

        return $this;
    }

    /**
     * Generate chunk identifier
     */
    public function getHashNameCallback(): callable | array
    {
        return $this->config['hashNameCallback'] ?? ['\Flow\Config', 'hashNameCallback'];
    }

    /**
     * Callback to pre-process chunk
     */
    public function setPreprocessCallback(callable | array $callback): static
    {
        $this->config['preprocessCallback'] = $callback;

        return $this;
    }

    /**
     * Callback to pre-process chunk
     */
    public function getPreprocessCallback(): callable | array | null
    {
        return $this->config['preprocessCallback'] ?? null;
    }

    /**
     * Delete chunks on save
     */
    public function setDeleteChunksOnSave(bool $delete): static
    {
        $this->config['deleteChunksOnSave'] = $delete;

        return $this;
    }

    /**
     * Delete chunks on save
     */
    public function getDeleteChunksOnSave(): bool
    {
        return $this->config['deleteChunksOnSave'] ?? true;
    }

    /**
     * Generate chunk identifier
     */
    public static function hashNameCallback(RequestInterface $request): string
    {
        return sha1($request->getIdentifier());
    }

    /**
     * Only defined for MongoConfig
     */
    public function getGridFs(): ?Bucket
    {
        return null;
    }
}
