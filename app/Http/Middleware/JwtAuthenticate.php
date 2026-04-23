<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

/**
 * Middleware to handle JWT Authentication for the API call which requires
 * a valid token.
 *
 * @author Manish Verma <manish.verma@ladybirdweb.com>
 *
 * @since  v1.10
 */
class JwtAuthenticate extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $this->authenticate($request);

            return $next($request);
        } catch (\Exception $e) {
            return response(
                ['success' => false, 'message' => $e->getMessage()],
                $e->getStatusCode()
            );
        }
    }
}
