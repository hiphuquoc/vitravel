<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Concerns\StripsAiCitations;
use RuntimeException;

/**
 * AI xây dựng nội dung trang listing (hub / country / chủ đề tour / cruise type / service category).
 *
 * Input tối thiểu: chỉ tiêu đề trang — tránh nhiễu từ nội dung cũ; AI tự research + viết lại.
 */
final class ListingPageEnrichService
{
    use StripsAiCitations;

    public const PROMPT_KEY = 'enrich_listing_page';

    /** @var list<string> */
    private const ENTITY_TYPES = [
        'listing_hub',
        'country',
        'tour_category',
        'cruise_type',
        'service_category',
    ];

    public function __construct(
        private readonly AiGateway $ai,
        private readonly PromptRepository $prompts,
        private readonly AiUsageLogger $usage,
    ) {}

    /**
     * @return array{
     *   fields: array<string, mixed>,
     *   provider: string,
     *   model: string,
     *   latency_ms: int,
     *   prompt_key: string,
     *   prompt_version: int|null
     * }
     */
    public function enrich(
        string $title,
        string $entityType,
        string $locale = 'vi',
        ?string $hubKey = null,
        ?string $provider = null,
        ?string $instructions = null,
    ): array {
        $title = trim($title);
        if ($title === '') {
            throw new RuntimeException('Thiếu tiêu đề trang để AI xử lý.');
        }

        if (! in_array($entityType, self::ENTITY_TYPES, true)) {
            throw new RuntimeException("entity_type không hỗ trợ: {$entityType}");
        }

        $contextJson = json_encode(['title' => $title], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($contextJson === false) {
            throw new RuntimeException('Không encode được context JSON.');
        }

        $hubKey = trim((string) $hubKey);
        $schemaHint = $this->schemaHintFor($entityType, $hubKey);
        $pageKind = $this->pageKindLabel($entityType, $hubKey);

        try {
            $rendered = $this->prompts->renderPrompt(self::PROMPT_KEY, array_merge(
                AiProjectBrand::vars(),
                [
                    'locale' => $locale,
                    'entity_type' => $entityType,
                    'hub_key' => $hubKey !== '' ? $hubKey : '(không áp dụng)',
                    'page_kind' => $pageKind,
                    'context_json' => $contextJson,
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

            $filtered = $this->normalizeOutput($enriched, $entityType);
            $filtered = $this->stripWebSearchCitations($filtered);

            $this->usage->logSuccess(
                self::PROMPT_KEY,
                'enrich_listing_page',
                $entityType,
                $result['provider'],
                $result['model'],
                $result['latency_ms'],
                [
                    'locale' => $locale,
                    'brand' => AiProjectBrand::vars()['brand'],
                    'hub_key' => $hubKey !== '' ? $hubKey : null,
                ],
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
                'enrich_listing_page',
                $entityType,
                $e->getMessage(),
            );
            throw $e;
        }
    }

    private function pageKindLabel(string $entityType, string $hubKey): string
    {
        return match ($entityType) {
            'listing_hub' => $hubKey !== '' ? "Hub listing ({$hubKey})" : 'Hub listing',
            'country' => 'Danh mục tour theo điểm đến / quốc gia',
            'tour_category' => 'Chủ đề / danh mục tour',
            'cruise_type' => 'Danh mục du thuyền theo loại',
            'service_category' => 'Danh mục dịch vụ',
            default => $entityType,
        };
    }

    private function schemaHintFor(string $entityType, string $hubKey): string
    {
        $base = <<<'TXT'
Canonical ListingChrome (public + admin aliases):
- title: H1 trang (có thể tinh chỉnh nhẹ từ input, không đổi ý)
- subtitle: copy ngắn dưới H1 (~1–3 câu, không HTML)
- seo_body: prose SEO dưới lưới sản phẩm (plain text hoặc HTML đơn giản p/strong/ul — không citation)
- seo_title: meta title ≤ ~60 ký tự
- seo_description: meta description ≤ ~155–160 ký tự
- seo_slug: Latin, dấu gạch ngang, gợi ý từ tiêu đề (không bắt buộc nếu admin đã có slug)
TXT;

        $extra = match ($entityType) {
            'listing_hub' => "\nHub key: {$hubKey}. subtitle → field body; seo_body → seo_body. Không FAQ.",
            'country' => "\nTrang tour theo điểm đến. subtitle = tagline; seo_body = long-form dưới grid.",
            'tour_category' => <<<'TXT'

Thêm faqs: 5–6 object { "question": "…", "answer": "…" } — key CHÍNH XÁC question/answer.
FAQ thực dụng: thời lượng, chi phí, ai phù hợp, mùa đi, visa/đi lại nếu liên quan.
TXT,
            'cruise_type' => "\nChỉ SEO + subtitle/seo_body nếu có giá trị (loại du thuyền / vịnh). Không FAQ.",
            'service_category' => "\nCụm dịch vụ — subtitle + seo_body mô tả danh mục con. Không FAQ.",
            default => '',
        };

        return $base.$extra."\n\nJSON output:\n{\n  \"fields\": { … }\n}";
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function normalizeOutput(array $fields, string $entityType): array
    {
        $out = [];

        foreach (['title', 'subtitle', 'seo_body', 'seo_title', 'seo_description', 'seo_slug'] as $key) {
            if (! array_key_exists($key, $fields)) {
                continue;
            }
            $val = $fields[$key];
            if (is_string($val) && trim($val) !== '') {
                $out[$key] = trim($val);
            }
        }

        if ($entityType === 'tour_category' && isset($fields['faqs']) && is_array($fields['faqs'])) {
            $faqs = $this->normalizeFaqsList($fields['faqs']);
            if ($faqs !== []) {
                $out['faqs'] = $faqs;
            }
        }

        if ($out === []) {
            throw new RuntimeException('AI không trả nội dung listing hợp lệ.');
        }

        return $out;
    }

    /**
     * @param  list<mixed>  $faqs
     * @return list<array{question: string, answer: string}>
     */
    private function normalizeFaqsList(array $faqs): array
    {
        $out = [];
        foreach ($faqs as $cell) {
            if (! is_array($cell)) {
                continue;
            }
            $question = $cell['question'] ?? $cell['q'] ?? '';
            $answer = $cell['answer'] ?? $cell['a'] ?? '';
            $q = is_string($question) ? trim($question) : '';
            $a = is_string($answer) ? trim($answer) : '';
            if ($q === '' && $a === '') {
                continue;
            }
            $out[] = ['question' => $q, 'answer' => $a];
        }

        return $out;
    }
}
