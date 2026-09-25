<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Utility\LibraryController as Utility;
use Illuminate\Support\Facades\Cache;

/**
 * One place that answers "which version are the files / the database on?" for the
 * updater, the update banner and the database-outdated gate, so they can't disagree.
 */
class UpdateVersion
{
    private const DB_VERSION_CACHE_KEY = 'faveo-update-db-version';

    /**
     * Turn a Faveo version tag into something version_compare() orders correctly.
     *
     * Same rule as SyncFaveoToLatestVersion: drop the "v" prefix and map "_" to ".",
     * but keep pre-release markers. Utility::getFileVersion() strips every letter, so
     * "v2.0.4.RC.1" became "2.0.4..1", which PHP ranks above "2.0.4" — an RC install
     * was never offered the final release. Kept as "2.0.4.RC.1" it sorts below it.
     */
    public static function normalize(mixed $version): string
    {
        $version = is_scalar($version) ? (string) $version : '';

        return ltrim(str_replace('_', '.', trim($version)), 'vV') ?: '0';
    }

    /** Version of the code on disk (as loaded by this request). */
    public static function files(): string
    {
        return self::normalize(config('app.version'));
    }

    /**
     * Version recorded in settings_system. Cached briefly because the database
     * gate asks on every admin page load; forgetDatabase() runs after each sync.
     */
    public static function database(): string
    {
        return self::normalize(Cache::remember(self::DB_VERSION_CACHE_KEY, 300, fn () => (string) Utility::getDatabaseVersion()));
    }

    public static function forgetDatabase(): void
    {
        Cache::forget(self::DB_VERSION_CACHE_KEY);
    }

    public static function isNewer(?string $candidate, string $than): bool
    {
        return $candidate !== null && $candidate !== '' && version_compare(self::normalize($candidate), self::normalize($than), '>');
    }

    public static function databaseOutdated(): bool
    {
        return self::isNewer(self::files(), self::database());
    }
}
