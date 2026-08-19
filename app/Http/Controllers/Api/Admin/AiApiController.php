<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSystemPrompt;
use App\Models\AiUsageLog;
use App\Services\AI\AiGateway;
use App\Services\AI\DetailProgramEnrichService;
use App\Services\AI\ListingPageEnrichService;
use App\Services\AI\StayEnrichService;
use App\Services\AI\PageTranslateService;
use App\Services\AI\PromptRepository;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function enrichDetailProgram(Request $request, DetailProgramEnrichService $service): JsonResponse
    {
        try {
            $validated = $request->validate([
                'locale' => 'nullable|string|max:12',
                'entity_type' => 'required|string|max:64',
                'stage' => 'required|string|in:meta,content,faq',
                'provider' => 'nullable|string|in:openai,google,gemini,deepseek',
                'instructions' => 'nullable|string|max:4000',
                'fields' => 'required|array|min:1',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $result = $service->enrich(
                fields: $validated['fields'],
                entityType: $validated['entity_type'],
                locale: $validated['locale'] ?? 'vi',
                provider: isset($validated['provider'])
                    ? ($validated['provider'] === 'gemini' ? 'google' : $validated['provider'])
                    : null,
                instructions: $validated['instructions'] ?? null,
                stage: $validated['stage'],
            );
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 'AI_ERROR', 502);
        }

        return ApiResponse::success($result, 'Đã xây dựng chương trình');
    }

    public function enrichListingPage(Request $request, ListingPageEnrichService $service): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'locale' => 'nullable|string|max:12',
                'entity_type' => 'required|string|in:listing_hub,country,tour_category,cruise_type,service_category',
                'stage' => 'required|string|in:meta,body,faq',
                'hub_key' => 'nullable|string|max:64',
                'provider' => 'nullable|string|in:openai,google,gemini,deepseek',
                'instructions' => 'nullable|string|max:4000',
                'fields' => 'nullable|array',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $result = $service->enrich(
                title: $validated['title'],
                entityType: $validated['entity_type'],
                locale: $validated['locale'] ?? 'vi',
                hubKey: $validated['hub_key'] ?? null,
                provider: isset($validated['provider'])
                    ? ($validated['provider'] === 'gemini' ? 'google' : $validated['provider'])
                    : null,
                instructions: $validated['instructions'] ?? null,
                stage: $validated['stage'],
                fields: $validated['fields'] ?? [],
            );
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 'AI_ERROR', 502);
        }

        return ApiResponse::success($result, 'Đã xây dựng nội dung listing');
    }

    public function enrichStay(Request $request, StayEnrichService $service): JsonResponse
    {
        try {
            $validated = $request->validate([
                'locale' => 'nullable|string|max:12',
                'stage' => 'required|string|in:meta,property,faq',
                'provider' => 'nullable|string|in:openai,google,gemini,deepseek',
                'instructions' => 'nullable|string|max:4000',
                'fields' => 'required|array|min:1',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $result = $service->enrich(
                fields: $validated['fields'],
                locale: $validated['locale'] ?? 'vi',
                provider: isset($validated['provider'])
                    ? ($validated['provider'] === 'gemini' ? 'google' : $validated['provider'])
                    : null,
                instructions: $validated['instructions'] ?? null,
                stage: $validated['stage'],
            );
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 'AI_ERROR', 502);
        }

        return ApiResponse::success($result, 'Đã xây dựng nội dung lưu trú');
    }

    public function prompts(Request $request, PromptRepository $repo): JsonResponse
    {
        $includeInactive = $request->boolean('include_inactive');

        return ApiResponse::success([
            'items' => $repo->listCatalog($includeInactive),
            'file_keys' => $repo->registeredFileKeys(),
        ]);
    }

    public function showPrompt(string $key, PromptRepository $repo): JsonResponse
    {
        $items = $repo->listCatalog(includeInactive: true);
        foreach ($items as $item) {
            if (($item['key'] ?? null) === $key) {
                return ApiResponse::success($item);
            }
        }

        return ApiResponse::error("Không tìm thấy prompt «{$key}».", 'NOT_FOUND', 404);
    }

    public function updatePrompt(Request $request, string $key): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:5000',
                'category' => 'sometimes|string|max:64',
                'system' => 'sometimes|string|min:20',
                'user' => 'sometimes|string|min:10',
                'output_format' => 'sometimes|string|in:json,text',
                'is_active' => 'sometimes|boolean',
                'variables' => 'sometimes|array',
                'variables.*' => 'string|max:64',
                'entity_types' => 'sometimes|array',
                'entity_types.*' => 'string|max:64',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $row = AiSystemPrompt::query()->where('key', $key)->first();
        if (! $row) {
            return ApiResponse::error(
                "Prompt «{$key}» chưa có trên DB. Chạy php artisan ai:sync-prompts trước.",
                'NOT_FOUND',
                404
            );
        }

        $row->fill($validated);
        $row->is_customized = true;
        $row->updated_by = Auth::id();
        if (array_key_exists('system', $validated) || array_key_exists('user', $validated)) {
            $row->version = (int) $row->version + 1;
        }
        $row->save();

        return ApiResponse::success([
            'id' => $row->id,
            'key' => $row->key,
            'name' => $row->name,
            'category' => $row->category,
            'description' => $row->description,
            'version' => $row->version,
            'system' => $row->system,
            'user' => $row->user,
            'output_format' => $row->output_format,
            'variables' => $row->variables ?? [],
            'entity_types' => $row->entity_types ?? [],
            'is_active' => $row->is_active,
            'is_customized' => $row->is_customized,
            'updated_at' => $row->updated_at?->toIso8601String(),
        ], 'Đã cập nhật prompt');
    }

    public function syncPrompts(Request $request, PromptRepository $repo): JsonResponse
    {
        $force = $request->boolean('force');
        $result = $repo->syncFromFiles($force);

        return ApiResponse::success($result, $force ? 'Đã sync (force) từ file seed' : 'Đã sync từ file seed');
    }

    public function usage(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->input('per_page', 30)));
        $q = AiUsageLog::query()->orderByDesc('id');

        if ($request->filled('prompt_key')) {
            $q->where('prompt_key', (string) $request->input('prompt_key'));
        }
        if ($request->filled('feature')) {
            $q->where('feature', (string) $request->input('feature'));
        }
        if ($request->has('success')) {
            $q->where('success', $request->boolean('success'));
        }

        $page = $q->paginate($perPage);

        return ApiResponse::success([
            'items' => collect($page->items())->map(fn (AiUsageLog $log) => [
                'id' => $log->id,
                'prompt_key' => $log->prompt_key,
                'feature' => $log->feature,
                'entity_type' => $log->entity_type,
                'project_id' => $log->project_id,
                'user_id' => $log->user_id,
                'provider' => $log->provider,
                'model' => $log->model,
                'latency_ms' => $log->latency_ms,
                'success' => $log->success,
                'error_code' => $log->error_code,
                'error_message' => $log->error_message,
                'created_at' => $log->created_at?->toIso8601String(),
            ])->all(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}
