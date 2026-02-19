<?php namespace Chumper\Datatable;

use Illuminate\Support\ServiceProvider;

class DatatableServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    public function boot()
    {
        //
    }

    /**
	 * Register the service provider.
	 *
	 * @return void
	 */
    public function register()
    {
        $this->app->singleton('datatable', function($app)
        {
            return new Datatable;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('datatable');
    }
}
