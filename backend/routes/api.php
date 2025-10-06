<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ApprovalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Document management
    Route::apiResource('documents', DocumentController::class);

    // Document approval system
    Route::prefix('documents/{document}/approval')->group(function () {
        Route::post('/submit', [ApprovalController::class, 'submit']);
        Route::get('/history', [ApprovalController::class, 'history']);
    });

    // Approval actions
    Route::prefix('approvals/{approval}')->group(function () {
        Route::post('/approve', [ApprovalController::class, 'approve']);
        Route::post('/reject', [ApprovalController::class, 'reject']);
        Route::post('/skip', [ApprovalController::class, 'skip']);
        Route::post('/delegate', [ApprovalController::class, 'delegate']);
        Route::post('/comment', [ApprovalController::class, 'comment']);
    });

    // Pending approvals for current user
    Route::get('/approvals/pending', [ApprovalController::class, 'pending']);

    // User management (admin only)
    Route::middleware('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/change-role', [UserController::class, 'changeRole']);
    });
});