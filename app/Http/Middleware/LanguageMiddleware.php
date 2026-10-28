<?php

namespace App\Http\Middleware;

use Cache;
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

        if ($lang == '' || !array_key_exists($lang, Config::get('languages'))) {
            // no user, session or system language: use the configured default (APP_LOCALE)
            $lang = Config::get('app.locale');
        }

        if ($lang != '' and array_key_exists($lang, Config::get('languages'))) {
            App::setLocale($lang);
        } else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            App::setLocale(Config::get('app.fallback_locale'));
        }

        return $next($request);
    }

    public function getLangFromSessionOrCache()
    {
        $lang = '';
        if (Session::has('language')) {
            $lang = Session::get('language');
        } elseif (Cache::has('language')) {
            $lang = Cache::get('language');
        }

        return $lang;
    }
}
