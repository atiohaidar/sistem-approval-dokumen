<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Health check endpoint for monitoring
     */
    public function index(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'failed';
            $status = 'unhealthy';
        }

        // Check storage
        try {
            Storage::disk('public')->exists('documents');
            $checks['storage'] = 'ok';
        } catch (\Exception $e) {
            $checks['storage'] = 'failed';
            $status = 'unhealthy';
        }

        // Check queue (if using database queue)
        try {
            if (config('queue.default') === 'database') {
                DB::table('jobs')->count();
                $checks['queue'] = 'ok';
            }
        } catch (\Exception $e) {
            $checks['queue'] = 'failed';
        }

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'version' => config('app.version', '1.0.0'),
        ], $status === 'healthy' ? 200 : 503);
    }

    /**
     * Detailed system information (admin only)
     */
    public function detailed(): JsonResponse
    {
        $info = [
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'version' => config('app.version', '1.0.0'),
            ],
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ],
            'database' => [
                'driver' => config('database.default'),
            ],
            'cache' => [
                'driver' => config('cache.default'),
            ],
            'queue' => [
                'driver' => config('queue.default'),
            ],
        ];

        // Add disk usage information
        try {
            $publicDisk = Storage::disk('public');
            $info['storage'] = [
                'documents_count' => count($publicDisk->allFiles('documents')),
                'qr_codes_count' => count($publicDisk->allFiles('qr_codes')),
            ];
        } catch (\Exception $e) {
            $info['storage'] = ['error' => 'Unable to read storage'];
        }

        return response()->json($info);
    }
}
