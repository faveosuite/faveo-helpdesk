<?php

namespace Diglactic\Breadcrumbs;

use Illuminate\Support\Facades\Facade;

/**
 * Breadcrumbs facade - allows easy access to the Manager instance.
 *
 * @method static void for(string $name, callable $callback)
 * @method static void before(callable $callback)
 * @method static void after(callable $callback)
 * @method static bool exists(?string $name = null)
 * @method static \Illuminate\Support\Collection generate(?string $name = null, ...$params)
 * @method static \Illuminate\Contracts\View\View view(string $view, ?string $name = null, ...$params)
 * @method static \Illuminate\Contracts\View\View render(?string $name = null, ...$params)
 * @method static object|null current()
 * @method static array getCurrentRoute()
 * @method static void setCurrentRoute(string $name, ...$params)
 * @method static void clearCurrentRoute()
 * @mixin \Illuminate\Support\Traits\Macroable
 * @see \Diglactic\Breadcrumbs\Manager
 */
class Breadcrumbs extends Facade
{
    /**
     * Get the name of the class registered in the Application container.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Manager::class;
    }
}
