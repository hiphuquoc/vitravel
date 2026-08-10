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
        $errors = $exception->errors();
        $first = collect($errors)->flatten()->first();
        $message = is_string($first) && $first !== ''
            ? $first
            : ($exception->getMessage() ?: 'Dữ liệu không hợp lệ.');

        // Thiếu lang validation → Laravel trả key thô (validation.unique, …)
        if (is_string($message) && str_starts_with($message, 'validation.')) {
            $message = match ($message) {
                'validation.unique' => 'Giá trị đã được dùng trong dự án khác hoặc bản ghi khác. Kiểm tra mã / slug.',
                'validation.in' => 'Giá trị không nằm trong danh sách cho phép.',
                'validation.required' => 'Thiếu trường bắt buộc.',
                default => 'Dữ liệu không hợp lệ.',
            };
        }

        return self::error($message, 'VALIDATION_ERROR', 422, $errors);
    }
}
