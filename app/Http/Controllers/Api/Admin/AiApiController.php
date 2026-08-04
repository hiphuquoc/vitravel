<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AI\AiGateway;
use App\Services\AI\PageTranslateService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AiApiController extends Controller
{
    public function status(AiGateway $ai): JsonResponse
    {
        return ApiResponse::success([
            'configured' => $ai->isConfigured(),
            'default_provider' => config('ai.default_provider'),
            'providers' => $ai->status(),
        ]);
    }

    public function translatePage(Request $request, PageTranslateService $service): JsonResponse
    {
        try {
            $validated = $request->validate([
                'source_locale' => 'required|string|max:12',
                'target_locale' => 'required|string|max:12',
                'entity_type' => 'nullable|string|max:64',
                'provider' => 'nullable|string|in:openai,google,gemini,deepseek',
                'fields' => 'required|array|min:1',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        if (strtolower($validated['source_locale']) === strtolower($validated['target_locale'])) {
            return ApiResponse::error('source_locale và target_locale phải khác nhau.', 'INVALID_LOCALE', 422);
        }

        try {
            $result = $service->translate(
                fields: $validated['fields'],
                sourceLocale: $validated['source_locale'],
                targetLocale: $validated['target_locale'],
                entityType: $validated['entity_type'] ?? 'page',
                provider: isset($validated['provider'])
                    ? ($validated['provider'] === 'gemini' ? 'google' : $validated['provider'])
                    : null,
            );
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 'AI_ERROR', 502);
        }

        return ApiResponse::success($result, 'Đã dịch nội dung');
    }
}
