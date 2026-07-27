<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;
use App\Models\CompanyValue;
use App\Models\CompanyValueTranslation;
use App\Models\ContentTypeTag;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Faq;
use App\Models\FaqTranslation;
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

    /** @var array<string, string> */
    protected array $contentTagMap = [
        'Ăn gì, uống gì?' => 'where-to-eat',
        'Ngủ ở đâu?' => 'where-to-stay',
        'Chơi gì, xem gì?' => 'what-to-do',
        'Mẹo du lịch' => 'travel-tips',
        'Chuyến đi thế nào?' => 'trip-report',
        'Chọn tour nào?' => 'which-tour',
    ];

    public function run(): void
    {
        $this->seo = app(SeoService::class);
        $this->viId = Language::idByCode('vi');
        $this->enId = Language::idByCode('en');

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
        $codes = [
            'viet-nam' => 'VN',
            'campuchia' => 'KH',
            'bali' => 'ID',
            'thai-lan' => 'TH',
            'lao' => 'LA',
            'tour-ket-hop' => 'COMBO',
        ];

        foreach (SampleData::countries() as $sort => $row) {
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

            if ($this->viId) {
                CountryTranslation::query()->updateOrCreate(
                    ['country_id' => $country->id, 'language_id' => $this->viId],
                    [
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'tagline' => $row['tagline'],
                    ]
                );
            }

            $this->countryIds[$row['slug']] = $country->id;

            $this->seo->syncSeo($country, 'vi', [
                'slug' => $row['slug'],
                'slug_full' => $this->seo->buildSlugFull('tours', $row['slug']),
                'title' => $row['name'],
                'description' => $row['tagline'],
                'status' => 'published',
            ]);
        }
    }

    protected function seedBlogCategories(): void
    {
        foreach (SampleData::blogCategories() as $sort => $row) {
            $countryId = $this->countryIds[$row['countrySlug']] ?? null;
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
                'slug_full' => $this->seo->buildSlugFull('cam-nang-du-lich', $row['countrySlug'], $row['slug']),
                'title' => $row['name'],
                'status' => 'published',
            ]);
        }
    }

    protected function seedKeywordTags(): void
    {
        foreach (SampleData::popularKeywords() as $weight => $label) {
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
            $countrySlug = $row['countrySlug'] ?? 'viet-nam';
            $countryId = $this->countryIds[$countrySlug] ?? $this->countryIds['viet-nam'];
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

            $styleIds = TravelStyle::query()
                ->whereIn('code', $row['styles'] ?? [])
                ->pluck('id');
            $package->travelStyles()->sync($styleIds);

            $package->itineraryDays()->delete();
            foreach ($row['itinerary'] ?? [] as $dayRow) {
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
            $this->syncFaqs($package, $row['faqs'] ?? []);

            $prefix = $isCruise ? 'cruises' : 'tours';
            $typeSegment = $isCruise ? ($row['typeSlug'] ?? 'du-thuyen-ha-long') : $row['countrySlug'];

            $this->seo->syncSeo($package, 'vi', [
                'slug' => $row['slug'],
                'slug_full' => $this->seo->buildSlugFull($prefix, $typeSegment, $row['slug']),
                'title' => $row['title'],
                'description' => $row['highlightsIntro'] ?? $row['title'],
                'rating_aggregate_star' => $row['rating'],
                'rating_aggregate_count' => $row['reviewCount'],
                'status' => 'published',
            ]);
        }
    }

    protected function seedArticles(): void
    {
        foreach (SampleData::articles() as $row) {
            $countryId = $this->countryIds[$row['countrySlug']] ?? null;
            $categoryId = $this->blogCategoryIds[$row['categorySlug']] ?? null;

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
                'slug_full' => $this->seo->buildSlugFull('cam-nang-du-lich', $row['countrySlug'], $row['slug']),
                'title' => $row['title'],
                'description' => $row['excerpt'],
                'rating_aggregate_star' => $row['rating'],
                'rating_aggregate_count' => $row['ratingCount'],
                'status' => 'published',
            ]);
        }
    }

    protected function seedTeam(): void
    {
        foreach (SampleData::team() as $sort => $row) {
            $member = TeamMember::query()->updateOrCreate(
                ['sort' => $sort],
                ['is_active' => true, 'show_on_home' => true]
            );

            if ($this->viId) {
                TeamMemberTranslation::query()->updateOrCreate(
                    ['team_member_id' => $member->id, 'language_id' => $this->viId],
                    [
                        'name' => $row['name'],
                        'role' => $row['role'],
                        'short_bio' => $row['bio'],
                    ]
                );
            }
        }
    }

    protected function seedOffices(): void
    {
        foreach (SampleData::offices() as $sort => $row) {
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

        foreach (SampleData::testimonials() as $sort => $row) {
            Review::query()->updateOrCreate(
                [
                    'author_name' => $row['name'],
                    'content' => $row['quote'],
                ],
                [
                    'author_country' => $row['country'],
                    'author_country_code' => $flags[$row['country']] ?? 'VN',
                    'rating' => (int) round($row['rating']),
                    'question_title' => $row['trip'],
                    'is_featured' => true,
                    'show_on_home' => true,
                    'status' => 'published',
                    'sort' => $sort,
                    'reviewable_type' => 'country',
                    'reviewable_id' => $this->countryIds['viet-nam'] ?? null,
                ]
            );
        }
    }

    protected function seedBrandContent(): void
    {
        foreach (SampleData::values() as $sort => $row) {
            $value = CompanyValue::query()->updateOrCreate(['sort' => $sort], ['is_active' => true]);
            if ($this->viId) {
                CompanyValueTranslation::query()->updateOrCreate(
                    ['company_value_id' => $value->id, 'language_id' => $this->viId],
                    ['name' => $row['name'], 'description' => $row['desc']]
                );
            }
        }

        foreach (SampleData::reasons() as $sort => $row) {
            $reason = ReasonToChooseUs::query()->updateOrCreate(['sort' => $sort], ['is_active' => true]);
            if ($this->viId) {
                ReasonToChooseUsTranslation::query()->updateOrCreate(
                    ['reason_to_choose_us_id' => $reason->id, 'language_id' => $this->viId],
                    ['title' => $row['title'], 'description' => $row['desc']]
                );
            }
        }

        foreach (SampleData::referencePersons() as $sort => $row) {
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
        $this->syncFaqs($page, SampleData::listingFaqs());
    }

    /**
     * @param  array<int, array{q: string, a: string}>  $faqs
     */
    protected function syncFaqs(object $model, array $faqs): void
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
