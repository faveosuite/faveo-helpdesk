<?php

namespace App\Providers;

use App\Model\helpdesk\Settings\CommonSettings;
use Illuminate\Support\ServiceProvider;
use Sentry\State\HubInterface;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Cache key holding the admin's error and debugging choices as read from the database.
     */
    public const DEBUGGING_CACHE_KEY = 'debugging_settings';

    /**
     * Seconds the cached copy is trusted for. It is also dropped as soon as an admin saves, so this is
     * only a safety net: if the cache store cannot be written or cleared (for example files created by
     * `artisan` run as another user than the web server), a stale copy expires on its own instead of
     * hiding a saved setting for good.
     */
    public const DEBUGGING_CACHE_TTL = 60;

    /**
     * Where each setting lives in the common_settings table: config key => [option_name, optional_field].
     */
    public const SETTINGS = [
        'app.debug'              => ['debugging', 'app_debug'],
        'app.sentry_reporting'   => ['sentry', 'crash_reporting'],
        'app.sentry_performance' => ['sentry', 'performance_monitoring'],
    ];

    /**
     * Overwrite any vendor / package configuration.
     *
     * This service provider is intended to provide a convenient location for you
     * to overwrite any "vendor" or package configuration that you may want to
     * modify before the application handles the incoming request / command.
     *
     * @return void
     */
    public function register()
    {
        config([
            'lfm.images_url' => url('uploads').'/',
            'lfm.images_dir' => '/public/uploads/',
            'lfm.files_url'  => url('uploads').'/',
            'lfm.files_dir'  => '/public/uploads/',
        ]);
    }

    /**
     * Apply the error and debugging choices an admin saved in the database.
     *
     * Only what the admin has actually saved is read from the database (and cached briefly, so there is
     * not a query per request). A setting that has never been saved keeps the value from .env / config, so
     * installs that have not touched the page behave exactly as before, and editing .env keeps working
     * for them.
     *
     * @return void
     */
    public function boot()
    {
        if (!isInstall()) {
            return;
        }

        try {
            $saved = \Cache::remember(self::DEBUGGING_CACHE_KEY, self::DEBUGGING_CACHE_TTL, function () {
                $saved = [];
                foreach (self::SETTINGS as $configKey => [$option, $field]) {
                    $value = CommonSettings::where('option_name', $option)->where('optional_field', $field)->value('option_value');
                    if ($value !== null) {
                        $saved[$configKey] = (bool) $value;
                    }
                }

                return $saved;
            });

            config($saved);

            if (array_key_exists('app.sentry_performance', $saved)) {
                self::applySentryTracing($saved['app.sentry_performance']);
            }
        } catch (\Throwable $e) {
            // DB or cache not available yet (e.g. mid-upgrade): keep the config defaults.
        }
    }

    /**
     * Set the Sentry trace sample rate for this request and for the already booted SDK.
     *
     * The SDK is built in its own provider, which boots before this one, so changing the config value
     * alone would not reach the live client options.
     *
     * @param bool $enabled
     *
     * @return void
     */
    public static function applySentryTracing(bool $enabled): void
    {
        $rate = $enabled ? 0.1 : 0;
        config(['sentry.traces_sample_rate' => $rate]);

        if (app()->bound(HubInterface::class)) {
            $client = app(HubInterface::class)->getClient();
            if ($client !== null) {
                $client->getOptions()->setTracesSampleRate($rate ?: null);
            }
        }
    }
}
