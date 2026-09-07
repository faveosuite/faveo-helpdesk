<?php

namespace Flow\Mongo;

use Flow\Config;
use Flow\ConfigInterface;
use MongoDB\GridFS\Bucket;

/**
 * @codeCoverageIgnore
 */
class MongoConfig extends Config implements ConfigInterface
{
    /**
     * @param Bucket $gridFS storage of the upload (and chunks)
     */
    public function __construct(private readonly Bucket $gridFS)
    {
        parent::__construct();
    }

    public function getGridFs(): Bucket
    {
        return $this->gridFS;
    }
}
