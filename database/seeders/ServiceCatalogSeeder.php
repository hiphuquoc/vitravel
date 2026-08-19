<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Language;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\ServiceOptionTranslation;
use App\Models\ServiceTranslation;
use App\Services\SeoService;
use App\Support\ProjectSeed;
use App\Support\StaySeed;
use Illuminate\Database\Seeder;

/**
 * Seed 5 cụm dịch vụ từ ProjectSeed (service_categories + services).
 */
class ServiceCatalogSeeder extends Seeder
{
    protected SeoService $seo;

    protected ?int $viId = null;

    protected ?int $enId = null;

    /** @var array<string, int> */
    protected array $countryIds = [];

    /** @var array<string, int> cluster|slug → id */
    protected array $categoryIds = [];

    public function run(): void
    {
        $this->seo = app(SeoService::class);
        $this->viId = Language::idByCode('vi');
        $this->enId = Language::idByCode('en');
        $this->countryIds = Country::query()
            ->with('translations')
            ->get()
            ->mapWithKeys(function (Country $c) {
                $slug = $c->translation('vi')?->slug ?? $c->slug ?? null;

                return $slug ? [$slug => $c->id] : [];
            })
            ->all();

        $this->seedCategories();
        $this->seedServices();
    }

    protected function seedCategories(): void
    {
        foreach (ProjectSeed::get('service_categories', []) as $sort => $row) {
            $cluster = (string) ($row['cluster'] ?? '');
            $slug = (string) ($row['slug'] ?? '');
            if ($cluster === '' || $slug === '') {
                continue;
            }

            $intro = $row['intro']
                ?? $row['subtitle']
                ?? null;
            $seoBody = $row['seo_body'] ?? null;

            $category = ServiceCategory::query()->updateOrCreate(
                ['cluster' => $cluster, 'slug' => $slug],
                [
                    'name' => $row['name'] ?? $slug,
                    'intro' => $intro,
                    'seo_body' => $seoBody ?: $intro,
                    'sort' => $row['sort'] ?? $sort,
                    'is_active' => true,
                ]
            );

            $this->categoryIds[$cluster.'|'.$slug] = $category->id;

            $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
            if (! $hubKey) {
                continue;
            }

            $hub = $this->seo->ensureHub($hubKey, 'vi');
            $this->seo->ensureSeoFor($category, 'service_category', 'vi', [
                'slug' => $slug,
                'title' => $category->name,
                'seo_title' => $category->name.' — ViTravel',
                'description' => $category->intro,
                'seo_description' => $category->intro,
                'status' => 'published',
                'parent_id' => $hub->id,
                'reclaim_slug_full' => true,
            ]);

            if ($this->enId) {
                $hubEn = $this->seo->ensureHub($hubKey, 'en');
                $this->seo->ensureSeoFor($category, 'service_category', 'en', [
                    'slug' => $slug,
                    'title' => $category->name,
                    'status' => 'published',
                    'parent_id' => $hubEn->id,
                    'reclaim_slug_full' => true,
                ]);
            }
        }
    }

