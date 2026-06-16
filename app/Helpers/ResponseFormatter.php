<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseFormatter
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(
        string|array $message = 'Error',
        mixed $errors = null,
        int $code = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => is_array($message) ? null : $message,
            'errors' => is_array($message) ? $message : $errors,
        ], $code);
    }
}
