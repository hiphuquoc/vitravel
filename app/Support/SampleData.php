<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Read-only accessors for demo / fallback UI data — backed by project/seed.php via ProjectSeed.
 */
class SampleData
{
    public static function countries(): array
    {
        return ProjectSeed::get('countries', []);
    }

    public static function country(string $slug): ?array
    {
        return Arr::first(static::countries(), fn ($c) => $c['slug'] === $slug);
    }

    /** @param  array<string, mixed>  $item */
    public static function enrichPackagePrice(array $item): array
    {
        if (! empty($item['priceFormatted'])) {
            return $item;
        }

        $amount = isset($item['priceFrom'])
            ? (float) $item['priceFrom']
            : (float) (($item['days'] ?? 7) * 2_800_000);

        $currency = $item['currency'] ?? 'VND';
        $item['priceFrom'] = $amount;
        $item['currency'] = $currency;
        $item['priceFormatted'] = self::formatMoney($amount, $currency);

        return $item;
    }

    public static function formatMoney(float $amount, string $currency = 'VND'): string
    {
        if (function_exists('format_price_plain') && strtoupper($currency) === 'VND') {
            return format_price_plain($amount);
        }

        if (function_exists('currency_manager')) {
            return currency_manager()->format($amount, strtoupper($currency), false);
        }

        if (strtoupper($currency) === 'VND') {
            return number_format($amount, 0, ',', '.').' ₫';
        }

        return number_format($amount, 2).' '.strtoupper($currency);
    }

    public static function travelStyles(): array
    {
        return ProjectSeed::get('travel_style_labels', []);
    }

    public static function durationBuckets(): array
    {
        return ProjectSeed::get('duration_buckets', []);
    }

    public static function tours(): array
    {
        return array_map([self::class, 'enrichPackagePrice'], ProjectSeed::get('tours', []));
    }

    public static function tour(string $slug): ?array
    {
        return Arr::first(static::tours(), fn ($t) => $t['slug'] === $slug);
    }

    public static function featuredTours(int $limit = 3): array
    {
        return array_slice(array_values(array_filter(static::tours(), fn ($t) => $t['featured'] ?? false)), 0, $limit);
    }

    public static function featuredCruises(int $limit = 3): array
    {
        return array_slice(static::cruises(), 0, $limit);
    }

    public static function toursByCountry(string $countrySlug): array
    {
        return array_values(array_filter(static::tours(), function ($t) use ($countrySlug) {
            $slugs = $t['countrySlugs'] ?? [($t['countrySlug'] ?? '')];

            return in_array($countrySlug, $slugs, true);
        }));
    }

    public static function cruiseTypes(): array
    {
        return ProjectSeed::get('cruise_types', []);
    }

    public static function cruises(): array
    {
        return array_map([self::class, 'enrichPackagePrice'], ProjectSeed::get('cruises', []));
    }

    public static function cruise(string $slug): ?array
    {
        return Arr::first(static::cruises(), fn ($c) => $c['slug'] === $slug);
    }

    public static function blogCategories(): array
    {
        return ProjectSeed::get('blog_categories', []);
    }

    public static function contentTags(): array
    {
        $map = ProjectSeed::get('content_tag_map', []);
        if (is_array($map) && $map !== []) {
            return array_keys($map);
        }

        $tags = ProjectSeed::get('content_tags', []);
        $out = [];
        foreach ($tags as $row) {
            if (is_array($row) && isset($row['vi'])) {
                $out[] = $row['vi'];
            } elseif (is_string($row)) {
                $out[] = $row;
            }
        }

        return $out;
    }

    public static function popularKeywords(): array
    {
        return ProjectSeed::get('popular_keywords', []);
    }

    public static function articles(): array
    {
        return ProjectSeed::get('articles', []);
    }

    public static function article(string $slug): ?array
    {
        return Arr::first(static::articles(), fn ($a) => $a['slug'] === $slug);
    }

    public static function articlesByCountry(string $countrySlug): array
    {
        return array_values(array_filter(static::articles(), fn ($a) => ($a['countrySlug'] ?? '') === $countrySlug));
    }

    public static function testimonials(): array
    {
        return ProjectSeed::get('testimonials', []);
    }

