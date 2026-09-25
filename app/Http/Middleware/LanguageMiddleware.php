<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
// use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Support\Facades\Config;
use Session;

class LanguageMiddleware
{
    public function handle($request, Closure $next)
    {
        $lang = '';
        if (\Auth::check()) {
            if (\Auth::user()->user_language != null) {
                $lang = \Auth::user()->user_language;
            } else {
                $lang = $this->getLangFromSessionOrCache();
            }
        } else {
            $lang = $this->getLangFromSessionOrCache();
        }

        if ($lang != '' and array_key_exists($lang, Config::get('languages'))) {
            App::setLocale($lang);
        } else {
            // No user/session/system language resolved: rest on the configured
            // default locale (app.locale), matching the enterprise edition.
            // Laravel still uses fallback_locale for any missing translation keys.
            App::setLocale(Config::get('app.locale'));
        }

        return $next($request);
    }

    public function getLangFromSessionOrCache()
    {
        $lang = '';
        if (Session::has('language')) {
            $lang = Session::get('language');
        } else {
            // cache, falling back to settings_system.content when the cache was cleared
            $lang = systemLanguage();
        }

        return $lang;
    }
}
