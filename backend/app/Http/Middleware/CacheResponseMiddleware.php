<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Don't cache authenticated requests
        if ($request->user()) {
            return $next($request);
        }

        $key = $this->getCacheKey($request);

        // Try to get from cache
        $cachedResponse = Cache::get($key);

        if ($cachedResponse) {
            return response($cachedResponse['content'])
                ->setStatusCode($cachedResponse['status'])
                ->withHeaders(array_merge($cachedResponse['headers'], [
                    'X-Cache' => 'HIT',
                    'X-Cache-Expires' => Cache::get($key . ':expires'),
                ]));
        }

        $response = $next($request);

        // Only cache successful responses
        if ($response->getStatusCode() === 200) {
            $expiresAt = now()->addMinutes($minutes);
            
            Cache::put($key, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], $expiresAt);

            Cache::put($key . ':expires', $expiresAt->toIso8601String(), $expiresAt);

            $response->headers->set('X-Cache', 'MISS');
            $response->headers->set('X-Cache-Expires', $expiresAt->toIso8601String());
        }

        return $response;
    }

    /**
     * Generate cache key for request
     */
    private function getCacheKey(Request $request): string
    {
        $url = $request->fullUrl();
        $accept = $request->header('Accept', '');
        
        return 'api:response:' . md5($url . $accept);
    }
}
