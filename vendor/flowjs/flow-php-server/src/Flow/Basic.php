<?php

namespace Flow;

/**
 * Class Basic
 *
 * Example for handling basic uploads
 *
 * @package Flow
 */
class Basic
{
    /**
     * @param  string                 $destination where to save file
     * @param  string|ConfigInterface $config
     */
    public static function save(string|ConfigInterface $destination, $config, ?RequestInterface $request = null): bool
    {
        if (! $config instanceof ConfigInterface) {
            $config = new Config([
                'tempDir' => $config,
            ]);
        }

        $file = new File($config, $request);

        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            if ($file->checkChunk()) {
                header('HTTP/1.1 200 Ok');
            } else {
                // The 204 response MUST NOT include a message-body, and thus is always terminated by the first empty line after the header fields.
                header('HTTP/1.1 204 No Content');

                return false;
            }
        } else {
            if ($file->validateChunk()) {
                $file->saveChunk();
            } else {
                // error, invalid chunk upload request, retry
                header('HTTP/1.1 400 Bad Request');

                return false;
            }
        }

        return (bool) ($file->validateFile() && $file->save($destination));
    }
}
