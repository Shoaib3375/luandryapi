<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = '', int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    protected function exceptionResponse(\Throwable $e, string $message = 'Server Error', int $code = 500): JsonResponse
    {
        if (config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], $code);
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }

}
