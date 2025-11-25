<?php

namespace App\Helpers;

/**
 * This is a helper to organize the response of each user who makes a request.
 * Note that in all methods the helper has an underscore separator and
 * has the suffix _helper in the last name of the method.
 */
class ResponseHelper
{
    /**
     * @param string $message
     * @param \Illuminate\Database\Eloquent\Collection|array $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function success_response_helper($message, $data, $statusCode): \Illuminate\Http\JsonResponse
    {
        return response()->json(['status' => 'OK', 'message' => $message, 'data' => $data], $statusCode);
    }

    /**
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function success_message_response_helper($message, $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message], $statusCode);
    }

    /**
     * @param string $message
     * @param \Illuminate\Database\Eloquent\Collection|array $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function custom_response_helper($status, $message, $data, $statusCode): \Illuminate\Http\JsonResponse
    {
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data], $statusCode);
    }

    /**
     * @param string $message
     * @param int|string $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function fail_response_helper($message, $statusCode): \Illuminate\Http\JsonResponse
    {
        if ($statusCode === 0) {
            return response()->json(['status' => 'Not OK', 'message' => $message, 'data' => null], 500);
        }

        return response()->json(['status' => 'Not OK', 'message' => $message, 'data' => null], $statusCode);
    }

    /**
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */

    public function fail_message_response_helper($message, $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $statusCode);
    }

    /**
     * @param \Exception $throwObj
     * @return \Exception
     */
    public function catch_throw_exception_helper($throwObj): \Exception
    {
        $statusCode = $throwObj->getCode() ?: 500;
        return new \Exception($throwObj->getMessage(), $statusCode);
    }

    public function success_data_response_helper($message, $data, $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $statusCode);
    }
}
