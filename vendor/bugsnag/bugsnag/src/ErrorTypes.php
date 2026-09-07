<?php

namespace Bugsnag;

class ErrorTypes
{
    /**
     * The error types map.
     *
     * @var array[]
     */
    protected static $ERROR_TYPES;

    /**
     * Static initializer to conditionally populate $ERROR_TYPES.
     *
     * @return void
     */
    protected static function initializeErrorTypes()
    {
        static::$ERROR_TYPES = [
            E_ERROR => [
                'name' => 'PHP Fatal Error',
                'severity' => 'error',
            ],

            E_WARNING => [
                'name' => 'PHP Warning',
                'severity' => 'warning',
            ],

            E_PARSE => [
                'name' => 'PHP Parse Error',
                'severity' => 'error',
            ],

            E_NOTICE => [
                'name' => 'PHP Notice',
                'severity' => 'info',
            ],

            E_CORE_ERROR => [
                'name' => 'PHP Core Error',
                'severity' => 'error',
            ],

            E_CORE_WARNING => [
                'name' => 'PHP Core Warning',
                'severity' => 'warning',
            ],

            E_COMPILE_ERROR => [
                'name' => 'PHP Compile Error',
                'severity' => 'error',
            ],

            E_COMPILE_WARNING => [
                'name' => 'PHP Compile Warning',
                'severity' => 'warning',
            ],

            E_USER_ERROR => [
                'name' => 'User Error',
                'severity' => 'error',
            ],

            E_USER_WARNING => [
                'name' => 'User Warning',
                'severity' => 'warning',
            ],

            E_USER_NOTICE => [
                'name' => 'User Notice',
                'severity' => 'info',
            ],

            E_RECOVERABLE_ERROR => [
                'name' => 'PHP Recoverable Error',
                'severity' => 'error',
            ],

            E_DEPRECATED => [
                'name' => 'PHP Deprecated',
                'severity' => 'info',
            ],

            E_USER_DEPRECATED => [
                'name' => 'User Deprecated',
                'severity' => 'info',
            ],
        ];

        // Conditionally add E_STRICT if PHP version is below 8.4
        // https://php.watch/versions/8.4/E_STRICT-deprecated
        if (PHP_VERSION_ID < 80400) {
            static::$ERROR_TYPES[E_STRICT] = [
                'name' => 'PHP Strict',
                'severity' => 'info',
            ];
        }
    }

    /**
     * Get the error types map.
     *
     * @return array[]
     */
    protected static function getErrorTypes()
    {
        if (static::$ERROR_TYPES === null)
            static::initializeErrorTypes();
        return static::$ERROR_TYPES;
    }

    /**
     * Is the given error code fatal?
     *
     * @param int $code the error code
     *
     * @return bool
     */
    public static function isFatal($code)
    {
        return static::getSeverity($code) === 'error';
    }

    /**
     * Get the name of the given error code.
     *
     * @param int $code the error code
     *
     * @return string
     */
    public static function getName($code)
    {
        $errorTypes = static::getErrorTypes();

        if (array_key_exists($code, $errorTypes)) {
            return $errorTypes[$code]['name'];
        }

        return 'Unknown';
    }

    /**
     * Get the severity of the given error code.
     *
     * @param int $code the error code
     *
     * @return string
     */
    public static function getSeverity($code)
    {
        $errorTypes = static::getErrorTypes();

        if (array_key_exists($code, $errorTypes)) {
            return $errorTypes[$code]['severity'];
        }

        return 'error';
    }

    /**
     * Get the levels for the given severity.
     *
     * @param string $severity the given severity
     *
     * @return int
     */
    public static function getLevelsForSeverity($severity)
    {
        $levels = 0;
        $errorTypes = static::getErrorTypes();

        foreach ($errorTypes as $level => $info) {
            if ($info['severity'] == $severity) {
                $levels |= $level;
            }
        }

        return $levels;
    }

    /**
     * Get a list of all PHP error codes.
     *
     * @return int[]
     */
    public static function getAllCodes()
    {
        return array_keys(static::getErrorTypes());
    }

    /**
     * Convert the given error code to a string representation.
     *
     * For example, E_ERROR => 'E_ERROR'.
     *
     * @param int $code
     *
     * @return string
     */
    public static function codeToString($code)
    {
        $map = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        // Conditionally add E_STRICT if PHP version is below 8.4
        // https://php.watch/versions/8.4/E_STRICT-deprecated
        if (PHP_VERSION_ID < 80400) {
            $map[E_STRICT] = 'E_STRICT';
        }

        return isset($map[$code]) ? $map[$code] : 'Unknown';
    }
}