    public static function reviewPlatforms(): array
    {
        return array_map(static function (array $platform): array {
            return [
                'name' => $platform['name'],
                'rating' => (float) ($platform['rating'] ?? 0),
                'quote' => $platform['quote'] ?? '',
                'link' => $platform['link_label'] ?? '',
                'url' => $platform['url'] ?? '#',
            ];
        }, ProjectSeed::get('review_platforms', []));
    }

    public static function team(): array
    {
        return ProjectSeed::get('team', []);
    }

    public static function videos(): array
    {
        return ProjectSeed::get('videos', []);
    }

    public static function galleryAlbums(): array
    {
        return ProjectSeed::get('gallery_albums', []);
    }

    public static function usps(): array
    {
        $locale = app()->getLocale();
        $code = $locale === 'en' ? 'en' : 'vi';

        return array_map(static fn (array $row) => [
            'icon' => $row['icon'],
            'title' => $row[$code]['title'],
            'desc' => $row[$code]['description'],
        ], ProjectSeed::get('usps', []));
    }

    public static function offices(): array
    {
        return ProjectSeed::get('offices', []);
    }

    public static function values(): array
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'vi';

        return array_map(static fn (array $row) => [
            'name' => $row[$locale]['name'] ?? $row['vi']['name'],
            'desc' => $row[$locale]['desc'] ?? $row['vi']['desc'],
        ], static::valueDefinitions());
    }

    /** @return list<array{vi: array{name: string, desc: string}, en: array{name: string, desc: string}}> */
    public static function valueDefinitions(): array
    {
        return ProjectSeed::get('value_definitions', []);
    }

    public static function reasons(): array
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'vi';

        return array_map(static fn (array $row) => [
            'title' => $row[$locale]['title'] ?? $row['vi']['title'],
            'desc' => $row[$locale]['desc'] ?? $row['vi']['desc'],
        ], static::reasonDefinitions());
    }

    /** @return list<array{vi: array{title: string, desc: string}, en: array{title: string, desc: string}}> */
    public static function reasonDefinitions(): array
    {
        return ProjectSeed::get('reason_definitions', []);
    }

    public static function referencePersons(): array
    {
        return ProjectSeed::get('reference_persons', []);
    }

    /** @return array<string, mixed> */
    public static function aboutPage(): array
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'vi';
        $pages = ProjectSeed::get('about_page', []);

        return $pages[$locale] ?? $pages['vi'] ?? [];
    }

    public static function heroPills(): array
    {
        $locale = app()->getLocale();
        $code = $locale === 'en' ? 'en' : 'vi';

        return array_map(static fn (array $row) => [
            'label' => $row[$code]['label'] ?? $row['vi']['label'],
            'url' => $row['url'],
        ], ProjectSeed::get('hero_pills', []));
    }

    /** @return list<array<string, mixed>> */
    public static function homeSlides(): array
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'vi';

        return array_map(static function (array $row) use ($locale): array {
            $t = $row[$locale] ?? $row['vi'];

            return [
                'image' => null,
                'imageMobile' => null,
                'imageAlt' => $t['image_alt'] ?? '',
                'title' => $t['title'] ?? '',
                'titleAccent' => $t['title_accent'] ?? '',
                'description' => $t['description'] ?? '',
                'buttonLabel' => $t['button_label'] ?? null,
                'linkUrl' => $row['link_url'] ?? null,
                'textAlign' => $row['text_align'] ?? 'center',
            ];
        }, ProjectSeed::get('home_slides', []));
    }

    /** @return array<string, array<string, mixed>> */
    public static function homeSections(): array
    {
        $locale = app()->getLocale();
        $sections = [];

        foreach (ProjectSeed::get('home_sections', []) as $key => $byLocale) {
            $sections[$key] = $byLocale[$locale] ?? $byLocale['vi'];
        }

        return $sections;
    }

    /** @return array<string, mixed> */
    public static function homeSection(string $key): array
    {
        return static::homeSections()[$key] ?? [];
    }

    public static function footerColumns(): array
    {
        return ProjectSeed::get('footer_columns', []);
    }

    public static function footerSeoLinks(): array
    {
        return ProjectSeed::get('footer_seo_links', []);
    }

    public static function tourCategories(): array
    {
        return ProjectSeed::get('tour_categories', []);
    }

    /** @return array<string, array{vi: string, en: string, tagline: array{vi: string, en: string}}> */
    public static function countryTranslations(): array
    {
        return ProjectSeed::get('country_translations', []);
    }

    public static function listingFaqs(): array
    {
        return ProjectSeed::get('listing_faqs', []);
    }
}
