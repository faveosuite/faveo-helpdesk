<?php

namespace Flow\Mongo;

use MongoDB\GridFS\Bucket;

/**
 * @codeCoverageIgnore
 */
class MongoUploader
{
    /**
     * Delete chunks older than expiration time.
     *
     * @param int $expirationTime seconds
     */
    public static function pruneChunks(Bucket $gridFs, int $expirationTime = 172800): void
    {
        $result = $gridFs->find([
            'flowUpdated' => ['$lt' => new \MongoDB\BSON\UTCDateTime(time() - $expirationTime)],
            'flowStatus' => 'uploading'
        ]);
        foreach ($result as $file) {
            $gridFs->delete($file['_id']);
        }
    }
}
