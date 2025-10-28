<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication Controller
 * 
 * Handles user authentication using Laravel Sanctum
 * Uses httpOnly cookies for secure token storage
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Authenticate user and return token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // Create httpOnly cookie with the access token to protect it from XSS
        $minutes = 60 * 24 * 7; // 7 days
        $cookie = cookie('access_token', $token, $minutes, '/', null, config('app.env') === 'production', true, false, 'Lax');

        return $this->successResponse([
            'user' => $user,
            'token' => $token, // For testing and explicit token usage
        ], 'Login successful')->withCookie($cookie);
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // default role
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $minutes = 60 * 24 * 7; // 7 days
        $cookie = cookie('access_token', $token, $minutes, '/', null, config('app.env') === 'production', true, false, 'Lax');

        return $this->createdResponse([
            'user' => $user,
            'token' => $token, // For testing and explicit token usage
        ], 'Registration successful')->withCookie($cookie);
    }

    /**
     * Logout user and revoke token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Delete current access token if present
        try {
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }
        } catch (\Exception $e) {
            // Log error but continue with logout
            \Log::warning('Token deletion failed during logout', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Remove access_token cookie
        $forget = cookie()->forget('access_token');

        return $this->successResponse(null, 'Logged out successfully')->withCookie($forget);
    }

    /**
     * Get authenticated user information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return $this->successResponse($request->user(), 'User retrieved successfully');
    }
}

