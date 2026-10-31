<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

class FileuploadController extends Controller
{
    public function __construct()
    {
        // checking authentication
        $this->middleware('auth');
        // checking if role is agent
        $this->middleware('role.agent');
    }

    // Returns a file size limit in bytes based on the PHP upload_max_filesize
    // and post_max_size
    public function file_upload_max_size()
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $max_size_in_bytes = $this->parse_size(ini_get('post_max_size'));
            $max_size_in_actual = ini_get('post_max_size');

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size_in_bytes = $upload_max;
                $max_size_in_actual = ini_get('upload_max_filesize');
            }
        }

        return ['0' => $max_size_in_bytes, '1' => $this->readableSize($max_size_in_actual)];
//        return $max_size_in_bytes;
    }

    /**
     * PHP's shorthand size (8M, 512K, 1G) as shown to people (8MB, 512KB, 1GB).
     *
     * @param string $size
     *
     * @return string
     */
    public function readableSize($size)
    {
        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*([KMGT])B?\s*$/i', (string) $size, $matches)) {
            return $matches[1].strtoupper($matches[2]).'B';
        }

        return $size;
    }

    public function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
}
