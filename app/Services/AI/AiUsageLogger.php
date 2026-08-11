<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiUsageLog;
use App\Support\ProjectContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Ghi nhận mỗi lần gọi AI (prompt key, provider, latency, lỗi).
 */
final class AiUsageLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function logSuccess(
        string $promptKey,
        string $feature,
        ?string $entityType,
        string $provider,
        string $model,
        int $latencyMs,
        array $meta = [],
    ): void {
        $this->write([
            'prompt_key' => $promptKey,
            'feature' => $feature,
            'entity_type' => $entityType,
            'provider' => $provider,
            'model' => $model,
            'latency_ms' => $latencyMs,
            'success' => true,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function logFailure(
        string $promptKey,
        string $feature,
        ?string $entityType,
        string $message,
        ?string $errorCode = 'AI_ERROR',
        array $meta = [],
    ): void {
        $this->write([
            'prompt_key' => $promptKey,
            'feature' => $feature,
            'entity_type' => $entityType,
            'success' => false,
            'error_code' => $errorCode,
            'error_message' => mb_substr($message, 0, 2000),
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function write(array $attrs): void
    {
        if (! $this->ready()) {
            return;
        }

        try {
            AiUsageLog::query()->create(array_merge($attrs, [
                'project_id' => ProjectContext::id(),
                'user_id' => Auth::id(),
            ]));
        } catch (Throwable) {
            // Không làm hỏng request AI vì lỗi log.
        }
    }

    private function ready(): bool
    {
        try {
            return Schema::hasTable('ai_usage_logs');
        } catch (Throwable) {
            return false;
        }
    }
}
