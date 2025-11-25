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

Route::get('/', function () {
    return response()->json([
        'name' => 'Sistem Approval Dokumen API',
        'state' => 'Normal'
    ], 200);
});

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Public document info (accessible via QR code) - DEPRECATED: Use secure token-based access instead
Route::get('/documents/{document}/public-info', [DocumentController::class, 'publicInfo']);
Route::get('/documents/{document}/public-preview', [DocumentController::class, 'publicPreview']);

// Secure token-based document access (public but requires valid token)
Route::get('/secure/documents/{token}', [DocumentController::class, 'secureAccess']);
Route::get('/secure/documents/{token}/preview', [DocumentController::class, 'securePreview']);
Route::get('/secure/documents/{token}/download', [DocumentController::class, 'secureDownload']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Document management
    Route::apiResource('documents', DocumentController::class);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    // Toggle public access for a document (creator or admin only)
    Route::post('documents/{document}/public-access', [DocumentController::class, 'setPublicAccess']);

    // Document access token management
    Route::post('documents/{document}/access-tokens', [DocumentController::class, 'generateAccessToken']);
    Route::get('documents/{document}/access-tokens', [DocumentController::class, 'getAccessTokens']);
    Route::post('documents/{document}/access-tokens/{tokenId}/revoke', [DocumentController::class, 'revokeAccessToken']);
    Route::post('documents/{document}/access-tokens/{tokenId}/rotate', [DocumentController::class, 'rotateAccessToken']);
    Route::get('documents/{document}/access-logs', [DocumentController::class, 'getAccessLogs']);

    // Approval system
    Route::prefix('approvals')->group(function () {
        Route::get('pending', [ApprovalController::class, 'getPendingApprovals']);
        Route::post('documents/{document}/process', [ApprovalController::class, 'processApproval']);
        Route::post('documents/{document}/delegate', [ApprovalController::class, 'delegateApproval']);
    });

    // User management (admin only)
    Route::middleware('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/change-role', [UserController::class, 'changeRole']);
    });
});
