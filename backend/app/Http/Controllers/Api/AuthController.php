<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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

        // Create httpOnly cookie with the access token to protect it from XSS.
        $minutes = 60 * 24 * 7; // 7 days
        $cookie = cookie('access_token', $token, $minutes, '/', null, config('app.env') === 'production', true, false, 'Lax');

        return response()->json([
            'user' => $user,
        ])->withCookie($cookie);
    }

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

        return response()->json([
            'user' => $user,
        ], 201)->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        // Delete current access token if present
        try {
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }
        } catch (\Exception $e) {
            // ignore
        }

        // Remove access_token cookie
        $forget = cookie()->forget('access_token');

        return response()->json(['message' => 'Logged out successfully'])->withCookie($forget);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
