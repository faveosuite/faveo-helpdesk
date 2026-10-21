<?php

namespace App\Support;

use Sentry\Event;
use Sentry\EventHint;

/**
 * Last line of defence for what leaves the application for Sentry.
 *
 * The SDK installs its own error/exception listeners when it boots, which is before the admin's
 * "Send app crash reports" choice (stored in the database) has been loaded, so that choice cannot be
 * applied by simply not creating the client. Instead every event is checked against the live
 * config at the moment it is about to be sent. It is a static method referenced by name from
 * config/sentry.php so the config stays serialisable for `php artisan config:cache`.
 */
class SentryGate
{
    public static function beforeSend(Event $event, ?EventHint $hint): ?Event
    {
        if (! app()->environment('production') || ! config('app.sentry_reporting')) {
            return null;
        }

        return $event;
    }
}
