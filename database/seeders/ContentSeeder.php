<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileTranslation;
use App\Models\CompanyValue;
use App\Models\CompanyValueTranslation;
use App\Models\ContentTypeTag;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\CruiseType;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\KeywordTag;
use App\Models\KeywordTagTranslation;
use App\Models\Language;
use App\Models\Office;
use App\Models\OfficeTranslation;
use App\Models\Package;
use App\Models\PackageCabinType;
use App\Models\PackageCabinTypeTranslation;
use App\Models\PackageItineraryDay;
use App\Models\PackageItineraryDayTranslation;
use App\Models\PackageTranslation;
use App\Models\ReasonToChooseUs;
use App\Models\ReasonToChooseUsTranslation;
use App\Models\ReferencePerson;
use App\Models\Review;
use App\Models\StaticPage;
use App\Models\StaticPageTranslation;
use App\Models\TeamMember;
use App\Models\TeamMemberTranslation;
use App\Models\TravelStyle;
use App\Services\SeoService;
use App\Support\ProjectSeed;
use App\Support\SampleData;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    protected SeoService $seo;

    protected ?int $viId;

    protected ?int $enId;

    /** @var array<string, int> */
    protected array $countryIds = [];

    /** @var array<string, int> */
    protected array $blogCategoryIds = [];

    /** @var array<string, string> label (trong bài viết) => content_type_tags.code */
    protected array $contentTagMap = [];

    public function run(): void
    {
        $this->seo = app(SeoService::class);
        $this->viId = Language::idByCode('vi');
        $this->enId = Language::idByCode('en');
        $map = ProjectSeed::get('content_tag_map', []);
        $this->contentTagMap = is_array($map) ? $map : [];

        $this->seedCountries();
        $this->seedBlogCategories();
        $this->seedKeywordTags();
        $this->seedPackages(SampleData::tours(), Package::TYPE_TOUR);
        $this->seedPackages(SampleData::cruises(), Package::TYPE_CRUISE);
        $this->seedArticles();
        $this->seedTeam();
        $this->seedOffices();
        $this->seedReviews();
        $this->seedBrandContent();
        $this->seedListingFaqs();
    }

    protected function seedCountries(): void
    {
        $codes = ProjectSeed::countryCodes();
        $translations = ProjectSeed::get('country_translations', []);

        foreach (ProjectSeed::get('countries', []) as $sort => $row) {
            $country = Country::query()->updateOrCreate(
                ['code' => $codes[$row['slug']] ?? strtoupper(substr($row['slug'], 0, 2))],
                [
                    'home_grid_size' => $row['size'],
                    'sort' => $sort,
                    'is_active' => true,
                    'show_in_menu' => true,
                    'show_in_customize_form' => $row['slug'] !== 'tour-ket-hop',
                ]
            );

            $i18n = is_array($translations[$row['slug']] ?? null) ? $translations[$row['slug']] : [];
            $viName = $i18n['vi'] ?? $row['name'];
            $viTagline = is_array($i18n['tagline'] ?? null)
                ? ($i18n['tagline']['vi'] ?? $row['tagline'])
                : $row['tagline'];

            if ($this->viId) {
                CountryTranslation::query()->updateOrCreate(
                    ['country_id' => $country->id, 'language_id' => $this->viId],
                    [
                        'name' => $viName,
                        'slug' => $row['slug'],
                        'tagline' => $viTagline,
                    ]
                );
            }

            $this->countryIds[$row['slug']] = $country->id;

            // Điểm đến / khu vực = trang SEO root — không trang cha (không gắn tours_hub).
            $this->seo->syncSeo($country, 'vi', [
                'slug' => $row['slug'],
                'title' => $viName,
                'description' => $viTagline,
                'status' => 'published',
                'parent_id' => null,
                'country_code' => $country->code,
                'reclaim_slug_full' => true,
            ]);

            if ($this->enId) {
                $enName = $i18n['en'] ?? $viName;
                $enTagline = is_array($i18n['tagline'] ?? null)
                    ? ($i18n['tagline']['en'] ?? $viTagline)
                    : $viTagline;

                CountryTranslation::query()->updateOrCreate(
                    ['country_id' => $country->id, 'language_id' => $this->enId],
                    [
                        'name' => $enName,
                        'slug' => $row['slug'],
                        'tagline' => $enTagline,
                    ]
                );

                $this->seo->syncSeo($country, 'en', [
                    'slug' => $row['slug'],
                    'title' => $enName,
                    'description' => $enTagline,
                    'status' => 'published',
                    'parent_id' => null,
                    'country_code' => $country->code,
                    'reclaim_slug_full' => true,
                ]);
            }
        }
    }

    protected function seedBlogCategories(): void
    {
        $guideHub = $this->seo->ensureGuideHub('vi');

        foreach (ProjectSeed::get('blog_categories', []) as $sort => $row) {
            $countryId = $this->countryIds[$row['countrySlug'] ?? $row['zoneSlug'] ?? ''] ?? null;
            if (! $countryId) {
                continue;
            }

            $category = BlogCategory::query()->updateOrCreate(
                [
                    'country_id' => $countryId,
                    'level' => 'destination',
                    'sort' => $sort,
                ],
                ['is_active' => true]
            );

            if ($this->viId) {
                BlogCategoryTranslation::query()->updateOrCreate(
                    ['blog_category_id' => $category->id, 'language_id' => $this->viId],
                    [
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                    ]
                );
            }

            $this->blogCategoryIds[$row['slug']] = $category->id;

            $this->seo->syncSeo($category, 'vi', [
                'slug' => $row['slug'],
                'title' => $row['name'],
                'status' => 'published',
                'parent_id' => $guideHub->id,
                'reclaim_slug_full' => true,
            ]);
        }
    }

    protected function seedKeywordTags(): void
    {
        foreach (ProjectSeed::get('popular_keywords', []) as $weight => $label) {
            $slug = \Illuminate\Support\Str::slug($label);

            $translation = KeywordTagTranslation::query()
                ->where('language_id', $this->viId)
                ->where('slug', $slug)
                ->first();

            $tag = $translation
                ? $translation->keywordTag
                : KeywordTag::query()->create([
                    'target_url' => '/cam-nang-du-lich',
                    'weight' => 100 - $weight,
                    'is_active' => true,
                ]);

            if ($this->viId) {
                KeywordTagTranslation::query()->updateOrCreate(
                    ['keyword_tag_id' => $tag->id, 'language_id' => $this->viId],
                    ['label' => $label, 'slug' => $slug]
                );
            }

            $tag->update(['weight' => 100 - $weight, 'is_active' => true]);
        }
    }

    protected function seedPackages(array $items, string $type): void
    {
        foreach ($items as $sort => $row) {
            $countrySlug = $row['countrySlug'] ?? $row['zoneSlug'] ?? null;
            $countryId = $countrySlug ? ($this->countryIds[$countrySlug] ?? null) : null;
            if (! $countryId) {
                $countryId = $this->countryIds === [] ? null : reset($this->countryIds);
            }
            if (! $countryId) {
                continue;
            }
            $countrySlug = $countrySlug ?: (string) (array_search($countryId, $this->countryIds, true) ?: '');
            $isCruise = $type === Package::TYPE_CRUISE;

            preg_match('/(\d+)\s*ngày\s*(\d+)\s*đêm/u', $row['duration'] ?? '', $matches);
            $days = $row['days'] ?? (int) ($matches[1] ?? 1);
            $nights = isset($matches[2]) ? (int) $matches[2] : max(0, $days - 1);

            $code = $row['tourCode'] ?? ($row['slug'] ?? "PKG-{$type}-{$sort}");

            $package = Package::query()->updateOrCreate(
                ['code' => $code],
                [
                    'type' => $type,
                    'country_id' => $countryId,
                    'duration_days' => $days,
                    'duration_nights' => $nights,
                    'price_from' => $row['priceFrom'] ?? ($days * 2_800_000),
                    'currency' => $row['currency'] ?? 'VND',
                    'rating' => $row['rating'],
                    'review_count' => $row['reviewCount'],
                    'is_featured' => $row['featured'] ?? false,
                    'discount_badge' => $row['badge'],
                    'status' => 'published',
                    'published_at' => now(),
                    'sort' => $sort,
                    'cruise_type' => $isCruise ? ($row['typeSlug'] ?? null) : null,
                    'departure_port' => $isCruise ? ($row['departurePort'] ?? null) : null,
                    'boat_class' => $isCruise ? ($row['boatClass'] ?? null) : null,
                    'nights_on_board' => $isCruise ? ($row['nightsOnBoard'] ?? null) : null,
                ]
            );

            if ($this->viId) {
                PackageTranslation::query()->updateOrCreate(
                    ['package_id' => $package->id, 'language_id' => $this->viId],
                    [
                        'title' => $row['title'],
                        'start_location' => $row['start'],
                        'end_location' => $row['end'],
                        'places_to_visit' => $row['places'],
                        'featured_quote_text' => $row['quote']['text'] ?? null,
                        'featured_quote_author' => $row['quote']['author'] ?? null,
                        'highlights_intro' => $row['highlightsIntro'] ?? null,
                        'highlight_bullets' => $row['highlights'] ?? [],
                        'inclusions' => $row['inclusions'] ?? [],
                        'exclusions' => $row['exclusions'] ?? [],
                        'notes' => $row['notes'] ?? [],
                    ]
                );
            }

            $en = $row['en'] ?? null;
            if ($this->enId && is_array($en)) {
                PackageTranslation::query()->updateOrCreate(
                    ['package_id' => $package->id, 'language_id' => $this->enId],
                    [
                        'title' => $en['title'] ?? $row['title'],
                        'start_location' => $en['start'] ?? $row['start'],
                        'end_location' => $en['end'] ?? $row['end'],
                        'places_to_visit' => $en['places'] ?? $row['places'],
                        'featured_quote_text' => $en['quote']['text'] ?? ($row['quote']['text'] ?? null),
                        'featured_quote_author' => $en['quote']['author'] ?? ($row['quote']['author'] ?? null),
                        'highlights_intro' => $en['highlightsIntro'] ?? ($row['highlightsIntro'] ?? null),
                        'highlight_bullets' => $en['highlights'] ?? ($row['highlights'] ?? []),
                        'inclusions' => $en['inclusions'] ?? ($row['inclusions'] ?? []),
                        'exclusions' => $en['exclusions'] ?? ($row['exclusions'] ?? []),
                        'notes' => $en['notes'] ?? ($row['notes'] ?? []),
                    ]
                );
            }

            $styleIds = TravelStyle::query()
                ->whereIn('code', $row['styles'] ?? [])
                ->pluck('id');
            $package->travelStyles()->sync($styleIds);

            $countrySlugList = $row['countrySlugs'] ?? [$countrySlug];
            $syncCountries = [];
            foreach (array_values(array_unique($countrySlugList)) as $sort => $slug) {
                $cid = $this->countryIds[$slug] ?? null;
                if ($cid) {
                    $syncCountries[$cid] = ['sort' => $sort];
                }
            }
            if ($syncCountries === [] && $countryId) {
                $syncCountries[$countryId] = ['sort' => 0];
            }
            $package->countries()->sync($syncCountries);

            $package->itineraryDays()->delete();
            foreach ($row['itinerary'] ?? [] as $dayIndex => $dayRow) {
                $day = PackageItineraryDay::query()->create([
                    'package_id' => $package->id,
                    'day_number' => $dayRow['day'],
                    'meals_included' => $dayRow['meals'] ?? null,
                    'transport_icons' => $dayRow['transport'] ?? [],
                    'sort' => $dayRow['day'],
                ]);

                if ($this->viId) {
                    PackageItineraryDayTranslation::query()->create([
                        'package_itinerary_day_id' => $day->id,
                        'language_id' => $this->viId,
                        'title' => $dayRow['title'],
                        'content' => $dayRow['content'] ?? '',
                        'overnight_at' => $dayRow['overnight'] ?? null,
                    ]);
                }

                $enDay = is_array($en) ? ($en['itinerary'][$dayIndex] ?? null) : null;
                if ($this->enId && is_array($enDay)) {
                    PackageItineraryDayTranslation::query()->create([
                        'package_itinerary_day_id' => $day->id,
                        'language_id' => $this->enId,
                        'title' => $enDay['title'] ?? $dayRow['title'],
                        'content' => $enDay['content'] ?? ($dayRow['content'] ?? ''),
                        'overnight_at' => $enDay['overnight'] ?? ($dayRow['overnight'] ?? null),
                    ]);
                }
            }

            if ($isCruise && ! empty($row['cabinTypes'])) {
                $package->cabinTypes()->delete();
                foreach ($row['cabinTypes'] as $cabinSort => $cabin) {
                    $cabinModel = PackageCabinType::query()->create([
                        'package_id' => $package->id,
                        'capacity' => $cabin['capacity'] ?? 2,
                        'sort' => $cabinSort,
                    ]);

                    if ($this->viId) {
                        PackageCabinTypeTranslation::query()->create([
                            'package_cabin_type_id' => $cabinModel->id,
                            'language_id' => $this->viId,
                            'name' => $cabin['name'],
                            'description' => $cabin['note'] ?? null,
                        ]);
                    }
                }
            }

            $package->faqs()->delete();
            $this->syncFaqs($package, $row['faqs'] ?? [], is_array($en) ? ($en['faqs'] ?? []) : []);

            $seoType = $isCruise ? 'package_cruise' : 'package_tour';
            $parentId = $this->resolvePackageSeoParentId($package, $isCruise, $row);

            $this->seo->syncSeo($package, 'vi', [
                'slug' => $row['slug'],
                'title' => $row['title'],
                'description' => $row['highlightsIntro'] ?? $row['title'],
                'rating_aggregate_star' => $row['rating'],
                'rating_aggregate_count' => $row['reviewCount'],
                'status' => 'published',
                'parent_id' => $parentId,
                'reclaim_slug_full' => true,
            ], $seoType);

            if (is_array($en)) {
                $this->seo->syncSeo($package, 'en', [
                    'slug' => $row['slug'],
                    'title' => $en['title'] ?? $row['title'],
                    'description' => $en['highlightsIntro'] ?? ($row['highlightsIntro'] ?? $row['title']),
                    'rating_aggregate_star' => $row['rating'],
                    'rating_aggregate_count' => $row['reviewCount'],
                    'status' => 'published',
                    'parent_id' => $parentId,
                    'reclaim_slug_full' => true,
                ], $seoType);
            }
        }
    }

    /**
     * Parent SEO: tour → country dưới tours hub; cruise → cruise_type dưới cruises hub.
     *
     * @param  array<string, mixed>  $row
     */
    protected function resolvePackageSeoParentId(Package $package, bool $isCruise, array $row): ?int
    {
        if ($isCruise) {
            $typeSlug = $row['typeSlug'] ?? $package->cruise_type;
            if (! filled($typeSlug)) {
                return null;
            }

            $cruiseType = CruiseType::query()->where('slug', $typeSlug)->first();
            if (! $cruiseType) {
                return null;
            }

            $hub = $this->seo->ensureCruisesHub('vi');
            $seo = $this->seo->ensureSeoFor($cruiseType, 'cruise_type', 'vi', [
                'slug' => $cruiseType->slug,
                'title' => $cruiseType->name,
                'seo_title' => $cruiseType->name,
                'status' => 'published',
                'parent_id' => $hub->id,
                'reclaim_slug_full' => true,
            ]);

            if ($this->enId) {
                $hubEn = $this->seo->ensureCruisesHub('en');
                $this->seo->ensureSeoFor($cruiseType, 'cruise_type', 'en', [
                    'slug' => $cruiseType->slug,
                    'title' => $cruiseType->name,
                    'seo_title' => $cruiseType->name,
                    'status' => 'published',
                    'parent_id' => $hubEn->id,
                    'reclaim_slug_full' => true,
                ]);
            }

            return $seo->id;
        }

        $country = $package->country ?? Country::query()->find($package->country_id);
        if (! $country) {
            return null;
        }

        $hub = $this->seo->ensureToursHub('vi');
        $countrySlug = $country->translation('vi')?->slug
            ?? $country->translation()?->slug
            ?? ($row['countrySlug'] ?? $row['zoneSlug'] ?? null);
        if (! filled($countrySlug)) {
            return $country->seoEntry()->withoutGlobalScope('project')->first()?->id;
        }

        $seo = $this->seo->ensureSeoFor($country, 'country', 'vi', [
            'slug' => $countrySlug,
            'title' => $country->translation('vi')?->name ?? $countrySlug,
            'seo_title' => $country->translation('vi')?->name ?? $countrySlug,
            'status' => 'published',
            'parent_id' => $hub->id,
            'country_code' => $country->code,
            'reclaim_slug_full' => true,
        ]);

        if ($this->enId) {
            $hubEn = $this->seo->ensureToursHub('en');
            $enName = $country->translation('en')?->name
                ?? $country->translation('vi')?->name
                ?? $countrySlug;
            $this->seo->ensureSeoFor($country, 'country', 'en', [
                'slug' => $countrySlug,
                'title' => $enName,
                'seo_title' => $enName,
                'status' => 'published',
                'parent_id' => $hubEn->id,
                'country_code' => $country->code,
                'reclaim_slug_full' => true,
            ]);
        }

        return $seo->id;
    }

    protected function seedArticles(): void
    {
        foreach (ProjectSeed::get('articles', []) as $row) {
            $countryId = $this->countryIds[$row['countrySlug'] ?? $row['zoneSlug'] ?? ''] ?? null;
            $categoryId = $this->blogCategoryIds[$row['categorySlug'] ?? ''] ?? null;

            $publishedAt = \DateTime::createFromFormat('d/m/Y', $row['publishedAt']) ?: now();

            $article = Article::query()->updateOrCreate(
                [
                    'author_name' => $row['author'],
                    'published_at' => $publishedAt,
                ],
                [
                    'country_id' => $countryId,
                    'blog_category_id' => $categoryId,
                    'rating' => $row['rating'],
                    'rating_count' => $row['ratingCount'],
                    'view_count' => $row['views'],
                    'status' => 'published',
                ]
            );

            if ($this->viId) {
                ArticleTranslation::query()->updateOrCreate(
                    ['article_id' => $article->id, 'language_id' => $this->viId],
                    [
                        'title' => $row['title'],
                        'excerpt' => $row['excerpt'],
                        'content' => $this->serializeContent($row['content'] ?? ''),
                    ]
                );
            }

            $tagIds = collect($row['tags'] ?? [])
                ->map(fn (string $label) => $this->contentTagMap[$label] ?? null)
                ->filter()
                ->map(fn (string $code) => ContentTypeTag::query()->where('code', $code)->value('id'))
                ->filter()
                ->values()
                ->all();
            $article->contentTypeTags()->sync($tagIds);

            $article->faqs()->delete();
            $this->syncFaqs($article, $row['faqs'] ?? []);

            $this->seo->syncSeo($article, 'vi', [
                'slug' => $row['slug'],
                'title' => $row['title'],
                'description' => $row['excerpt'],
                'rating_aggregate_star' => $row['rating'],
                'rating_aggregate_count' => $row['ratingCount'],
                'status' => 'published',
                'parent_id' => isset($this->blogCategoryIds[$row['categorySlug'] ?? ''])
                    ? (BlogCategory::query()->find($this->blogCategoryIds[$row['categorySlug']])?->seoEntry?->id)
                    : null,
                'reclaim_slug_full' => true,
            ], 'article');
        }
    }

    protected function seedTeam(): void
    {
        $hub = $this->seo->ensureHub('team_hub', 'vi');
        if ($this->enId) {
            $this->seo->ensureHub('team_hub', 'en');
        }

        foreach (ProjectSeed::get('team', []) as $sort => $row) {
            $member = TeamMember::query()->updateOrCreate(
                ['sort' => $sort],
                [
                    'is_active' => true,
                    'show_on_home' => true,
                    'is_verified' => (bool) ($row['is_verified'] ?? true),
                    'phone' => $row['phone'] ?? null,
                    'email' => $row['email'] ?? null,
                    'area' => $row['area'] ?? null,
                    'years_experience' => $row['years_experience'] ?? null,
                    'languages' => $row['languages'] ?? null,
                    'stat_clients' => $row['stat_clients'] ?? 0,
                    'stat_tours' => $row['stat_tours'] ?? 0,
                    'stat_awards' => $row['stat_awards'] ?? 0,
                ]
            );

            if ($this->viId) {
                TeamMemberTranslation::query()->updateOrCreate(
                    ['team_member_id' => $member->id, 'language_id' => $this->viId],
                    [
                        'name' => $row['name'],
                        'role' => $row['role'],
                        'short_bio' => $row['bio'],
                        'bio_html' => $row['bio_html'] ?? null,
                    ]
                );
            }

            if ($this->enId) {
                TeamMemberTranslation::query()->updateOrCreate(
                    ['team_member_id' => $member->id, 'language_id' => $this->enId],
                    [
                        'name' => $row['name_en'] ?? $row['name'],
                        'role' => $row['role_en'] ?? $row['role'],
                        'short_bio' => $row['short_bio_en'] ?? $row['bio'],
                        'bio_html' => $row['bio_html_en'] ?? ($row['bio_html'] ?? null),
                    ]
                );
            }

            $member->achievements()->delete();
            foreach (array_values($row['achievements'] ?? []) as $i => $content) {
                $member->achievements()->create([
                    'content' => $content,
                    'ordering' => $i,
                ]);
            }

            $member->skills()->delete();
            foreach (array_values($row['skills'] ?? []) as $i => $skill) {
                $member->skills()->create([
                    'skill' => $skill['skill'],
                    'percent' => (int) ($skill['percent'] ?? 0),
                    'ordering' => $i,
                ]);
            }

            $member->experiences->each(function ($exp) {
                $exp->items()->delete();
                $exp->delete();
            });
            foreach (array_values($row['experiences'] ?? []) as $i => $expRow) {
                $exp = $member->experiences()->create([
                    'title' => $expRow['title'],
                    'company' => $expRow['company'] ?? null,
                    'ordering' => $i,
                ]);
                foreach ($expRow['items'] ?? [] as $line) {
                    $exp->items()->create(['content' => $line]);
                }
            }

            $member->degrees->each(function ($degree) {
                $degree->items()->delete();
                $degree->delete();
            });
            foreach (array_values($row['degrees'] ?? []) as $i => $degRow) {
                $degree = $member->degrees()->create([
                    'title' => $degRow['title'],
                    'school' => $degRow['school'] ?? null,
                    'ordering' => $i,
                ]);
                foreach ($degRow['items'] ?? [] as $line) {
                    $degree->items()->create(['content' => $line]);
                }
            }

            $slug = $row['slug'] ?? \Illuminate\Support\Str::slug($row['name']);

            $this->seo->ensureSeoFor($member, 'team_member', 'vi', [
                'slug' => $slug,
                'title' => $row['name'],
                'seo_title' => $row['name'].' — Đội ngũ ViTravel',
                'seo_description' => \Illuminate\Support\Str::limit(strip_tags((string) ($row['bio'] ?? '')), 160),
                'status' => 'published',
                'parent_id' => $hub->id,
                'rating_aggregate_star' => 5,
                'rating_aggregate_count' => 12 + $sort * 3,
            ]);

            if ($this->enId) {
                $hubEn = $this->seo->ensureHub('team_hub', 'en');
                $this->seo->ensureSeoFor($member, 'team_member', 'en', [
                    'slug' => $slug,
                    'title' => $row['name_en'] ?? $row['name'],
                    'seo_title' => ($row['name_en'] ?? $row['name']).' — ViTravel Team',
                    'seo_description' => \Illuminate\Support\Str::limit(strip_tags((string) ($row['short_bio_en'] ?? $row['bio'] ?? '')), 160),
                    'status' => 'published',
                    'parent_id' => $hubEn->id,
                    'rating_aggregate_star' => 5,
                    'rating_aggregate_count' => 12 + $sort * 3,
                ]);
            }
        }
    }

    protected function seedOffices(): void
    {
        foreach (ProjectSeed::get('offices', []) as $sort => $row) {
            $office = Office::query()->updateOrCreate(
                ['sort' => $sort],
                [
                    'phone' => $row['phone'],
                    'is_active' => true,
                    'country_id' => str_contains($row['city'], 'Campuchia')
                        ? ($this->countryIds['campuchia'] ?? null)
                        : ($this->countryIds['viet-nam'] ?? null),
                ]
            );

            if ($this->viId) {
                OfficeTranslation::query()->updateOrCreate(
                    ['office_id' => $office->id, 'language_id' => $this->viId],
                    [
                        'city_label' => $row['city'],
                        'address_line' => $row['address'],
                    ]
                );
            }
        }
    }

    protected function seedReviews(): void
    {
        $flags = ['Việt Nam' => 'VN', 'Úc' => 'AU', 'Pháp' => 'FR', 'Ý' => 'IT'];

        foreach (ProjectSeed::get('testimonials', []) as $sort => $row) {
            Review::query()->updateOrCreate(
                [
                    'author_name' => $row['name'],
                    'content' => $row['quote'],
                ],
                [
                    'author_country' => $row['country'],
                    'author_country_code' => $flags[$row['country']] ?? 'VN',
                    'rating' => max(1, min(5, (int) round($row['rating']))),
                    'question_title' => $row['trip'],
                    'photos_count' => (int) ($row['photos'] ?? 0),
                    'reviewed_on' => now()->subDays(10 + $sort * 7)->toDateString(),
                    'is_featured' => true,
                    'show_on_home' => true,
                    'status' => 'published',
                    'sort' => $sort,
                    'reviewable_type' => 'company',
                    'reviewable_id' => null,
                ]
            );
        }
    }

    protected function seedBrandContent(): void
    {
        foreach (ProjectSeed::get('value_definitions', []) as $sort => $row) {
            $value = CompanyValue::query()->updateOrCreate(['sort' => $sort], ['is_active' => true]);
            if ($this->viId) {
                CompanyValueTranslation::query()->updateOrCreate(
                    ['company_value_id' => $value->id, 'language_id' => $this->viId],
                    ['name' => $row['vi']['name'], 'description' => $row['vi']['desc']]
                );
            }
            if ($this->enId) {
                CompanyValueTranslation::query()->updateOrCreate(
                    ['company_value_id' => $value->id, 'language_id' => $this->enId],
                    ['name' => $row['en']['name'], 'description' => $row['en']['desc']]
                );
            }
        }

        foreach (ProjectSeed::get('reason_definitions', []) as $sort => $row) {
            $reason = ReasonToChooseUs::query()->updateOrCreate(['sort' => $sort], ['is_active' => true]);
            if ($this->viId) {
                ReasonToChooseUsTranslation::query()->updateOrCreate(
                    ['reason_to_choose_us_id' => $reason->id, 'language_id' => $this->viId],
                    ['title' => $row['vi']['title'], 'description' => $row['vi']['desc']]
                );
            }
            if ($this->enId) {
                ReasonToChooseUsTranslation::query()->updateOrCreate(
                    ['reason_to_choose_us_id' => $reason->id, 'language_id' => $this->enId],
                    ['title' => $row['en']['title'], 'description' => $row['en']['desc']]
                );
            }
        }

        foreach (ProjectSeed::get('reference_persons', []) as $sort => $row) {
            ReferencePerson::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'skype' => $row['skype'],
                    'sort' => $sort,
                    'is_active' => true,
                ]
            );
        }

        $this->seedCompanyProfileAbout();
    }

    protected function seedCompanyProfileAbout(): void
    {
        $profile = CompanyProfile::query()->first() ?? new CompanyProfile;
        if (! $profile->exists) {
            $profile->save();
        }

        $aboutPages = ProjectSeed::get('about_page', []);
        $aboutVi = $aboutPages['vi'] ?? [];
        $aboutEn = $aboutPages['en'] ?? [];

        $mapAbout = static function (array $about): array {
            return [
                'about_page_title' => $about['page_title'] ?? null,
                'about_page_subtitle' => $about['page_subtitle'] ?? null,
                'about_seo_title' => $about['seo_title'] ?? null,
                'about_seo_description' => $about['seo_description'] ?? null,
                'mission_title' => $about['mission']['title'] ?? null,
                'mission_text' => $about['mission']['text'] ?? null,
                'vision_title' => $about['vision']['title'] ?? null,
                'vision_text' => $about['vision']['text'] ?? null,
                'sales_policy_title' => $about['sales_policy']['title'] ?? null,
                'sales_policy_content' => $about['sales_policy']['content'] ?? null,
                'sales_policy_cta_label' => $about['sales_policy']['cta_label'] ?? null,
                'sales_policy_cta_url' => $about['sales_policy']['cta_url'] ?? null,
                'values_section_title' => $about['values_section']['title'] ?? null,
                'values_hub_label' => $about['values_section']['hub_label'] ?? null,
                'reasons_section_title' => $about['reasons_section']['title'] ?? null,
                'reasons_cta_label' => $about['reasons_section']['cta_label'] ?? null,
                'reasons_cta_url' => $about['reasons_section']['cta_url'] ?? null,
                'reference_section_title' => $about['reference_section']['title'] ?? null,
                'reference_section_subtitle' => $about['reference_section']['subtitle'] ?? null,
            ];
        };

        if ($this->viId) {
            CompanyProfileTranslation::query()->updateOrCreate(
                ['company_profile_id' => $profile->id, 'language_id' => $this->viId],
                $mapAbout($aboutVi)
            );
        }

        if ($this->enId) {
            CompanyProfileTranslation::query()->updateOrCreate(
                ['company_profile_id' => $profile->id, 'language_id' => $this->enId],
                $mapAbout($aboutEn)
            );
        }
    }

    protected function seedListingFaqs(): void
    {
        $page = StaticPage::query()->updateOrCreate(
            ['template' => 'listing_faqs'],
            ['status' => 'published', 'published_at' => now()]
        );

        if ($this->viId) {
            StaticPageTranslation::query()->updateOrCreate(
                ['static_page_id' => $page->id, 'language_id' => $this->viId],
                ['title' => 'Listing FAQs', 'body' => '']
            );
        }

        $page->faqs()->delete();
        $this->syncFaqs($page, ProjectSeed::get('listing_faqs', []));
    }

    /**
     * @param  array<int, array{q: string, a: string}>  $faqs
     * @param  array<int, array{q: string, a: string}>  $faqsEn
     */
    protected function syncFaqs(object $model, array $faqs, array $faqsEn = []): void
    {
        foreach ($faqs as $sort => $faqRow) {
            $faq = Faq::query()->create([
                'faqable_type' => $model->getMorphClass(),
                'faqable_id' => $model->id,
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

            $enFaq = $faqsEn[$sort] ?? null;
            if ($this->enId && is_array($enFaq)) {
                FaqTranslation::query()->create([
                    'faq_id' => $faq->id,
                    'language_id' => $this->enId,
                    'question' => $enFaq['q'] ?? $faqRow['q'],
                    'answer' => $enFaq['a'] ?? $faqRow['a'],
                ]);
            }
        }
    }
    protected function serializeContent(mixed $content): string
    {
        if (is_array($content)) {
            return json_encode($content, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return (string) ($content ?? '');
    }

}
