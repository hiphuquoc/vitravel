<?php

namespace App\Services;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\CompanyValue;
use App\Models\ContentTypeTag;
use App\Models\Country;
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
use App\Models\StaticPage;
use App\Models\TeamMember;
use App\Models\TravelStyle;
use App\Models\Usp;
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
            ->with(['translations', 'banner', 'packages' => fn ($q) => $q->published()->tours()])
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
            ->with(['translations', 'banner'])
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
            ->where('country_id', $country->id)
            ->get()
            ->map(fn (Package $package) => $this->mapPackage($package))
            ->values()
            ->all();
    }

    public function cruiseTypes(): array
    {
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
            ])
            ->values()
            ->all();
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
            return SampleData::team();
        }

        return TeamMember::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with(['translations', 'avatar'])
            ->get()
            ->map(fn (TeamMember $member) => [
                'name' => $member->name,
                'role' => $member->role,
                'bio' => $member->short_bio,
                'image' => $member->avatarUrl('thumb'),
                'imageSrcset' => $member->avatarSrcset(),
            ])
            ->values()
            ->all();
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
            ->with('country.translations')
            ->get()
            ->map(fn (ReferencePerson $person) => [
                'name' => $person->name,
                'country' => $person->country?->name ?? '',
                'email' => $person->email ?? '',
                'phone' => $person->phone ?? '',
                'skype' => $person->skype ?? '',
            ])
            ->values()
            ->all();
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

        return [
            'slug' => $translation?->slug ?? '',
            'name' => $translation?->name ?? '',
            'size' => $country->home_grid_size === 'large' ? 'large' : 'normal',
            'tourCount' => $country->relationLoaded('packages')
                ? $country->packages->count()
                : $country->packages()->published()->tours()->count(),
            'tagline' => $translation?->tagline ?? '',
            'image' => $cardImage,
            'imageHero' => $heroImage,
            'imageSrcset' => $country->bannerSrcset(),
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

        $data = [
            'slug' => $seoTranslation?->slug ?? '',
            'title' => $translation?->title ?? '',
            'countrySlug' => $countryTranslation?->slug ?? '',
            'country' => $countryTranslation?->name ?? '',
            'tourCode' => $package->code ?? '',
            'duration' => $this->formatDuration($package->duration_days, $package->duration_nights),
            'days' => $package->duration_days,
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
            'gallery' => $package->mediaAttachments
                ->where('role', 'gallery')
                ->take(8)
                ->map(fn ($a) => media_payload($a->media, 'card'))
                ->filter(fn (array $p) => filled($p['src'] ?? null))
                ->values()
                ->all(),
            'galleryCount' => max(
                1,
                $package->mediaAttachments->where('role', 'gallery')->count()
                    ?: ($package->coverMedia() ? 1 : 4)
            ),
        ];

        if ($isCruise) {
            $typeLabels = [
                'du-thuyen-ha-long' => 'Du thuyền Hạ Long',
                'du-thuyen-mekong' => 'Du thuyền Mekong',
                'du-thuyen-lan-ha' => 'Du thuyền Lan Hạ',
            ];
            $typeSlug = $package->cruise_type ?? '';

            $data['typeSlug'] = $typeSlug;
            $data['typeName'] = $typeLabels[$typeSlug] ?? $typeSlug;
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

    protected function countryFlag(?string $code): string
    {
        $flags = [
            'VN' => '🇻🇳', 'AU' => '🇦🇺', 'FR' => '🇫🇷', 'IT' => '🇮🇹', 'US' => '🇺🇸',
            'GB' => '🇬🇧', 'DE' => '🇩🇪', 'KH' => '🇰🇭', 'TH' => '🇹🇭', 'LA' => '🇱🇦',
        ];

        return $flags[strtoupper((string) $code)] ?? '🌍';
    }
}
