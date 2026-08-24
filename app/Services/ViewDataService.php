<?php

namespace App\Services;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\CompanyProfile;
use App\Models\CompanyValue;
use App\Models\ContentTypeTag;
use App\Models\Country;
use App\Models\CruiseType;
use App\Models\ExperienceAlbum;
use App\Models\ExperienceVideo;
use App\Models\Faq;
use App\Models\HeroPill;
use App\Models\HomeFeaturedCountry;
use App\Models\HomeFeaturedCruise;
use App\Models\HomeFeaturedReview;
use App\Models\HomeFeaturedReviewPlatform;
use App\Models\HomeFeaturedService;
use App\Models\HomeFeaturedTeamMember;
use App\Models\HomeFeaturedTour;
use App\Models\HomeFeaturedVideo;
use App\Models\HomeSection;
use App\Models\KeywordTag;
use App\Models\Language;
use App\Models\Office;
use App\Models\Package;
use App\Models\ReasonToChooseUs;
use App\Models\ReferencePerson;
use App\Models\Review;
use App\Models\ReviewPlatform;
use App\Models\SeoEntryTranslation;
use App\Models\Media;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Models\StayCrawlItem;
use App\Models\StaticPage;
use App\Models\TeamMember;
use App\Models\TourCategory;
use App\Models\TravelStyle;
use App\Models\Usp;
use App\Support\HomeFeaturedSchema;
use App\Support\LocaleContent;
use App\Support\ProjectSeed;
use App\Support\SampleData;
use Illuminate\Database\Eloquent\Model;

class ViewDataService
{
    protected function locale(): string
    {
        return app()->getLocale();
    }

    protected function languageId(): ?int
    {
        return Language::idByCode($this->locale());
    }

    /** @return list<int> */
    protected function languageIdChain(): array
    {
        return Language::contentLanguageIdChain($this->locale());
    }

    public function homeCountries(): array
    {
        $featured = HomeFeaturedCountry::query()
            ->orderBy('sort')
            ->with(['country.translations', 'country.banner', 'country.packages' => fn ($q) => $q->published()->tours()])
            ->get();

        $mapped = $featured->isNotEmpty()
            ? $featured
                ->map(fn (HomeFeaturedCountry $row) => $row->country ? $this->mapCountry($row->country) : null)
                ->filter()
                ->values()
            : collect($this->countries());

        // Hero luôn là quốc gia size=large (Việt Nam) — đưa lên đầu danh sách.
        return $mapped
            ->sortByDesc(fn (array $c) => ($c['size'] ?? '') === 'large')
            ->values()
            ->all();
    }

    public function countries(): array
    {
        if (! Country::query()->active()->exists()) {
            return SampleData::countries();
        }

        return Country::query()
            ->active()
            ->with(['translations', 'banner', 'listingBanner', 'packages' => fn ($q) => $q->published()->tours()])
            ->get()
            ->map(fn (Country $country) => $this->mapCountry($country))
            ->values()
            ->all();
    }

    public function country(string $slug): ?array
    {
        $ids = $this->languageIdChain();
        if ($ids === []) {
            return null;
        }

        $country = Country::query()
            ->active()
            ->with(['translations', 'banner', 'listingBanner'])
            ->whereHas('translations', fn ($q) => $q->whereIn('language_id', $ids)->where('slug', $slug))
            ->first();

        if (! $country) {
            $country = Country::withoutGlobalScope('project')
                ->active()
                ->with(['translations' => fn ($q) => $q->withoutGlobalScope('project'), 'banner', 'listingBanner'])
                ->whereHas('translations', fn ($q) => $q->withoutGlobalScope('project')->whereIn('language_id', $ids)->where('slug', $slug))
                ->first();
        }

        return $country ? $this->mapCountry($country) : SampleData::country($slug);
    }

    public function travelStyles(): array
    {
        if (! TravelStyle::query()->where('is_active', true)->exists()) {
            return SampleData::travelStyles();
        }

        return TravelStyle::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->mapWithKeys(fn (TravelStyle $style) => [$style->code => $style->name])
            ->all();
    }

    public function durationBuckets(): array
    {
        return SampleData::durationBuckets();
    }

    public function homeSections(): array
    {
        if (! HomeSection::query()->active()->exists()) {
            return apply_site_brand_deep(SampleData::homeSections());
        }

        return apply_site_brand_deep(HomeSection::query()
            ->active()
            ->with(['translations', 'image'])
            ->get()
            ->mapWithKeys(fn (HomeSection $section) => [$section->key => $this->mapHomeSection($section)])
            ->all());
    }

    public function homeSection(string $key): array
    {
        $section = HomeSection::query()
            ->with(['translations', 'image'])
            ->where('key', $key)
            ->first();

        if ($section) {
            if (! $section->is_active) {
                return ['key' => $key, 'hidden' => true];
            }

            return apply_site_brand_deep($this->mapHomeSection($section));
        }

        return apply_site_brand_deep(SampleData::homeSection($key));
    }

    public function homeSlides(): array
    {
        if (! \App\Models\HomeSlide::query()->active()->exists()) {
            return SampleData::homeSlides();
        }

        return \App\Models\HomeSlide::query()
            ->active()
            ->with(['translations', 'image', 'imageMobile'])
            ->get()
            ->map(fn (\App\Models\HomeSlide $slide) => $this->mapHomeSlide($slide))
            ->values()
            ->all();
    }

    public function tours(): array
    {
        if (! Package::query()->published()->tours()->exists()) {
            return SampleData::tours();
        }

        return $this->packageQuery(Package::TYPE_TOUR)
            ->get()
            ->map(fn (Package $package) => $this->mapPackage($package))
            ->values()
            ->all();
    }

    public function tour(string $slug): ?array
    {
        if (! Package::query()->published()->tours()->exists()) {
            return SampleData::tour($slug);
        }

        $package = $this->findPackageBySlug($slug, Package::TYPE_TOUR);

        return $package ? $this->mapPackage($package) : null;
    }

    public function featuredTours(int $limit = 3): array
    {
        $packages = HomeFeaturedTour::query()
            ->orderBy('sort')
            ->with([
                'package.translations',
                'package.country.translations',
                'package.countries.translations',
                'package.travelStyles',
                'package.itineraryDays.translations',
                'package.cabinTypes.translations',
                    'package.faqs.translations',
                    'package.mediaAttachments.media',
                    'package.seoEntry.translations',
                ])
            ->get()
            ->map(fn (HomeFeaturedTour $row) => $row->package)
            ->filter(fn (?Package $package) => $package && $package->status === 'published' && $package->type === Package::TYPE_TOUR)
            ->take($limit);

        if ($packages->isNotEmpty()) {
            return $packages
                ->map(fn (Package $package) => $this->mapPackage($package))
                ->values()
                ->all();
        }

        return SampleData::featuredTours($limit);
    }

    public function featuredCruises(int $limit = 3): array
    {
        $packages = HomeFeaturedCruise::query()
            ->orderBy('sort')
            ->with([
                'package.translations',
                'package.country.translations',
                'package.travelStyles',
                'package.itineraryDays.translations',
                'package.cabinTypes.translations',
                'package.faqs.translations',
                'package.mediaAttachments.media',
                'package.seoEntry.translations',
            ])
            ->get()
            ->map(fn (HomeFeaturedCruise $row) => $row->package)
            ->filter(fn (?Package $package) => $package && $package->status === 'published' && $package->type === Package::TYPE_CRUISE)
            ->take($limit);

        if ($packages->isNotEmpty()) {
            return $packages
                ->map(fn (Package $package) => $this->mapPackage($package, true))
                ->values()
                ->all();
        }

        return SampleData::featuredCruises($limit);
    }

    /**
     * Dịch vụ nổi bật theo cụm (home / merchandising).
     * Ưu tiên danh sách curated `home_featured_services`; không có thì is_featured + sort.
     *
     * @return list<array<string, mixed>>
     */
    public function featuredServices(string $cluster, int $limit = 3): array
    {
        $limit = max(1, min(12, $limit));

        if (! config("services_catalog.clusters.{$cluster}")) {
            return [];
        }

        $with = [
            'translations', 'category', 'country.translations',
            'seoEntry.translations', 'faqs.translations',
            'mediaAttachments.media',
            'priceTable.variants.translations',
            'priceTable.periods.rates',
        ];

        if (HomeFeaturedSchema::hasServices()) {
            $curated = HomeFeaturedService::query()
                ->orderBy('sort')
                ->with(['service' => fn ($q) => $q->with($with)])
                ->whereHas(
                    'service',
                    fn ($q) => $q->published()->forCluster($cluster),
                )
                ->get()
                ->map(fn (HomeFeaturedService $row) => $row->service)
                ->filter()
                ->take($limit);

            if ($curated->isNotEmpty()) {
                return $curated
                    ->map(fn (Service $s) => $this->mapService($s))
                    ->values()
                    ->all();
            }
        }

        $query = Service::query()
            ->published()
            ->forCluster($cluster)
            ->with($with);

        if (! Service::query()->published()->forCluster($cluster)->exists()) {
            return SampleData::featuredServices($cluster, $limit);
        }

        $featured = (clone $query)
            ->featured()
            ->orderBy('sort')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($featured->count() < $limit) {
            $exclude = $featured->pluck('id')->all();
            $fill = (clone $query)
                ->when($exclude !== [], fn ($q) => $q->whereNotIn('id', $exclude))
                ->orderBy('sort')
                ->orderByDesc('id')
                ->limit($limit - $featured->count())
                ->get();
            $featured = $featured->concat($fill);
        }

        return $featured
            ->map(fn (Service $s) => $this->mapService($s))
            ->values()
            ->all();
    }

    /**
     * Dịch vụ bổ trợ curated trên trang chủ (stay / experience / other).
     * Rỗng → blade giữ hub links mặc định.
     *
     * @return list<array<string, mixed>>
     */
    public function featuredSupportServices(int $limit = 12): array
    {
        $limit = max(1, min(12, $limit));
        $clusters = HomeFeaturedService::SUPPORT_CLUSTERS;

        $with = [
            'translations', 'category', 'country.translations',
            'seoEntry.translations', 'faqs.translations',
            'mediaAttachments.media',
            'priceTable.variants.translations',
            'priceTable.periods.rates',
        ];

        if (! HomeFeaturedSchema::hasServices()) {
            return [];
        }

        return HomeFeaturedService::query()
            ->orderBy('sort')
            ->with(['service' => fn ($q) => $q->with($with)])
            ->whereHas(
                'service',
                fn ($q) => $q->published()->whereIn('cluster', $clusters),
            )
            ->get()
            ->map(fn (HomeFeaturedService $row) => $row->service)
            ->filter()
            ->take($limit)
            ->map(fn (Service $s) => $this->mapService($s))
            ->values()
            ->all();
    }

    public function toursByCountry(string $countrySlug): array
    {
        if (! Package::query()->published()->tours()->exists()) {
            return SampleData::toursByCountry($countrySlug);
        }

        $country = $this->findCountryBySlug($countrySlug);
        if (! $country) {
            return [];
        }

        return $this->packageQuery(Package::TYPE_TOUR)
            ->where(function ($q) use ($country) {
                $q->where('country_id', $country->id)
                    ->orWhereHas('countries', fn ($c) => $c->where('countries.id', $country->id));
            })
            ->get()
            ->map(fn (Package $package) => $this->mapPackage($package))
            ->values()
            ->all();
    }

    public function tourCategory(string $countrySlug, string $categorySlug): ?array
    {
        $country = $this->findCountryBySlug($countrySlug);
        if (! $country) {
            return null;
        }

        $ids = $this->languageIdChain();
        $category = TourCategory::query()
            ->where('is_active', true)
            ->where('country_id', $country->id)
            ->whereHas('translations', fn ($q) => $q->whereIn('language_id', $ids)->where('slug', $categorySlug))
            ->with(['translations', 'country.translations', 'faqs.translations', 'mediaAttachments.media', 'seoEntry.translations'])
            ->first();

        return $category ? $this->mapTourCategory($category) : null;
    }

