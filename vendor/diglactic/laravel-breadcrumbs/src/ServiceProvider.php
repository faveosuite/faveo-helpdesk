<?php

namespace Diglactic\Breadcrumbs;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * The Laravel service provider, which registers, configures and bootstraps the package.
 */
class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided for deferred loading.
     *
     * @return array
     */
    public function provides(): array
    {
        return [Manager::class];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        // Load the default config values
        $this->mergeConfigFrom(__DIR__ . '/../config/breadcrumbs.php', 'breadcrumbs');

        // Register Manager class singleton with the app container
        $this->app->singleton(Manager::class, config('breadcrumbs.manager-class'));

        // Register Generator class so it can be overridden
        $this->app->bind(Generator::class, config('breadcrumbs.generator-class'));
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        // Register 'breadcrumbs::' view namespace
        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'breadcrumbs');

        // Publish the config/breadcrumbs.php file
        $this->publishes([
            __DIR__ . '/../config/breadcrumbs.php' => config_path('breadcrumbs.php'),
        ], 'breadcrumbs-config');

        // Publish resources/views/* files
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/breadcrumbs'),
        ], 'breadcrumbs-views');

        // Load the routes/breadcrumbs.php file
        $this->registerBreadcrumbs();
    }

    /**
     * Load the routes/breadcrumbs.php file (if it exists) which registers available breadcrumbs.
     *
     * This method can be overridden in a child class. It is called by the boot() method, which Laravel calls
     * automatically when bootstrapping the application.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function registerBreadcrumbs(): void
    {
        // Load the routes/breadcrumbs.php file, or other configured file(s)
        $files = config('breadcrumbs.files');

        if (!$files) {
            return;
        }

        // If it is set to the default value and that file doesn't exist, skip loading it rather than causing an error
        if ($files === base_path('routes/breadcrumbs.php') && !is_file($files)) {
            return;
        }

        // Support both Breadcrumbs:: and $breadcrumbs-> syntax by making $breadcrumbs variable available
        /** @noinspection PhpUnusedLocalVariableInspection */
        $breadcrumbs = $this->app->make(Manager::class);

        // Support both a single string filename and an array of filenames (e.g. returned by glob())
        foreach ((array)$files as $file) {
            require $file;
        }
    }
}
