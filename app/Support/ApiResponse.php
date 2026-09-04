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
            $attr = is_string(array_key_first($errors)) ? (string) array_key_first($errors) : '';
            $label = self::fieldLabel($attr);
            $message = match ($message) {
                'validation.unique' => 'Giá trị đã được dùng trong dự án khác hoặc bản ghi khác. Kiểm tra mã / slug.',
                'validation.in' => 'Giá trị không nằm trong danh sách cho phép.',
                'validation.required' => $label !== '' ? "Thiếu trường bắt buộc: {$label}." : 'Thiếu trường bắt buộc.',
                'validation.max.string', 'validation.max.array' => $label !== ''
                    ? "{$label} vượt quá độ dài cho phép."
                    : 'Nội dung vượt quá độ dài cho phép (quote / tiêu đề ngày / mô tả SEO).',
                'validation.exists' => $label !== ''
                    ? "{$label} tham chiếu không tồn tại."
                    : 'ID lịch trình / FAQ không hợp lệ.',
                'validation.string' => $label !== ''
                    ? "{$label} phải là chuỗi, không phải danh sách."
                    : 'Định dạng trường không đúng.',
                'validation.integer', 'validation.numeric' => $label !== ''
                    ? "{$label} phải là số."
                    : 'Giá trị số không hợp lệ.',
                'validation.size.string' => 'Mã tiền tệ phải đúng 3 ký tự (vd. VND).',
                default => $label !== '' ? "Dữ liệu không hợp lệ: {$label}." : 'Dữ liệu không hợp lệ.',
            };
        }

        return self::error($message, 'VALIDATION_ERROR', 422, $errors);
    }

    private static function fieldLabel(string $attribute): string
    {
        $base = (string) preg_replace('/\.\d+/', '', $attribute);
        $base = explode('.', $base)[0] ?? $base;

        return match ($base) {
            'featured_quote_text' => 'Trích dẫn nổi bật',
            'featured_quote_author' => 'Tác giả trích dẫn',
            'seo_title' => 'Tiêu đề SEO',
            'seo_description' => 'Mô tả SEO',
            'seo_slug' => 'Slug',
            'title' => 'Tiêu đề',
            'itinerary' => 'Lịch trình',
            'faqs' => 'FAQ',
            'meals_included' => 'Bữa ăn',
            'currency' => 'Tiền tệ',
            'duration_days' => 'Số ngày',
            'country_id' => 'Điểm đến',
            'cruise_type' => 'Loại du thuyền',
            'content' => 'Nội dung',
            default => $attribute !== '' ? $attribute : '',
        };
    }
}