    public function toursByCategory(string $countrySlug, string $categorySlug): array
    {
        $category = $this->tourCategory($countrySlug, $categorySlug);
        if (! $category) {
            return [];
        }

        $country = $this->findCountryBySlug($countrySlug);
        if (! $country) {
            return [];
        }

        return $this->packageQuery(Package::TYPE_TOUR)
            ->where(function ($q) use ($country) {
                $q->where('country_id', $country->id)
                    ->orWhereHas('countries', fn ($c) => $c->where('countries.id', $country->id));
            })
            ->whereHas('categories', function ($q) use ($category) {
                $q->where('tour_categories.id', $category['id']);
            })
            ->get()
            ->map(fn (Package $package) => $this->mapPackage($package))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapTourCategory(TourCategory $category): array
    {
        $translation = $category->translation($this->locale());
        $country = $category->country;
        $countryTrans = $country?->translation($this->locale());
        $seoTrans = $category->seoEntry?->translation($this->locale());
        $cover = $category->coverUrl('lg') ?: $category->coverUrl('card');
        $subtitle = trim((string) ($translation?->description ?? ''));
        $seoBody = trim((string) ($translation?->seo_intro ?? ''));

        return [
            'id' => $category->id,
            'kind' => 'tour_category',
            'slug' => $translation?->slug ?? '',
            'name' => $translation?->name ?? '',
            'title' => $translation?->name ?? '',
            'type' => $category->type,
            'subtitle' => $subtitle,
            'description' => $subtitle,
            'seoBody' => $seoBody,
            'seoIntro' => $seoBody,
            'seoTitle' => apply_site_brand((string) ($seoTrans?->seo_title ?? ($translation?->name ?? ''))),
            'seoDescription' => apply_site_brand((string) ($seoTrans?->seo_description ?? $subtitle)),
            'countrySlug' => $countryTrans?->slug ?? '',
            'countryName' => $countryTrans?->name ?? '',
            'banner' => $cover,
            'bannerSrcset' => $category->coverSrcset(),
            'faqs' => $category->faqs->where('is_active', true)->map(fn (Faq $faq) => [
                'q' => apply_site_brand($faq->question),
                'a' => apply_site_brand($faq->answer),
            ])->values()->all(),
        ];
    }

    public function cruiseTypes(): array
    {
        $types = CruiseType::query()->active()->with(['banner', 'cover', 'seoEntry.translations'])->get();

        if ($types->isNotEmpty()) {
            return $types
                ->map(fn (CruiseType $type) => $this->mapCruiseType($type))
                ->values()
                ->all();
        }

        if (! Package::query()->published()->cruises()->exists()) {
            return SampleData::cruiseTypes();
        }

        $labels = [
            'du-thuyen-ha-long' => 'Du thuyền Hạ Long',
            'du-thuyen-mekong' => 'Du thuyền Mekong',
            'du-thuyen-lan-ha' => 'Du thuyền Lan Hạ',
        ];

        return Package::query()
            ->published()
            ->cruises()
            ->select('cruise_type')
            ->distinct()
            ->pluck('cruise_type')
            ->filter()
            ->map(fn (string $type) => [
                'slug' => $type,
                'name' => $labels[$type] ?? ucfirst(str_replace('-', ' ', $type)),
                'count' => Package::query()->published()->cruises()->where('cruise_type', $type)->count(),
                'image' => null,
                'imageHero' => null,
                'imageSrcset' => null,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{slug: string, name: string, count: int, image: ?string, imageHero: ?string, imageSrcset: ?string}
     */
    protected function mapCruiseType(CruiseType $type): array
    {
        $cardImage = $type->coverUrl('card') ?: $type->bannerUrl('card');
        $heroImage = $type->bannerUrl('lg') ?: $type->bannerUrl('full') ?: $cardImage;
        $subtitle = trim((string) ($type->intro ?? ''));
        $seoBody = trim((string) ($type->seo_body ?: $type->intro ?? ''));
        $seoTrans = $type->seoEntry?->translation($this->locale());

        return [
            'slug' => $type->slug,
            'name' => $type->name,
            'title' => $type->name,
            'subtitle' => $subtitle,
            'seoBody' => $seoBody,
            'intro' => $subtitle,
            'seoTitle' => apply_site_brand((string) ($seoTrans?->seo_title ?? $type->name)),
            'seoDescription' => apply_site_brand((string) ($seoTrans?->seo_description ?? $subtitle)),
            'count' => Package::query()->published()->cruises()->where('cruise_type', $type->slug)->count(),
            'image' => $cardImage,
            'imageHero' => $heroImage,
            'banner' => $heroImage,
            'imageSrcset' => $type->coverSrcset() ?: $type->bannerSrcset(),
            'bannerSrcset' => $type->bannerSrcset() ?: $type->coverSrcset(),
        ];
    }

    public function cruises(): array
    {
        if (! Package::query()->published()->cruises()->exists()) {
            return SampleData::cruises();
        }

        return $this->packageQuery(Package::TYPE_CRUISE)
            ->get()
            ->map(fn (Package $package) => $this->mapPackage($package, true))
            ->values()
            ->all();
    }

    public function cruise(string $slug): ?array
    {
        if (! Package::query()->published()->cruises()->exists()) {
            return SampleData::cruise($slug);
        }

        $package = $this->findPackageBySlug($slug, Package::TYPE_CRUISE);

        return $package ? $this->mapPackage($package, true) : null;
    }

    public function articles(): array
    {
        if (! Article::query()->published()->exists()) {
            return SampleData::articles();
        }

        return $this->articleQuery()
            ->get()
            ->map(fn (Article $article) => $this->mapArticle($article))
            ->values()
            ->all();
    }

    public function article(string $slug): ?array
    {
        if (! Article::query()->published()->exists()) {
            return SampleData::article($slug);
        }

        $article = $this->findArticleBySlug($slug);

        return $article ? $this->mapArticle($article) : null;
    }

    public function articlesByCountry(string $countrySlug): array
    {
        if (! Article::query()->published()->exists()) {
            return SampleData::articlesByCountry($countrySlug);
        }

        $country = $this->findCountryBySlug($countrySlug);
        if (! $country) {
            return [];
        }

        return $this->articleQuery()
            ->where('country_id', $country->id)
            ->get()
            ->map(fn (Article $article) => $this->mapArticle($article))
            ->values()
            ->all();
    }

    public function blogCategoryBySlug(string $slug): ?array
    {
        foreach ($this->blogCategories() as $category) {
            if (($category['slug'] ?? '') === $slug) {
                return $category;
            }
        }

        return null;
    }

    public function articlesByCategorySlug(string $categorySlug): array
    {
        if ($categorySlug === '') {
            return [];
        }

        if (! Article::query()->published()->exists()) {
            return array_values(array_filter(
                SampleData::articles(),
                fn ($a) => ($a['categorySlug'] ?? '') === $categorySlug
            ));
        }

        $ids = $this->languageIdChain();
        if ($ids === []) {
            return [];
        }

        $category = BlogCategory::query()
            ->where('is_active', true)
            ->whereHas('translations', fn ($q) => $q->whereIn('language_id', $ids)->where('slug', $categorySlug))
            ->first();

        if (! $category) {
            return [];
        }

        return $this->articleQuery()
            ->where('blog_category_id', $category->id)
            ->get()
            ->map(fn (Article $article) => $this->mapArticle($article))
            ->values()
            ->all();
    }

    public function blogCategories(): array
    {
        if (! BlogCategory::query()->where('is_active', true)->exists()) {
            return SampleData::blogCategories();
        }

        return BlogCategory::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with(['translations', 'country' => fn ($q) => $q->withTrashed()->with('translations'), 'articles'])
            ->get()
            ->map(function (BlogCategory $category) {
                $countryTranslation = $category->country?->translation($this->locale());

                return [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'countrySlug' => $countryTranslation?->slug ?? '',
                    'count' => $category->articles()->published()->count(),
                ];
            })
            ->values()
            ->all();
    }

    public function contentTags(): array
    {
        if (! ContentTypeTag::query()->where('is_active', true)->exists()) {
            return SampleData::contentTags();
        }

        return ContentTypeTag::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->map(fn (ContentTypeTag $tag) => $tag->label)
            ->values()
            ->all();
    }

    public function popularKeywords(): array
    {
        if (! KeywordTag::query()->where('is_active', true)->exists()) {
            return SampleData::popularKeywords();
        }

        return KeywordTag::query()
            ->where('is_active', true)
            ->orderByDesc('weight')
            ->with('translations')
            ->get()
            ->map(fn (KeywordTag $tag) => $tag->label)
            ->values()
            ->all();
    }

    /**
     * Tìm kiếm toàn site (tour, điểm đến, bài viết, du thuyền).
     *
     * @return array{tours: list<array>, destinations: list<array>, articles: list<array>, cruises: list<array>}
     */
    public function search(string $q, int $limit = 24): array
    {
        $needle = mb_strtolower(trim($q));
        if ($needle === '') {
            return ['tours' => [], 'destinations' => [], 'articles' => [], 'cruises' => []];
        }

        $match = function (array $haystacks) use ($needle): bool {
            foreach ($haystacks as $text) {
                if ($text !== '' && mb_stripos((string) $text, $needle) !== false) {
                    return true;
                }
            }

            return false;
        };

        $destinations = array_values(array_filter(
            $this->countries(),
            fn (array $c) => $match([$c['name'] ?? '', $c['tagline'] ?? '', $c['slug'] ?? ''])
        ));

        $tours = array_values(array_filter(
            $this->tours(),
            fn (array $t) => $match([
                $t['title'] ?? '',
                $t['country'] ?? '',
                $t['start'] ?? '',
                $t['end'] ?? '',
                implode(' ', $t['places'] ?? []),
                $t['quote']['text'] ?? '',
            ])
        ));

        $cruises = array_values(array_filter(
            $this->cruises(),
            fn (array $c) => $match([
                $c['title'] ?? '',
                $c['country'] ?? '',
                $c['start'] ?? '',
                $c['end'] ?? '',
            ])
        ));

        $articles = array_values(array_filter(
            $this->articles(),
            fn (array $a) => $match([
                $a['title'] ?? '',
                $a['excerpt'] ?? '',
                $a['country'] ?? '',
                $a['category'] ?? '',
                implode(' ', $a['tags'] ?? []),
            ])
        ));

        return [
            'destinations' => array_slice($destinations, 0, min(8, $limit)),
            'tours' => array_slice($tours, 0, $limit),
            'cruises' => array_slice($cruises, 0, min(12, $limit)),
            'articles' => array_slice($articles, 0, min(12, $limit)),
        ];
    }

    public function testimonials(bool $homeOnly = false): array
    {
        if ($homeOnly && HomeFeaturedSchema::hasReviews()) {
            $curated = HomeFeaturedReview::query()
                ->orderBy('sort')
                ->with(['review.avatar', 'review.mediaAttachments.media', 'review.reviewable'])
                ->whereHas('review', fn ($q) => $q->published())
                ->get()
                ->map(fn (HomeFeaturedReview $row) => $row->review)
                ->filter();

            if ($curated->isNotEmpty()) {
                return $curated
                    ->map(fn (Review $review) => $this->mapReview($review))
                    ->values()
                    ->all();
            }
        }

        if (! Review::query()->published()->exists()) {
            return SampleData::testimonials();
        }

        $query = Review::query()
            ->published()
            ->with(['avatar', 'mediaAttachments.media', 'reviewable'])
            ->orderBy('sort')
            ->orderByDesc('id');

        if ($homeOnly && Review::query()->published()->where('show_on_home', true)->exists()) {
            $query->where('show_on_home', true);
        }

        return $query
            ->get()
            ->map(fn (Review $review) => $this->mapReview($review))
            ->values()
            ->all();
    }

    /**
     * @return array{name: string, country: string, flag: string, rating: float, quote: string, photos: int, photoUrls: list<string>, avatar: ?string, trip: string, reviewedOn: ?string}
     */
    protected function mapReview(Review $review): array
    {
        $avatar = media_payload($review->avatar, 'thumb');
        $photos = $review->galleryPayloads(3, 'thumb');

        return [
            'name' => $review->author_name,
            'country' => $review->author_country ?? '',
            'flag' => $this->countryFlag($review->author_country_code),
            'rating' => (float) $review->rating,
            'quote' => $review->content,
            'photos' => $review->displayPhotosCount(),
            'photoUrls' => array_values(array_filter(array_column($photos, 'src'))),
            'photoSrcsets' => array_values(array_filter(array_column($photos, 'srcset'))),
            'avatar' => $avatar['src'],
            'avatarSrcset' => $avatar['srcset'],
            'trip' => $review->question_title
                ?? (is_object($review->reviewable) && isset($review->reviewable->title) ? (string) $review->reviewable->title : ''),
            'reviewedOn' => optional($review->reviewed_on)?->format('d/m/Y'),
        ];
    }

    public function team(): array
    {
        if (! TeamMember::query()->where('is_active', true)->exists()) {
            return array_map(fn (array $row) => $this->mapSampleTeamCard($row), SampleData::team());
        }

        return TeamMember::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with(['translations', 'avatar', 'seoEntry.translations'])
            ->get()
            ->map(fn (TeamMember $member) => $this->mapTeamCard($member))
            ->values()
            ->all();
    }

    public function teamForHome(): array
    {
        if (HomeFeaturedSchema::hasTeamMembers()) {
            $curated = HomeFeaturedTeamMember::query()
            ->orderBy('sort')
            ->with(['teamMember.translations', 'teamMember.avatar', 'teamMember.seoEntry.translations'])
            ->whereHas('teamMember', fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(fn (HomeFeaturedTeamMember $row) => $row->teamMember)
            ->filter();

        if ($curated->isNotEmpty()) {
            return $curated
                ->map(fn (TeamMember $member) => $this->mapTeamCard($member))
                ->values()
                ->all();
        }
        }

        if (! TeamMember::query()->where('is_active', true)->where('show_on_home', true)->exists()) {
            if (! TeamMember::query()->where('is_active', true)->exists()) {
                return array_map(fn (array $row) => $this->mapSampleTeamCard($row), SampleData::team());
            }
        }

        $hasHome = TeamMember::query()->where('is_active', true)->where('show_on_home', true)->exists();

        return TeamMember::query()
            ->where('is_active', true)
            ->when($hasHome, fn ($q) => $q->where('show_on_home', true))
            ->orderBy('sort')
            ->with(['translations', 'avatar', 'seoEntry.translations'])
            ->get()
            ->map(fn (TeamMember $member) => $this->mapTeamCard($member))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapTeamCard(TeamMember $member): array
    {
        $locale = app()->getLocale();
        $seoTrans = $member->seoEntry?->translation($locale);

        return [
            'id' => $member->id,
            'name' => $member->name,
            'role' => $member->role,
            'bio' => $member->short_bio,
            'image' => $member->avatarUrl('thumb'),
            'imageSrcset' => $member->avatarSrcset(),
            'slug' => $seoTrans?->slug,
            'url' => $seoTrans ? seo_public_url($seoTrans, $locale) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function mapSampleTeamCard(array $row): array
    {
        return [
            'id' => null,
            'name' => $row['name'],
            'role' => $row['role'],
            'bio' => $row['bio'] ?? $row['short_bio'] ?? null,
            'image' => $row['image'] ?? null,
            'imageSrcset' => $row['imageSrcset'] ?? null,
            'slug' => $row['slug'] ?? null,
            'url' => $row['url'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatTeamMember(TeamMember $member): array
    {
        $locale = app()->getLocale();

        $member->loadMissing([
            'translations',
            'avatar',
            'seoEntry.translations',
            'achievements',
            'skills',
            'experiences.items',
            'degrees.items',
            'activityImages.media',
        ]);

        $seoTrans = $member->seoEntry?->translation($locale);
        $rating = (float) ($member->seoEntry?->rating_aggregate_star ?? 5);
        $ratingCount = (int) ($member->seoEntry?->rating_aggregate_count ?? 0);

        $languages = $member->languages;
        if (is_array($languages)) {
            $languagesLabel = implode(', ', array_filter(array_map('strval', $languages)));
        } else {
            $languagesLabel = is_string($languages) ? $languages : '';
        }

        return [
            'id' => $member->id,
            'name' => $member->name,
            'role' => $member->role,
            'short_bio' => $member->short_bio,
            'bio_html' => $member->bio_html,
            'image' => $member->avatarUrl('lg') ?? $member->avatarUrl('thumb'),
            'imageSrcset' => $member->avatarSrcset(),
            'phone' => $member->phone,
            'email' => $member->email,
            'area' => $member->area,
            'years_experience' => $member->years_experience,
            'languages' => $languagesLabel,
            'stat_clients' => (int) $member->stat_clients,
            'stat_tours' => (int) $member->stat_tours,
            'stat_awards' => (int) $member->stat_awards,
            'is_verified' => (bool) $member->is_verified,
            'slug' => $seoTrans?->slug,
            'url' => $seoTrans ? seo_public_url($seoTrans, $locale) : null,
            'rating' => $rating,
            'rating_count' => $ratingCount,
            'achievements' => $member->achievements->map(fn ($a) => [
                'content' => $a->content,
            ])->values()->all(),
            'skills' => $member->skills->map(fn ($s) => [
                'skill' => $s->skill,
                'percent' => (int) $s->percent,
            ])->values()->all(),
            'experiences' => $member->experiences->map(fn ($e) => [
                'title' => $e->title,
                'company' => $e->company,
                'items' => $e->items->pluck('content')->values()->all(),
            ])->values()->all(),
            'degrees' => $member->degrees->map(fn ($d) => [
                'title' => $d->title,
                'school' => $d->school,
                'items' => $d->items->pluck('content')->values()->all(),
            ])->values()->all(),
            'activity_images' => $member->activityImages->map(fn ($img) => [
                'url' => $img->imageUrl('lg') ?? $img->imageUrl(),
                'thumb' => $img->thumbUrl() ?? $img->imageUrl('thumb'),
            ])->filter(fn ($row) => filled($row['url']))->values()->all(),
        ];
    }

    public function offices(): array
    {
        if (! Office::query()->where('is_active', true)->exists()) {
            return SampleData::offices();
        }

        return Office::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->map(fn (Office $office) => [
                'city' => $office->city_label,
                'address' => $office->address_line,
                'phone' => $office->phone ?? '',
            ])
            ->values()
            ->all();
    }

    public function usps(): array
    {
        if (! Usp::query()->where('is_active', true)->exists()) {
            return SampleData::usps();
        }

        return Usp::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->map(fn (Usp $usp) => [
                'icon' => $usp->icon,
                'title' => $usp->title,
                'desc' => $usp->description,
            ])
            ->values()
            ->all();
    }

    public function heroPills(): array
    {
        if (! HeroPill::query()->where('is_active', true)->exists()) {
            return SampleData::heroPills();
        }

        return HeroPill::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with(['translations', 'tourCategory.country.translations', 'tourCategory.translations', 'country.translations'])
            ->get()
            ->map(fn (HeroPill $pill) => [
                'label' => $pill->resolveDefaultLabel($this->locale()),
                'url' => $pill->resolveUrl($this->locale()),
            ])
            ->values()
            ->all();
    }

    public function values(): array
    {
        if (! CompanyValue::query()->where('is_active', true)->exists()) {
            return SampleData::values();
        }

        return CompanyValue::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->map(fn (CompanyValue $value) => [
                'name' => $value->name,
                'desc' => $value->description,
            ])
            ->values()
            ->all();
    }

    public function reasons(): array
    {
        if (! ReasonToChooseUs::query()->where('is_active', true)->exists()) {
            return SampleData::reasons();
        }

        return ReasonToChooseUs::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->map(fn (ReasonToChooseUs $reason) => [
                'title' => $reason->title,
                'desc' => $reason->description,
            ])
            ->values()
            ->all();
    }

    public function referencePersons(): array
    {
        if (! ReferencePerson::query()->where('is_active', true)->exists()) {
            return SampleData::referencePersons();
        }

        return ReferencePerson::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with(['country.translations', 'photo'])
            ->get()
            ->map(fn (ReferencePerson $person) => [
                'name' => $person->name,
                'country' => $person->country?->name ?? '',
                'email' => $person->email ?? '',
                'phone' => $person->phone ?? '',
                'skype' => $person->skype ?? '',
                'image' => $person->photoUrl('card'),
                'imageSrcset' => $person->photoSrcset(),
            ])
            ->values()
            ->all();
    }

    /**
     * Nội dung CMS trang Về chúng tôi (SEO, chrome sections, mission/vision/policy).
     *
     * @return array<string, mixed>
     */
    public function aboutPage(): array
    {
        $fallback = SampleData::aboutPage();
        $profile = CompanyProfile::current();

        if (! $profile) {
            return $fallback;
        }

        $pick = function (?string $value, string $key) use ($fallback): string {
            return filled($value) ? (string) $value : (string) ($fallback[$key] ?? '');
        };

        $nested = function (?string $value, string $section, string $key) use ($fallback): string {
            $fallbackValue = $fallback[$section][$key] ?? '';

            return filled($value) ? (string) $value : (string) $fallbackValue;
        };

        return [
            'seo_title' => $pick($profile->about_seo_title, 'seo_title'),
            'seo_description' => $pick($profile->about_seo_description, 'seo_description'),
            'page_title' => $pick($profile->about_page_title, 'page_title'),
            'page_subtitle' => $pick($profile->about_page_subtitle, 'page_subtitle'),
            'banner' => [
                'src' => $profile->mediaUrl('aboutBanner', 'lg'),
                'srcset' => $profile->mediaSrcset('aboutBanner'),
                'alt' => $fallback['banner']['alt'] ?? $pick($profile->about_page_title, 'page_title'),
            ],
            'mission' => [
                'title' => $nested($profile->mission_title, 'mission', 'title'),
                'text' => $nested($profile->mission_text, 'mission', 'text'),
                'image' => $profile->mediaUrl('missionImage', 'lg'),
                'imageSrcset' => $profile->mediaSrcset('missionImage'),
            ],
            'vision' => [
                'title' => $nested($profile->vision_title, 'vision', 'title'),
                'text' => $nested($profile->vision_text, 'vision', 'text'),
                'image' => $profile->mediaUrl('visionImage', 'lg'),
                'imageSrcset' => $profile->mediaSrcset('visionImage'),
            ],
            'sales_policy' => [
                'title' => $nested($profile->sales_policy_title, 'sales_policy', 'title'),
                'content' => $nested($profile->sales_policy_content, 'sales_policy', 'content'),
                'cta_label' => $nested($profile->sales_policy_cta_label, 'sales_policy', 'cta_label'),
                'cta_url' => filled($profile->sales_policy_cta_url)
                    ? (string) $profile->sales_policy_cta_url
                    : null,
                'image' => $profile->mediaUrl('policyImage', 'lg'),
                'imageSrcset' => $profile->mediaSrcset('policyImage'),
            ],
            'values_section' => [
                'title' => $nested($profile->values_section_title, 'values_section', 'title'),
                'hub_label' => $nested($profile->values_hub_label, 'values_section', 'hub_label'),
                'eyebrow' => (string) ($fallback['values_section']['eyebrow'] ?? ''),
                'subtitle' => (string) ($fallback['values_section']['subtitle'] ?? ''),
            ],
            'reasons_section' => [
                'title' => $nested($profile->reasons_section_title, 'reasons_section', 'title'),
                'cta_label' => $nested($profile->reasons_cta_label, 'reasons_section', 'cta_label'),
                'cta_url' => filled($profile->reasons_cta_url)
                    ? (string) $profile->reasons_cta_url
                    : null,
                'image' => $profile->mediaUrl('reasonsImage', 'lg'),
                'imageSrcset' => $profile->mediaSrcset('reasonsImage'),
                'eyebrow' => (string) ($fallback['reasons_section']['eyebrow'] ?? ''),
                'subtitle' => (string) ($fallback['reasons_section']['subtitle'] ?? ''),
            ],
            'reference_section' => [
                'title' => $nested($profile->reference_section_title, 'reference_section', 'title'),
                'subtitle' => $nested($profile->reference_section_subtitle, 'reference_section', 'subtitle'),
                'eyebrow' => (string) ($fallback['reference_section']['eyebrow'] ?? ''),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function pageChrome(string $key): array
    {
        return SampleData::pageChrome($key);
    }

    public function reviewPlatforms(): array
    {
        $featured = HomeFeaturedReviewPlatform::query()
            ->orderBy('sort')
            ->with('platform')
            ->get()
            ->pluck('platform')
            ->filter(fn ($p) => $p && $p->is_active);

        $platforms = $featured->isNotEmpty()
            ? $featured
            : ReviewPlatform::query()->where('is_active', true)->where('show_on_home', true)->orderBy('sort')->get();

        if ($platforms->isEmpty()) {
            return SampleData::reviewPlatforms();
        }

        return $platforms
            ->map(fn (ReviewPlatform $platform) => [
                'name' => $platform->name,
                'rating' => (float) ($platform->rating ?? 5),
                'quote' => $platform->quote
                    ?: "{$platform->rating}/5 từ {$platform->review_count} đánh giá.",
                'link' => $platform->link_label
                    ?: "Đọc đánh giá trên {$platform->name}",
                'url' => $platform->url ?: '#',
            ])
            ->values()
            ->all();
    }

    public function companyContact(): array
    {
        return \App\Models\CompanyProfile::contact();
    }

    public function quickInquiry(): array
    {
        return $this->homeSection('quick_inquiry');
    }

    /**
     * Tuỳ chọn form «Tour riêng» — label + điểm đến + lưu trú theo seed dự án.
     * Điểm đến mặc định lấy từ countries/zones có show_in_customize_form.
     *
     * @return array{
     *   brand: string,
     *   destinations_label: string,
     *   destinations: list<string>,
     *   accommodation_label: string,
     *   accommodation: list<string>,
     *   budget_note: string
     * }
     */
    public function customizeForm(): array
    {
        $seed = [];
        try {
            $raw = ProjectSeed::get('customize_form', []);
            $seed = is_array($raw) ? $raw : [];
        } catch (\Throwable) {
            $seed = [];
        }

        $pickText = function (string $key, string $fallback) use ($seed): string {
            $val = $seed[$key] ?? null;
            if (is_string($val) && $val !== '') {
                return $val;
            }
            if (is_array($val)) {
                $picked = LocaleContent::pick($val, $this->locale(), null);
                if (is_string($picked) && $picked !== '') {
                    return $picked;
                }
            }

            return $fallback;
        };

        $pickList = function (string $key) use ($seed): array {
            $val = $seed[$key] ?? null;
            if (! is_array($val) || $val === []) {
                return [];
            }
            // { vi: [...], en: [...] } hoặc list phẳng
            if (array_is_list($val)) {
                return array_values(array_filter(array_map('strval', $val)));
            }
            $picked = LocaleContent::pick($val, $this->locale(), $val['vi'] ?? []);
            if (! is_array($picked)) {
                return [];
            }

            return array_values(array_filter(array_map('strval', $picked)));
        };

        $destinations = $pickList('destinations');
        if ($destinations === []) {
            $destinations = $this->customizeFormDestinationsFromCountries();
        }

        $accommodation = $pickList('accommodation');
        if ($accommodation === []) {
            $accommodation = [
                'Tiêu chuẩn (khách sạn 3*)',
                'Cao cấp (khách sạn 4*)',
                'Sang trọng (khách sạn 5*)',
                'Nhờ tư vấn giúp tôi',
            ];
        }

        $brand = (string) ($this->companyContact()['name'] ?? site_brand());

        $isIsland = collect($this->serviceClusters())->contains(fn ($c) => ($c['code'] ?? '') === 'ferry');

        return [
            'brand' => $brand,
            'destinations_label' => $pickText(
                'destinations_label',
                $isIsland ? 'Bạn muốn khám phá khu vực nào trên đảo?' : 'Bạn muốn ghé thăm quốc gia nào?'
            ),
            'destinations' => $destinations,
            'accommodation_label' => $pickText('accommodation_label', 'Bạn thích loại lưu trú nào?'),
            'accommodation' => $accommodation,
            'budget_note' => $pickText(
                'budget_note',
                $isIsland
                    ? 'Ngân sách dự kiến (chưa gồm vé tàu cao tốc / đưa đón cửa ngõ)'
                    : 'Ngân sách dự kiến (chưa gồm vé máy bay quốc tế)'
            ),
        ];
    }

    /**
     * Nhãn header / tìm kiếm theo dự án (seed `nav`, không qua admin).
     * `nav.cruise` đổi «Du thuyền» ↔ «Thuyền câu / trải nghiệm biển» tuỳ profile.
     *
     * @return array{
     *   brand: string,
     *   tagline: string,
     *   about_group: string,
     *   tours: array{label: string},
     *   cruise: array{label: string, all_label: string, all_meta: string, search_hint: string, search_placeholder: string, hub_title: string, hub_subtitle: string}
     * }
     */
    public function siteNav(): array
    {
        $contact = $this->companyContact();
        $brand = (string) ($contact['name'] ?? site_brand());
        $tagline = (string) ($contact['tagline'] ?? '');
        if ($tagline === '') {
            $tagline = trim((string) ($contact['slogan'] ?? ''), " \t\n\r\0\x0B\"'");
        }

        $seed = [];
        try {
            $raw = ProjectSeed::get('nav', []);
            $seed = is_array($raw) ? $raw : [];
        } catch (\Throwable) {
            $seed = [];
        }

        $pick = function (mixed $val, string $fallback): string {
            if (is_string($val) && $val !== '') {
                return $val;
            }
            if (is_array($val)) {
                $picked = LocaleContent::pick($val, $this->locale(), null);
                if (is_string($picked) && $picked !== '') {
                    return $picked;
                }
            }

            return $fallback;
        };

        $cruiseSeed = is_array($seed['cruise'] ?? null) ? $seed['cruise'] : [];
        $toursSeed = is_array($seed['tours'] ?? null) ? $seed['tours'] : [];

        return [
            'brand' => $brand,
            'tagline' => $tagline,
            'about_group' => $pick($seed['about_group'] ?? null, 'Về '.$brand),
            'tours' => [
                'label' => $pick($toursSeed['label'] ?? null, 'Tour trọn gói'),
            ],
            'cruise' => [
                'label' => $pick($cruiseSeed['label'] ?? null, 'Du thuyền'),
                'all_label' => $pick($cruiseSeed['all_label'] ?? null, 'Tất cả du thuyền'),
                'all_meta' => $pick($cruiseSeed['all_meta'] ?? null, 'Xem toàn bộ lịch trình du thuyền'),
                'search_hint' => $pick($cruiseSeed['search_hint'] ?? null, 'Tour, điểm đến, du thuyền, cẩm nang…'),
                'search_placeholder' => $pick(
                    $cruiseSeed['search_placeholder'] ?? null,
                    'Tìm tour, điểm đến, du thuyền, bài viết…'
                ),
                'hub_title' => $pick($cruiseSeed['hub_title'] ?? $cruiseSeed['label'] ?? null, 'Du thuyền'),
                'hub_subtitle' => $pick(
                    $cruiseSeed['hub_subtitle'] ?? null,
                    'Chọn lịch trình trên mặt nước phù hợp với bạn'
                ),
            ],
        ];
    }

    /** @return list<string> */
    protected function customizeFormDestinationsFromCountries(): array
    {
        if (! Country::query()->active()->exists()) {
            return array_values(array_filter(array_map(
                fn ($c) => (string) ($c['name'] ?? ''),
                array_filter(
                    SampleData::countries(),
                    fn ($c) => ($c['slug'] ?? '') !== 'tour-ket-hop'
                )
            )));
        }

        return Country::query()
            ->active()
            ->where('show_in_customize_form', true)
            ->with('translations')
            ->orderBy('sort')
            ->get()
            ->map(fn (Country $country) => (string) ($country->translation($this->locale())?->name ?? ''))
            ->filter()
            ->values()
            ->all();
    }

    public function toursHub(): array
    {
        return $this->listingHub('tours_hub');
    }

    public function cruisesHub(): array
    {
        return $this->listingHub('cruises_hub');
    }

    public function guideHub(): array
    {
        return $this->listingHub('guide_hub');
    }

    /** @return list<array<string, mixed>> */
    public function serviceClusters(): array
    {
        $seed = ProjectSeed::get('service_clusters', []);
        if (is_array($seed) && $seed !== []) {
            return array_values($seed);
        }

        $out = [];
        foreach (config('services_catalog.clusters', []) as $code => $cfg) {
            $out[] = [
                'code' => $code,
                'nav_label' => $cfg['nav_label'] ?? $code,
                'label' => $cfg['label'] ?? $code,
                'icon' => $cfg['icon'] ?? 'sparkles',
                'hub_key' => $cfg['hub_key'] ?? null,
                'sort' => $cfg['sort'] ?? 0,
            ];
        }

        usort($out, fn ($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        return $out;
    }

    public function serviceCluster(string $code): ?array
    {
        return collect($this->serviceClusters())->firstWhere('code', $code);
    }

    /** @return list<string> */
    public function serviceClusterCodes(): array
    {
        return collect($this->serviceClusters())
            ->pluck('code')
            ->filter(fn ($code) => is_string($code) && $code !== '')
            ->values()
            ->all();
    }

    /**
     * Chuẩn hoá ?cluster= cho admin theo seed dự án.
     * Đảo: train → ferry; mainland: ferry → train khi cụm kia không có trong seed.
     */
    public function resolveAdminServiceCluster(?string $requested): string
    {
        $codes = $this->serviceClusterCodes();
        $requested = trim((string) $requested);

        if ($requested !== '' && in_array($requested, $codes, true)) {
            return $requested;
        }

        if ($requested === 'train' && in_array('ferry', $codes, true)) {
            return 'ferry';
        }

        if ($requested === 'ferry' && in_array('train', $codes, true)) {
            return 'train';
        }

        if ($codes !== []) {
            return $codes[0];
        }

        $catalog = array_keys(config('services_catalog.clusters', []));

        if ($requested !== '' && in_array($requested, $catalog, true)) {
            return $requested;
        }

        return $catalog[0] ?? 'stay';
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function adminServiceClusterOptions(): array
    {
        return collect($this->serviceClusters())
            ->map(fn (array $c) => [
                'value' => (string) ($c['code'] ?? ''),
                'label' => (string) ($c['label'] ?? $c['nav_label'] ?? $c['code'] ?? ''),
            ])
            ->filter(fn (array $row) => $row['value'] !== '')
            ->values()
            ->all();
    }

    /**
     * Cluster vận tải nổi bật trên trang chủ / menu tắt:
     * ưu tiên `ferry` (đảo) nếu dự án có cụm này, không thì `train`.
     */
    public function featuredTransportCluster(): string
    {
        $codes = collect($this->serviceClusters())->pluck('code');
        if ($codes->contains('ferry')) {
            return 'ferry';
        }
        if ($codes->contains('train')) {
            return 'train';
        }

        return 'train';
    }

    /** Số dịch vụ published theo cluster (cho badge menu). */
    public function serviceCount(?string $cluster = null): int
    {
        $query = Service::query()->published();
        if ($cluster) {
            $query->forCluster($cluster);
        }

        $count = $query->count();
        if ($count > 0) {
            return $count;
        }

        if (! Service::query()->exists()) {
            return count(SampleData::services($cluster));
        }

        return 0;
    }

    public function serviceHub(string $cluster): array
    {
        $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
        if (! $hubKey) {
            abort(404);
        }

        $hub = $this->listingHub($hubKey);
        $cfg = config("services_catalog.clusters.{$cluster}", []);

        return array_merge($hub, [
            'cluster' => $cluster,
            'navLabel' => $cfg['nav_label'] ?? ($hub['title'] ?? ''),
            'icon' => $cfg['icon'] ?? 'sparkles',
            'unitLabel' => $cfg['unit_label'] ?? 'dịch vụ',
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function serviceCategories(?string $cluster = null): array
    {
        $query = ServiceCategory::query()->active()->with(['banner', 'cover', 'seoEntry.translations']);
        if ($cluster) {
            $query->forCluster($cluster);
        }

        $rows = $query->withCount(['services' => fn ($q) => $q->published()])->get();
        if ($rows->isEmpty()) {
            return SampleData::serviceCategories($cluster);
        }

        return $rows->map(fn (ServiceCategory $cat) => $this->mapServiceCategory($cat))->values()->all();
    }

    /**
     * Danh mục hiển thị trên hub — trang "Tất cả dịch vụ" (other) kèm Vé tàu / Vé máy bay.
     *
     * @return list<array<string, mixed>>
     */
    public function serviceCategoriesForHub(string $cluster): array
    {
        $categories = $this->serviceCategories($cluster);
        if ($cluster !== 'other') {
            return $categories;
        }

        $linked = [];
        foreach ([
            'train' => 'Vé tàu hỏa',
            'flight' => 'Vé máy bay',
        ] as $code => $name) {
            $count = $this->serviceCount($code);
            if ($count < 1) {
                continue;
            }
            $linked[] = [
                'slug' => '_cluster_'.$code,
                'name' => $name,
                'intro' => '',
                'cluster' => $code,
                'count' => $count,
                'imageHero' => null,
                'imageSrcset' => null,
                'isClusterGroup' => true,
            ];
        }

        return array_values(array_merge($linked, $categories));
    }

    /**
     * Dịch vụ trên hub — trang other gộp thêm train + flight.
     *
     * @return list<array<string, mixed>>
     */
    public function servicesForHub(string $cluster): array
    {
        if ($cluster !== 'other') {
            return $this->services($cluster);
        }

        return array_values(array_merge(
            $this->services('train'),
            $this->services('flight'),
            $this->services('other'),
        ));
    }

    public function serviceCategory(string $cluster, string $slug): ?array
    {
        $cat = ServiceCategory::query()
            ->active()
            ->forCluster($cluster)
            ->where('slug', $slug)
            ->with(['banner', 'cover', 'seoEntry.translations'])
            ->withCount(['services' => fn ($q) => $q->published()])
            ->first();

        if (! $cat) {
            $cat = ServiceCategory::withoutGlobalScope('project')
                ->active()
                ->forCluster($cluster)
                ->where('slug', $slug)
                ->with(['banner', 'cover', 'seoEntry.translations'])
                ->withCount(['services' => fn ($q) => $q->withoutGlobalScope('project')->published()])
                ->first();
        }

        if ($cat) {
            return $this->mapServiceCategory($cat);
        }

        return SampleData::serviceCategory($cluster, $slug);
    }

    /** @return list<array<string, mixed>> */
    
    /**
     * Lấy 3 dịch vụ liên quan cùng danh mục với truy vấn giới hạn (Zero overhead).
     */
    public function relatedServicesForCategory(string $cluster, ?int $categoryId, ?int $excludeServiceId = null, int $limit = 3): array
    {
        if (! $categoryId) {
            return [];
        }

        $query = \App\Models\Service::withoutGlobalScope('project')
            ->published()
            ->where('service_category_id', $categoryId)
            ->where('cluster', $cluster)
            ->when($excludeServiceId, fn ($q) => $q->where('id', '!=', $excludeServiceId))
            ->with([
                'translations', 'category', 'seoEntry.translations',
                'mediaAttachments.media',
                'priceTable.periods.rates',
            ])
            ->orderBy('sort')
            ->orderByDesc('id')
            ->limit($limit);

        return $query->get()->map(fn (\App\Models\Service $s) => $this->mapService($s))->values()->all();
    }

    /**
     * Schema Items siêu nhẹ cho Hub & Category Listing (Zero Overhead, chỉ lấy title + url).
     *
     * @return list<array{name: string, url: string}>
     */
    public function serviceSchemaItems(?string $cluster = null, ?string $categorySlug = null): array
    {
        $query = Service::withoutGlobalScope('project')
            ->published()
            ->with([
                'translations:id,service_id,language_id,title',
                'seoEntry.translations:id,seo_entry_id,language_id,slug',
                'category:id,slug',
            ])
            ->select(['id', 'project_id', 'cluster', 'service_category_id', 'code', 'status', 'sort'])
            ->orderBy('sort')
            ->orderByDesc('id');

        if ($cluster) {
            $query->forCluster($cluster);
        }

        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->withoutGlobalScope('project')->where('slug', $categorySlug));
        }

        $locale = $this->locale();

        return $query->get()->map(function (Service $s) use ($locale, $cluster) {
            $tr = $s->translation($locale) ?? $s->translations->first();
            $seoTr = $s->seoEntry?->translation($locale) ?? $s->seoEntry?->translations->first();
            $slug = $seoTr?->slug ?? ($s->code ?? '');
            $catSlug = $s->category?->slug ?? '';

            return [
                'name' => (string) ($tr?->title ?? ''),
                'url' => locale_route('services.show', [
                    'cluster' => $s->cluster ?? $cluster,
                    'category' => $catSlug,
                    'slug' => $slug,
                ]),
            ];
        })->filter(fn ($item) => $item['name'] !== '' && $item['url'] !== '')->values()->all();
    }

    public function services(?string $cluster = null): array
    {
        $query = Service::withoutGlobalScope('project')
            ->published()
            ->with([
                'translations', 'category', 'country.translations',
                'seoEntry.translations', 'faqs.translations',
                'mediaAttachments.media',
                'priceTable.variants.translations',
                'priceTable.periods.rates',
            ])
            ->orderBy('sort')
            ->orderByDesc('id');

        if ($cluster) {
            $query->forCluster($cluster);
        }

        $res = $query->get();
        if ($res->isEmpty()) {
            return SampleData::services($cluster);
        }

        return $res->map(fn (Service $s) => $this->mapService($s, false))->values()->all();
    }

    public function service(string $slug, ?string $cluster = null): ?array
    {
        $preview = app()->environment('local') && request()?->boolean('preview');
        $query = Service::withoutGlobalScope('project')
            ->when(! $preview, fn ($q) => $q->published())
            ->with([
                'translations' => fn ($q) => $q->withoutGlobalScope('project'),
                'category' => fn ($q) => $q->withoutGlobalScope('project'),
                'country.translations',
                'seoEntry' => fn ($q) => $q->withoutGlobalScope('project'),
                'seoEntry.translations' => fn ($q) => $q->withoutGlobalScope('project'),
                'faqs.translations',
                'options' => fn ($q) => $q->withoutGlobalScope('project'),
                'options.translations' => fn ($q) => $q->withoutGlobalScope('project'),
                'mediaAttachments.media',
                'priceTable.variants.translations',
                'priceTable.periods.rates',
            ]);

        if ($cluster) {
            $query->forCluster($cluster);
        }

        $matches = $query->get()->filter(function (Service $s) use ($slug) {
            $seoSlug = $s->seoEntry?->translation($this->locale())?->slug
                ?? $s->seoEntry?->translations->first()?->slug;

            return $seoSlug === $slug || $s->code === $slug;
        });

        // Nhiều bản draft trùng slug (crawl lại) — luôn ưu tiên published, rồi id mới nhất.
        $service = $matches->first(fn (Service $s) => $s->status === 'published')
            ?? $matches->sortByDesc('id')->first();

        if ($service) {
            return $this->mapService($service);
        }

        return SampleData::service($slug, $cluster);
    }

    /** @return list<array{q: string, a: string}> */
    public function serviceListingFaqs(): array
    {
        $faqs = ProjectSeed::get('service_listing_faqs', []);
        if (is_array($faqs) && $faqs !== []) {
            return array_values(array_map(fn ($f) => [
                'q' => $f['q'] ?? $f['question'] ?? '',
                'a' => $f['a'] ?? $f['answer'] ?? '',
            ], $faqs));
        }

        return $this->listingFaqs();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapServiceCategory(ServiceCategory $cat): array
    {
        $cardImage = $cat->coverUrl('card') ?: $cat->bannerUrl('card');

        $seoTrans = $cat->seoEntry?->translation($this->locale());
        $subtitle = trim((string) ($cat->intro ?? ''));
        $seoBody = trim((string) ($cat->seo_body ?: $cat->intro ?? ''));

        return [
            'slug' => $cat->slug,
            'name' => $cat->name,
            'title' => $cat->name,
            'intro' => $subtitle,
            'subtitle' => $subtitle,
            'seoBody' => $seoBody,
            'seoTitle' => apply_site_brand((string) ($seoTrans?->seo_title ?? $cat->name)),
            'seoDescription' => apply_site_brand((string) ($seoTrans?->seo_description ?? $subtitle)),
            'cluster' => $cat->cluster,
            'count' => (int) ($cat->services_count ?? $cat->services()->published()->count()),
            'image' => $cardImage,
            'imageHero' => $cat->bannerUrl('lg') ?: $cat->bannerUrl('full') ?: $cardImage,
            'banner' => $cat->bannerUrl('lg') ?: $cat->bannerUrl('full') ?: $cardImage,
            'imageSrcset' => $cat->coverSrcset() ?: $cat->bannerSrcset(),
            'bannerSrcset' => $cat->bannerSrcset() ?: $cat->coverSrcset(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapService(Service $service, bool $isDetail = true): array
    {
        $translation = $service->translation($this->locale())
            ?? ($service->relationLoaded('translations') ? $service->translations->first() : $service->translations()->withoutGlobalScope('project')->first());
        $seoTranslation = $service->seoEntry?->translation($this->locale())
            ?? ($service->relationLoaded('seoEntry') ? $service->seoEntry?->translations?->first() : $service->seoEntry()->withoutGlobalScope('project')->first()?->translations()->withoutGlobalScope('project')->first());
        $category = $service->relationLoaded('category') ? $service->category : ($service->category ?? $service->category()->withoutGlobalScope('project')->first());
        $cfg = config("services_catalog.clusters.{$service->cluster}", []);

        $highlights = $translation?->highlights ?? [];
        if (is_string($highlights)) {
            $decoded = json_decode($highlights, true);
            $highlights = is_array($decoded) ? $decoded : [];
        }

        $priceFrom = $service->price_from !== null ? (float) $service->price_from : null;
        $priceLabel = null;
        if ($priceFrom !== null && $priceFrom > 0) {
            $priceLabel = $this->formatMoney($priceFrom, $service->currency ?? 'VND');
        } elseif ($priceFrom !== null && $priceFrom <= 0) {
            $priceLabel = 'Liên hệ';
        }

        $payload = [
            'slug' => $seoTranslation?->slug ?? ($service->code ?? ''),
            'slugFull' => $seoTranslation?->slug_full ?? null,
            'code' => $service->code,
            'title' => $translation?->title ?? '',
            'cluster' => $service->cluster,
            'clusterLabel' => $cfg['label'] ?? $service->cluster,
            'clusterIcon' => $cfg['icon'] ?? 'sparkles',
            'categorySlug' => $category?->slug ?? '',
            'categoryName' => $category?->name ?? '',
            'countrySlug' => $service->country?->translation($this->locale())?->slug ?? '',
            'location' => $translation?->location_label ?? '',
            'places' => array_values(array_filter([$translation?->location_label])),
            'start' => is_array($service->attrs) ? ($service->attrs['from'] ?? '') : '',
            'end' => is_array($service->attrs) ? ($service->attrs['to'] ?? '') : '',
            'duration' => $this->serviceDurationLabel($service),
            'priceFrom' => $priceFrom,
            'currency' => $service->currency ?? 'VND',
            'priceFormatted' => $priceLabel,
            'priceUnitLabel' => $service->cluster === 'stay' ? '/ đêm' : null,
            'rating' => (float) $service->rating,
            'reviewCount' => (int) $service->review_count,
            'starRating' => $service->star_rating,
            'badge' => $service->discount_badge,
            'isFeatured' => (bool) $service->is_featured,
            'isHotDeal' => (bool) $service->is_hot_deal,
            'image' => $service->coverUrl('card'),
            'imageSrcset' => $service->coverSrcset(),
            'imageDetail' => $service->coverUrl('lg'),
            'imageDetailSrcset' => $service->coverSrcset(),
            'summary' => $translation?->summary ?? '',
            'highlightsIntro' => $translation?->summary ?? '',
            'highlights' => is_array($highlights) ? $highlights : [],
            'inclusions' => $translation?->inclusions ?? [],
            'exclusions' => $translation?->exclusions ?? [],
            'notes' => $translation?->notes ?? [],
            'content' => $translation?->content ?? '',
            'attrs' => is_array($service->attrs) ? $service->attrs : [],
            'faqs' => $service->faqs->where('is_active', true)->map(fn (Faq $faq) => [
                'q' => apply_site_brand($faq->question),
                'a' => apply_site_brand($faq->answer),
            ])->values()->all(),
            'quote' => $this->serviceQuote($service, $translation),
            'styles' => [],
            'gallery' => $this->mapGalleryAttachments($service),
            'galleryCount' => $this->galleryAttachmentCount($service),
        ];

        $payload = $this->attachPriceTable($payload, $service, $isDetail);

        if ($service->cluster === Service::CLUSTER_STAY) {
            return $isDetail
                ? $this->attachStayPayload($payload, $service, $translation)
                : $this->attachStayListingPayload($payload, $service, $translation);
        }

        return $payload;
    }

    /**
     * Bổ sung payload trang chi tiết lưu trú (Booking-style).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    /**
     * Payload siêu nhẹ cho card danh mục lưu trú / khách sạn (Không N+1 query, không parse rooms nặng).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function attachStayListingPayload(array $payload, Service $service, ?ServiceTranslation $translation): array
    {
        $attrs = is_array($service->attrs) ? $service->attrs : [];
        $propertyType = (string) ($attrs['property_type'] ?? 'hotel');

        $payload['isStay'] = true;
        $payload['summary'] = '';
        $payload['highlightsIntro'] = '';
        $payload['highlights'] = [];
        $payload['propertyType'] = $propertyType;
        $payload['propertyTypeLabel'] = config("stay.property_types.{$propertyType}") ?? ucfirst($propertyType);

        $fullAddress = (string) ($attrs['address'] ?? '');
        if (filled($fullAddress)) {
            $payload['address'] = $fullAddress;
            $payload['location'] = $fullAddress;
            $payload['places'] = [$fullAddress];
        }

        $payload['totalRooms'] = isset($attrs['total_rooms']) ? (int) $attrs['total_rooms'] : null;
        $payload['roomsCount'] = $service->relationLoaded('options')
            ? $service->options->count()
            : null;

        // Giá từ: ưu tiên giá trên Service model, nếu chưa có thì tìm giá thấp nhất từ options đã load
        if (($payload['priceFrom'] === null || $payload['priceFrom'] <= 0) && $service->relationLoaded('options')) {
            $lowest = null;
            foreach ($service->options as $opt) {
                if ($opt->price_from !== null && (float) $opt->price_from > 0) {
                    if ($lowest === null || (float) $opt->price_from < $lowest) {
                        $lowest = (float) $opt->price_from;
                    }
                }
            }
            if ($lowest !== null && $lowest > 0) {
                $payload['priceFrom'] = $lowest;
                $payload['priceFormatted'] = $this->formatMoney($lowest, $service->currency ?? 'VND');
            }
        }

        return $payload;
    }

    protected function attachStayPayload(array $payload, Service $service, ?ServiceTranslation $translation): array
    {
        $attrs = \App\Support\StayFacilities::normalizeStayAttrs(
            is_array($service->attrs) ? $service->attrs : [],
        );
        try {
            $crawlAttrs = $this->stayCrawlMappedAttrs($service, $attrs);
            if ($crawlAttrs !== []) {
                $attrs = \App\Support\StayFacilities::overlayRicherStayAttrs($attrs, $crawlAttrs);
            }
        } catch (\Throwable) {
            // Crawl overlay chỉ để hiển thị — không được làm rơi trang public.
        }
        $propertyType = (string) ($attrs['property_type'] ?? 'hotel');
        // Ưu tiên dữ liệu từ relations (StayTaxonomyService), fallback JSON attrs
        $taxonomyData = app(\App\Services\StayTaxonomyService::class)->resolvePublicData($service, $this->locale());
        $sections = \App\Support\StayFacilities::resolvePublicSections($attrs);

        $payload['isStay'] = true;
        $payload['summary'] = '';
        $payload['highlightsIntro'] = '';
        $payload['highlights'] = [];
        $payload['propertyType'] = $propertyType;
        $payload['propertyTypeLabel'] = config("stay.property_types.{$propertyType}") ?? ucfirst($propertyType);
        $fullAddress = (string) ($attrs['address'] ?? '');
        $payload['address'] = $fullAddress;
        if (filled($fullAddress)) {
            $payload['location'] = $fullAddress;
            $payload['places'] = [$fullAddress];
        }
        $payload['checkIn'] = (string) ($attrs['check_in'] ?? '15:00');
        $payload['checkOut'] = (string) ($attrs['check_out'] ?? '12:00');
        $payload['amenities'] = [];
        $payload['highlightBadges'] = ! empty($taxonomyData['highlightBadges'])
            ? $taxonomyData['highlightBadges']
            : \App\Support\StayFacilities::stringList($attrs['highlight_badges'] ?? $attrs['most_popular'] ?? null);
        $payload['amenityGroups'] = ! empty($taxonomyData['amenityGroups'])
            ? $taxonomyData['amenityGroups']
            : $sections['amenityGroups'];
        $payload['nearbyGroups'] = ! empty($taxonomyData['nearbyGroups'])
            ? $taxonomyData['nearbyGroups']
            : $sections['nearbyGroups'];
        $payload['reviewScores'] = $sections['reviewScores'];
        $payload['attrs'] = $attrs;
        $payload['totalRooms'] = isset($attrs['total_rooms']) ? (int) $attrs['total_rooms'] : null;
        $payload['languages'] = is_array($attrs['languages'] ?? null) ? $attrs['languages'] : [];
        $rooms = $this->mapStayRooms($service);
        $payload['rooms'] = $rooms;
        $payload['roomsCount'] = count($rooms);

        // Tính giá thấp nhất (Giá từ) dựa trên tất cả các options / hạng phòng
        $lowestPrice = null;
        foreach ($rooms as $rm) {
            $rPrice = $rm['priceFrom'] ?? ($rm['price_from'] ?? null);
            if ($rPrice !== null && (float) $rPrice > 0) {
                if ($lowestPrice === null || (float) $rPrice < $lowestPrice) {
                    $lowestPrice = (float) $rPrice;
                }
            }
            // Check rateOptions if any
            foreach ($rm['rateOptions'] ?? [] as $rate) {
                $rtPrice = $rate['price'] ?? ($rate['priceTotal'] ?? null);
                if ($rtPrice !== null && (float) $rtPrice > 0) {
                    if ($lowestPrice === null || (float) $rtPrice < $lowestPrice) {
                        $lowestPrice = (float) $rtPrice;
                    }
                }
            }
        }

        if ($lowestPrice !== null && $lowestPrice > 0) {
            $payload['priceFrom'] = $lowestPrice;
            $payload['priceFormatted'] = $this->formatMoney($lowestPrice, $service->currency ?? 'VND');
        }
        $payload['policies'] = [
            'check_in' => $payload['checkIn'],
            'check_out' => $payload['checkOut'],
            'cancellation' => (string) ($attrs['cancellation_policy'] ?? ''),
            'child' => (string) ($attrs['child_policy'] ?? ''),
            'extra_bed' => (string) ($attrs['extra_bed_policy'] ?? ''),
            'age_restriction' => (string) ($attrs['age_restriction'] ?? ''),
            'pet' => (string) ($attrs['pet_policy'] ?? ''),
            'smoking' => (string) ($attrs['smoking_policy'] ?? ''),
            'payment' => (string) ($attrs['payment_policy'] ?? ''),
            'payment_cards' => is_array($attrs['payment_cards'] ?? null)
                ? implode(', ', $attrs['payment_cards'])
                : (string) ($attrs['payment_cards'] ?? ''),
            'id_required' => (string) ($attrs['id_required_policy'] ?? ''),
        ];
        $payload['featuredQuote'] = ['text' => '', 'author' => ''];

        $remoteGallery = $this->mapStayRemotePhotos(is_array($attrs['photos'] ?? null) ? $attrs['photos'] : []);
        app(MediaService::class)->hydrateStayGalleryAttachments($service);
        if (! $service->relationLoaded('mediaAttachments')) {
            $service->load('mediaAttachments.media');
        }
        // Stay: giữ cover trong gallery (lightbox đủ ảnh); tour vẫn lọc cover để tránh trùng.
        $attachedGallery = $this->mapGalleryAttachments($service, includeCover: true);
        $mergedGallery = $this->mergeStayGallery(
            $attachedGallery,
            $remoteGallery !== [] ? $remoteGallery : (is_array($payload['gallery'] ?? null) ? $payload['gallery'] : []),
        );
        if ($mergedGallery !== []) {
            $payload['gallery'] = $mergedGallery;
        }
        $payload['galleryCount'] = count($mergedGallery !== [] ? $mergedGallery : $attachedGallery);
        // Chỉ đổ hero từ gallery khi chưa có cover media.
        $hasCover = filled($payload['imageDetail'] ?? null) || filled($payload['image'] ?? null);
        if (! $hasCover && $mergedGallery !== []) {
            $hero = $mergedGallery[0];
            $heroSrc = (string) ($hero['full'] ?? $hero['src'] ?? '');
            if ($heroSrc !== '') {
                $payload['image'] = $heroSrc;
                $payload['imageDetail'] = $heroSrc;
                $payload['imageSrcset'] = $hero['srcset'] ?? null;
                $payload['imageDetailSrcset'] = $hero['fullSrcset'] ?? ($hero['srcset'] ?? null);
            }
        }
        $payload['policySections'] = \App\Support\StayFacilities::policySections($attrs);
        $payload['paymentCards'] = is_array($attrs['payment_cards'] ?? null)
            ? array_values(array_filter($attrs['payment_cards'], fn ($c) => filled($c)))
            : array_values(array_filter(preg_split('/[\n,]+/', (string) ($attrs['payment_cards'] ?? '')) ?: [], fn ($c) => filled(trim($c))));

        return $payload;
    }

    public function stayScoreIcon(string $tagOrLabel): string
    {
        $tag = mb_strtolower(trim($tagOrLabel));
        $icons = config('stay.review_score_icons', []);
        if (isset($icons[$tag])) {
            return (string) $icons[$tag];
        }

        $labels = array_flip(config('stay.review_score_tags', []));
        $lowerLabels = [];
        foreach ($labels as $lbl => $tTag) {
            $lowerLabels[mb_strtolower((string) $lbl)] = (string) $tTag;
        }

        if (isset($lowerLabels[$tag], $icons[$lowerLabels[$tag]])) {
            return (string) $icons[$lowerLabels[$tag]];
        }

        return 'star';
    }

    public function stayAmenityIcon(string $label): string
    {
        $lower = mb_strtolower($label);
        foreach (config('stay.amenity_icons', []) as $needle => $icon) {
            if (str_contains($lower, (string) $needle)) {
                return (string) $icon;
            }
        }

        return 'check';
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function mapStayRooms(Service $service): array
    {
        if (! $service->relationLoaded('options')) {
            $service->load('options.translations');
        }

        $locale = $this->locale();
        $currency = $service->currency ?? 'VND';
        $crawlRooms = [];
        try {
            $crawlRooms = $this->stayCrawlRoomPhotos($service);
        } catch (\Throwable) {
            $crawlRooms = [];
        }

        return $service->options->map(function ($opt) use ($locale, $currency, $crawlRooms) {
            $t = $opt->translation($locale);
            $attrs = is_array($opt->attrs) ? $opt->attrs : [];
            if (empty($attrs['photos'])) {
                $code = (string) $opt->code;
                $name = mb_strtolower((string) ($t?->name ?? ''));
                $photos = $crawlRooms['code:'.$code] ?? $crawlRooms['name:'.$name] ?? [];
                if ($photos !== []) {
                    $attrs['photos'] = $photos;
                }
            }

            return \App\Support\StayFacilities::mapRoom([
                'code' => $opt->code,
                'name' => $t?->name ?? '',
                'description' => $t?->description ?? '',
                'price_from' => $opt->price_from,
                'capacity' => $opt->capacity,
                'amenities' => is_array($t?->amenities) ? $t->amenities : [],
                'attrs' => $attrs,
            ], $currency, fn ($amount, $cur) => $this->formatMoney($amount, $cur));
        })->values()->all();
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function stayCrawlRoomPhotos(Service $service): array
    {
        $itemId = (int) data_get($service->attrs, 'crawl.item_id', 0);
        $item = $itemId > 0 ? StayCrawlItem::query()->find($itemId) : null;
        if (! $item) {
            $item = StayCrawlItem::query()->where('service_id', $service->id)->latest('id')->first();
        }
        if (! $item || ! is_array($item->ai_json)) {
            return [];
        }
        $out = [];
        foreach (is_array($item->ai_json['options'] ?? null) ? $item->ai_json['options'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $photos = is_array($row['photos'] ?? null) ? $row['photos'] : [];
            if ($photos === [] && is_array($row['attrs']['photos'] ?? null)) {
                $photos = $row['attrs']['photos'];
            }
            if ($photos === []) {
                continue;
            }
            $code = trim((string) ($row['code'] ?? ''));
            $name = mb_strtolower(trim((string) ($row['name'] ?? '')));
            if ($code !== '') {
                $out['code:'.$code] = $photos;
            }
            if ($name !== '') {
                $out['name:'.$name] = $photos;
            }
        }

        return $out;
    }

    /**
     * attrs đã map từ crawl (ai_json) — bổ sung amenity_groups / nearby / photos nếu service.attrs thiếu.
     *
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    protected function stayCrawlMappedAttrs(Service $service, array $attrs): array
    {
        $itemId = (int) data_get($attrs, 'crawl.item_id', 0);
        $item = $itemId > 0
            ? StayCrawlItem::query()->find($itemId)
            : null;
        if (! $item) {
            $item = StayCrawlItem::query()->where('service_id', $service->id)->latest('id')->first();
        }
        if (! $item || ! is_array($item->ai_json)) {
            return [];
        }

        return is_array($item->ai_json['attrs'] ?? null) ? $item->ai_json['attrs'] : [];
    }

    /**
     * @return array{text: string, author: string}
     */
    protected function serviceQuote(Service $service, ?ServiceTranslation $translation): array
    {
        $text = trim((string) ($translation?->featured_quote_text ?? ''));
        $author = trim((string) ($translation?->featured_quote_author ?? ''));

        if ($text === '') {
            $fallback = $this->fallbackServiceQuote($service);
            $text = $fallback['text'];
            $author = $author !== '' ? $author : $fallback['author'];
        }

        return [
            'text' => $text,
            'author' => $author,
        ];
    }

    /**
     * Quote dự phòng theo cụm — dùng khi seed/CMS chưa có featured quote.
     *
     * @return array{text: string, author: string}
     */
    protected function fallbackServiceQuote(Service $service): array
    {
        $pool = match ($service->cluster) {
            'train' => [
                ['text' => 'Đặt vé tàu qua :brand rất nhanh, e-ticket rõ ràng và hỗ trợ đổi ngày linh hoạt.', 'author' => 'Anh Tuấn'],
                ['text' => 'Ghế mềm êm, lên tàu đúng hướng dẫn — tiết kiệm được một đêm khách sạn so với bay.', 'author' => 'Chị Hương'],
                ['text' => 'Nhân viên tư vấn rõ lịch SE và giao vé tận nơi đúng hẹn.', 'author' => 'Anh Đức'],
            ],
            'flight' => [
                ['text' => 'Giá vé máy bay minh bạch, xác nhận nhanh và hỗ trợ chọn giờ bay hợp lịch trình.', 'author' => 'Chị Mai'],
                ['text' => 'Đặt combo bay + tour rất tiện, không phải tự so sánh nhiều hãng.', 'author' => 'Anh Khoa'],
                ['text' => 'Đổi lịch bay được hỗ trợ kịp thời trước ngày khởi hành.', 'author' => 'Chị Lan'],
            ],
            'stay' => [
                ['text' => 'Resort đúng như mô tả, phòng sạch và view đẹp — book qua :brand được giá tốt.', 'author' => 'Gia đình Anh Nam'],
                ['text' => 'Check-in suôn sẻ, đội ngũ tư vấn chọn hạng phòng rất hợp nhu cầu.', 'author' => 'Chị Trang'],
                ['text' => 'Vị trí thuận tiện, bữa sáng ổn và nhân viên khách sạn nhiệt tình.', 'author' => 'Anh Minh'],
            ],
            'experience' => [
                ['text' => 'Vé vào cửa nhận nhanh bằng QR, không xếp hàng lâu như mua tại chỗ.', 'author' => 'Chị Hà'],
                ['text' => 'Trải nghiệm đáng tiền, hướng dẫn rõ ràng trước giờ tham quan.', 'author' => 'Anh Phong'],
                ['text' => 'Đặt trước rất tiện, đặc biệt vào cuối tuần đông khách.', 'author' => 'Chị My'],
            ],
            default => [
                ['text' => 'Dịch vụ đúng cam kết, hỗ trợ nhanh và giá rõ ràng từ đầu.', 'author' => 'Anh Long'],
                ['text' => 'Đặt qua :brand tiện hơn tự tìm — có người đồng hành khi cần hỗ trợ.', 'author' => 'Chị Ngọc'],
                ['text' => 'Phản hồi nhanh, điều chỉnh theo nhu cầu đoàn rất linh hoạt.', 'author' => 'Anh Việt'],
            ],
        };

        $index = abs(crc32((string) ($service->code ?: $service->id))) % count($pool);
        $pick = $pool[$index];

        return [
            'text' => apply_site_brand($pick['text']),
            'author' => $pick['author'],
        ];
    }

    protected function serviceDurationLabel(Service $service): string
    {
        $attrs = is_array($service->attrs) ? $service->attrs : [];
        if (! empty($attrs['duration_hours'])) {
            return ((int) $attrs['duration_hours']).' giờ';
        }
        if (! empty($attrs['flight_time'])) {
            return (string) $attrs['flight_time'];
        }
        if ($service->cluster === 'stay') {
            return 'Theo đêm';
        }

        return '';
    }

    /**
     * Hub cấp 1 từ config('seo.hubs.{hubKey}').
     *
     * @return array{title: string, subtitle: string, seoBody: string, listingBanner: ?string, listingBannerSrcset: ?string, seoTitle: ?string, seoDescription: ?string}
     */
    public function listingHub(string $hubKey): array
    {
        $cfg = config("seo.hubs.{$hubKey}");
        if (! is_array($cfg)) {
            return [
                'title' => 'Hub',
                'subtitle' => '',
                'seoBody' => '',
                'listingBanner' => null,
                'listingBannerSrcset' => null,
                'cover' => null,
                'coverSrcset' => null,
                'seoTitle' => null,
                'seoDescription' => null,
            ];
        }

        // Seed-only override cho cụm cruise (du thuyền / thuyền câu…) — không qua admin.
        if ($hubKey === 'cruises_hub') {
            $cruiseNav = $this->siteNav()['cruise'] ?? [];
            if (! empty($cruiseNav['hub_title'])) {
                $cfg['default_title'] = $cruiseNav['hub_title'];
            }
            if (! empty($cruiseNav['hub_subtitle'])) {
                $cfg['default_subtitle'] = $cruiseNav['hub_subtitle'];
            }
            if (! empty($cruiseNav['hub_title'])) {
                $cfg['default_seo_title'] = $cruiseNav['hub_title'];
            }
        }

        $this->seoService()->ensureHub($hubKey, $this->locale());
        $this->syncListingHubSeoBodyFromSeed($hubKey, $this->locale());

        $page = \App\Models\StaticPage::query()
            ->with(['translations', 'banner', 'cover', 'seoEntry.translations'])
            ->where('template', $cfg['template'])
            ->first();

        if (! $page) {
            return [
                'title' => $cfg['default_title'] ?? 'Hub',
                'subtitle' => $cfg['default_subtitle'] ?? '',
                'seoBody' => \App\Support\ListingHubCopy::seoBody($hubKey, $this->locale()),
                'listingBanner' => null,
                'listingBannerSrcset' => null,
                'cover' => null,
                'coverSrcset' => null,
                'seoTitle' => apply_site_brand((string) ($cfg['default_seo_title'] ?? '')),
                'seoDescription' => apply_site_brand((string) ($cfg['default_seo_description'] ?? '')),
            ];
        }

        $translation = $page->translation($this->locale());
        $seoTrans = $page->seoEntry?->translation($this->locale());
        $banner = $page->bannerUrl('lg') ?: $page->bannerUrl('full');
        $subtitleRaw = $translation?->body ?: ($cfg['default_subtitle'] ?? '');
        // null = chưa set → lấy seed; '' = admin cố ý ẩn.
        if ($translation !== null && $translation->seo_body !== null) {
            $seoBody = trim((string) $translation->seo_body);
        } else {
            $seoBody = \App\Support\ListingHubCopy::seoBody($hubKey, $this->locale());
        }

        $title = $translation?->title ?: ($cfg['default_title'] ?? 'Hub');
        // Nếu static page vẫn mang title mặc định «Du thuyền» của ViTravel → ưu tiên seed dự án.
        if ($hubKey === 'cruises_hub' && ! empty($cfg['default_title']) && in_array($title, ['Du thuyền', 'Cruises'], true)) {
            $title = $cfg['default_title'];
        }

        return [
            'title' => $title,
            'subtitle' => trim(html_entity_decode(strip_tags((string) $subtitleRaw), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'seoBody' => $seoBody,
            'listingBanner' => $banner,
            'listingBannerSrcset' => $page->bannerSrcset(),
            'cover' => $page->coverUrl('card') ?: $page->coverUrl(),
            'coverSrcset' => $page->coverSrcset(),
            'seoTitle' => apply_site_brand((string) ($seoTrans?->seo_title ?? ($cfg['default_seo_title'] ?? ''))),
            'seoDescription' => apply_site_brand((string) ($seoTrans?->seo_description ?? ($cfg['default_seo_description'] ?? ''))),
        ];
    }

    /**
     * Ghi seo_body từ seed vào DB chỉ khi cột đang trống (không cần re-seed full).
     */
    protected function syncListingHubSeoBodyFromSeed(string $hubKey, string $locale): void
    {
        $cfg = config("seo.hubs.{$hubKey}");
        if (! is_array($cfg)) {
            return;
        }
        $fromSeed = \App\Support\ListingHubCopy::seoBody($hubKey, $locale);
        if ($fromSeed === '') {
            return;
        }

        $page = \App\Models\StaticPage::query()
            ->where('template', $cfg['template'])
            ->first();
        if (! $page) {
            return;
        }

        $languageId = \App\Models\Language::idByCode($locale);
        if (! $languageId) {
            return;
        }

        $trans = $page->translations()->where('language_id', $languageId)->first();
        // Chỉ soft-fill khi chưa từng set (null). Chuỗi rỗng sau khi admin xoá = cố ý ẩn.
        if (! $trans || $trans->seo_body !== null) {
            return;
        }

        $trans->forceFill(['seo_body' => $fromSeed])->save();
    }

    protected function seoService(): \App\Services\SeoService
    {
        return app(\App\Services\SeoService::class);
    }

    public function listingFaqs(): array
    {
        $page = StaticPage::query()->where('template', 'listing_faqs')->first();
        if ($page) {
            $page->load(['faqs.translations']);
            $faqs = $page->faqs
                ->where('is_active', true)
                ->map(fn (Faq $faq) => ['q' => $faq->question, 'a' => $faq->answer])
                ->values()
                ->all();
            if ($faqs !== []) {
                return $faqs;
            }
        }

        return SampleData::listingFaqs();
    }

    public function galleryAlbums(): array
    {
        if (! ExperienceAlbum::query()->where('status', 'published')->exists()) {
            return array_map(function (array $album) {
                $title = (string) ($album['title'] ?? '');

                return [
                    'title' => $title,
                    'description' => $album['description'] ?? null,
                    'photos' => (int) ($album['photos'] ?? 0),
                    'date' => $album['date'] ?? '',
                    'image' => $album['image'] ?? null,
                    'imageSrcset' => $album['imageSrcset'] ?? null,
                    'tag' => $album['tag'] ?? null,
                    'slides' => [[
                        'type' => 'image',
                        'src' => $album['image'] ?? null,
                        'srcset' => $album['imageSrcset'] ?? null,
                        'title' => $title,
                        'description' => $album['description'] ?? null,
                        'caption' => $album['date'] ?? null,
                    ]],
                ];
            }, SampleData::galleryAlbums());
        }

        return ExperienceAlbum::query()
            ->where('status', 'published')
            ->orderBy('sort')
            ->with(['translations', 'cover', 'photos.media', 'country.translations'])
            ->get()
            ->map(fn (ExperienceAlbum $album) => $this->mapGalleryAlbum($album))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapGalleryAlbum(ExperienceAlbum $album): array
    {
        $title = (string) ($album->title ?? '');
        $description = $album->description;
        $coverUrl = $album->cover ? media_url($album->cover, 'card') : null;
        $coverSrcset = $album->cover ? media_srcset($album->cover) : null;

        $slides = $album->photos
            ->map(function ($photo) use ($title) {
                $media = $photo->media;
                if (! $media) {
                    return null;
                }

                return [
                    'type' => 'image',
                    'src' => media_url($media, 'lg') ?: media_url($media, 'full') ?: media_url($media, 'card'),
                    'srcset' => media_srcset($media),
                    'title' => $title,
                    'description' => $photo->caption,
                    'caption' => $photo->caption,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($slides === [] && $coverUrl) {
            $slides[] = [
                'type' => 'image',
                'src' => media_url($album->cover, 'lg') ?: media_url($album->cover, 'full') ?: $coverUrl,
                'srcset' => $coverSrcset,
                'title' => $title,
                'description' => $description,
                'caption' => optional($album->trip_date)?->format('m/Y'),
            ];
        }

        if ($slides === []) {
            $slides[] = [
                'type' => 'image',
                'src' => null,
                'srcset' => null,
                'title' => $title,
                'description' => $description,
                'caption' => optional($album->trip_date)?->format('m/Y'),
            ];
        }

        return [
            'title' => $title,
            'description' => $description,
            'photos' => (int) ($album->photo_count ?: count($slides)),
            'date' => optional($album->trip_date)?->format('m/Y') ?? '',
            'image' => $coverUrl,
            'imageSrcset' => $coverSrcset,
            'tag' => $album->country?->translation($this->locale())?->name,
            'slides' => $slides,
        ];
    }

    public function videos(bool $homeOnly = false, int $limit = 24): array
    {
        $limit = max(1, min(12, $limit));

        if ($homeOnly && HomeFeaturedSchema::hasVideos()) {
            $curated = HomeFeaturedVideo::query()
                ->orderBy('sort')
                ->with(['video.translations', 'video.thumbnail', 'video.videoFile', 'video.country.translations'])
                ->whereHas('video', fn ($q) => $q->published())
                ->get()
                ->map(fn (HomeFeaturedVideo $row) => $row->video)
                ->filter()
                ->take($limit);

            if ($curated->isNotEmpty()) {
                return $curated
                    ->map(fn (ExperienceVideo $video) => $this->mapVideo($video))
                    ->values()
                    ->all();
            }
        }

        $query = ExperienceVideo::query()->published()->orderBy('sort')->orderByDesc('id');

        if ($homeOnly) {
            $query->where('show_on_home', true);
        }

        if (! (clone $query)->exists()) {
            $samples = SampleData::videos();

            return $homeOnly ? array_slice($samples, 0, min(4, $limit)) : array_slice($samples, 0, $limit);
        }

        return $query
            ->with(['translations', 'thumbnail', 'videoFile', 'country.translations'])
            ->limit($limit)
            ->get()
            ->map(fn (ExperienceVideo $video) => $this->mapVideo($video))
            ->values()
            ->all();
    }

    /**
     * @return array{title: string, description: ?string, date: string, duration: ?string, tag: ?string, image: ?string, imageSrcset: ?string, embedUrl: ?string, provider: ?string, youtubeId: ?string, type: string}
     */
    protected function mapVideo(ExperienceVideo $video): array
    {
        return [
            'type' => 'video',
            'title' => $video->title,
            'description' => $video->description,
            'date' => optional($video->published_at)?->format('d/m/Y') ?? '',
            'duration' => $video->duration,
            'tag' => $video->tag ?: ($video->country?->name),
            'image' => $video->thumbnailUrl('card'),
            'imageSrcset' => $video->thumbnailSrcset(),
            'embedUrl' => $video->embedUrl(),
            'provider' => $video->provider(),
            'youtubeId' => $video->resolvedYoutubeId(),
        ];
    }

    public function footerColumns(): array
    {
        return SampleData::footerColumns();
    }

    public function footerSeoLinks(): array
    {
        return SampleData::footerSeoLinks();
    }

    protected function packageQuery(string $type)
    {
        return Package::query()
            ->published()
            ->where('type', $type)
            ->orderBy('sort')
            ->with([
                'translations',
                'country.translations',
                'countries.translations',
                'travelStyles',
                'categories.translations',
                'itineraryDays.translations',
                'cabinTypes.translations',
                'faqs.translations',
                'mediaAttachments.media',
                'seoEntry.translations',
                'priceTable.variants.translations',
                'priceTable.periods.rates',
            ]);
    }

    protected function articleQuery()
    {
        return Article::query()
            ->published()
            ->orderByDesc('published_at')
            ->with([
                'translations',
                'country' => fn ($q) => $q->withTrashed()->with('translations'),
                'blogCategory.translations',
                'blogCategory.country' => fn ($q) => $q->withTrashed()->with('translations'),
                'contentTypeTags.translations',
                'faqs.translations',
                'mediaAttachments.media',
                'seoEntry.translations',
            ]);
    }

    protected function findCountryBySlug(string $slug): ?Country
    {
        $ids = $this->languageIdChain();
        if ($ids === []) {
            return null;
        }

        return Country::query()
            ->with('translations')
            ->whereHas('translations', fn ($q) => $q->whereIn('language_id', $ids)->where('slug', $slug))
            ->first();
    }

    protected function findPackageBySlug(string $slug, string $type): ?Package
    {
        $ids = $this->languageIdChain();
        if ($ids === []) {
            return null;
        }

        return $this->packageQuery($type)
            ->whereHas('seoEntry.translations', fn ($q) => $q
                ->whereIn('language_id', $ids)
                ->where('slug', $slug))
            ->first();
    }

    protected function findArticleBySlug(string $slug): ?Article
    {
        $ids = $this->languageIdChain();
        if ($ids === []) {
            return null;
        }

        return $this->articleQuery()
            ->whereHas('seoEntry.translations', fn ($q) => $q
                ->whereIn('language_id', $ids)
                ->where('slug', $slug))
            ->first();
    }

    protected function mapHomeSection(HomeSection $section): array
    {
        $translation = $section->translation($this->locale());

        return [
            'key' => $section->key,
            'eyebrow' => $translation?->eyebrow,
            'title' => $translation?->title,
            'subtitle' => $translation?->subtitle,
            'body' => $translation?->body,
            'metaLine' => $translation?->meta_line,
            'ctaLabel' => $translation?->cta_label,
            'ctaUrl' => $this->resolveCtaUrl($translation?->cta_url),
            'image' => $section->imageUrl('lg'),
            'imageSrcset' => $section->imageSrcset(),
            'imageAlt' => $translation?->image_alt,
        ];
    }

    protected function resolveCtaUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return $url;
    }

    protected function mapHomeSlide(\App\Models\HomeSlide $slide): array
    {
        $translation = $slide->translation($this->locale());

        return [
            'image' => $slide->imageUrl('full'),
            'imageSrcset' => $slide->imageSrcset(),
            'imageMobile' => $slide->imageMobileUrl('lg'),
            'imageMobileSrcset' => $slide->imageMobileSrcset(),
            'imageAlt' => $translation?->image_alt ?? $slide->image?->alt,
            'title' => $translation?->title,
            'titleAccent' => $translation?->title_accent,
            'description' => $translation?->description,
            'buttonLabel' => $translation?->button_label,
            'linkUrl' => $slide->link_url,
            'textAlign' => $slide->text_align,
        ];
    }

    protected function mapCountry(Country $country): array
    {
        $translation = $country->translation($this->locale());
        $cardImage = $country->bannerUrl('card');
        $heroImage = $country->bannerUrl('lg') ?: $country->bannerUrl('full') ?: $cardImage;
        $listingBanner = $country->listingBannerUrl('lg')
            ?: $country->listingBannerUrl('full');

        return [
            'slug' => $translation?->slug ?? '',
            'name' => $translation?->name ?? '',
            'size' => $country->home_grid_size === 'large' ? 'large' : 'normal',
            'tourCount' => Package::query()
                ->published()
                ->tours()
                ->where(function ($q) use ($country) {
                    $q->where('country_id', $country->id)
                        ->orWhereHas('countries', fn ($c) => $c->where('countries.id', $country->id));
                })
                ->count(),
            'tagline' => $translation?->tagline ?? '',
            'subtitle' => $translation?->tagline ?? '',
            'seoBody' => trim((string) ($translation?->long_form_content ?? $translation?->intro_text ?? '')),
            'longForm' => trim((string) ($translation?->long_form_content ?? $translation?->intro_text ?? '')),
            'image' => $cardImage,
            'imageHero' => $heroImage,
            'imageSrcset' => $country->bannerSrcset(),
            'listingBanner' => $listingBanner,
            'listingBannerSrcset' => $country->listingBannerSrcset(),
        ];
    }

    protected function mapPackage(Package $package, bool $isCruise = false): array
    {
        $translation = $package->translation($this->locale());
        $countryTranslation = $package->country?->translation($this->locale());
        $seoTranslation = $package->seoEntry?->translation($this->locale());
        $places = $translation?->places_to_visit ?? [];
        if (is_string($places)) {
            $decoded = json_decode($places, true);
            $places = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $places) ?: [])));
        }

        $seoRating = (float) ($package->seoEntry?->rating_aggregate_star ?? 0);
        $seoCount = (int) ($package->seoEntry?->rating_aggregate_count ?? 0);
        $pkgRating = (float) ($package->rating ?? 0);
        $pkgCount = (int) ($package->review_count ?? 0);

        $primarySlug = $countryTranslation?->slug ?? '';
        $countrySlugs = $package->relationLoaded('countries') && $package->countries->isNotEmpty()
            ? $package->countries
                ->map(fn (Country $c) => $c->translation($this->locale())?->slug)
                ->filter()
                ->values()
                ->all()
            : array_values(array_filter([$primarySlug]));
        if ($primarySlug !== '' && ! in_array($primarySlug, $countrySlugs, true)) {
            array_unshift($countrySlugs, $primarySlug);
        }
        if ($primarySlug === '' && $countrySlugs !== []) {
            $primarySlug = (string) $countrySlugs[0];
        }

        $data = [
            'slug' => $seoTranslation?->slug ?? '',
            'slugFull' => $seoTranslation?->slug_full ?? null,
            'title' => $translation?->title ?? '',
            'countrySlug' => $primarySlug,
            'countrySlugs' => $countrySlugs,
            'country' => $countryTranslation?->name
                ?? ($package->relationLoaded('countries')
                    ? (string) ($package->countries->first()?->translation($this->locale())?->name ?? '')
                    : ''),
            'tourCode' => $package->code ?? '',
            'duration' => $this->formatDuration($package->duration_days, $package->duration_nights),
            'days' => $package->duration_days,
            'priceFrom' => $package->price_from !== null ? (float) $package->price_from : null,
            'currency' => $package->currency ?? 'VND',
            'priceFormatted' => $package->price_from !== null
                ? $this->formatMoney((float) $package->price_from, $package->currency ?? 'VND')
                : null,
            'rating' => $seoRating > 0 ? $seoRating : $pkgRating,
            'reviewCount' => $seoCount > 0 ? $seoCount : $pkgCount,
            'badge' => $package->discount_badge,
            'image' => $package->coverUrl('card'),
            'imageSrcset' => $package->coverSrcset(),
            'imageDetail' => $package->coverUrl('lg'),
            'imageDetailSrcset' => $package->coverSrcset(),
            'styles' => $package->travelStyles->pluck('code')->all(),
            'categorySlugs' => $package->relationLoaded('categories')
                ? $package->categories
                    ->map(fn (TourCategory $c) => $c->translation($this->locale())?->slug)
                    ->filter()
                    ->values()
                    ->all()
                : [],
            'quote' => [
                'text' => $translation?->featured_quote_text ?? '',
                'author' => $translation?->featured_quote_author ?? '',
            ],
            'places' => is_array($places) ? $places : [],
            'start' => $translation?->start_location ?? '',
            'end' => $translation?->end_location ?? '',
            'highlightsIntro' => $translation?->highlights_intro ?? '',
            'highlights' => $translation?->highlight_bullets ?? [],
            'itinerary' => $package->itineraryDays->map(fn ($day) => [
                'day' => $day->day_number,
                'title' => $day->title,
                'meals' => $day->meals_included,
                'transport' => $day->transport_icons ?? [],
                'overnight' => $day->overnight_at,
                'content' => $day->content,
            ])->values()->all(),
            'inclusions' => $translation?->inclusions ?? [],
            'exclusions' => $translation?->exclusions ?? [],
            'notes' => $translation?->notes ?? [],
            'faqs' => $package->faqs->where('is_active', true)->map(fn (Faq $faq) => [
                'q' => apply_site_brand($faq->question),
                'a' => apply_site_brand($faq->answer),
            ])->values()->all(),
            'gallery' => $this->mapGalleryAttachments($package),
            'galleryCount' => $this->galleryAttachmentCount($package),
        ];

        if ($isCruise) {
            $typeSlug = $package->cruise_type ?? '';
            $typeName = CruiseType::query()->where('slug', $typeSlug)->value('name')
                ?? match ($typeSlug) {
                    'du-thuyen-ha-long' => 'Du thuyền Hạ Long',
                    'du-thuyen-mekong' => 'Du thuyền Mekong',
                    'du-thuyen-lan-ha' => 'Du thuyền Lan Hạ',
                    default => $typeSlug,
                };

            $data['typeSlug'] = $typeSlug;
            $data['typeName'] = $typeName;
            $data['departurePort'] = $package->departure_port ?? '';
            $data['boatClass'] = $package->boat_class ?? '';
            $data['nightsOnBoard'] = $package->nights_on_board;
        }

        return $this->attachPriceTable($data, $package);
    }

    protected function mapArticle(Article $article): array
    {
        $translation = $article->translation($this->locale());
        $country = $article->country ?? $article->blogCategory?->country;
        $countryTranslation = $country?->translation($this->locale());
        $categoryTranslation = $article->blogCategory?->translation($this->locale());
        $seoTranslation = $article->seoEntry?->translation($this->locale());
        $content = $translation?->content ?? [];
        if (is_string($content)) {
            $content = json_decode($content, true) ?? [];
        }

        return [
            'id' => $article->id,
            'slug' => $seoTranslation?->slug ?? '',
            'slugFull' => $seoTranslation?->slug_full ?? null,
            'title' => $translation?->title ?? '',
            'countrySlug' => $countryTranslation?->slug ?? '',
            'country' => $countryTranslation?->name ?? '',
            'category' => $categoryTranslation?->name ?? '',
            'categorySlug' => $categoryTranslation?->slug ?? '',
            'tags' => $article->contentTypeTags->map(fn (ContentTypeTag $tag) => $tag->label)->values()->all(),
            'author' => $article->author_name ?? '',
            'publishedAt' => optional($article->published_at)?->format('d/m/Y') ?? '',
            'updatedAt' => optional($article->updated_at)?->format('d/m/Y') ?? '',
            'views' => $article->view_count,
            'rating' => (float) $article->rating,
            'ratingCount' => $article->rating_count,
            'excerpt' => $translation?->excerpt ?? '',
            'image' => $article->coverUrl('card'),
            'imageSrcset' => $article->coverSrcset(),
            'imageDetail' => $article->coverUrl('lg'),
            'imageDetailSrcset' => $article->coverSrcset(),
            'content' => is_array($content) ? $content : [],
            'faqs' => $article->faqs->where('is_active', true)->map(fn (Faq $faq) => [
                'q' => apply_site_brand($faq->question),
                'a' => apply_site_brand($faq->answer),
            ])->values()->all(),
            'gallery' => $article->mediaAttachments
                ->where('role', 'gallery')
                ->take(8)
                ->map(fn ($a) => media_payload($a->media, 'card'))
                ->filter(fn (array $p) => filled($p['src'] ?? null))
                ->values()
                ->all(),
            'galleryCount' => max(
                1,
                $article->mediaAttachments->where('role', 'gallery')->count()
                    ?: ($article->coverMedia() ? 1 : 4)
            ),
        ];
    }

    protected function formatDuration(int $days, int $nights): string
    {
        return "{$days} ngày {$nights} đêm";
    }

    protected function formatMoney(float $amount, string $currency = 'VND'): string
    {
        $code = strtoupper(trim($currency));
        if ($code === '' || $code === 'VND') {
            return function_exists('format_price_plain')
                ? format_price_plain($amount)
                : number_format($amount, 0, ',', '.').' ₫';
        }

        if (function_exists('format_price_plain') && function_exists('currency_manager')) {
            // Giá đã ở đúng đơn vị currency (không phải VND) — format trực tiếp
            return currency_manager()->format($amount, $code, false);
        }

        return number_format($amount, 2).' '.strtoupper($currency);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attachPriceTable(array $data, Model $priceable, bool $isDetail = true): array
    {
        $svc = app(PriceTableService::class);
        $data['priceTable'] = $isDetail ? $svc->publicPayload($priceable, $this->locale()) : null;

        $current = $data['priceFrom'] ?? null;
        if ($current === null || (float) $current <= 0) {
            $min = $svc->minAmount($priceable);
            if ($min !== null && $min > 0) {
                $currency = (string) ($data['currency'] ?? 'VND');
                $data['priceFrom'] = $min;
                $data['priceFormatted'] = $this->formatMoney($min, $currency);
            }
        }

        return $data;
    }

    /**
     * Gallery public (role=gallery), loại trừ ảnh trùng cover; kèm URL full cho lightbox.
     *
     * @return list<array<string, mixed>>
     */
    protected function mapGalleryAttachments(Package|Service $model, bool $includeCover = false): array
    {
        if (! $model->relationLoaded('mediaAttachments')) {
            $model->load('mediaAttachments.media');
        }

        $coverMediaId = $includeCover ? null : $model->coverMedia()?->id;

        return $model->mediaAttachments
            ->where('role', 'gallery')
            ->sortBy('sort')
            ->filter(fn ($a) => ! $coverMediaId || (int) $a->media_id !== (int) $coverMediaId)
            ->take((int) config('stay.crawl.max_images', 120))
            ->map(function ($a) {
                $card = media_payload($a->media, 'card');
                if (! filled($card['src'] ?? null)) {
                    return null;
                }
                $full = media_payload($a->media, 'lg');

                return array_merge($card, [
                    'full' => $full['src'] ?? $card['src'],
                    'fullSrcset' => $full['srcset'] ?? ($card['srcset'] ?? null),
                    'type' => 'image',
                    'title' => filled($a->caption) ? (string) $a->caption : (string) ($a->media?->alt ?? ''),
                    'caption' => $a->caption,
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function galleryAttachmentCount(Package|Service $model): int
    {
        if (! $model->relationLoaded('mediaAttachments')) {
            $model->load('mediaAttachments');
        }

        return max(0, $model->mediaAttachments->where('role', 'gallery')->count());
    }

    /**
     * Ảnh hotlink từ crawler (Booking CDN) khi chưa upload media_attachments.
     *
     * @param  list<mixed>  $photos
     * @return list<array<string, mixed>>
     */
    protected function mapStayRemotePhotos(array $photos): array
    {
        $out = [];
        $seen = [];
        foreach ($photos as $photo) {
            $url = '';
            $alt = '';
            $mediaId = 0;
            if (is_string($photo)) {
                $url = $photo;
            } elseif (is_array($photo)) {
                $url = (string) ($photo['url'] ?? $photo['src'] ?? $photo['full'] ?? '');
                $alt = (string) ($photo['alt'] ?? $photo['title'] ?? '');
                $mediaId = (int) ($photo['media_id'] ?? 0);
            }
            if (! \App\Support\StayFacilities::shouldExposePublicPhoto($url, $mediaId)) {
                continue;
            }
            // Có media_id → luôn lấy URL GCS từ Media (tránh hotlink Booking còn trong attrs).
            if ($mediaId > 0) {
                $media = Media::query()->find($mediaId);
                $resolved = $this->absoluteStayPhotoUrl((string) ($media?->url('lg') ?: $media?->url('card') ?: ''));
                if ($resolved !== '') {
                    $url = $resolved;
                }
                if ($alt === '') {
                    $alt = (string) ($media?->alt ?? '');
                }
            }
            $url = $this->absoluteStayPhotoUrl(trim($url));
            if ($url === '') {
                continue;
            }
            $key = $this->normalizeStayGalleryUrl($url);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = [
                'src' => $url,
                'srcset' => null,
                'full' => $url,
                'fullSrcset' => null,
                'type' => 'image',
                'title' => $alt,
                'caption' => null,
            ];
        }

        return array_slice($out, 0, (int) config('stay.crawl.max_images', 120));
    }

    /**
     * Gộp gallery từ media_attachments với ảnh hotlink/GCS trong attrs.photos.
     *
     * @param  list<array<string, mixed>>  $attached
     * @param  list<array<string, mixed>>  $remote
     * @return list<array<string, mixed>>
     */
    protected function mergeStayGallery(array $attached, array $remote): array
    {
        if ($remote === []) {
            return $attached;
        }

        $merged = [];
        $seen = [];
        foreach ($attached as $item) {
            $url = (string) ($item['src'] ?? $item['full'] ?? '');
            if ($url === '') {
                continue;
            }
            $seen[$this->normalizeStayGalleryUrl($url)] = true;
            $merged[] = $item;
        }

        foreach ($remote as $item) {
            $url = (string) ($item['src'] ?? $item['full'] ?? '');
            if ($url === '') {
                continue;
            }
            $key = $this->normalizeStayGalleryUrl($url);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $merged[] = $item;
        }

        return array_slice($merged, 0, (int) config('stay.crawl.max_images', 120));
    }

    protected function absoluteStayPhotoUrl(string $url): string
    {
        $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($url === '' || str_starts_with($url, 'data:')) {
            return '';
        }
        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            $url = rtrim((string) config('app.url'), '/').$url;
        }
        if (! preg_match('#^https?://#i', $url)) {
            return '';
        }

        return $url;
    }

    protected function normalizeStayGalleryUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return preg_replace('/-(thumb|card|sm|md|lg|xl)(\.[a-z0-9]+)$/i', '$2', $path) ?: $path;
    }

    protected function countryFlag(?string $code): string
    {
        $flags = [
            'VN' => '🇻🇳', 'AU' => '🇦🇺', 'FR' => '🇫🇷', 'IT' => '🇮🇹', 'US' => '🇺🇸',
            'GB' => '🇬🇧', 'DE' => '🇩🇪', 'KH' => '🇰🇭', 'TH' => '🇹🇭', 'LA' => '🇱🇦',
        ];

        return $flags[strtoupper((string) $code)] ?? '🌍';
    }
}
