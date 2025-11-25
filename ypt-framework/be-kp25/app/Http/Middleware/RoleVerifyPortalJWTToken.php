<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;
use Symfony\Component\HttpFoundation\Response;

class RoleVerifyPortalJWTToken
{
    public $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $token = trim($request->bearerToken());
        if (empty($token)) {
            return response()->json(['status' => false, 'message' => "Unauthorized"], 401);
        } else {
            $getProfile = $this->authService->getRoleByPortal($token);
            if (!$getProfile) {
                return response()->json(['status' => false, 'message' => "Unauthorized"], 401);
            }
        }

        return $next($request);
    }
}
