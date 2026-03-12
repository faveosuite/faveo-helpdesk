<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $service_desk = '';
        // if (isInstall()) {
        //     if (isPlugin()) {
        //         $service_desk = 'service::interface.agent.sidebar';
        //     }
        // }
        view()->composer(
            [
                'themes.default1.agent.layout.agent',
                'themes.default1.agent.helpdesk.dashboard.dashboard',
                'themes.default1.admin.layout.admin',
                'themes.default1.admin.helpdesk.setting',
                $service_desk,
            ],
            \App\Http\ViewComposers\AgentLayout::class
        );
        view()->composer(
            [
                'themes.default1.update.notification',
            ],
            \App\Http\ViewComposers\UpdateNotification::class
        );
        view()->composer(
            [
                'themes.default1.agent.layout.agent',
                'themes.default1.admin.layout.admin',
            ],
            \App\Http\ViewComposers\AuthUser::class
        );
        view()->composer(
            [
                'themes.default1.admin.layout.admin',
                'themes.default1.agent.layout.agent',
                'themes.default1.client.layout.client',
                'themes.default1.client.layout.logclient',
            ],
            \App\Http\ViewComposers\UserLanguage::class
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
