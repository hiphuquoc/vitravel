<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Concerns\StripsAiCitations;
use RuntimeException;

/**
 * AI xây dựng / hoàn thiện chương trình chi tiết (tour, cruise, service)
 * theo đúng shape field form admin — 3 luồng độc lập (meta / content / faq).
 */
final class DetailProgramEnrichService
{
    use StripsAiCitations;

    public const STAGE_META = 'meta';
    public const STAGE_CONTENT = 'content';
    public const STAGE_FAQ = 'faq';

    /** @deprecated Dùng STAGE_* + promptKeys() */
    public const PROMPT_KEY = 'enrich_detail_content';

    /** @var list<string> */
    public const STAGES = [self::STAGE_META, self::STAGE_CONTENT, self::STAGE_FAQ];

    /** @var array<string, string> */
    public const PROMPT_KEYS = [
        self::STAGE_META => 'enrich_detail_meta',
        self::STAGE_CONTENT => 'enrich_detail_content',
        self::STAGE_FAQ => 'enrich_detail_faq',
    ];

    public function __construct(
        private readonly AiGateway $ai,
        private readonly PromptRepository $prompts,
        private readonly AiUsageLogger $usage,
    ) {}

    /**
     * @param  array<string, mixed>  $fields
     * @return array{fields: array<string, mixed>, provider: string, model: string, latency_ms: int, prompt_key: string, prompt_version: int|null, stage: string}
     */
    public function enrich(
        array $fields,
        string $entityType = 'tour_package',
        string $locale = 'vi',
        ?string $provider = null,
        ?string $instructions = null,
        string $stage = self::STAGE_CONTENT,
    ): array {
        $stage = $this->normalizeStage($stage);
        $promptKey = self::PROMPT_KEYS[$stage];
        $context = $this->contextForStage($fields, $stage);

        if ($stage === self::STAGE_META && trim((string) ($context['title'] ?? '')) === '') {
            throw new RuntimeException('Thiếu tiêu đề chương trình để AI xử lý thông tin + SEO.');
        }
        if ($context === []) {
            throw new RuntimeException('Không có field nào để AI xử lý.');
        }

        $fieldsJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($fieldsJson === false) {
            throw new RuntimeException('Không encode được fields JSON.');
        }

        $schemaHint = $this->schemaHintFor($entityType, $stage);
        $maxTokens = $stage === self::STAGE_CONTENT
            ? (int) config('ai.enrich_max_tokens', 16384)
            : min(8192, (int) config('ai.enrich_max_tokens', 16384));

        try {
            $rendered = $this->prompts->renderPrompt($promptKey, array_merge(
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
                maxTokens: $maxTokens,
                webSearch: $webSearch,
                timeout: (int) config('ai.enrich_timeout', 240),
            );

            $enriched = $this->extractFieldsObject($result['parsed'] ?? null);
            $allowed = $this->outputKeys($entityType, $stage);
            $enriched = array_intersect_key($enriched, array_flip($allowed));

            $mergeSource = $stage === self::STAGE_META ? $enriched : $context;
            $filtered = $this->mergePreferringAiLists($mergeSource, $enriched);
            $filtered = array_intersect_key($filtered, array_flip($allowed));
            $filtered = $this->normalizeStructuredLists($filtered, $context);
            $filtered = $this->stripWebSearchCitations($filtered);
            $filtered = $this->sanitizeForAdminSave($filtered);
            $filtered = array_intersect_key($filtered, array_flip($allowed));

            if ($stage === self::STAGE_FAQ && empty($filtered['faqs'])) {
                throw new RuntimeException('AI không trả FAQ (faqs). Thử chạy lại luồng câu hỏi.');
            }
            if (
                $stage === self::STAGE_CONTENT
                && in_array($entityType, ['tour_package', 'cruise_package'], true)
                && empty($filtered['itinerary'])
            ) {
                throw new RuntimeException('AI không trả lịch trình (itinerary). Thử chạy lại luồng nội dung chi tiết.');
            }
            if (
                $stage === self::STAGE_CONTENT
                && in_array($entityType, ['service', 'service_product'], true)
                && trim((string) ($filtered['content'] ?? '')) === ''
            ) {
                throw new RuntimeException('AI không trả nội dung chi tiết (content). Thử chạy lại luồng nội dung.');
            }

            $this->usage->logSuccess(
                $promptKey,
                'enrich_detail_program',
                $entityType,
                $result['provider'],
                $result['model'],
                $result['latency_ms'],
                ['locale' => $locale, 'brand' => AiProjectBrand::vars()['brand'], 'stage' => $stage],
            );

            return [
                'fields' => $filtered,
                'provider' => $result['provider'],
                'model' => $result['model'],
                'latency_ms' => $result['latency_ms'],
                'prompt_key' => $promptKey,
                'prompt_version' => $rendered['version'] ?? null,
                'stage' => $stage,
            ];
        } catch (\Throwable $e) {
            $this->usage->logFailure(
                $promptKey,
                'enrich_detail_program',
                $entityType,
                $e->getMessage(),
            );
            throw $e;
        }
    }

    public function normalizeStage(string $stage): string
    {
        $stage = strtolower(trim($stage));
        if (! in_array($stage, self::STAGES, true)) {
            throw new RuntimeException('stage không hợp lệ. Dùng: meta, content, faq.');
        }

        return $stage;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function contextForStage(array $fields, string $stage): array
    {
        if ($stage === self::STAGE_META) {
            return ['title' => trim((string) ($fields['title'] ?? ''))];
        }

        return $fields;
    }

    /**
     * @return list<string>
     */
    private function outputKeys(string $entityType, string $stage): array
    {
        $isService = in_array($entityType, ['service', 'service_product'], true);

        return match ($stage) {
            self::STAGE_META => $isService
                ? ['title', 'summary', 'location_label', 'seo_slug', 'seo_title', 'seo_description']
                : [
                    'title', 'summary', 'highlights_intro',
                    'featured_quote_text', 'featured_quote_author',
                    'places_to_visit', 'start_location', 'end_location',
                    'departure_port', 'boat_class',
                    'seo_slug', 'seo_title', 'seo_description',
                ],
            self::STAGE_CONTENT => $isService
                ? ['content', 'highlights', 'inclusions', 'exclusions', 'notes']
                : ['itinerary', 'highlight_bullets', 'inclusions', 'exclusions', 'notes'],
            self::STAGE_FAQ => ['faqs'],
            default => [],
        };
    }

    /**
     * @param  mixed  $parsed
     * @return array<string, mixed>
     */
    private function extractFieldsObject(mixed $parsed): array
    {
        $enriched = is_array($parsed) ? ($parsed['fields'] ?? null) : null;
        if (! is_array($enriched)) {
            $enriched = is_array($parsed) ? $parsed : null;
        }
        if (! is_array($enriched)) {
            throw new RuntimeException('Phản hồi AI thiếu object «fields».');
        }

        return $enriched;
    }

    private function schemaHintFor(string $entityType, string $stage): string
    {
        $isService = in_array($entityType, ['service', 'service_product'], true);

        if ($stage === self::STAGE_META) {
            if ($isService) {
                return <<<'TXT'
Chỉ các key sau (service):
{
  "title": "string",
  "summary": "string (2–4 câu)",
  "location_label": "string",
  "seo_slug": "string",
  "seo_title": "string ≤ ~60",
  "seo_description": "string ≤ ~160"
}
CẤM content, highlights, inclusions, faqs.
TXT;
            }

            return <<<'TXT'
Chỉ các key sau (tour / cruise):
{
  "title": "string",
  "summary": "string (2–4 câu)",
  "highlights_intro": "string",
  "featured_quote_text": "string",
  "featured_quote_author": "string",
  "places_to_visit": "string — mỗi địa điểm một dòng",
  "start_location": "string",
  "end_location": "string",
  "departure_port": "string (cruise)",
  "boat_class": "string (cruise)",
  "seo_slug": "string",
  "seo_title": "string ≤ ~60",
  "seo_description": "string ≤ ~160"
}
CẤM itinerary, faqs, highlight_bullets, inclusions/exclusions/notes.
TXT;
        }

        if ($stage === self::STAGE_CONTENT) {
            if ($isService) {
                return <<<'TXT'
Chỉ các key sau (service content):
{
  "content": "string HTML dài: p/h2/h3/ul/ol/strong + cuối bài 1 <figure><img placehold.co… alt+figcaption></figure>",
  "highlights": "string — mỗi ý một dòng",
  "inclusions": "string — mỗi ý một dòng",
  "exclusions": "string — mỗi ý một dòng",
  "notes": "string — mỗi ý một dòng"
}
CẤM title, summary, seo_*, faqs, location_label.
TXT;
            }

            return <<<'TXT'
Chỉ các key sau (tour / cruise content):
{
  "highlight_bullets": "string — mỗi ý một dòng",
  "inclusions": "string — mỗi ý một dòng",
  "exclusions": "string — mỗi ý một dòng",
  "notes": "string — mỗi ý một dòng",
  "itinerary": [
    {
      "day_number": 1,
      "meals_included": "Sáng; Trưa; Tối | Sáng; Trưa | … | \"\"",
      "title": "string",
      "content": "string HTML DÀI: mở đầu; timeline strong giờ+điểm đến; (tuỳ chọn) 1 blockquote mẹo; cuối ngày 1 figure placehold.co",
      "overnight_at": "string"
    }
  ]
}
Số ngày khớp duration_days nếu có. Mỗi ngày ~180–420 từ, unique.
HTML: p, br, strong, em, u, ul, ol, li, h3, blockquote, figure, figcaption, img.
CẤM faqs, seo_*, summary, highlights_intro, featured_quote_*.
TXT;
        }

        return <<<'TXT'
Chỉ faqs:
{
  "faqs": [
    { "question": "string (bắt buộc)", "answer": "string (bắt buộc, 2–4 câu)" }
  ]
}
5–8 phần tử. Key đúng question/answer (KHÔNG q/a). CẤM mọi field khác.
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

            // Giữ id kỹ thuật từ row cũ — không nhận id do AI bịa.
            $merged = $this->mergePreferringAiLists($row, $cell);
            unset($merged['id']);
            if (isset($row['id']) && $row['id'] !== null && $row['id'] !== '') {
                $merged['id'] = $row['id'];
            }
            if (array_key_exists('meals_included', $merged)) {
                $merged['meals_included'] = $this->stringifyLines($merged['meals_included'], '; ');
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
            unset($row['id']);
            $src = $source[$i] ?? null;
            if (is_array($src) && array_key_exists('id', $src) && $src['id'] !== null && $src['id'] !== '') {
                $row['id'] = $src['id'];
            }

            return $row;
        }, $faqs, array_keys($faqs));
    }

    /**
     * Cắt / ép kiểu để khớp rule lưu form admin (tránh 422 validation.* mơ hồ).
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function sanitizeForAdminSave(array $fields): array
    {
        $limits = [
            'title' => 255,
            'featured_quote_text' => 255,
            'featured_quote_author' => 255,
            'start_location' => 255,
            'end_location' => 255,
            'location_label' => 255,
            'seo_title' => 255,
            'seo_description' => 320,
            'seo_slug' => 191,
            'discount_badge' => 100,
        ];

        foreach ($limits as $key => $max) {
            if (! isset($fields[$key]) || ! is_string($fields[$key])) {
                continue;
            }
            $fields[$key] = $this->mbClip($fields[$key], $max);
        }

        foreach (['highlights', 'inclusions', 'exclusions', 'notes', 'highlight_bullets', 'places_to_visit'] as $listKey) {
            if (array_key_exists($listKey, $fields)) {
                $fields[$listKey] = $this->stringifyLines($fields[$listKey], "\n");
            }
        }

        if (isset($fields['itinerary']) && is_array($fields['itinerary'])) {
            foreach ($fields['itinerary'] as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (array_key_exists('meals_included', $row)) {
                    $row['meals_included'] = $this->mbClip($this->stringifyLines($row['meals_included'], '; '), 100);
                }
                if (isset($row['title']) && is_string($row['title'])) {
                    $row['title'] = $this->mbClip($row['title'], 255);
                }
                if (isset($row['overnight_at']) && is_string($row['overnight_at'])) {
                    $row['overnight_at'] = $this->mbClip($row['overnight_at'], 255);
                }
                $fields['itinerary'][$i] = $row;
            }
        }

        if (isset($fields['faqs']) && is_array($fields['faqs'])) {
            foreach ($fields['faqs'] as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (isset($row['question']) && is_string($row['question'])) {
                    $row['question'] = $this->mbClip($row['question'], 500);
                }
                $fields['faqs'][$i] = $row;
            }
        }

        unset(
            $fields['cruise_type'],
            $fields['country_id'],
            $fields['category_ids'],
            $fields['travel_style_ids'],
            $fields['seo_parent_id'],
            $fields['id'],
            $fields['type'],
        );

        return $fields;
    }

    private function stringifyLines(mixed $value, string $glue = "\n"): string
    {
        if ($value === null) {
            return '';
        }
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                if (is_string($item) || is_numeric($item)) {
                    $part = trim((string) $item);
                    if ($part !== '') {
                        $parts[] = $part;
                    }
                }
            }

            return implode($glue, $parts);
        }

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function mbClip(string $value, int $max): string
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) <= $max) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $max));
    }
}
