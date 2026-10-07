<?php

namespace App\FaveoLog;

use Illuminate\Support\Facades\File;
use Psr\Log\LogLevel;
use ReflectionClass;
use UTC;

/**
 * Class LaravelLogViewer.
 */
class LaravelLogViewer
{
    /**
     * @var string file
     */
    private static $file;

    private static $levels_classes = [
        'debug'     => 'info',
        'info'      => 'info',
        'notice'    => 'info',
        'warning'   => 'warning',
        'error'     => 'danger',
        'critical'  => 'danger',
        'alert'     => 'danger',
        'emergency' => 'danger',
    ];

    private static $levels_imgs = [
        'debug'     => 'info',
        'info'      => 'info',
        'notice'    => 'info',
        'warning'   => 'warning',
        'error'     => 'warning',
        'critical'  => 'warning',
        'alert'     => 'warning',
        'emergency' => 'warning',
    ];

    const MAX_FILE_SIZE = 52428800; // Why? Uh... Sorry

    /**
     * @param string $file
     */
    public static function setFile($file)
    {
        $file = self::pathToLogFile($file);

        if (File::exists($file)) {
            self::$file = $file;
        }
    }

    public static function pathToLogFile($file)
    {
        $logsPath = realpath(storage_path('logs'));

        if ($logsPath === false || !is_string($file) || $file === '' || str_contains($file, "\0")) {
            throw new \Exception('No such log file');
        }

        // Always resolve the name relative to the logs directory; absolute paths are not accepted.
        $filePath = realpath($logsPath.DIRECTORY_SEPARATOR.$file);

        // realpath() resolves "../" and symlinks, so the check below cannot be bypassed by either.
        if ($filePath === false || !is_file($filePath) || dirname($filePath) !== $logsPath) {
            throw new \Exception('No such log file');
        }

        return $filePath;
    }

    /**
     * @return string
     */
    public static function getFileName()
    {
        return basename(self::$file);
    }

    /**
     * @return array
     */
    public static function all()
    {
        $log = [];

        $log_levels = self::getLogLevels();

        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/';

        if (!self::$file) {
            $log_file = self::getFiles();
            if (!count($log_file)) {
                return [];
            }
            self::$file = $log_file[0];
        }

        if (File::size(self::$file) > self::MAX_FILE_SIZE) {
            return;
        }

        $file = File::get(self::$file);

        preg_match_all($pattern, $file, $headings);

        if (!is_array($headings)) {
            return $log;
        }

        $log_data = preg_split($pattern, $file);

        if ($log_data[0] < 1) {
            array_shift($log_data);
        }

        foreach ($headings as $h) {
            for ($i = 0, $j = count($h); $i < $j; $i++) {
                foreach ($log_levels as $level_key => $level_value) {
                    if (strpos(strtolower($h[$i]), '.'.$level_value)) {
                        preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(\w+)\.'.$level_key.': (.*?)( in .*?:[0-9]+)?$/', $h[$i], $current);

                        if (!isset($current[3])) {
                            continue;
                        }

                        $array = explode(':-:-:-', $current[3]);
                        $message = $current[3];
                        $context = $current[2];

                        if (is_array($array)) {
                            if (array_key_exists(0, $array)) {
                                $message = $array[0];
                            }
                            if (array_key_exists(1, $array)) {
                                $context = $array[1];
                            } else {
                                $context = $current[2];
                            }
                        }
                        //dd($current);
                        $log[] = [
                            'context'     => $context,
                            'level'       => $level_value,
                            'level_class' => self::$levels_classes[$level_value],
                            'level_img'   => self::$levels_imgs[$level_value],
                            'date'        => self::date($current[1]),
                            'text'        => $message,
                            'in_file'     => isset($current[4]) ? $current[4] : null,
                            'stack'       => preg_replace("/^\n*/", '', $log_data[$i]),
                        ];
                    }
                }
            }
        }

        return array_reverse($log);
    }

    /**
     * @param bool $basename
     *
     * @return array
     */
    public static function getFiles($basename = false)
    {
        $files = glob(storage_path().'/logs/*');
        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        if ($basename && is_array($files)) {
            foreach ($files as $k => $file) {
                $files[$k] = basename($file);
            }
        }

        return array_values($files);
    }

    /**
     * @return array
     */
    private static function getLogLevels()
    {
        $class = new ReflectionClass(new LogLevel());

        return $class->getConstants();
    }

    public static function date($utc)
    {
        $system_date = UTC::usertimezone($utc);

        return $system_date;
    }
}
