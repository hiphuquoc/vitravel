<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Language;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\ServiceOptionTranslation;
use App\Models\ServiceTranslation;
use App\Models\StayCrawlItem;
use App\Services\HtmlCacheService;
use App\Services\SeoService;
use App\Services\ServicePurgeService;
use App\Services\StayTaxonomyService;
use App\Support\StayBookingUrl;
use App\Support\StayFacilities;
use App\Support\StaySeed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class StayCrawlImporter
{
    public function __construct(
        private readonly SeoService $seo,
        private readonly ServicePurgeService $purger,
        private readonly StayTaxonomyService $taxonomy,
    ) {}

    /**
     * @param  array<string, mixed>  $fields  AI / normalized payload
     */
    public function import(
        StayCrawlItem $item,
        array $fields,
        ?int $categoryId = null,
        string $locale = 'vi',
        bool $dryRun = false,
        string $strategy = 'replace',
    ): Service {
            $title = trim((string) ($fields['title'] ?? ''));
        if ($title === '') {
            throw new RuntimeException('Payload chỗ nghỉ thiếu title — không import.');
        }

        $slug = StayBookingUrl::hotelSlug($item->canonical_url)
            ?: StayBookingUrl::hotelSlug($item->source_url)
            ?: '';
        $existingId = $item->service_id;
        $existing = $existingId ? Service::query()->find($existingId) : null;
        if (! $existing && $slug !== '') {
            $baseCode = 'bk-'.$slug;
            $existing = Service::query()->where('code', $baseCode)->first();
        }
        $code = $existing?->code ?: $this->uniqueCode('bk-'.($slug ?: Str::slug($title)));
        if ($existing) {
            $code = $existing->code;
        }

        $row = $this->toSeedRow($fields, $item, $code);
        if ($strategy === 'improve' && $existing) {
            $row = $this->mergeSeedRow($existing, $row, $locale);
        }
        $row = StaySeed::complete($row);

        if ($dryRun) {
            $service = new Service($this->serviceFill($row, $categoryId, $existing));
            $service->id = 0;

            return $service;
        }

        return DB::transaction(function () use ($item, $row, $categoryId, $locale, $existing, $strategy) {
            $service = $existing ?? new Service;
            $service->fill($this->serviceFill($row, $categoryId, $existing));
            // Cào xong mặc định publish (public); improve giữ published_at cũ nếu đã có.
            $service->status = 'published';
            $service->published_at = $existing?->published_at ?? $service->published_at ?? now();
            $service->save();

            $langId = Language::idByCode($locale);
            if ($langId) {
                ServiceTranslation::query()->updateOrCreate(
                    ['service_id' => $service->id, 'language_id' => $langId],
                    [
                        'title' => $row['title'],
                        'location_label' => $row['location_label'] ?? null,
                        'summary' => null,
                        'featured_quote_text' => null,
                        'featured_quote_author' => null,
                        'highlights' => [],
                        'inclusions' => [],
                        'exclusions' => [],
                        'notes' => [],
                        'content' => $row['content'] ?? null,
                    ],
                );
            }

            $this->syncOptions(
                $service,
                is_array($row['options'] ?? null) ? $row['options'] : [],
                $locale,
                prune: $strategy !== 'improve',
            );
            $incomingFaqs = is_array($row['faqs'] ?? null) ? $row['faqs'] : [];
            if ($strategy !== 'improve' || $this->hasFaqRows($incomingFaqs)) {
                $this->syncFaqs($service, $incomingFaqs, $locale);
            }

            $this->seo->syncSeo($service, $locale, [
                'slug' => (string) ($row['seo_slug'] ?? $row['code']),
                'title' => $row['title'],
                'description' => $row['seo_description'] ?? ($row['content'] ?? $row['title']),
                'seo_title' => $row['seo_title'] ?? $row['title'],
                'seo_description' => $row['seo_description'] ?? ($row['content'] ?? $row['title']),
                'status' => 'published',
                'parent_id' => $this->seoParentIdForCategory($categoryId, $locale),
                'reclaim_slug_full' => true,
            ], 'service');

            $item->service_id = $service->id;
            $item->status = StayCrawlItem::STATUS_IMPORTED;
            $item->imported_at = now();
            $item->save();

            // Đồng bộ Tags Tiện ích & Địa danh lân cận từ attrs
            $serviceAttrs = is_array($service->attrs) ? $service->attrs : [];
            $this->taxonomy->syncServiceTaxonomies($service, $serviceAttrs, $locale);

            // Đồng bộ tiện ích phòng cho từng hạng phòng
            foreach ($service->options as $opt) {
                $optAttrs = is_array($opt->attrs) ? $opt->attrs : [];
                $roomAmenities = \App\Support\StayFacilities::stringList($optAttrs['amenities'] ?? null);
                if ($roomAmenities !== []) {
                    $this->taxonomy->syncOptionAmenities($opt, $roomAmenities, $locale);
                }
            }

            app(HtmlCacheService::class)->clearAll();

            return $service->fresh(['options', 'faqs', 'seoEntry.translations', 'stayAmenities.translations', 'stayPlaces.translations']);
        });
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function toSeedRow(array $fields, StayCrawlItem $item, string $code): array
    {
        $attrs = is_array($fields['attrs'] ?? null) ? $fields['attrs'] : [];
        $crawl = is_array($attrs['crawl'] ?? null) ? $attrs['crawl'] : [];
        $crawl['source_url'] = $item->source_url;
        $crawl['canonical_url'] = $item->canonical_url;
        $crawl['source'] = $crawl['source'] ?? 'booking.com';
        $crawl['item_id'] = $item->id;
        $crawl['job_id'] = $item->job_id;
        $crawl['crawled_at'] = optional($item->crawled_at)->toIso8601String();
        $attrs['crawl'] = $crawl;
        foreach (['cancellation_policy', 'child_policy', 'pet_policy', 'smoking_policy', 'payment_policy', 'id_required_policy'] as $policyKey) {
            $val = trim((string) ($attrs[$policyKey] ?? ''));
            if ($val === '' || preg_match('/amount_with_currency|language_exception|famex_|bhqc_|paycom_|"[a-z0-9_]{8,}"\s*:/', $val)) {
                unset($attrs[$policyKey]);
            }
        }

        $options = [];
        foreach (is_array($fields['options'] ?? null) ? $fields['options'] : [] as $opt) {
            if (! is_array($opt)) {
                continue;
            }
            $optAttrs = is_array($opt['attrs'] ?? null) ? $opt['attrs'] : [];
            if (empty($optAttrs['photos']) && ! empty($opt['photos'])) {
                $optAttrs['photos'] = $opt['photos'];
            }
            $opt['attrs'] = $optAttrs;
            $options[] = StaySeed::normalizeOption($opt);
        }

        return [
            'cluster' => Service::CLUSTER_STAY,
            'code' => $code,
            'title' => $fields['title'],
            'location_label' => $fields['location_label'] ?? ($attrs['address'] ?? null),
            'content' => $fields['content'] ?? null,
            'featured_quote_text' => $fields['featured_quote_text'] ?? null,
            'featured_quote_author' => $fields['featured_quote_author'] ?? null,
            'star_rating' => $fields['star_rating'] ?? null,
            'price_from' => $fields['price_from'] ?? null,
            'currency' => strtoupper((string) ($fields['currency'] ?? 'VND')),
            'rating' => min(9.99, (float) ($fields['rating'] ?? 0)),
            'review_count' => $fields['review_count'] ?? 0,
            'seo_slug' => $fields['seo_slug'] ?? $code,
            'seo_title' => $fields['seo_title'] ?? null,
            'seo_description' => $fields['seo_description'] ?? null,
            'attrs' => $attrs,
            'options' => $options,
            'faqs' => $fields['faqs'] ?? [],
        ];
    }

    /** @param  array<string, mixed>  $row */
    private function serviceFill(array $row, ?int $categoryId, ?Service $existing = null): array
    {
        return [
            'cluster' => Service::CLUSTER_STAY,
            'service_category_id' => $categoryId ?: $existing?->service_category_id,
            'code' => $row['code'],
            'price_from' => $row['price_from'] ?? $existing?->price_from,
            'currency' => $row['currency'] ?? $existing?->currency ?? 'VND',
            'rating' => $row['rating'] ?? $existing?->rating ?? 0,
            'review_count' => $row['review_count'] ?? $existing?->review_count ?? 0,
            'star_rating' => $row['star_rating'] ?? $existing?->star_rating,
            'status' => $existing?->status ?? 'published',
            'attrs' => $row['attrs'] ?? [],
        ];
    }

    /** @param  list<array<string, mixed>>  $options */
    private function syncOptions(Service $service, array $options, string $locale, bool $prune = true): void
    {
        $langId = Language::idByCode($locale);
        $keep = [];
        $sort = 0;
        foreach (array_slice($options, 0, (int) config('stay.crawl.max_rooms', 16)) as $opt) {
            $name = trim((string) ($opt['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $code = trim((string) ($opt['code'] ?? '')) ?: Str::limit(Str::slug($name), 120, '');
            $option = $this->findOption($service, $name, $code);
            $mergedAttrs = is_array($opt['attrs'] ?? null) ? $opt['attrs'] : [];
            if ($option->exists && is_array($option->attrs)) {
                $mergedAttrs = self::mergeFilled($option->attrs, $mergedAttrs);
            }
            $option->fill([
                'price_from' => $opt['price_from'] ?? $option->price_from,
                'capacity' => $opt['capacity'] ?? $option->capacity,
                'sort' => $sort,
                'attrs' => $mergedAttrs,
            ]);
            $option->save();
            $keep[] = $option->id;
            $optAmenities = is_array($opt['amenities'] ?? null)
                ? array_values(array_filter(array_map('strval', $opt['amenities'])))
                : StayFacilities::stringList($opt['amenities'] ?? ($opt['attrs']['amenities'] ?? null));

            if ($langId) {
                ServiceOptionTranslation::query()->updateOrCreate(
                    ['service_option_id' => $option->id, 'language_id' => $langId],
                    [
                        'name' => $name,
                        'description' => $opt['description'] ?? null,
                        'amenities' => $optAmenities,
                    ],
                );
            }
            if (! empty($optAmenities)) {
                $this->taxonomy->syncOptionAmenities($option, $optAmenities, $locale);
            }
            $sort++;
        }
        if ($prune && $keep !== []) {
            $service->options()->whereNotIn('id', $keep)->delete();
        }
    }

    /** @param  list<mixed>  $faqs */
    private function syncFaqs(Service $service, array $faqs, string $locale): void
    {
        $langId = Language::idByCode($locale);
        $service->faqs()->delete();
        $sort = 0;
        foreach ($faqs as $row) {
            if (! is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['question'] ?? $row['q'] ?? ''));
            $a = trim((string) ($row['answer'] ?? $row['a'] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }
            $faq = Faq::query()->create([
                'faqable_type' => $service->getMorphClass(),
                'faqable_id' => $service->id,
                'sort' => $sort,
                'is_active' => true,
            ]);
            if ($langId) {
                FaqTranslation::query()->create([
                    'faq_id' => $faq->id,
                    'language_id' => $langId,
                    'question' => $q,
                    'answer' => $a,
                ]);
            }
            $sort++;
        }
    }

    /**
     * Trang chỗ nghỉ là con SEO của danh mục đang cào: level = cha+1, slug_full = cha.slug_full / slug.
     * Không ghi đè parent_id của danh mục (tránh kéo category về hub).
     */
    private function seoParentIdForCategory(?int $categoryId, string $locale): int
    {
        $hubSeo = $this->seo->ensureHub('stays_hub', $locale);
        if (! $categoryId) {
            return $hubSeo->id;
        }

        $category = ServiceCategory::query()->with('seoEntry')->find($categoryId);
        if (! $category) {
            return $hubSeo->id;
        }

        $existing = $category->seoEntry;
        if ($existing) {
            return $existing->id;
        }

        $catSeo = $this->seo->ensureSeoFor($category, 'service_category', $locale, [
            'slug' => $category->slug,
            'title' => $category->name,
            'seo_title' => $category->name,
            'status' => 'draft',
            'parent_id' => $hubSeo->id,
        ]);

        return $catSeo->id;
    }

    /**
     * Xóa hết chỗ nghỉ (SEO, FAQ, hạng phòng, media GCS orphan) — dùng chung với admin DELETE.
     *
     * @see ServicePurgeService::purge()
     */
    public function purgeCrawledService(Service $service): void
    {
        $this->purger->purge($service);
    }

    /**
     * Incoming non-empty replaces; empty keeps previous (box còn thiếu được bổ sung).
     *
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    public static function mergeFilled(array $old, array $incoming): array
    {
        $out = $old;
        foreach ($incoming as $key => $value) {
            if (self::isBlank($value)) {
                continue;
            }
            if ($key === 'rate_options' && is_array($value) && ! self::isAssoc($value)) {
                $out[$key] = self::mergeRateOptionLists(
                    is_array($old[$key] ?? null) ? $old[$key] : [],
                    $value,
                );

                continue;
            }
            if (is_array($value) && self::isAssoc($value) && is_array($old[$key] ?? null) && self::isAssoc($old[$key])) {
                $out[$key] = self::mergeFilled($old[$key], $value);

                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * @param  list<mixed>  $old
     * @param  list<mixed>  $incoming
     * @return list<array<string, mixed>>
     */
    private static function mergeRateOptionLists(array $old, array $incoming): array
    {
        $by = [];
        foreach (array_merge($old, $incoming) as $rate) {
            if (! is_array($rate)) {
                continue;
            }
            $id = (string) ($rate['block_id'] ?? '');
            if ($id === '') {
                continue;
            }
            if (! isset($by[$id])) {
                $by[$id] = $rate;

                continue;
            }
            $by[$id] = self::mergeFilled($by[$id], $rate);
        }

        return array_values($by);
    }

    /** @param  array<string, mixed>  $incoming */
    private function mergeSeedRow(Service $existing, array $incoming, string $locale): array
    {
        $existing->loadMissing(['translations', 'options.translations']);
        $langId = Language::idByCode($locale);
        $tr = $langId
            ? $existing->translations->firstWhere('language_id', $langId)
            : $existing->translations->first();
        $old = [
            'cluster' => Service::CLUSTER_STAY,
            'code' => $existing->code,
            'title' => $tr?->title,
            'location_label' => $tr?->location_label,
            'summary' => $tr?->summary,
            'content' => $tr?->content,
            'highlights' => is_array($tr?->highlights) ? $tr->highlights : [],
            'featured_quote_text' => $tr?->featured_quote_text,
            'featured_quote_author' => $tr?->featured_quote_author,
            'star_rating' => $existing->star_rating,
            'price_from' => $existing->price_from,
            'currency' => $existing->currency,
            'rating' => $existing->rating,
            'review_count' => $existing->review_count,
            'seo_slug' => $incoming['seo_slug'] ?? $existing->code,
            'seo_title' => $incoming['seo_title'] ?? null,
            'seo_description' => $incoming['seo_description'] ?? null,
            'attrs' => is_array($existing->attrs) ? $existing->attrs : [],
            'options' => $incoming['options'] ?? [],
            'faqs' => $incoming['faqs'] ?? [],
        ];
        $merged = self::mergeFilled($old, $incoming);
        $merged['code'] = $existing->code;
        $merged['options'] = $incoming['options'] ?? [];
        $merged['summary'] = null;
        $merged['highlights'] = [];

        return $merged;
    }

    /** @param  list<mixed>  $faqs */
    private function hasFaqRows(array $faqs): bool
    {
        foreach ($faqs as $row) {
            if (! is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['question'] ?? $row['q'] ?? ''));
            $a = trim((string) ($row['answer'] ?? $row['a'] ?? ''));
            if ($q !== '' && $a !== '') {
                return true;
            }
        }

        return false;
    }

    private static function isBlank(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_array($value)) {
            return $value === [];
        }

        return false;
    }

    /** @param  array<mixed>  $arr */
    private static function isAssoc(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Bổ sung 1 hạng phòng từ modal (không xóa option khác).
     *
     * @param  array<string, mixed>  $option
     */
    public function overlayRoom(Service $service, array $option, string $locale = 'vi'): void
    {
        $this->syncOptions($service, [$option], $locale, prune: false);
    }

    private function findOption(Service $service, string $name, string $code): ServiceOption
    {
        $option = $service->options()->where('code', $code)->first();
        if ($option) {
            return $option;
        }
        $matched = $service->options()
            ->whereHas('translations', fn ($q) => $q->where('name', $name))
            ->first();
        if ($matched) {
            return $matched;
        }

        $option = new ServiceOption;
        $option->service_id = $service->id;
        $option->code = $code;

        return $option;
    }

    private function uniqueCode(string $base): string
    {
        $base = Str::limit(Str::slug($base) ?: 'stay', 48, '');
        $code = $base;
        $i = 2;
        while (Service::query()->where('cluster', Service::CLUSTER_STAY)->where('code', $code)->exists()) {
            $code = $base.'-'.$i;
            $i++;
        }

        return $code;
    }
}
