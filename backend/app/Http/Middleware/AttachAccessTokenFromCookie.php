<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachAccessTokenFromCookie
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If Authorization header missing and httpOnly cookie 'access_token' exists,
        // attach it as Bearer token so Laravel token guard can authenticate.
        if (!$request->headers->has('Authorization') && $request->cookies->has('access_token')) {
            $token = $request->cookie('access_token');
            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }

        return $next($request);
    }
}
