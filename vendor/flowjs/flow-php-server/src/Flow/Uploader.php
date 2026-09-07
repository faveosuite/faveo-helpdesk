<?php

namespace Flow;

class Uploader
{
    /**
     * Delete chunks older than expiration time.
     *
     * @param int    $expirationTime seconds
     *
     * @throws FileOpenException
     */
    public static function pruneChunks(string $chunksFolder, int $expirationTime = 172800)
    {
        $handle = opendir($chunksFolder);

        if (! $handle) {
            throw new FileOpenException('failed to open folder: '.$chunksFolder);
        }

        while (false !== ($entry = readdir($handle))) {
            if ('.' == $entry || '..' == $entry || '.gitignore' == $entry) {
                continue;
            }

            $path = $chunksFolder.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($path)) {
                continue;
            }

            if (time() - filemtime($path) > $expirationTime) {
                unlink($path);
            }
        }

        closedir($handle);
    }
}
