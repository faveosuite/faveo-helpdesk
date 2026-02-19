<?php

namespace Flow;

class File
{
    /**
     * File hashed unique identifier
     */
    private string $identifier;

    public function __construct(protected ConfigInterface $config, protected ?RequestInterface $request = null)
    {
        if (null === $request) {
            $this->request = new Request();
        }

        $this->identifier = \call_user_func($this->config->getHashNameCallback(), $this->request);
    }

    /**
     * Get file identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Return chunk path
     */
    public function getChunkPath(int $index): string
    {
        return $this->config->getTempDir().DIRECTORY_SEPARATOR.basename($this->identifier).'_'.(int) $index;
    }

    /**
     * Check if chunk exist
     */
    public function checkChunk(): bool
    {
        return file_exists($this->getChunkPath($this->request->getCurrentChunkNumber()));
    }

    /**
     * Validate file request
     */
    public function validateChunk(): bool
    {
        $file = $this->request->getFile();

        if (! $file) {
            return false;
        }

        if (! isset($file['tmp_name']) || ! isset($file['size']) || ! isset($file['error'])) {
            return false;
        }

        if ($this->request->getCurrentChunkSize() != $file['size']) {
            return false;
        }

        return ! (UPLOAD_ERR_OK !== $file['error']);
    }

    /**
     * Save chunk
     */
    public function saveChunk(): bool
    {
        $file = $this->request->getFile();

        return $this->_move_uploaded_file($file['tmp_name'], $this->getChunkPath($this->request->getCurrentChunkNumber()));
    }

    /**
     * Check if file upload is complete
     */
    public function validateFile(): bool
    {
        $totalChunks = $this->request->getTotalChunks();
        $totalChunksSize = 0;

        for ($i = $totalChunks; $i >= 1; $i--) {
            $file = $this->getChunkPath($i);
            if (! file_exists($file)) {
                return false;
            }
            $totalChunksSize += filesize($file);
        }

        return $this->request->getTotalSize() == $totalChunksSize;
    }

    /**
     * Merge all chunks to single file
     *
     * @param string $destination final file location
     *
     * @throws FileLockException
     * @throws FileOpenException
     * @throws \Exception
     *
     * @return bool indicates if file was saved
     */
    public function save(string $destination): bool
    {
        $fh = fopen($destination, 'wb');
        if (! $fh) {
            throw new FileOpenException('failed to open destination file: '.$destination);
        }

        if (! flock($fh, LOCK_EX | LOCK_NB, $blocked)) {
            // @codeCoverageIgnoreStart
            if ($blocked) {
                // Concurrent request has requested a lock.
                // File is being processed at the moment.
                // Warning: lock is not checked in windows.
                return false;
            }
            // @codeCoverageIgnoreEnd

            throw new FileLockException('failed to lock file: '.$destination);
        }

        $totalChunks = $this->request->getTotalChunks();

        try {
            $preProcessChunk = $this->config->getPreprocessCallback();

            for ($i = 1; $i <= $totalChunks; $i++) {
                $file = $this->getChunkPath($i);
                $chunk = fopen($file, 'rb');

                if (! $chunk) {
                    throw new FileOpenException('failed to open chunk: '.$file);
                }

                if (null !== $preProcessChunk) {
                    \call_user_func($preProcessChunk, $chunk);
                }

                stream_copy_to_stream($chunk, $fh);
                fclose($chunk);
            }
        } catch (\Exception $e) {
            flock($fh, LOCK_UN);
            fclose($fh);

            throw $e;
        }

        if ($this->config->getDeleteChunksOnSave()) {
            $this->deleteChunks();
        }

        flock($fh, LOCK_UN);
        fclose($fh);

        return true;
    }

    /**
     * Delete chunks dir
     */
    public function deleteChunks(): static
    {
        $totalChunks = $this->request->getTotalChunks();

        for ($i = 1; $i <= $totalChunks; $i++) {
            $path = $this->getChunkPath($i);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        return $this;
    }

    /**
     * This method is used only for testing
     *
     * @private
     * @codeCoverageIgnore
     */
    public function _move_uploaded_file(string $filePath, string $destinationPath): bool
    {
        return move_uploaded_file($filePath, $destinationPath);
    }
}
