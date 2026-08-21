<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Concerns\StripsAiCitations;
use RuntimeException;

/**
 * AI xây dựng trang chi tiết lưu trú (cluster stay) — 3 luồng: meta / property (chỉ content) / faq.
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

    /**
     * Context gửi AI — property nhận đủ facts khách sạn (read-only); output chỉ content.
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function contextForStage(array $fields, string $stage): array
    {
        if ($stage === self::STAGE_META) {
            return ['title' => trim((string) ($fields['title'] ?? ''))];
        }

        if ($stage === self::STAGE_PROPERTY) {
            return $this->propertyContext($fields);
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function propertyContext(array $fields): array
    {
        $attrs = is_array($fields['attrs'] ?? null) ? $fields['attrs'] : [];
        $options = is_array($fields['options'] ?? null) ? $fields['options'] : [];

        $roomSummaries = [];
        foreach ($options as $opt) {
            if (! is_array($opt)) {
                continue;
            }
            $name = trim((string) ($opt['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $roomSummaries[] = array_filter([
                'name' => $name,
                'capacity' => $opt['capacity'] ?? null,
                'size_sqm' => $opt['size_sqm'] ?? null,
                'view' => $opt['view'] ?? null,
                'bed_label' => $opt['bed_label'] ?? null,
                'amenities' => is_array($opt['amenities'] ?? null)
                    ? array_values(array_slice(array_map('strval', $opt['amenities']), 0, 8))
                    : null,
            ], static fn ($v) => $v !== null && $v !== '' && $v !== []);
        }

        return array_filter([
            'title' => trim((string) ($fields['title'] ?? '')),
            'location_label' => trim((string) ($fields['location_label'] ?? '')),
            'star_rating' => $fields['star_rating'] ?? null,
            'price_from' => $fields['price_from'] ?? null,
            'currency' => $fields['currency'] ?? null,
            'seo_title' => $fields['seo_title'] ?? null,
            'seo_description' => $fields['seo_description'] ?? null,
            'property_type' => $attrs['property_type'] ?? null,
            'address' => $attrs['address'] ?? null,
            'check_in' => $attrs['check_in'] ?? null,
            'check_out' => $attrs['check_out'] ?? null,
            'highlight_badges' => $attrs['highlight_badges'] ?? null,
            'amenities' => $attrs['amenities'] ?? null,
            'amenity_groups' => $attrs['amenity_groups'] ?? null,
            'nearby_groups' => $attrs['nearby_groups'] ?? null,
            'review_scores' => $attrs['review_scores'] ?? null,
            'cancellation_policy' => $attrs['cancellation_policy'] ?? null,
            'child_policy' => $attrs['child_policy'] ?? null,
            'extra_bed_policy' => $attrs['extra_bed_policy'] ?? null,
            'age_restriction' => $attrs['age_restriction'] ?? null,
            'pet_policy' => $attrs['pet_policy'] ?? null,
            'smoking_policy' => $attrs['smoking_policy'] ?? null,
            'payment_policy' => $attrs['payment_policy'] ?? null,
            'payment_cards' => $attrs['payment_cards'] ?? null,
            'id_required_policy' => $attrs['id_required_policy'] ?? null,
            'rooms' => $roomSummaries !== [] ? $roomSummaries : null,
            'content_existing' => trim((string) ($fields['content'] ?? '')) !== ''
                ? '(đã có bản nháp — viết lại mới, đừng copy)'
                : null,
        ], static fn ($v) => $v !== null && $v !== '' && $v !== []);
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
                'content',
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
CẤM content, faqs, options, attrs, amenities.
TXT;
        }

        if ($stage === self::STAGE_PROPERTY) {
            return <<<'TXT'
Chỉ một key:
{
  "content": "string HTML DÀI (~700–1200 từ): p/h2/h3/ul/ol/strong + 2–3 <figure><img src=\"https://placehold.co/1200x675?text=…\" alt=\"…\" loading=\"lazy\" /><figcaption>…</figcaption></figure> xen giữa các mục"
}
CẤM attrs, options, faqs, seo_*, summary, highlights, inclusions, exclusions, notes.
HTML cho phép: p, br, strong, em, u, ul, ol, li, h2, h3, blockquote, figure, figcaption, img.
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
        unset($source);
        $out = [];
        foreach ($ai as $key => $aiVal) {
            if ($aiVal === null) {
                continue;
            }
            if ($key === 'faqs' && is_array($aiVal)) {
                $out['faqs'] = $this->normalizeFaqs($aiVal);
                continue;
            }
            // Property: chỉ nhận content — bỏ qua attrs/options nếu model vẫn trả.
            if ($stage === self::STAGE_PROPERTY && $key !== 'content') {
                continue;
            }
            if (in_array($key, ['attrs', 'options'], true)) {
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

    /** @param  array<string, mixed>  $fields */
    private function sanitize(array $fields, string $stage): array
    {
        unset($stage);
        foreach (['title', 'location_label', 'featured_quote_text', 'featured_quote_author', 'seo_title'] as $k) {
            if (isset($fields[$k]) && is_string($fields[$k])) {
                $fields[$k] = mb_substr(trim($fields[$k]), 0, 255);
            }
        }
        if (isset($fields['seo_description']) && is_string($fields['seo_description'])) {
            $fields['seo_description'] = mb_substr(trim($fields['seo_description']), 0, 320);
        }
        if (isset($fields['content']) && is_string($fields['content'])) {
            $fields['content'] = trim($fields['content']);
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
