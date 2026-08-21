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
        $countries = ProjectSeed::get('countries', []);
        if (is_array($countries) && $countries !== []) {
            return $countries;
        }

        $zones = ProjectSeed::get('zones', []);

        return is_array($zones) ? $zones : [];
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

    /** @return list<array<string, mixed>> */
    public static function featuredServices(string $cluster, int $limit = 3): array
    {
        $all = static::services($cluster);
        $featured = array_values(array_filter($all, fn ($s) => $s['isFeatured'] ?? false));
        $pool = $featured !== [] ? $featured : $all;

        return array_slice($pool, 0, max(1, min(12, $limit)));
    }

    public static function toursByCountry(string $countrySlug): array
    {
        return array_values(array_filter(static::tours(), function ($t) use ($countrySlug) {
            $slugs = $t['countrySlugs'] ?? $t['zoneSlugs'] ?? [($t['countrySlug'] ?? $t['zoneSlug'] ?? '')];

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
        $pages = ProjectSeed::get('about_page', []);

        return LocaleContent::pick($pages, null, $pages['vi'] ?? []);
    }

    public static function pageChrome(string $key): array
    {
        return apply_site_brand_deep(PageChromeDefaults::get($key));
    }

    public static function heroPills(): array
    {
        return array_map(static function (array $row): array {
            $labelRow = LocaleContent::pick([
                'vi' => $row['vi'] ?? null,
                'en' => $row['en'] ?? null,
            ], null, $row['vi'] ?? []);

            return [
                'label' => is_array($labelRow) ? ($labelRow['label'] ?? '') : '',
                'url' => $row['url'],
            ];
        }, ProjectSeed::get('hero_pills', []));
    }

    /** @return list<array<string, mixed>> */
    public static function homeSlides(): array
    {
        return array_map(static function (array $row): array {
            $t = LocaleContent::pick([
                'vi' => $row['vi'] ?? null,
                'en' => $row['en'] ?? null,
            ], null, $row['vi'] ?? []);

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
        $sections = [];

        foreach (ProjectSeed::get('home_sections', []) as $key => $byLocale) {
            $picked = LocaleContent::pick($byLocale, null, $byLocale['vi'] ?? []);
            $sections[$key] = is_array($picked) ? $picked : [];
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

    /** @return list<array<string, mixed>> */
    public static function serviceCategories(?string $cluster = null): array
    {
        $rows = ProjectSeed::get('service_categories', []);
        if (! is_array($rows)) {
            return [];
        }
        if ($cluster) {
            $rows = array_values(array_filter($rows, fn ($r) => ($r['cluster'] ?? '') === $cluster));
        }

        return array_map(fn ($r) => [
            'slug' => $r['slug'] ?? '',
            'name' => $r['name'] ?? '',
            'intro' => $r['intro'] ?? '',
            'cluster' => $r['cluster'] ?? '',
            'count' => count(array_filter(
                ProjectSeed::get('services', []),
                fn ($s) => ($s['category_slug'] ?? '') === ($r['slug'] ?? '') && ($s['cluster'] ?? '') === ($r['cluster'] ?? '')
            )),
            'imageHero' => null,
            'imageSrcset' => null,
        ], $rows);
    }

    public static function serviceCategory(string $cluster, string $slug): ?array
    {
        return Arr::first(static::serviceCategories($cluster), fn ($c) => $c['slug'] === $slug);
    }

    /** @return list<array<string, mixed>> */
    public static function services(?string $cluster = null): array
    {
        $rows = ProjectSeed::get('services', []);
        if (! is_array($rows)) {
            return [];
        }
        if ($cluster) {
            $rows = array_values(array_filter($rows, fn ($r) => ($r['cluster'] ?? '') === $cluster));
        }

        return array_map(function (array $row) {
            $price = isset($row['price_from']) ? (float) $row['price_from'] : null;
            $cfg = config('services_catalog.clusters.'.($row['cluster'] ?? ''), []);
            $attrs = is_array($row['attrs'] ?? null) ? $row['attrs'] : [];
            $isStay = ($row['cluster'] ?? '') === 'stay';
            $propertyType = (string) ($attrs['property_type'] ?? 'hotel');

            $base = [
                'slug' => $row['slug'] ?? '',
                'code' => $row['code'] ?? '',
                'title' => $row['title'] ?? '',
                'cluster' => $row['cluster'] ?? '',
                'clusterLabel' => $cfg['label'] ?? ($row['cluster'] ?? ''),
                'clusterIcon' => $cfg['icon'] ?? 'sparkles',
                'categorySlug' => $row['category_slug'] ?? '',
                'categoryName' => '',
                'countrySlug' => $row['country_slug'] ?? 'viet-nam',
                'location' => $row['location_label'] ?? '',
                'places' => array_values(array_filter([$row['location_label'] ?? null])),
                'start' => $attrs['from'] ?? '',
                'end' => $attrs['to'] ?? '',
                'duration' => ! empty($attrs['duration_hours'])
                    ? ((int) $attrs['duration_hours']).' giờ'
                    : ($isStay ? 'Theo đêm' : ($attrs['flight_time'] ?? '')),
                'priceFrom' => $price,
                'currency' => $row['currency'] ?? 'VND',
                'priceFormatted' => $price !== null && $price > 0
                    ? self::formatMoney($price, $row['currency'] ?? 'VND')
                    : ($price !== null ? 'Liên hệ' : null),
                'priceUnitLabel' => $isStay ? '/ đêm' : null,
                'rating' => (float) ($row['rating'] ?? 0),
                'reviewCount' => (int) ($row['review_count'] ?? 0),
                'starRating' => $row['star_rating'] ?? null,
                'badge' => $row['discount_badge'] ?? null,
                'isFeatured' => (bool) ($row['is_featured'] ?? false),
                'image' => null,
                'imageSrcset' => null,
                'imageDetail' => null,
                'summary' => $row['summary'] ?? '',
                'highlightsIntro' => $row['summary'] ?? '',
                'highlights' => $row['highlights'] ?? [],
                'inclusions' => $row['inclusions'] ?? [],
                'exclusions' => $row['exclusions'] ?? [],
                'notes' => $row['notes'] ?? [],
                'content' => $row['content'] ?? '',
                'attrs' => $attrs,
                'faqs' => $row['faqs'] ?? [],
                'quote' => self::serviceQuoteFromRow($row),
                'styles' => [],
                'gallery' => [],
                'galleryCount' => 0,
            ];

            if (! $isStay) {
                return $base;
            }

            $amenities = \App\Support\StayFacilities::stringList($attrs['amenities'] ?? null);
            $sections = \App\Support\StayFacilities::resolvePublicSections($attrs);
            $rooms = array_map(function (array $opt) use ($row) {
                return \App\Support\StayFacilities::mapRoom($opt, $row['currency'] ?? 'VND', function ($amount, $cur) {
                    return self::formatMoney($amount, $cur);
                });
            }, is_array($row['options'] ?? null) ? $row['options'] : []);

            $base['isStay'] = true;
            $base['propertyType'] = $propertyType;
            $base['propertyTypeLabel'] = config("stay.property_types.{$propertyType}") ?? ucfirst($propertyType);
            $base['address'] = (string) ($attrs['address'] ?? '');
            $base['checkIn'] = (string) ($attrs['check_in'] ?? '15:00');
            $base['checkOut'] = (string) ($attrs['check_out'] ?? '12:00');
            $base['amenities'] = [];
            $base['highlightBadges'] = \App\Support\StayFacilities::stringList(
                $attrs['highlight_badges'] ?? $attrs['most_popular'] ?? null,
            );
            $base['amenityGroups'] = $sections['amenityGroups'];
            $base['nearbyGroups'] = $sections['nearbyGroups'];
            $base['reviewScores'] = $sections['reviewScores'];
            $base['rooms'] = $rooms;
            $base['policies'] = [
                'check_in' => $base['checkIn'],
                'check_out' => $base['checkOut'],
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
            $base['featuredQuote'] = ($row['cluster'] ?? '') === 'stay'
                ? ['text' => '', 'author' => '']
                : $base['quote'];

            return $base;
        }, $rows);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{text: string, author: string}
     */
    protected static function serviceQuoteFromRow(array $row): array
    {
        $text = trim((string) ($row['quote']['text'] ?? ''));
        $author = trim((string) ($row['quote']['author'] ?? ''));
        if ($text !== '') {
            return ['text' => $text, 'author' => $author];
        }

        $pool = match ($row['cluster'] ?? '') {
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

        $index = abs(crc32((string) ($row['code'] ?? $row['slug'] ?? 'svc'))) % count($pool);
        $pick = $pool[$index];

        return [
            'text' => apply_site_brand($pick['text']),
            'author' => $pick['author'],
        ];
    }

    public static function service(string $slug, ?string $cluster = null): ?array
    {
        return Arr::first(
            static::services($cluster),
            fn ($s) => ($s['slug'] ?? '') === $slug || ($s['code'] ?? '') === $slug
        );
    }
}