    protected function seedServices(): void
    {
        foreach (ProjectSeed::get('services', []) as $sort => $row) {
            $cluster = (string) ($row['cluster'] ?? '');
            $code = (string) ($row['code'] ?? ($row['slug'] ?? "SVC-{$sort}"));
            if ($cluster === '') {
                continue;
            }

            $hadOptions = array_key_exists('options', $row);
            $hadFaqs = array_key_exists('faqs', $row);
            if ($cluster === Service::CLUSTER_STAY) {
                $row = StaySeed::complete($row);
            }

            $categoryKey = $cluster.'|'.($row['category_slug'] ?? '');
            $categoryId = $this->categoryIds[$categoryKey] ?? null;
            $destSlug = (string) ($row['country_slug'] ?? $row['zone_slug'] ?? '');
            $countryId = $destSlug !== '' ? ($this->countryIds[$destSlug] ?? null) : null;
            if (! $countryId && $this->countryIds !== []) {
                $countryId = reset($this->countryIds) ?: null;
            }

            $service = Service::query()->updateOrCreate(
                ['code' => $code],
                [
                    'cluster' => $cluster,
                    'service_category_id' => $categoryId,
                    'country_id' => $countryId,
                    'price_from' => $row['price_from'] ?? null,
                    'currency' => $row['currency'] ?? 'VND',
                    'rating' => $row['rating'] ?? 0,
                    'review_count' => $row['review_count'] ?? 0,
                    'star_rating' => $row['star_rating'] ?? null,
                    'is_featured' => (bool) ($row['is_featured'] ?? false),
                    'is_hot_deal' => (bool) ($row['is_hot_deal'] ?? false),
                    'discount_badge' => $row['discount_badge'] ?? null,
                    'status' => 'published',
                    'published_at' => now(),
                    'sort' => $sort,
                    'attrs' => $row['attrs'] ?? null,
                ]
            );

            if ($this->viId) {
                ServiceTranslation::query()->updateOrCreate(
                    ['service_id' => $service->id, 'language_id' => $this->viId],
                    [
                        'title' => $row['title'],
                        'location_label' => $row['location_label'] ?? null,
                        'summary' => $row['summary'] ?? null,
                        'featured_quote_text' => $row['quote']['text'] ?? null,
                        'featured_quote_author' => $row['quote']['author'] ?? null,
                        'highlights' => $row['highlights'] ?? [],
                        'inclusions' => $row['inclusions'] ?? [],
                        'exclusions' => $row['exclusions'] ?? [],
                        'notes' => $row['notes'] ?? [],
                        'content' => $row['content'] ?? null,
                    ]
                );
            }

            $en = $row['en'] ?? null;
            if ($this->enId && is_array($en)) {
                ServiceTranslation::query()->updateOrCreate(
                    ['service_id' => $service->id, 'language_id' => $this->enId],
                    [
                        'title' => $en['title'] ?? $row['title'],
                        'location_label' => $en['location_label'] ?? ($row['location_label'] ?? null),
                        'summary' => $en['summary'] ?? ($row['summary'] ?? null),
                        'featured_quote_text' => $en['quote']['text'] ?? ($row['quote']['text'] ?? null),
                        'featured_quote_author' => $en['quote']['author'] ?? ($row['quote']['author'] ?? null),
                        'highlights' => $en['highlights'] ?? ($row['highlights'] ?? []),
                        'inclusions' => $en['inclusions'] ?? ($row['inclusions'] ?? []),
                        'exclusions' => $en['exclusions'] ?? ($row['exclusions'] ?? []),
                        'notes' => $en['notes'] ?? ($row['notes'] ?? []),
                        'content' => $en['content'] ?? ($row['content'] ?? null),
                    ]
                );
            }

            $options = $row['options'] ?? [];
            if ($hadOptions || $cluster === Service::CLUSTER_STAY) {
                $this->syncServiceOptions($service, is_array($options) ? $options : []);
            }

            $faqs = $row['faqs'] ?? [];
            if ($hadFaqs || ($cluster === Service::CLUSTER_STAY && $faqs !== [])) {
                $service->faqs()->delete();
                $this->syncFaqs($service, is_array($faqs) ? $faqs : [], is_array($en) ? ($en['faqs'] ?? []) : []);
            }

            $parentId = $this->resolveSeoParentId($service, $cluster, $row['category_slug'] ?? null);
            $slug = (string) ($row['slug'] ?? $code);

            $this->seo->syncSeo($service, 'vi', [
                'slug' => $slug,
                'title' => $row['title'],
                'description' => $row['summary'] ?? $row['title'],
                'seo_title' => ($row['title'] ?? '').' — ViTravel',
                'seo_description' => $row['summary'] ?? $row['title'],
                'rating_aggregate_star' => $row['rating'] ?? null,
                'rating_aggregate_count' => $row['review_count'] ?? null,
                'status' => 'published',
                'parent_id' => $parentId,
                'reclaim_slug_full' => true,
            ], 'service');

            if ($this->enId && is_array($en)) {
                // Đảm bảo cha (category/hub) đã có bản dịch EN trước khi gắn con
                $this->ensureServiceParentLocale($cluster, $row['category_slug'] ?? null, 'en');
                $this->seo->syncSeo($service, 'en', [
                    'slug' => $en['slug'] ?? $slug,
                    'title' => $en['title'] ?? $row['title'],
                    'description' => $en['summary'] ?? ($row['summary'] ?? null),
                    'status' => 'published',
                    'parent_id' => $parentId,
                    'reclaim_slug_full' => true,
                ], 'service');
            }
        }
    }

