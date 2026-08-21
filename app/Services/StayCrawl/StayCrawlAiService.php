<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Services\AI\AiGateway;
use App\Services\AI\AiProjectBrand;
use App\Services\AI\AiUsageLogger;
use App\Services\AI\Concerns\StripsAiCitations;
use App\Services\AI\PromptRepository;
use RuntimeException;

final class StayCrawlAiService
{
    use StripsAiCitations;

    public const PROMPT_KEY = 'crawl_stay_extract';

    public function __construct(
        private readonly AiGateway $ai,
        private readonly PromptRepository $prompts,
        private readonly AiUsageLogger $usage,
    ) {}

    /**
     * @param  array<string, mixed>  $rawJson
     * @return array<string, mixed>
     */
    public function extract(
        string $sourceUrl,
        string $extractedHtml,
        array $rawJson = [],
        string $locale = 'vi',
        ?string $provider = null,
        ?string $instructions = null,
    ): array {
        if (trim($extractedHtml) === '' && $rawJson === []) {
            throw new RuntimeException('Thiếu HTML đã lọc / JSON để AI xử lý.');
        }

        $rawJsonStr = json_encode($this->compactRaw($rawJson), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($rawJsonStr === false) {
            $rawJsonStr = '{}';
        }

        $rendered = $this->prompts->renderPrompt(self::PROMPT_KEY, array_merge(
            AiProjectBrand::vars(),
            [
                'locale' => $locale,
                'source_url' => $sourceUrl,
                'extracted_html' => $extractedHtml,
                'raw_json' => $rawJsonStr,
                'schema_hint' => $this->schemaHint(),
                'extra_instructions' => trim((string) $instructions) !== ''
                    ? trim((string) $instructions)
                    : '(không có)',
            ],
        ));

        try {
            $result = $this->ai->chat(
                system: $rendered['system'],
                user: $rendered['user'],
                json: true,
                provider: $provider,
                maxTokens: (int) config('ai.enrich_max_tokens', 16384),
                webSearch: false,
                timeout: (int) config('ai.enrich_timeout', 240),
            );

            $fields = $this->fieldsFromParsed($result['parsed'] ?? null);
            $fields = $this->stripWebSearchCitations($fields);
            $fields = $this->enforceSourceUrl($fields, $sourceUrl);

            $this->usage->logSuccess(
                self::PROMPT_KEY,
                'crawl_stay',
                'accommodation_stay',
                $result['provider'],
                $result['model'],
                $result['latency_ms'],
                ['locale' => $locale, 'source_url' => $sourceUrl],
            );

            return $fields;
        } catch (\Throwable $e) {
            $this->usage->logFailure(self::PROMPT_KEY, 'crawl_stay', 'accommodation_stay', $e->getMessage());
            throw $e;
        }
    }

    private function schemaHint(): string
    {
        return <<<'TXT'
{
  "title": "string",
  "summary": "string",
  "location_label": "string",
  "content": "string HTML",
  "highlights": ["… USP, không thêm tiện ích mới"],
  "star_rating": 5,
  "price_from": 0,
  "currency": "VND",
  "rating": 8.6,
  "review_count": 0,
  "seo_slug": "string",
  "seo_title": "string",
  "seo_description": "string",
  "attrs": {
    "property_type": "hotel|resort|villa|homestay|apartment|boutique|hostel|bungalow",
    "address": "string",
    "check_in": "15:00",
    "check_out": "12:00",
    "highlight_badges": ["…"],
    "amenities": ["giữ nguyên label nguồn"],
    "amenity_groups": { "bathroom": ["…"], "kitchen": ["…"] },
    "nearby_groups": { "beach": [{ "name": "", "distance": "", "category": "beach" }], "landmark": [], "transport": [] },
    "review_scores": [{ "tag": "staff", "score": 8.6 }, { "tag": "wifi", "score": 8.3 }],
    "cancellation_policy": "string|null",
    "child_policy": "string|null",
    "extra_bed_policy": "string|null",
    "age_restriction": "string|null",
    "pet_policy": "string|null",
    "smoking_policy": "string|null",
    "payment_policy": "string|null",
    "payment_cards": ["Visa"],
    "id_required_policy": "string|null",
    "crawl": { "source_url": "https://www.booking.com/hotel/…", "source": "booking.com" }
  },
  "options": [
    {
      "code": "string",
      "name": "string — giữ tên nguồn",
      "description": "string — viết lại hay",
      "price_from": 0,
      "capacity": 2,
      "amenities": ["giữ nguyên"],
      "attrs": {
        "unit_type": "hotel_room|entire_apartment|entire_villa|private_room",
        "size_sqm": 0,
        "view": "string",
        "bathroom_count": 1,
        "bedroom_count": 1,
        "smoking": "string",
        "highlights": ["…"],
        "beds": [{ "name": "Phòng ngủ 1", "items": [{ "type": "king", "count": 1, "label": "1 giường đôi cực lớn" }] }],
        "amenity_groups": {},
        "photos": [{ "url": "https://… nguồn", "alt": "…" }]
      }
    }
  ],
  "faqs": [{ "question": "", "answer": "" }]
}
CẤM inclusions, exclusions, notes, price_table.
TXT;
    }

    /** @param  mixed  $parsed */
    private function fieldsFromParsed(mixed $parsed): array
    {
        $fields = is_array($parsed) ? ($parsed['fields'] ?? null) : null;
        if (! is_array($fields)) {
            $fields = is_array($parsed) ? $parsed : null;
        }
        if (! is_array($fields)) {
            throw new RuntimeException('AI không trả object fields.');
        }

        return $fields;
    }

    /** @param  array<string, mixed>  $fields @return array<string, mixed> */
    private function enforceSourceUrl(array $fields, string $sourceUrl): array
    {
        $attrs = is_array($fields['attrs'] ?? null) ? $fields['attrs'] : [];
        $crawl = is_array($attrs['crawl'] ?? null) ? $attrs['crawl'] : [];
        $crawl['source_url'] = $sourceUrl;
        $crawl['source'] = $crawl['source'] ?? 'booking.com';
        $attrs['crawl'] = $crawl;
        $fields['attrs'] = $attrs;

        return $fields;
    }

    /** @param  array<string, mixed>  $raw @return array<string, mixed> */
    private function compactRaw(array $raw): array
    {
        unset($raw['extracted_html']);
        if (isset($raw['html'])) {
            unset($raw['html']);
        }

        return $raw;
    }
}
