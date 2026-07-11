<?php

namespace App\Providers;

use App\Model\Update\BarNotification;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;
use Queue;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Illuminate\Contracts\Auth\Registrar');
        require_once __DIR__.'/../Http/helpers.php';
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }
        if (isInstall()) {
            $this->registerPlugin();
        }
    }

    public function boot()
    {
        Queue::failing(function (JobFailed $event) {
            loging('Failed Job - '.$event->connectionName, json_encode([$event->job->payload(), 'error' => $event->exception->getMessage().' file=>'.$event->exception->getFile().' line=>'.$event->exception->getLine()]));
        });
        Route::singularResourceParameters(false);
        $this->composer();
        // URL::forceScheme('https');
    }

    public function composer()
    {
        \View::composer('themes.default1.update.notification', function () {
            $notification = new BarNotification();
            $not = [
                'notification' => $notification->where('value', '!=', '')->get(),
            ];
            view()->share($not);
        });
    }

    public function registerPlugin()
    {
        $activePlugins = \DB::table('plugins')->select('name', 'path')->where('status', 1)->get();
        foreach ($activePlugins as $activePlugin) {
            if ($this->isPluginDir($activePlugin->name)) {
                $class = '\App\Plugins\\'.$activePlugin->name.'\ServiceProvider';
                $this->app->register($class);
            }
        }
    }

    public function isPluginDir($name)
    {
        $check = false;
        if (is_dir(app_path('Plugins'.DIRECTORY_SEPARATOR.$name))) {
            $check = true;
        }

        return $check;
    }

    public function isModuleDir($name)
    {
        $check = false;
        if (is_dir(app_path($name))) {
            $check = true;
        }

        return $check;
    }
}
