<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Language;
use App\Models\Package;
use App\Models\TourCategory;
use App\Models\TourCategoryTranslation;
use App\Services\SeoService;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;

class TourCategorySeeder extends Seeder
{
    protected SeoService $seo;

    protected ?int $viId;

    protected ?int $enId;

    /** @var array<string, int> */
    protected array $countryIds = [];

    /** @var array<string, int> */
    protected array $categoryIds = [];

    public function run(): void
    {
        $this->seo = app(SeoService::class);
        $this->viId = Language::idByCode('vi');
        $this->enId = Language::idByCode('en');

        $this->ensureCountryTranslations();
        $this->ensureCountrySeo();
        $this->seedTourCategories();
        $this->linkPackagesToCategories();
    }

    protected function ensureCountryTranslations(): void
    {
        $codes = ProjectSeed::countryCodes();

        foreach (ProjectSeed::get('countries', []) as $sort => $row) {
            $country = Country::query()->updateOrCreate(
                ['code' => $codes[$row['slug']] ?? strtoupper(substr($row['slug'], 0, 2))],
                [
                    'home_grid_size' => $row['size'],
                    'sort' => $sort,
                    'is_active' => true,
                    'show_in_menu' => true,
                    'show_in_customize_form' => $row['slug'] !== 'tour-ket-hop',
                ],
            );

            $this->countryIds[$row['slug']] = $country->id;

            $labels = ProjectSeed::get('country_translations', [])[$row['slug']] ?? null;

            if ($this->viId) {
                CountryTranslation::query()->updateOrCreate(
                    ['country_id' => $country->id, 'language_id' => $this->viId],
                    [
                        'name' => $labels['vi'] ?? $row['name'],
                        'slug' => $row['slug'],
                        'tagline' => $labels['tagline']['vi'] ?? $row['tagline'],
                    ],
                );
            }

            if ($this->enId && $labels) {
                CountryTranslation::query()->updateOrCreate(
                    ['country_id' => $country->id, 'language_id' => $this->enId],
                    [
                        'name' => $labels['en'],
                        'slug' => $row['slug'],
                        'tagline' => $labels['tagline']['en'],
                    ],
                );
            }
        }
    }

    protected function ensureCountrySeo(): void
    {
        foreach ($this->countryIds as $slug => $countryId) {
            $country = Country::query()->with('translations')->find($countryId);
            if (! $country) {
                continue;
            }

            foreach (['vi', 'en'] as $locale) {
                $translation = $country->translation($locale);
                if (! $translation) {
                    continue;
                }

                // Điểm đến / khu vực = root SEO — không gắn tours_hub.
                $this->seo->ensureSeoFor($country, 'country', $locale, [
                    'slug' => $slug,
                    'title' => $translation->name,
                    'seo_title' => $translation->name,
                    'description' => $translation->tagline,
                    'seo_description' => $translation->tagline,
                    'status' => 'published',
                    'country_code' => $country->code,
                    'parent_id' => null,
                    'reclaim_slug_full' => true,
                ]);
            }
        }
    }

    protected function seedTourCategories(): void
    {
        foreach (ProjectSeed::get('tour_categories', []) as $row) {
            $zoneKey = $row['countrySlug'] ?? $row['zoneSlug'] ?? '';
            $countryId = $this->countryIds[$zoneKey] ?? null;
            if (! $countryId) {
                continue;
            }

            $slug = $row['slug'];
            $compositeKey = $zoneKey.'|'.$slug;

            // Unique within zone: same slug under different zones must not collide.
            $category = TourCategoryTranslation::query()
                ->where('language_id', $this->viId)
                ->where('slug', $slug)
                ->whereHas('tourCategory', fn ($q) => $q->where('country_id', $countryId))
                ->first()
                ?->tourCategory;

            if (! $category) {
                $category = new TourCategory;
            }

            $category->fill([
                'country_id' => $countryId,
                'type' => $row['type'],
                'sort' => $row['sort'],
                'is_active' => true,
            ]);
            $category->save();

            $this->categoryIds[$compositeKey] = $category->id;
            // Backward-compat: last write wins for bare slug lookups
            $this->categoryIds[$slug] = $category->id;

            foreach (['vi', 'en'] as $locale) {
                $languageId = $locale === 'vi' ? $this->viId : $this->enId;
                if (! $languageId) {
                    continue;
                }

                TourCategoryTranslation::query()->updateOrCreate(
                    [
                        'tour_category_id' => $category->id,
                        'language_id' => $languageId,
                    ],
                    [
                        'name' => $row['name'][$locale] ?? $row['name']['vi'],
                        'slug' => $row['slug'],
                        'description' => $this->i18nField($row, ['subtitle', 'description'], $locale),
                        'seo_intro' => $this->i18nField($row, ['seo_body', 'seoBody', 'seoIntro'], $locale),
                    ],
                );
            }

            $country = Country::query()->with('seoEntry')->find($countryId);
            $parentId = $country?->seoEntry?->id;

            foreach (['vi', 'en'] as $locale) {
                $name = $row['name'][$locale] ?? $row['name']['vi'];
                $subtitle = $this->i18nField($row, ['subtitle', 'description'], $locale);

                $this->seo->syncSeo($category, $locale, [
                    'slug' => $row['slug'],
                    'title' => $name,
                    'seo_title' => $name,
                    'description' => $subtitle,
                    'seo_description' => $subtitle,
                    'status' => 'published',
                    'parent_id' => $parentId,
                    'country_code' => $country?->code,
                ], 'tour_category');
            }

            $category->faqs()->delete();
            $this->syncFaqs($category, $row['faqs'] ?? []);
        }
    }

