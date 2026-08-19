<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Concerns\StripsAiCitations;
use RuntimeException;

/**
 * AI xây dựng trang chi tiết lưu trú (cluster stay) — 3 luồng: meta / property / faq.
 */
final class StayEnrichService
{
    use StripsAiCitations;

    public const STAGE_META = 'meta';
    public const STAGE_PROPERTY = 'property';
    public const STAGE_FAQ = 'faq';

    /** @var list<string> */
    public const STAGES = [self::STAGE_META, self::STAGE_PROPERTY, self::STAGE_FAQ];

    /** @var array<string, string> */
    public const PROMPT_KEYS = [
        self::STAGE_META => 'enrich_stay_meta',
        self::STAGE_PROPERTY => 'enrich_stay_property',
        self::STAGE_FAQ => 'enrich_stay_faq',
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
        string $locale = 'vi',
        ?string $provider = null,
        ?string $instructions = null,
        string $stage = self::STAGE_META,
    ): array {
        $stage = $this->normalizeStage($stage);
        $promptKey = self::PROMPT_KEYS[$stage];
        $context = $this->contextForStage($fields, $stage);

        if ($stage === self::STAGE_META && trim((string) ($context['title'] ?? '')) === '') {
            throw new RuntimeException('Thiếu tên chỗ nghỉ để AI xử lý thông tin + SEO.');
        }
        if ($context === []) {
            throw new RuntimeException('Không có field nào để AI xử lý.');
        }

        $fieldsJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($fieldsJson === false) {
            throw new RuntimeException('Không encode được fields JSON.');
        }

        $maxTokens = $stage === self::STAGE_PROPERTY
            ? (int) config('ai.enrich_max_tokens', 16384)
            : min(8192, (int) config('ai.enrich_max_tokens', 16384));

        try {
            $rendered = $this->prompts->renderPrompt($promptKey, array_merge(
                AiProjectBrand::vars(),
                [
                    'locale' => $locale,
                    'fields_json' => $fieldsJson,
                    'schema_hint' => $this->schemaHintFor($stage),
                    'extra_instructions' => trim((string) $instructions) !== ''
                        ? trim((string) $instructions)
                        : '(không có hướng dẫn thêm)',
                ],
            ));

            $result = $this->ai->chat(
                system: $rendered['system'],
                user: $rendered['user'],
                json: true,
                provider: $provider,
                maxTokens: $maxTokens,
                webSearch: (bool) config('ai.enrich_web_search', true),
                timeout: (int) config('ai.enrich_timeout', 240),
            );

            $enriched = $this->extractFieldsObject($result['parsed'] ?? null);
            $allowed = $this->outputKeys($stage);
            $enriched = array_intersect_key($enriched, array_flip($allowed));
            $mergeSource = $stage === self::STAGE_META ? $enriched : $context;
            $filtered = $this->mergePreferringAi($mergeSource, $enriched, $stage);
            $filtered = array_intersect_key($filtered, array_flip($allowed));
            $filtered = $this->stripWebSearchCitations($filtered);
            $filtered = $this->sanitize($filtered, $stage);

            if ($stage === self::STAGE_FAQ && empty($filtered['faqs'])) {
                throw new RuntimeException('AI không trả FAQ (faqs). Thử chạy lại luồng FAQ.');
            }
            if ($stage === self::STAGE_PROPERTY && trim((string) ($filtered['content'] ?? '')) === '') {
                throw new RuntimeException('AI không trả nội dung giới thiệu (content). Thử chạy lại luồng property.');
            }

            $this->usage->logSuccess(
                $promptKey,
                'enrich_stay',
                'accommodation_stay',
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
            $this->usage->logFailure($promptKey, 'enrich_stay', 'accommodation_stay', $e->getMessage());
            throw $e;
        }
    }

    public function normalizeStage(string $stage): string
    {
        $stage = strtolower(trim($stage));
        if (! in_array($stage, self::STAGES, true)) {
            throw new RuntimeException('stage không hợp lệ. Dùng: meta, property, faq.');
        }

        return $stage;
    }

    /** @param  array<string, mixed>  $fields */
    private function contextForStage(array $fields, string $stage): array
    {
        if ($stage === self::STAGE_META) {
            return ['title' => trim((string) ($fields['title'] ?? ''))];
        }

        return $fields;
    }

    /** @return list<string> */
    private function outputKeys(string $stage): array
    {
        return match ($stage) {
            self::STAGE_META => [
                'title', 'summary', 'location_label', 'featured_quote_text', 'featured_quote_author',
                'seo_slug', 'seo_title', 'seo_description',
            ],
            self::STAGE_PROPERTY => [
                'content', 'highlights',
                'attrs', 'options',
            ],
            self::STAGE_FAQ => ['faqs'],
            default => [],
        };
    }

    /** @param  mixed  $parsed */
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

    private function schemaHintFor(string $stage): string
    {
        if ($stage === self::STAGE_META) {
            return <<<'TXT'
Chỉ các key:
{
  "title": "string",
  "summary": "string (2–4 câu bán hàng)",
  "location_label": "string — địa chỉ / khu vực cụ thể",
  "featured_quote_text": "string ≤ 255",
  "featured_quote_author": "string",
  "seo_slug": "string",
  "seo_title": "string ≤ ~60",
  "seo_description": "string ≤ ~160"
}
CẤM content, faqs, options, attrs amenities.
TXT;
        }

        if ($stage === self::STAGE_PROPERTY) {
            return <<<'TXT'
{
  "content": "string HTML: 4–6 đoạn p, h2/h3, ul — mô tả chỗ nghỉ, không bịa giá",
  "highlights": "string — mỗi ý một dòng (6–10 ý USP, không thêm tiện ích mới)",
  "attrs": {
    "property_type": "hotel|resort|villa|homestay|apartment|boutique",
    "check_in": "15:00",
    "check_out": "12:00",
    "amenities": ["… giữ nguyên label nguồn"],
    "amenity_groups": {},
    "nearby": [{"name": "Sân bay", "distance": "20 phút", "category": "transport"}],
    "cancellation_policy": "string",
    "child_policy": "string",
    "pet_policy": "string",
    "payment_policy": "string",
    "id_required_policy": "string"
  },
  "options": [
    {
      "code": "string",
      "name": "string — tên hạng phòng (giữ nguyên nếu nguồn có)",
      "description": "string — viết lại hay, không thêm tiện ích",
      "price_from": 0,
      "capacity": 2,
      "bed_label": "1 giường king",
      "size_sqm": 45,
      "view": "Hướng biển",
      "amenities": ["Ban công", "Bồn tắm"],
      "photos": [{"url": "https://…", "alt": "…"}]
    }
  ]
}
Giữ chính xác số liệu / tiện ích / ảnh nếu input đã có — chỉ viết lại mô tả. CẤM inclusions, exclusions, notes, seo_*, faqs.
TXT;
        }

        return <<<'TXT'
Chỉ faqs:
{ "faqs": [{ "question": "string", "answer": "string 2–4 câu" }] }
5–8 phần tử. CẤM field khác.
TXT;
    }

    /** @param  array<string, mixed>  $source @param  array<string, mixed>  $ai */
    private function mergePreferringAi(array $source, array $ai, string $stage): array
    {
        $out = [];
        foreach ($ai as $key => $aiVal) {
            if ($aiVal === null) {
                continue;
            }
            if ($key === 'faqs' && is_array($aiVal)) {
                $out['faqs'] = $this->normalizeFaqs($aiVal);
                continue;
            }
            if ($key === 'options' && is_array($aiVal)) {
                $src = is_array($source['options'] ?? null) ? $source['options'] : [];
                $out['options'] = $this->mergeOptions($src, $aiVal);
                continue;
            }
            if ($key === 'attrs' && is_array($aiVal)) {
                $src = is_array($source['attrs'] ?? null) ? $source['attrs'] : [];
                $out['attrs'] = array_merge($src, $aiVal);
                continue;
            }
            if ($key === 'highlights') {
                $out[$key] = $this->stringifyLines($aiVal);
                continue;
            }
            $out[$key] = $aiVal;
        }

        return $out;
    }

    /** @param  list<mixed>  $faqs @return list<array{question: string, answer: string}> */
    private function normalizeFaqs(array $faqs): array
    {
        $out = [];
        foreach ($faqs as $cell) {
            if (! is_array($cell)) {
                continue;
            }
            $q = trim((string) ($cell['question'] ?? $cell['q'] ?? ''));
            $a = trim((string) ($cell['answer'] ?? $cell['a'] ?? ''));
            if ($q === '' && $a === '') {
                continue;
            }
            $out[] = ['question' => $q, 'answer' => $a];
        }

        return $out;
    }

    /** @param  list<mixed>  $source @param  list<mixed>  $ai */
    private function mergeOptions(array $source, array $ai): array
    {
        $out = [];
        foreach ($ai as $i => $cell) {
            if (! is_array($cell)) {
                continue;
            }
            $old = is_array($source[$i] ?? null) ? $source[$i] : [];
            if (($cell['code'] ?? '') === '' && ($old['code'] ?? '') !== '') {
                $cell['code'] = $old['code'];
            }
            if (! isset($cell['price_from']) && isset($old['price_from'])) {
                $cell['price_from'] = $old['price_from'];
            }
            if (! isset($cell['capacity']) && isset($old['capacity'])) {
                $cell['capacity'] = $old['capacity'];
            }
            foreach (['photos', 'photos_json', 'beds', 'beds_json', 'amenity_groups', 'amenity_groups_json', 'unit_type'] as $keep) {
                if ((empty($cell[$keep]) || $cell[$keep] === []) && ! empty($old[$keep])) {
                    $cell[$keep] = $old[$keep];
                }
            }
            if (is_array($old['attrs'] ?? null) && is_array($cell['attrs'] ?? null)) {
                $cell['attrs'] = array_merge($old['attrs'], $cell['attrs']);
            } elseif (empty($cell['attrs']) && ! empty($old['attrs'])) {
                $cell['attrs'] = $old['attrs'];
            }
            $out[] = $cell;
        }

        return $out;
    }

    /** @param  array<string, mixed>  $fields */
    private function sanitize(array $fields, string $stage): array
    {
        foreach (['title', 'location_label', 'featured_quote_text', 'featured_quote_author', 'seo_title'] as $k) {
            if (isset($fields[$k]) && is_string($fields[$k])) {
                $fields[$k] = mb_substr(trim($fields[$k]), 0, $k === 'seo_description' ? 320 : 255);
            }
        }
        if (isset($fields['seo_description']) && is_string($fields['seo_description'])) {
            $fields['seo_description'] = mb_substr(trim($fields['seo_description']), 0, 320);
        }
        foreach (['highlights'] as $k) {
            if (array_key_exists($k, $fields)) {
                $fields[$k] = $this->stringifyLines($fields[$k]);
            }
        }

        return $fields;
    }

    private function stringifyLines(mixed $value): string
    {
        if (is_array($value)) {
            return implode("\n", array_values(array_filter(array_map(
                static fn ($v) => trim((string) $v),
                $value,
            ))));
        }

        return is_scalar($value) ? trim((string) $value) : '';
    }
}
