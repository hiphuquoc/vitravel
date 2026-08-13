<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Concerns\StripsAiCitations;
use RuntimeException;

/**
 * AI xây dựng / hoàn thiện chương trình chi tiết (tour, cruise, service)
 * theo đúng shape field form admin.
 */
final class DetailProgramEnrichService
{
    use StripsAiCitations;
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
            $rendered = $this->prompts->renderPrompt(self::PROMPT_KEY, array_merge(
                AiProjectBrand::vars(),
                [
                    'locale' => $locale,
                    'entity_type' => $entityType,
                    'fields_json' => $fieldsJson,
                    'schema_hint' => $schemaHint,
                    'extra_instructions' => trim((string) $instructions) !== ''
                        ? trim((string) $instructions)
                        : '(không có hướng dẫn thêm)',
                ],
            ));

            $webSearch = (bool) config('ai.enrich_web_search', true);
            $result = $this->ai->chat(
                system: $rendered['system'],
                user: $rendered['user'],
                json: true,
                provider: $provider,
                maxTokens: (int) config('ai.enrich_max_tokens', 16384),
                webSearch: $webSearch,
                timeout: (int) config('ai.enrich_timeout', 240),
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
            $filtered = $this->normalizeStructuredLists($filtered, $fields);
            $filtered = $this->stripWebSearchCitations($filtered);

            if (
                in_array($entityType, ['tour_package', 'cruise_package'], true)
                && ! empty($fields['faq_rewrite'])
                && empty($filtered['faqs'])
            ) {
                throw new RuntimeException(
                    'AI không trả FAQ (faqs). Thử chạy lại hoặc tăng AI_ENRICH_MAX_TOKENS nếu tour nhiều ngày.',
                );
            }

            $this->usage->logSuccess(
                self::PROMPT_KEY,
                'enrich_detail_program',
                $entityType,
                $result['provider'],
                $result['model'],
                $result['latency_ms'],
                ['locale' => $locale, 'brand' => AiProjectBrand::vars()['brand']],
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
  "content": "string HTML dài: p/h2/h3/ul/ol/strong + cuối bài 1 <figure><img placehold.co… alt+figcaption></figure>",
  "highlights": "string — mỗi ý một dòng hoặc HTML list",
  "inclusions": "string — mỗi ý một dòng",
  "exclusions": "string — mỗi ý một dòng",
  "notes": "string — mỗi ý một dòng",
  "location_label": "string",
  "seo_slug": "string",
  "seo_title": "string",
  "seo_description": "string"
}
Ưu tiên content HTML giàu trải nghiệm; strong điểm đến / khung giờ nếu có quy trình.
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
      "content": "string HTML DÀI: mở đầu; timeline strong giờ+điểm đến; (tuỳ chọn) 1 <blockquote><p><strong>Mẹo nhỏ|Ghi chú|Lưu ý:</strong> …</p></blockquote>; cuối ngày 1 figure placehold.co",
      "overnight_at": "string"
    }
  ],
  "faqs": [
    { "question": "string (bắt buộc)", "answer": "string (bắt buộc, 2–4 câu)" }
  ]
}
Bắt buộc trả faqs: 5–8 phần tử, key đúng question/answer (KHÔNG dùng q/a).
Số ngày itinerary phải khớp duration_days trong context nếu có.
Mỗi ngày content ~180–420 từ, unique SEO, không lặp mở bài giữa các ngày.
HTML cho phép: p, br, strong, em, u, ul, ol, li, h3, blockquote, figure, figcaption, img.
Không chèn citation / markdown link / URL nguồn.
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
                if ($key === 'faqs') {
                    $normalized = $this->normalizeFaqsList($aiVal);
                    if ($normalized === []) {
                        continue;
                    }
                    $srcList = is_array($srcVal) && array_is_list($srcVal) ? $srcVal : [];
                    $out[$key] = $this->attachFaqIds($normalized, $srcList);
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
                if ($key === 'faqs' && is_array($aiVal)) {
                    $normalized = $this->normalizeFaqsList($aiVal);
                    if ($normalized !== []) {
                        $out[$key] = $normalized;
                    }
                    continue;
                }
                if ($key === 'itinerary' && is_array($aiVal)) {
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
            if (! is_array($cell) || array_is_list($cell)) {
                $out[] = $cell;
                continue;
            }

            $row = [];
            if (isset($cell['day_number'])) {
                foreach ($source as $srcRow) {
                    if (is_array($srcRow) && (int) ($srcRow['day_number'] ?? -1) === (int) $cell['day_number']) {
                        $row = $srcRow;
                        break;
                    }
                }
            }
            if ($row === [] && is_array($source[$i] ?? null)) {
                $row = $source[$i];
            }

            // Giữ id kỹ thuật từ row cũ nếu AI không trả.
            $merged = $this->mergePreferringAiLists($row, $cell);
            if (isset($row['id']) && ! array_key_exists('id', $merged)) {
                $merged['id'] = $row['id'];
            }
            // content HTML ngày: nếu AI trả chuỗi (kể cả dài) luôn nhận — không giữ bản cũ khi AI có key.
            if (array_key_exists('content', $cell) && is_string($cell['content'])) {
                $merged['content'] = $cell['content'];
            }
            $out[] = $merged;
        }

        return $out;
    }

    /**
     * Chuẩn hoá FAQ + itinerary sau merge (key AI lệch, list rỗng ẩn).
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function normalizeStructuredLists(array $data, array $source): array
    {
        if (isset($data['faqs']) && is_array($data['faqs'])) {
            $normalized = $this->normalizeFaqsList($data['faqs']);
            if ($normalized !== []) {
                $srcList = is_array($source['faqs'] ?? null) && array_is_list($source['faqs'])
                    ? $source['faqs']
                    : [];
                $data['faqs'] = $this->attachFaqIds($normalized, $srcList);
            } else {
                unset($data['faqs']);
            }
        }

        return $data;
    }

    /**
     * @param  list<mixed>  $faqs
     * @return list<array{question: string, answer: string, id?: mixed}>
     */
    private function normalizeFaqsList(array $faqs): array
    {
        $out = [];
        foreach ($faqs as $cell) {
            if (! is_array($cell)) {
                continue;
            }
            $row = $this->normalizeFaqRow($cell);
            if ($row['question'] === '' && $row['answer'] === '') {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $cell
     * @return array{question: string, answer: string, id?: mixed}
     */
    private function normalizeFaqRow(array $cell): array
    {
        $question = $cell['question'] ?? $cell['q'] ?? $cell['Question'] ?? $cell['cau_hoi'] ?? '';
        $answer = $cell['answer'] ?? $cell['a'] ?? $cell['Answer'] ?? $cell['cau_tra_loi'] ?? $cell['tra_loi'] ?? '';

        $row = [
            'question' => is_string($question) ? trim($question) : '',
            'answer' => is_string($answer) ? trim($answer) : '',
        ];

        if (array_key_exists('id', $cell) && $cell['id'] !== null && $cell['id'] !== '') {
            $row['id'] = $cell['id'];
        }

        return $row;
    }

    /**
     * @param  list<array{question: string, answer: string, id?: mixed}>  $faqs
     * @param  list<mixed>  $source
     * @return list<array{question: string, answer: string, id?: mixed}>
     */
    private function attachFaqIds(array $faqs, array $source): array
    {
        return array_map(function (array $row, int $i) use ($source): array {
            if (array_key_exists('id', $row)) {
                return $row;
            }
            $src = $source[$i] ?? null;
            if (is_array($src) && array_key_exists('id', $src) && $src['id'] !== null && $src['id'] !== '') {
                $row['id'] = $src['id'];
            }

            return $row;
        }, $faqs, array_keys($faqs));
    }
}
