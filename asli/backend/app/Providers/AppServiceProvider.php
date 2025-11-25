<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

use App\Http\Middleware\AttachAccessTokenFromCookie;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Attach middleware to api group to allow reading access_token cookie as Bearer token
        try {
            $router = $this->app->make(Router::class);
            $router->pushMiddlewareToGroup('api', AttachAccessTokenFromCookie::class);
        } catch (\Exception $e) {
            // If router not available yet, ignore. This is best-effort.
        }
    }
}
