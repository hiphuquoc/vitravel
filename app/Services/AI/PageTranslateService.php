<?php

declare(strict_types=1);

namespace App\Services\AI;

use RuntimeException;

/**
 * Dịch toàn bộ field nội dung form admin → locale đích (JSON fields).
 */
final class PageTranslateService
{
    public function __construct(
        private readonly AiGateway $ai,
        private readonly PromptRepository $prompts,
        private readonly AiUsageLogger $usage,
    ) {}

    /**
     * @param  array<string, mixed>  $fields
     * @return array{fields: array<string, mixed>, provider: string, model: string, latency_ms: int, prompt_key: string, prompt_version: int|null}
     */
    public function translate(
        array $fields,
        string $sourceLocale,
        string $targetLocale,
        string $entityType = 'page',
        ?string $provider = null,
    ): array {
        if ($fields === []) {
            throw new RuntimeException('Không có field nào để dịch.');
        }

        $fieldsJson = json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($fieldsJson === false) {
            throw new RuntimeException('Không encode được fields JSON.');
        }

        try {
            $rendered = $this->prompts->renderPrompt('translate_page', array_merge(
                AiProjectBrand::vars(),
                [
                    'source_locale' => $sourceLocale,
                    'target_locale' => $targetLocale,
                    'entity_type' => $entityType,
                    'fields_json' => $fieldsJson,
                ],
            ));

            $result = $this->ai->chat(
                system: $rendered['system'],
                user: $rendered['user'],
                json: true,
                provider: $provider,
            );

            $parsed = $result['parsed'] ?? [];
            $translated = $parsed['fields'] ?? null;
            if (! is_array($translated)) {
                // Một số model trả thẳng object fields (không bọc).
                $translated = is_array($parsed) ? $parsed : null;
            }
            if (! is_array($translated)) {
                throw new RuntimeException('Phản hồi AI thiếu object «fields».');
            }

            $filtered = $this->intersectKeys($fields, $translated);

            $this->usage->logSuccess(
                'translate_page',
                'translate_page',
                $entityType,
                $result['provider'],
                $result['model'],
                $result['latency_ms'],
                [
                    'source_locale' => $sourceLocale,
                    'target_locale' => $targetLocale,
                ],
            );

            return [
                'fields' => $filtered,
                'provider' => $result['provider'],
                'model' => $result['model'],
                'latency_ms' => $result['latency_ms'],
                'prompt_key' => 'translate_page',
                'prompt_version' => $rendered['version'] ?? null,
            ];
        } catch (\Throwable $e) {
            $this->usage->logFailure(
                'translate_page',
                'translate_page',
                $entityType,
                $e->getMessage(),
            );
            throw $e;
        }
    }

    /**
     * Chỉ giữ key có trong input (tránh AI thêm field lạ).
     *
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $translated
     * @return array<string, mixed>
     */
    private function intersectKeys(array $source, array $translated): array
    {
        $out = [];
        foreach ($source as $key => $srcVal) {
            if (! array_key_exists($key, $translated)) {
                continue;
            }
            $dstVal = $translated[$key];
            if (is_array($srcVal) && is_array($dstVal) && ! array_is_list($srcVal)) {
                $out[$key] = $this->intersectKeys($srcVal, $dstVal);
            } elseif (is_array($srcVal) && is_array($dstVal) && array_is_list($srcVal)) {
                $out[$key] = [];
                foreach ($srcVal as $i => $row) {
                    if (! array_key_exists($i, $dstVal)) {
                        continue;
                    }
                    $cell = $dstVal[$i];
                    if (is_array($row) && is_array($cell) && ! array_is_list($row)) {
                        $out[$key][$i] = $this->intersectKeys($row, $cell);
                    } else {
                        $out[$key][$i] = $cell;
                    }
                }
            } else {
                $out[$key] = $dstVal;
            }
        }

        return $out;
    }
}
