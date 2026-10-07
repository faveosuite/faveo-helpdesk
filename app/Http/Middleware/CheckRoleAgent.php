<?php

namespace App\Http\Middleware;

use Closure;

/**
 * CheckRoleAgent.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class CheckRoleAgent
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
        // `auth` is expected to have run before this and normally has: every other
        // group registers it first. routes/web.php's admin group did not — it was
        // ('install', 'roles', 'auth', 'update'), so an anonymous request reached
        // this line BEFORE authentication, $request->user() was null, and reading
        // ->role raised "Attempt to read property \"role\" on null": HTTP 500 on
        // every admin URL instead of a redirect to the login page. That ordering is
        // fixed, and this guard keeps a future group that gets it wrong again
        // degrading to a login redirect rather than a crash.
        if (!$request->user()) {
            if ($request->ajax()) {
                $result = ['fails' => 'Unauthorized! Please login again'];

                return response()->json(compact('result'));
            }

            return redirect()->guest('auth/login');
        }

        if ($request->user()->role == 'agent' || $request->user()->role == 'admin') {
            return $next($request);
        }

        return redirect('/')->with('fails', 'You are not Authorised');
    }
}
