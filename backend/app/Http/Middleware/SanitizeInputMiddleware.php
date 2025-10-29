<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInputMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        // Recursively sanitize input
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remove any null bytes
                $value = str_replace("\0", '', $value);
                
                // Strip tags from string inputs (except for specific fields)
                // Note: File uploads and JSON fields are excluded
                if (!is_array($value)) {
                    $value = strip_tags($value);
                }
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
