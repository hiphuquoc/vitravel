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
use App\Models\HomeFeaturedReviewPlatform;
use App\Models\HomeFeaturedCruise;
use App\Models\HomeFeaturedTour;
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
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Models\StaticPage;
use App\Models\TeamMember;
use App\Models\TravelStyle;
use App\Models\Usp;
use App\Support\ProjectSeed;
use App\Support\SampleData;

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
        if (! Country::query()->exists()) {
            return SampleData::country($slug);
        }

        $country = Country::query()
            ->active()
            ->with(['translations', 'banner', 'listingBanner'])
            ->whereHas('translations', fn ($q) => $q->where('language_id', $this->languageId())->where('slug', $slug))
            ->first();

        return $country ? $this->mapCountry($country) : null;
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
            return SampleData::homeSections();
        }

        return HomeSection::query()
            ->active()
            ->with(['translations', 'image'])
            ->get()
            ->mapWithKeys(fn (HomeSection $section) => [$section->key => $this->mapHomeSection($section)])
            ->all();
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

            return $this->mapHomeSection($section);
        }

        return SampleData::homeSection($key);
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
     * Dịch vụ nổi bật theo cụm (home / merchandising) — ưu tiên is_featured, fallback sort.
     *
     * @return list<array<string, mixed>>
     */
    public function featuredServices(string $cluster, int $limit = 3): array
    {
        $limit = max(1, min(12, $limit));

        if (! config("services_catalog.clusters.{$cluster}")) {
            return [];
        }

        $query = Service::query()
            ->published()
            ->forCluster($cluster)
            ->with([
                'translations', 'category', 'country.translations',
                'seoEntry.translations', 'options.translations', 'faqs.translations',
                'mediaAttachments.media',
            ]);

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

    public function cruiseTypes(): array
    {
        $types = CruiseType::query()->active()->with('banner')->get();

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
        $cardImage = $type->bannerUrl('card');
        $heroImage = $type->bannerUrl('lg') ?: $type->bannerUrl('full') ?: $cardImage;

        return [
            'slug' => $type->slug,
            'name' => $type->name,
            'count' => Package::query()->published()->cruises()->where('cruise_type', $type->slug)->count(),
            'image' => $cardImage,
            'imageHero' => $heroImage,
            'imageSrcset' => $type->bannerSrcset(),
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

        $category = BlogCategory::query()
            ->where('is_active', true)
            ->whereHas('translations', fn ($q) => $q->where('language_id', $this->languageId())->where('slug', $categorySlug))
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
            ],
            'reasons_section' => [
                'title' => $nested($profile->reasons_section_title, 'reasons_section', 'title'),
                'cta_label' => $nested($profile->reasons_cta_label, 'reasons_section', 'cta_label'),
                'cta_url' => filled($profile->reasons_cta_url)
                    ? (string) $profile->reasons_cta_url
                    : null,
                'image' => $profile->mediaUrl('reasonsImage', 'lg'),
                'imageSrcset' => $profile->mediaSrcset('reasonsImage'),
            ],
            'reference_section' => [
                'title' => $nested($profile->reference_section_title, 'reference_section', 'title'),
                'subtitle' => $nested($profile->reference_section_subtitle, 'reference_section', 'subtitle'),
            ],
        ];
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
        $query = ServiceCategory::query()->active()->with('banner');
        if ($cluster) {
            $query->forCluster($cluster);
        }

        $rows = $query->withCount(['services' => fn ($q) => $q->published()])->get();
        if ($rows->isEmpty()) {
            return SampleData::serviceCategories($cluster);
        }

        return $rows->map(fn (ServiceCategory $cat) => $this->mapServiceCategory($cat))->values()->all();
    }

    public function serviceCategory(string $cluster, string $slug): ?array
    {
        $cat = ServiceCategory::query()
            ->active()
            ->forCluster($cluster)
            ->where('slug', $slug)
            ->with(['banner'])
            ->withCount(['services' => fn ($q) => $q->published()])
            ->first();

        if ($cat) {
            return $this->mapServiceCategory($cat);
        }

        return SampleData::serviceCategory($cluster, $slug);
    }

    /** @return list<array<string, mixed>> */
    public function services(?string $cluster = null): array
    {
        $query = Service::query()
            ->published()
            ->with([
                'translations', 'category', 'country.translations',
                'seoEntry.translations', 'options.translations', 'faqs.translations',
                'mediaAttachments.media',
            ])
            ->orderBy('sort')
            ->orderByDesc('id');

        if ($cluster) {
            $query->forCluster($cluster);
        }

        if (! Service::query()->published()->when($cluster, fn ($q) => $q->forCluster($cluster))->exists()) {
            return SampleData::services($cluster);
        }

        return $query->get()->map(fn (Service $s) => $this->mapService($s))->values()->all();
    }

    public function service(string $slug, ?string $cluster = null): ?array
    {
        $query = Service::query()
            ->published()
            ->with([
                'translations', 'category', 'country.translations',
                'seoEntry.translations', 'options.translations', 'faqs.translations',
                'mediaAttachments.media',
            ]);

        if ($cluster) {
            $query->forCluster($cluster);
        }

        $service = $query->get()->first(function (Service $s) use ($slug) {
            $seoSlug = $s->seoEntry?->translation($this->locale())?->slug;

            return $seoSlug === $slug || $s->code === $slug;
        });

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
        return [
            'slug' => $cat->slug,
            'name' => $cat->name,
            'intro' => $cat->intro,
            'cluster' => $cat->cluster,
            'count' => (int) ($cat->services_count ?? $cat->services()->published()->count()),
            'imageHero' => $cat->bannerUrl('lg') ?: $cat->bannerUrl('card'),
            'imageSrcset' => $cat->bannerSrcset(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapService(Service $service): array
    {
        $translation = $service->translation($this->locale());
        $seoTranslation = $service->seoEntry?->translation($this->locale());
        $category = $service->category;
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

        return [
            'slug' => $seoTranslation?->slug ?? ($service->code ?? ''),
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
            'options' => $service->options->map(fn ($opt) => [
                'code' => $opt->code,
                'name' => $opt->name,
                'description' => $opt->description,
                'priceFrom' => $opt->price_from !== null ? (float) $opt->price_from : null,
                'priceFormatted' => $opt->price_from !== null && (float) $opt->price_from > 0
                    ? $this->formatMoney((float) $opt->price_from, $service->currency ?? 'VND')
                    : null,
                'capacity' => $opt->capacity,
                'amenities' => $opt->amenities ?? [],
            ])->values()->all(),
            'faqs' => $service->faqs->where('is_active', true)->map(fn (Faq $faq) => [
                'q' => $faq->question,
                'a' => $faq->answer,
            ])->values()->all(),
            'quote' => $this->serviceQuote($service, $translation),
            'styles' => [],
            'gallery' => [],
            'galleryCount' => 0,
        ];
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
                ['text' => 'Đặt vé tàu qua ViTravel rất nhanh, e-ticket rõ ràng và hỗ trợ đổi ngày linh hoạt.', 'author' => 'Anh Tuấn'],
                ['text' => 'Ghế mềm êm, lên tàu đúng hướng dẫn — tiết kiệm được một đêm khách sạn so với bay.', 'author' => 'Chị Hương'],
                ['text' => 'Nhân viên tư vấn rõ lịch SE và giao vé tận nơi đúng hẹn.', 'author' => 'Anh Đức'],
            ],
            'flight' => [
                ['text' => 'Giá vé máy bay minh bạch, xác nhận nhanh và hỗ trợ chọn giờ bay hợp lịch trình.', 'author' => 'Chị Mai'],
                ['text' => 'Đặt combo bay + tour rất tiện, không phải tự so sánh nhiều hãng.', 'author' => 'Anh Khoa'],
                ['text' => 'Đổi lịch bay được hỗ trợ kịp thời trước ngày khởi hành.', 'author' => 'Chị Lan'],
            ],
            'stay' => [
                ['text' => 'Resort đúng như mô tả, phòng sạch và view đẹp — book qua ViTravel được giá tốt.', 'author' => 'Gia đình Anh Nam'],
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
                ['text' => 'Đặt qua ViTravel tiện hơn tự tìm — có người đồng hành khi cần hỗ trợ.', 'author' => 'Chị Ngọc'],
                ['text' => 'Phản hồi nhanh, điều chỉnh theo nhu cầu đoàn rất linh hoạt.', 'author' => 'Anh Việt'],
            ],
        };

        $index = abs(crc32((string) ($service->code ?: $service->id))) % count($pool);

        return $pool[$index];
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
     * @return array{title: string, subtitle: string, listingBanner: ?string, listingBannerSrcset: ?string, seoTitle: ?string, seoDescription: ?string}
     */
    public function listingHub(string $hubKey): array
    {
        $cfg = config("seo.hubs.{$hubKey}");
        if (! is_array($cfg)) {
            return [
                'title' => 'Hub',
                'subtitle' => '',
                'listingBanner' => null,
                'listingBannerSrcset' => null,
                'seoTitle' => null,
                'seoDescription' => null,
            ];
        }

        $this->seoService()->ensureHub($hubKey, $this->locale());

        $page = \App\Models\StaticPage::query()
            ->with(['translations', 'banner', 'seoEntry.translations'])
            ->where('template', $cfg['template'])
            ->first();

        if (! $page) {
            return [
                'title' => $cfg['default_title'] ?? 'Hub',
                'subtitle' => $cfg['default_subtitle'] ?? '',
                'listingBanner' => null,
                'listingBannerSrcset' => null,
                'seoTitle' => $cfg['default_seo_title'] ?? null,
                'seoDescription' => $cfg['default_seo_description'] ?? null,
            ];
        }

        $translation = $page->translation($this->locale());
        $seoTrans = $page->seoEntry?->translation($this->locale());
        $banner = $page->bannerUrl('lg') ?: $page->bannerUrl('full');
        $subtitleRaw = $translation?->body ?: ($cfg['default_subtitle'] ?? '');

        return [
            'title' => $translation?->title ?: ($cfg['default_title'] ?? 'Hub'),
            'subtitle' => trim(html_entity_decode(strip_tags((string) $subtitleRaw), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'listingBanner' => $banner,
            'listingBannerSrcset' => $page->bannerSrcset(),
            'seoTitle' => $seoTrans?->seo_title ?? ($cfg['default_seo_title'] ?? null),
            'seoDescription' => $seoTrans?->seo_description ?? ($cfg['default_seo_description'] ?? null),
        ];
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
            return SampleData::galleryAlbums();
        }

        return ExperienceAlbum::query()
            ->where('status', 'published')
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->map(fn (ExperienceAlbum $album) => [
                'title' => $album->title,
                'photos' => $album->photo_count,
                'date' => optional($album->trip_date)?->format('m/Y') ?? '',
            ])
            ->values()
            ->all();
    }

    public function videos(bool $homeOnly = false, int $limit = 24): array
    {
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
     * @return array{title: string, description: ?string, date: string, duration: ?string, tag: ?string, image: ?string, imageSrcset: ?string, embedUrl: ?string, provider: ?string, youtubeId: ?string}
     */
    protected function mapVideo(ExperienceVideo $video): array
    {
        return [
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
                'itineraryDays.translations',
                'cabinTypes.translations',
                'faqs.translations',
                'mediaAttachments.media',
                'seoEntry.translations',
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
        return Country::query()
            ->with('translations')
            ->whereHas('translations', fn ($q) => $q->where('language_id', $this->languageId())->where('slug', $slug))
            ->first();
    }

    protected function findPackageBySlug(string $slug, string $type): ?Package
    {
        return $this->packageQuery($type)
            ->whereHas('seoEntry.translations', fn ($q) => $q
                ->where('language_id', $this->languageId())
                ->where('slug', $slug))
            ->first();
    }

    protected function findArticleBySlug(string $slug): ?Article
    {
        return $this->articleQuery()
            ->whereHas('seoEntry.translations', fn ($q) => $q
                ->where('language_id', $this->languageId())
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

        $data = [
            'slug' => $seoTranslation?->slug ?? '',
            'title' => $translation?->title ?? '',
            'countrySlug' => $primarySlug,
            'countrySlugs' => $countrySlugs,
            'country' => $countryTranslation?->name ?? '',
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
                'q' => $faq->question,
                'a' => $faq->answer,
            ])->values()->all(),
            'gallery' => (function () use ($package) {
                $coverMediaId = $package->coverMedia()?->id;

                return $package->mediaAttachments
                    ->where('role', 'gallery')
                    ->filter(fn ($a) => ! $coverMediaId || (int) $a->media_id !== (int) $coverMediaId)
                    ->take(8)
                    ->map(fn ($a) => media_payload($a->media, 'card'))
                    ->filter(fn (array $p) => filled($p['src'] ?? null))
                    ->values()
                    ->all();
            })(),
            'galleryCount' => max(
                0,
                $package->mediaAttachments->where('role', 'gallery')->count()
            ),
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
            $data['cabinTypes'] = $package->cabinTypes->map(fn ($cabin) => [
                'name' => $cabin->name,
                'capacity' => $cabin->capacity,
                'note' => $cabin->description,
            ])->values()->all();
        }

        return $data;
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
                'q' => $faq->question,
                'a' => $faq->answer,
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

    protected function countryFlag(?string $code): string
    {
        $flags = [
            'VN' => '🇻🇳', 'AU' => '🇦🇺', 'FR' => '🇫🇷', 'IT' => '🇮🇹', 'US' => '🇺🇸',
            'GB' => '🇬🇧', 'DE' => '🇩🇪', 'KH' => '🇰🇭', 'TH' => '🇹🇭', 'LA' => '🇱🇦',
        ];

        return $flags[strtoupper((string) $code)] ?? '🌍';
    }
}
