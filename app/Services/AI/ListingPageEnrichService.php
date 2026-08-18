<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Concerns\StripsAiCitations;
use RuntimeException;

/**
 * AI xây dựng nội dung trang listing (hub / country / chủ đề tour / cruise type / service category).
 *
 * 3 luồng: meta (chỉ title) → body (title + meta) → faq (title + SEO + body).
 */
final class ListingPageEnrichService
{
    use StripsAiCitations;

    public const STAGE_META = 'meta';
    public const STAGE_BODY = 'body';
    public const STAGE_FAQ = 'faq';

    /** @deprecated Dùng STAGE_* */
    public const PROMPT_KEY = 'enrich_listing_body';

    /** @var list<string> */
    public const STAGES = [self::STAGE_META, self::STAGE_BODY, self::STAGE_FAQ];

    /** @var array<string, string> */
    public const PROMPT_KEYS = [
        self::STAGE_META => 'enrich_listing_meta',
        self::STAGE_BODY => 'enrich_listing_body',
        self::STAGE_FAQ => 'enrich_listing_faq',
    ];

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
     * @param  array<string, mixed>  $fields
     * @return array{
     *   fields: array<string, mixed>,
     *   provider: string,
     *   model: string,
     *   latency_ms: int,
     *   prompt_key: string,
     *   prompt_version: int|null,
     *   stage: string
     * }
     */
    public function enrich(
        string $title,
        string $entityType,
        string $locale = 'vi',
        ?string $hubKey = null,
        ?string $provider = null,
        ?string $instructions = null,
        string $stage = self::STAGE_META,
        array $fields = [],
    ): array {
        $title = trim($title);
        if ($title === '') {
            throw new RuntimeException('Thiếu tiêu đề trang để AI xử lý.');
        }

        if (! in_array($entityType, self::ENTITY_TYPES, true)) {
            throw new RuntimeException("entity_type không hỗ trợ: {$entityType}");
        }

        $stage = $this->normalizeStage($stage);
        $promptKey = self::PROMPT_KEYS[$stage];
        $context = $this->contextForStage($title, $fields, $stage);

        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($contextJson === false) {
            throw new RuntimeException('Không encode được context JSON.');
        }

        $hubKey = trim((string) $hubKey);
        $schemaHint = $this->schemaHintFor($entityType, $hubKey, $stage);
        $pageKind = $this->pageKindLabel($entityType, $hubKey);
        $maxTokens = $stage === self::STAGE_BODY
            ? min(8192, (int) config('ai.enrich_max_tokens', 16384))
            : min(4096, (int) config('ai.enrich_max_tokens', 16384));

        try {
            $rendered = $this->prompts->renderPrompt($promptKey, array_merge(
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
                maxTokens: $maxTokens,
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

            $filtered = $this->normalizeOutput($enriched, $stage);
            $filtered = $this->stripWebSearchCitations($filtered);

            $this->usage->logSuccess(
                $promptKey,
                'enrich_listing_page',
                $entityType,
                $result['provider'],
                $result['model'],
                $result['latency_ms'],
                [
                    'locale' => $locale,
                    'brand' => AiProjectBrand::vars()['brand'],
                    'hub_key' => $hubKey !== '' ? $hubKey : null,
                    'stage' => $stage,
                ],
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
                'enrich_listing_page',
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
            throw new RuntimeException('stage không hợp lệ. Dùng: meta, body, faq.');
        }

        return $stage;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function contextForStage(string $title, array $fields, string $stage): array
    {
        if ($stage === self::STAGE_META) {
            return ['title' => $title];
        }

        $out = ['title' => $title];
        foreach (['subtitle', 'seo_title', 'seo_description', 'seo_slug', 'seo_body'] as $key) {
            $val = $fields[$key] ?? null;
            if (is_string($val) && trim($val) !== '') {
                $out[$key] = trim($val);
            }
        }

        if ($stage === self::STAGE_BODY) {
            unset($out['seo_body']);
        }

        return $out;
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

    private function schemaHintFor(string $entityType, string $hubKey, string $stage): string
    {
        if ($stage === self::STAGE_META) {
            $extra = $entityType === 'listing_hub' && $hubKey !== ''
                ? "\nHub key: {$hubKey}. subtitle → field body trên admin."
                : '';

            return <<<TXT
Chỉ các key:
{
  "title": "H1 — giữ ý tiêu đề input",
  "subtitle": "1–3 câu, PLAIN TEXT — cấm HTML",
  "seo_title": "≤ ~60 ký tự",
  "seo_description": "≤ ~155–160 ký tự",
  "seo_slug": "Latin, dấu gạch ngang"
}
CẤM seo_body, faqs.{$extra}
TXT;
        }

        if ($stage === self::STAGE_BODY) {
            return <<<'TXT'
Chỉ seo_body:
{
  "seo_body": "HTML bắt buộc — 3–5 <p>, có <strong> điểm đến/chủ đề/brand (cấm plain text, cấm markdown)"
}
CẤM title, subtitle, seo_title, seo_description, seo_slug, faqs.
TXT;
        }

        return <<<'TXT'
Chỉ faqs:
{
  "faqs": [
    { "question": "…", "answer": "…" }
  ]
}
5–6 object, key CHÍNH XÁC question/answer. CẤM mọi field khác.
TXT;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function normalizeOutput(array $fields, string $stage): array
    {
        $out = [];

        if ($stage === self::STAGE_META) {
            foreach (['title', 'subtitle', 'seo_title', 'seo_description', 'seo_slug'] as $key) {
                if (! array_key_exists($key, $fields)) {
                    continue;
                }
                $val = $fields[$key];
                if (is_string($val) && trim($val) !== '') {
                    $out[$key] = trim($val);
                }
            }
        } elseif ($stage === self::STAGE_BODY) {
            $val = $fields['seo_body'] ?? null;
            if (is_string($val) && trim($val) !== '') {
                $out['seo_body'] = $this->ensureSeoBodyHtml(trim($val));
            }
        } elseif ($stage === self::STAGE_FAQ) {
            if (isset($fields['faqs']) && is_array($fields['faqs'])) {
                $faqs = $this->normalizeFaqsList($fields['faqs']);
                if ($faqs !== []) {
                    $out['faqs'] = $faqs;
                }
            }
        }

        if ($out === []) {
            $hint = match ($stage) {
                self::STAGE_FAQ => 'AI không trả FAQ listing hợp lệ.',
                self::STAGE_BODY => 'AI không trả seo_body HTML hợp lệ.',
                default => 'AI không trả nội dung listing hợp lệ.',
            };
            throw new RuntimeException($hint);
        }

        return $out;
    }

    /**
     * seo_body phải là HTML (p/strong). Nếu model trả plain/markdown thì bọc lại.
     */
    private function ensureSeoBodyHtml(string $raw): string
    {
        $s = trim($raw);
        if ($s === '') {
            return $s;
        }

        $s = (string) preg_replace('/^```(?:html)?\s*|\s*```$/iu', '', $s);
        $s = (string) preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);

        if (str_contains($s, '<p') || str_contains($s, '<ul') || str_contains($s, '<ol')) {
            return $s;
        }

        $parts = preg_split('/\n{2,}/u', $s) ?: [$s];
        $html = '';
        foreach ($parts as $part) {
            $part = trim((string) preg_replace('/\s+/u', ' ', str_replace(["\r\n", "\n"], ' ', $part)));
            if ($part === '') {
                continue;
            }
            $html .= '<p>'.$part.'</p>';
        }

        return $html !== '' ? $html : $s;
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