    /**
     * Upsert hạng phòng / tuỳ chọn theo code — không xoá bản ghi ngoài list seed.
     *
     * @param  list<array<string, mixed>>  $options
     */
    protected function syncServiceOptions(Service $service, array $options): void
    {
        $keepIds = [];

        foreach ($options as $optSort => $opt) {
            if (! is_array($opt) || trim((string) ($opt['name'] ?? '')) === '') {
                continue;
            }

            $code = (string) ($opt['code'] ?? '');
            if ($code === '') {
                $code = 'opt-'.$service->id.'-'.$optSort;
            }

            $option = ServiceOption::query()->updateOrCreate(
                ['service_id' => $service->id, 'code' => $code],
                [
                    'price_from' => $opt['price_from'] ?? null,
                    'capacity' => $opt['capacity'] ?? null,
                    'sort' => $optSort,
                    'attrs' => $opt['attrs'] ?? null,
                ]
            );
            $keepIds[] = $option->id;

            if ($this->viId) {
                ServiceOptionTranslation::query()->updateOrCreate(
                    ['service_option_id' => $option->id, 'language_id' => $this->viId],
                    [
                        'name' => $opt['name'],
                        'description' => $opt['description'] ?? null,
                        'amenities' => $opt['amenities'] ?? null,
                    ]
                );
            }

            if ($this->enId && ! empty($opt['en']['name'])) {
                ServiceOptionTranslation::query()->updateOrCreate(
                    ['service_option_id' => $option->id, 'language_id' => $this->enId],
                    [
                        'name' => $opt['en']['name'],
                        'description' => $opt['en']['description'] ?? ($opt['description'] ?? null),
                        'amenities' => $opt['en']['amenities'] ?? ($opt['amenities'] ?? null),
                    ]
                );
            }
        }

        if ($keepIds !== []) {
            $service->options()->whereNotIn('id', $keepIds)->delete();
        }
    }

    protected function resolveSeoParentId(Service $service, string $cluster, ?string $categorySlug): ?int
    {
        if ($categorySlug) {
            $category = ServiceCategory::query()
                ->where('cluster', $cluster)
                ->where('slug', $categorySlug)
                ->first();
            if ($category?->seoEntry?->id) {
                return $category->seoEntry->id;
            }
            // Ensure SEO exists
            $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
            if ($category && $hubKey) {
                $hub = $this->seo->ensureHub($hubKey, 'vi');
                $seo = $this->seo->ensureSeoFor($category, 'service_category', 'vi', [
                    'slug' => $category->slug,
                    'title' => $category->name,
                    'status' => 'published',
                    'parent_id' => $hub->id,
                    'reclaim_slug_full' => true,
                ]);

                return $seo->id;
            }
        }

        $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
        if (! $hubKey) {
            return null;
        }

        return $this->seo->ensureHub($hubKey, 'vi')->id;
    }

    protected function ensureServiceParentLocale(string $cluster, ?string $categorySlug, string $locale): void
    {
        $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
        if (! $hubKey) {
            return;
        }

        $hub = $this->seo->ensureHub($hubKey, $locale);

        if (! $categorySlug) {
            return;
        }

        $category = ServiceCategory::query()
            ->where('cluster', $cluster)
            ->where('slug', $categorySlug)
            ->first();

        if (! $category) {
            return;
        }

        $this->seo->ensureSeoFor($category, 'service_category', $locale, [
            'slug' => $category->slug,
            'title' => $category->name,
            'status' => 'published',
            'parent_id' => $hub->id,
            'reclaim_slug_full' => true,
        ]);
    }

    /**
     * @param  list<array{q?: string, a?: string, question?: string, answer?: string}>  $faqs
     * @param  list<array{q?: string, a?: string}>  $faqsEn
     */
    protected function syncFaqs(object $model, array $faqs, array $faqsEn = []): void
    {
        foreach ($faqs as $i => $faq) {
            $q = $faq['q'] ?? $faq['question'] ?? null;
            $a = $faq['a'] ?? $faq['answer'] ?? null;
            if (! filled($q) || ! filled($a)) {
                continue;
            }

            $faqModel = Faq::query()->create([
                'faqable_type' => $model->getMorphClass(),
                'faqable_id' => $model->getKey(),
                'sort' => $i,
            ]);

            if ($this->viId) {
                FaqTranslation::query()->create([
                    'faq_id' => $faqModel->id,
                    'language_id' => $this->viId,
                    'question' => $q,
                    'answer' => $a,
                ]);
            }

            $enFaq = $faqsEn[$i] ?? null;
            if ($this->enId && is_array($enFaq)) {
                FaqTranslation::query()->create([
                    'faq_id' => $faqModel->id,
                    'language_id' => $this->enId,
                    'question' => $enFaq['q'] ?? $enFaq['question'] ?? $q,
                    'answer' => $enFaq['a'] ?? $enFaq['answer'] ?? $a,
                ]);
            }
        }
    }
}
