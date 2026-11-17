<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncrypter;

class EncryptCookies extends BaseEncrypter
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        // The updater hands this admin the maintenance-mode bypass cookie so the
        // database step can still reach the site while it is down. Laravel checks
        // it before cookies are decrypted, so it must be sent unencrypted.
        'laravel_maintenance',
    ];
}
