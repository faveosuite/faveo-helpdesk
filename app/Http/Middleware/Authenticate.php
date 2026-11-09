<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

/**
 * Authenticate.
 *
 * @author    Ladybird <info@ladybirdweb.com>
 */
class Authenticate
{
    /**
     * The auth factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     *
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   ...$guards
     *
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (!$this->guest($guards)) {
            return $next($request);
        }

        // Guard-protected API routes (e.g. 'auth:api', the Passport guard that
        // replaced the old JWT middleware) get a JSON 401 so token clients can
        // detect an auth failure. The guardless web `auth` path keeps its
        // historical ajax envelope / login redirect unchanged.
        if (!empty($guards)) {
            return response()->json(['result' => ['fails' => 'Unauthorized! Please login again']], 401);
        }

        if ($request->ajax()) {
            $result = ['fails' => 'Unauthorized! Please login again'];

            return response()->json(compact('result'));
        }

        return redirect()->guest('auth/login');
    }

    /**
     * Determine if the request is unauthenticated for the given guards.
     *
     * With no guards the default (web/session) guard is used, preserving the
     * original behaviour. When one or more guards are named (e.g. 'api'), the
     * first that authenticates becomes the default guard for this request.
     *
     * @param array $guards
     *
     * @return bool
     */
    protected function guest(array $guards)
    {
        if (empty($guards)) {
            return $this->auth->guard()->guest();
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);

                return false;
            }
        }

        return true;
    }
}
