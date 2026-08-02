<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(
        string $message,
        string $code = 'ERROR',
        int $status = 400,
        mixed $details = null,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== null) {
            $payload['error']['details'] = $details;
        }

        return response()->json($payload, $status);
    }

    public static function fromValidation(ValidationException $exception): JsonResponse
    {
        return self::error(
            $exception->getMessage() ?: 'Dữ liệu không hợp lệ.',
            'VALIDATION_ERROR',
            422,
            $exception->errors(),
        );
    }
}
