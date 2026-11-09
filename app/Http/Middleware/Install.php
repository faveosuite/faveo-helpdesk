<?php

namespace App\Http\Middleware;

use Closure;

class Install
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $env = base_path('.env');
        if (\File::exists($env)) {
            return $next($request);
        } else {
            return redirect('/probe.php');
        }
    }
}