    protected function linkPackagesToCategories(): void
    {
        foreach (ProjectSeed::get('tour_categories', []) as $row) {
            $zoneKey = $row['countrySlug'] ?? $row['zoneSlug'] ?? '';
            $compositeKey = $zoneKey.'|'.($row['slug'] ?? '');
            $categoryId = $this->categoryIds[$compositeKey] ?? $this->categoryIds[$row['slug'] ?? ''] ?? null;
            if (! $categoryId) {
                continue;
            }

            $category = TourCategory::query()->find($categoryId);
            if (! $category) {
                continue;
            }

            $packageIds = collect();

            if (! empty($row['packageSlugs'])) {
                $packageIds = $packageIds->merge(
                    $this->packageIdsBySeoSlugs($row['packageSlugs']),
                );
            }

            $type = $row['type'] ?? '';

            // region (danh mục GEO/combo): nếu không khai báo packageSlugs → lấy mọi tour thuộc zone đó
            if ($type === TourCategory::TYPE_REGION && empty($row['packageSlugs'])) {
                $countryId = $this->countryIds[$zoneKey] ?? null;
                if ($countryId) {
                    $packageIds = $packageIds->merge(
                        Package::query()
                            ->published()
                            ->tours()
                            ->where('country_id', $countryId)
                            ->pluck('id'),
                    );
                }
            }

            // theme thời lượng: minDays/maxDays khớp project-wide (không khóa theo hub zone)
            // để tour gắn zone huyện vẫn vào đúng chủ đề thời lượng.
            if ($type === TourCategory::TYPE_THEME && isset($row['minDays'])) {
                $min = (int) $row['minDays'];
                $max = array_key_exists('maxDays', $row) && $row['maxDays'] !== null
                    ? (int) $row['maxDays']
                    : 999;
                $packageIds = $packageIds->merge(
                    Package::query()
                        ->published()
                        ->tours()
                        ->whereBetween('duration_days', [$min, $max])
                        ->pluck('id'),
                );
            }

            // duration legacy (vitravel): giữ filter theo country của category
            if ($type === TourCategory::TYPE_DURATION && isset($row['minDays'], $row['maxDays'])) {
                $countryId = $this->countryIds[$zoneKey] ?? null;
                if ($countryId) {
                    $packageIds = $packageIds->merge(
                        Package::query()
                            ->published()
                            ->tours()
                            ->where('country_id', $countryId)
                            ->whereBetween('duration_days', [$row['minDays'], $row['maxDays']])
                            ->pluck('id'),
                    );
                }
            }

            $category->packages()->sync($packageIds->unique()->values()->all());
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys  canonical first, then legacy aliases
     */
    protected function i18nField(array $row, array $keys, string $locale): ?string
    {
        foreach ($keys as $key) {
            $value = $row[$key] ?? null;
            if (! is_array($value)) {
                continue;
            }
            $text = $value[$locale] ?? ($value['vi'] ?? null);
            if (is_string($text) && $text !== '') {
                return $text;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $slugs
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function packageIdsBySeoSlugs(array $slugs)
    {
        return Package::query()
            ->whereHas('seoEntry.translations', fn ($q) => $q->whereIn('slug', $slugs))
            ->pluck('id');
    }

    /**
     * @param  array<int, array{q: string, a: string}>  $faqs
     */
    protected function syncFaqs(TourCategory $category, array $faqs): void
    {
        foreach ($faqs as $sort => $faqRow) {
            $faq = Faq::query()->create([
                'faqable_type' => $category->getMorphClass(),
                'faqable_id' => $category->id,
                'sort' => $sort,
                'is_active' => true,
            ]);

            if ($this->viId) {
                FaqTranslation::query()->create([
                    'faq_id' => $faq->id,
                    'language_id' => $this->viId,
                    'question' => $faqRow['q'],
                    'answer' => $faqRow['a'],
                ]);
            }
        }
    }
}
