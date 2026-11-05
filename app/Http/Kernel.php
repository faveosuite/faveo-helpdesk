<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Kernel.
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // before the CSRF check: PHP drops the whole body (and _token) of a request over
            // post_max_size, which otherwise surfaces as a misleading "session expired"
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\LanguageMiddleware::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'roles'            => \App\Http\Middleware\CheckRole::class,
        'role.agent'       => \App\Http\Middleware\CheckRoleAgent::class,
        'role.user'        => \App\Http\Middleware\CheckRoleUser::class,
        'api'              => \App\Http\Middleware\ApiKey::class,
        'jwt.authOveride'  => \App\Http\Middleware\JwtAuthenticate::class,
        'update'           => \App\Http\Middleware\CheckUpdate::class,
        'board'            => \App\Http\Middleware\CheckBoard::class,
        'install'          => \App\Http\Middleware\Install::class,
        'redirect'         => \App\Http\Middleware\Redirect::class,
        'installer'        => \App\Http\Middleware\IsInstalled::class,
        'force.option'     => \App\Http\Middleware\TicketViewURL::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'signed'           => \Illuminate\Routing\Middleware\ValidateSignature::class,
    ];
}
