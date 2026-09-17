<?php

namespace Flow;

/**
 * Class FustyRequest
 *
 * Imitates single file request as a single chunk file upload
 *
 * @package Flow
 */
class FustyRequest extends Request
{
    private bool $isFusty = false;

    public function __construct(?array $params = null, ?array $file = null)
    {
        parent::__construct($params, $file);

        $this->isFusty = null === $this->getTotalSize() && $this->getFileName() && $this->getFile();

        if ($this->isFusty) {
            $this->params['flowTotalSize'] = $this->file['size'] ?? 0;
            $this->params['flowTotalChunks'] = 1;
            $this->params['flowChunkNumber'] = 1;
            $this->params['flowChunkSize'] = $this->params['flowTotalSize'];
            $this->params['flowCurrentChunkSize'] = $this->params['flowTotalSize'];
        }
    }

    /**
     * Checks if request is formed by fusty flow
     */
    public function isFustyFlowRequest(): bool
    {
        return $this->isFusty;
    }
}
