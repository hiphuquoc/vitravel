<?php

declare(strict_types=1);

namespace App\Services\AI;

use RuntimeException;

/**
 * AI xây dựng / hoàn thiện chương trình chi tiết (tour, cruise, service)
 * theo đúng shape field form admin.
 */
final class DetailProgramEnrichService
{
    public const PROMPT_KEY = 'enrich_detail_program';

    public function __construct(
        private readonly AiGateway $ai,
        private readonly PromptRepository $prompts,
        private readonly AiUsageLogger $usage,
    ) {}

    /**
     * @param  array<string, mixed>  $fields
     * @return array{fields: array<string, mixed>, provider: string, model: string, latency_ms: int, prompt_key: string, prompt_version: int|null}
     */
    public function enrich(
        array $fields,
        string $entityType = 'tour_package',
        string $locale = 'vi',
        ?string $provider = null,
        ?string $instructions = null,
    ): array {
        if ($fields === []) {
            throw new RuntimeException('Không có field nào để AI xử lý.');
        }

        $fieldsJson = json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($fieldsJson === false) {
            throw new RuntimeException('Không encode được fields JSON.');
        }

        $schemaHint = $this->schemaHintFor($entityType);

        try {
            $rendered = $this->prompts->renderPrompt(self::PROMPT_KEY, [
                'locale' => $locale,
                'entity_type' => $entityType,
                'fields_json' => $fieldsJson,
                'schema_hint' => $schemaHint,
                'extra_instructions' => trim((string) $instructions) !== ''
                    ? trim((string) $instructions)
                    : '(không có hướng dẫn thêm)',
            ]);

            $result = $this->ai->chat(
                system: $rendered['system'],
                user: $rendered['user'],
                json: true,
                provider: $provider,
                maxTokens: (int) config('ai.enrich_max_tokens', 12288),
            );

            $parsed = $result['parsed'] ?? [];
            $enriched = $parsed['fields'] ?? null;
            if (! is_array($enriched)) {
                $enriched = is_array($parsed) ? $parsed : null;
            }
            if (! is_array($enriched)) {
                throw new RuntimeException('Phản hồi AI thiếu object «fields».');
            }

            $filtered = $this->mergePreferringAiLists($fields, $enriched);

            $this->usage->logSuccess(
                self::PROMPT_KEY,
                'enrich_detail_program',
                $entityType,
                $result['provider'],
                $result['model'],
                $result['latency_ms'],
                ['locale' => $locale],
            );

            return [
                'fields' => $filtered,
                'provider' => $result['provider'],
                'model' => $result['model'],
                'latency_ms' => $result['latency_ms'],
                'prompt_key' => self::PROMPT_KEY,
                'prompt_version' => $rendered['version'] ?? null,
            ];
        } catch (\Throwable $e) {
            $this->usage->logFailure(
                self::PROMPT_KEY,
                'enrich_detail_program',
                $entityType,
                $e->getMessage(),
            );
            throw $e;
        }
    }

    private function schemaHintFor(string $entityType): string
    {
        if (in_array($entityType, ['service', 'service_product'], true)) {
            return <<<'TXT'
Schema fields (service product) — giữ đúng key:
{
  "title": "string",
  "summary": "string (ngắn)",
  "content": "string HTML (p, h2, h3, ul, ol, strong, a…)",
  "highlights": "string — mỗi ý một dòng hoặc HTML list",
  "inclusions": "string — mỗi ý một dòng",
  "exclusions": "string — mỗi ý một dòng",
  "notes": "string — mỗi ý một dòng",
  "location_label": "string",
  "seo_slug": "string",
  "seo_title": "string",
  "seo_description": "string"
}
TXT;
        }

        return <<<'TXT'
Schema fields (tour_package / cruise_package) — giữ đúng key:
{
  "title": "string",
  "summary": "string",
  "highlights_intro": "string",
  "featured_quote_text": "string",
  "featured_quote_author": "string",
  "places_to_visit": "string — mỗi địa điểm một dòng",
  "highlight_bullets": "string — mỗi ý một dòng",
  "inclusions": "string — mỗi ý một dòng",
  "exclusions": "string — mỗi ý một dòng",
  "notes": "string — mỗi ý một dòng",
  "start_location": "string",
  "end_location": "string",
  "departure_port": "string (cruise)",
  "boat_class": "string (cruise)",
  "seo_slug": "string",
  "seo_title": "string",
  "seo_description": "string",
  "itinerary": [
    {
      "day_number": 1,
      "meals_included": "Sáng; Trưa; Tối | Sáng; Trưa | … | \"\"",
      "title": "string",
      "content": "string HTML chi tiết ngày (p/ul/ol/h3/strong…)",
      "overnight_at": "string"
    }
  ],
  "faqs": [
    { "question": "string", "answer": "string" }
  ]
}
Số ngày itinerary phải khớp duration_days trong context nếu có.
TXT;
    }

    /**
     * Giống intersectKeys nhưng với list (itinerary/faqs) lấy bản AI nếu AI trả list không rỗng.
     *
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $ai
     * @return array<string, mixed>
     */
    private function mergePreferringAiLists(array $source, array $ai): array
    {
        $out = [];
        $keys = array_unique(array_merge(array_keys($source), array_keys($ai)));

        foreach ($keys as $key) {
            if (! is_string($key) && ! is_int($key)) {
                continue;
            }
            $srcVal = $source[$key] ?? null;
            $aiVal = $ai[$key] ?? null;

            if ($aiVal === null) {
                continue;
            }

            // List (itinerary / faqs): nhận full list từ AI khi hợp lệ.
            if (is_array($aiVal) && array_is_list($aiVal)) {
                if ($aiVal === []) {
                    continue;
                }
                if (is_array($srcVal) && array_is_list($srcVal) && $srcVal !== []) {
                    $out[$key] = $this->mergeListRows($srcVal, $aiVal);
                } else {
                    $out[$key] = $aiVal;
                }
                continue;
            }

            if (is_array($srcVal) && is_array($aiVal) && ! array_is_list($srcVal) && ! array_is_list($aiVal)) {
                $out[$key] = $this->mergePreferringAiLists($srcVal, $aiVal);
                continue;
            }

            // Chỉ nhận key đã có trong input (tránh field lạ) — trừ list đã xử lý.
            if (! array_key_exists($key, $source)) {
                // Cho phép AI thêm itinerary/faqs khi input thiếu.
                if (in_array($key, ['itinerary', 'faqs'], true) && is_array($aiVal)) {
                    $out[$key] = $aiVal;
                }
                continue;
            }

            $out[$key] = $aiVal;
        }

        return $out;
    }

    /**
     * @param  list<mixed>  $source
     * @param  list<mixed>  $ai
     * @return list<mixed>
     */
    private function mergeListRows(array $source, array $ai): array
    {
        $out = [];
        foreach ($ai as $i => $cell) {
            $row = is_array($source[$i] ?? null) ? $source[$i] : [];
            if (is_array($cell) && is_array($row) && ! array_is_list($cell)) {
                // Giữ id kỹ thuật từ row cũ nếu AI không trả.
                $merged = $this->mergePreferringAiLists($row, $cell);
                if (isset($row['id']) && ! array_key_exists('id', $merged)) {
                    $merged['id'] = $row['id'];
                }
                $out[] = $merged;
            } else {
                $out[] = $cell;
            }
        }

        return $out;
    }
}
