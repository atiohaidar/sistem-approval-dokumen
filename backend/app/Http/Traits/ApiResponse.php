<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * API Response Wrapper Trait
 * 
 * Provides consistent response structure across all API endpoints
 * 
 * Success Response:
 * {
 *   "success": true,
 *   "message": "Operation successful",
 *   "data": { ... }
 * }
 * 
 * Error Response:
 * {
 *   "success": false,
 *   "message": "Error message",
 *   "errors": { ... }
 * }
 */
trait ApiResponse
{
    /**
     * Return a success JSON response
     *
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param mixed $errors Additional error details
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Operation failed', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response
     *
     * @param array $errors Validation errors
     * @param string $message Error message
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return an unauthorized response
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return a not found response
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return a created response
     *
     * @param mixed $data Created resource data
     * @param string $message Success message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return an accepted response (for async operations)
     *
     * @param mixed $data Response data
     * @param string $message Success message
     * @return JsonResponse
     */
    protected function acceptedResponse($data = null, string $message = 'Request accepted for processing'): JsonResponse
    {
        return $this->successResponse($data, $message, 202);
    }

    /**
     * Return a no content response
     *
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
