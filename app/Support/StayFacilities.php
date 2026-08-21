<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Nhóm tiện ích / nearby / hạng phòng — dùng chung public, seed fallback, crawler.
 */
final class StayFacilities
{
    /**
     * @param  list<string>  $flat
     * @param  array<string, mixed>|null  $explicit  keyed groups từ crawler
     * @return list<array{key: string, label: string, items: list<string>}>
     */
    public static function displayGroups(array $flat, ?array $explicit = null): array
    {
        $config = config('stay.amenity_groups', []);
        if (is_array($explicit) && $explicit !== []) {
            $out = [];
            foreach ($config as $key => $cfg) {
                $items = self::stringList($explicit[$key] ?? null);
                if ($items === []) {
                    continue;
                }
                $out[] = [
                    'key' => (string) $key,
                    'label' => (string) ($cfg['label'] ?? $key),
                    'items' => $items,
                ];
            }
            foreach ($explicit as $key => $items) {
                if (isset($config[$key])) {
                    continue;
                }
                $list = self::stringList($items);
                if ($list === []) {
                    continue;
                }
                $out[] = [
                    'key' => (string) $key,
                    'label' => self::groupLabel((string) $key, $config),
                    'items' => $list,
                ];
            }

            return $out;
        }

        $assigned = [];
        $result = [];
        foreach ($config as $key => $cfg) {
            if ($key === 'popular' || $key === 'other') {
                continue;
            }
            $needles = is_array($cfg['match'] ?? null) ? $cfg['match'] : [];
            if ($needles === []) {
                continue;
            }
            $items = [];
            foreach ($flat as $amenity) {
                $label = (string) $amenity;
                if ($label === '' || isset($assigned[$label])) {
                    continue;
                }
                $lower = mb_strtolower($label);
                foreach ($needles as $needle) {
                    if ($needle !== '' && str_contains($lower, mb_strtolower((string) $needle))) {
                        $items[] = $label;
                        $assigned[$label] = true;
                        break;
                    }
                }
            }
            if ($items !== []) {
                $result[] = [
                    'key' => (string) $key,
                    'label' => (string) ($cfg['label'] ?? $key),
                    'items' => array_values(array_unique($items)),
                ];
            }
        }

        $rest = array_values(array_filter(
            $flat,
            fn ($a) => (string) $a !== '' && empty($assigned[(string) $a]),
        ));
        if ($rest !== []) {
            $result[] = ['key' => 'other', 'label' => (string) ($config['other']['label'] ?? 'Khác'), 'items' => $rest];
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $nearby
     * @param  array<string, mixed>|null  $groups
     * @return list<array{key: string, label: string, items: list<array<string, mixed>>}>
     */
    public static function nearbyGroups(array $nearby, ?array $groups = null): array
    {
        $labels = config('stay.nearby_groups', []);
        if (is_array($groups) && $groups !== []) {
            $out = [];
            foreach ($labels as $key => $label) {
                $items = self::normalizeNearbyItems($groups[$key] ?? null);
                if ($items === []) {
                    continue;
                }
                $out[] = ['key' => (string) $key, 'label' => (string) $label, 'items' => $items];
            }
            foreach ($groups as $key => $items) {
                if (isset($labels[$key])) {
                    continue;
                }
                $normalized = self::normalizeNearbyItems($items);
                if ($normalized === []) {
                    continue;
                }
                $out[] = [
                    'key' => (string) $key,
                    'label' => self::groupLabel((string) $key, [], $labels),
                    'items' => $normalized,
                ];
            }

            return $out;
        }

        $bucket = [];
        foreach ($nearby as $place) {
            if (! is_array($place) || trim((string) ($place['name'] ?? '')) === '') {
                continue;
            }
            $cat = (string) ($place['category'] ?? $place['group'] ?? 'other');
            if (! isset($labels[$cat])) {
                $cat = 'other';
            }
            $bucket[$cat][] = $place;
        }
        if (count($bucket) <= 1) {
            return $nearby === []
                ? []
                : [['key' => 'other', 'label' => (string) ($labels['other'] ?? 'Lân cận'), 'items' => array_values($nearby)]];
        }

        $out = [];
        foreach ($labels as $key => $label) {
            if (empty($bucket[$key])) {
                continue;
            }
            $out[] = ['key' => (string) $key, 'label' => (string) $label, 'items' => $bucket[$key]];
        }

        return $out;
    }

    /**
     * Chuẩn hóa services.attrs trước khi lưu DB hoặc render public — một nguồn duy nhất.
     *
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public static function normalizeStayAttrs(array $attrs): array
    {
        $out = $attrs;

        foreach ([
            'amenity_groups_json' => 'amenity_groups',
            'nearby_groups_json' => 'nearby_groups',
            'review_scores_json' => 'review_scores',
            // Legacy flat list — promote rồi bỏ.
            'nearby_json' => 'nearby',
        ] as $from => $to) {
            if (! array_key_exists($from, $out)) {
                continue;
            }
            $raw = $out[$from];
            unset($out[$from]);
            if (($out[$to] ?? null) === null || $out[$to] === '' || $out[$to] === []) {
                if ($raw !== null && $raw !== '') {
                    $out[$to] = $raw;
                }
            }
        }

        foreach (['amenity_groups', 'nearby', 'nearby_groups', 'review_scores', 'amenities'] as $key) {
            if (! isset($out[$key]) || ! is_string($out[$key])) {
                continue;
            }
            $decoded = json_decode(trim($out[$key]), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $out[$key] = $decoded;
            }
        }

        $amenityGroups = self::normalizeAmenityGroups($out['amenity_groups'] ?? null);
        if ($amenityGroups !== null) {
            $out['amenity_groups'] = $amenityGroups;
        } else {
            unset($out['amenity_groups']);
        }

        $nearbyGroups = self::normalizeNearbyGroups($out['nearby_groups'] ?? null) ?? [];
        $legacyNearby = self::normalizeNearbyItems($out['nearby'] ?? []);
        if ($legacyNearby !== []) {
            $labels = config('stay.nearby_groups', []);
            foreach ($legacyNearby as $item) {
                $cat = (string) ($item['category'] ?? $item['group'] ?? 'other');
                if (! isset($labels[$cat])) {
                    $cat = 'other';
                }
                $nearbyGroups[$cat] ??= [];
                $nearbyGroups[$cat][] = $item;
            }
            $nearbyGroups = self::normalizeNearbyGroups($nearbyGroups) ?? [];
        }
        unset($out['nearby'], $out['nearby_json']);
        if ($nearbyGroups !== []) {
            $out['nearby_groups'] = $nearbyGroups;
        } else {
            unset($out['nearby_groups']);
        }

        $scores = self::normalizeReviewScores($out['review_scores'] ?? null);
        if ($scores !== null) {
            $out['review_scores'] = $scores;
        } else {
            unset($out['review_scores']);
        }

        return $out;
    }

    /**
     * Gộp attrs crawl/AI vào attrs service: lấy bản có nhiều nhóm/ảnh hơn, không ghi đè bằng popular-only.
     *
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overlay
     * @return array<string, mixed>
     */
    public static function overlayRicherStayAttrs(array $base, array $overlay): array
    {
        $base = self::normalizeStayAttrs($base);
        $overlay = self::normalizeStayAttrs($overlay);
        if ($overlay === []) {
            return $base;
        }

        $baseGroups = self::normalizeAmenityGroups($base['amenity_groups'] ?? null) ?? [];
        $overGroups = self::normalizeAmenityGroups($overlay['amenity_groups'] ?? null) ?? [];
        if (count($overGroups) > count($baseGroups)) {
            $base['amenity_groups'] = array_merge($baseGroups, $overGroups);
        }

        $baseNearbyGroups = self::normalizeNearbyGroups($base['nearby_groups'] ?? null) ?? [];
        $overNearbyGroups = self::normalizeNearbyGroups($overlay['nearby_groups'] ?? null) ?? [];
        if (count($overNearbyGroups) > count($baseNearbyGroups)) {
            $base['nearby_groups'] = array_merge($baseNearbyGroups, $overNearbyGroups);
        }
        unset($base['nearby'], $overlay['nearby']);

        $baseScores = self::normalizeReviewScores($base['review_scores'] ?? null) ?? [];
        $overScores = self::normalizeReviewScores($overlay['review_scores'] ?? null) ?? [];
        if (count($overScores) > count($baseScores)) {
            $base['review_scores'] = $overScores;
        }

        $basePhotos = is_array($base['photos'] ?? null) ? $base['photos'] : [];
        $overPhotos = is_array($overlay['photos'] ?? null) ? $overlay['photos'] : [];
        $baseMedia = self::countMediaBackedPhotos($basePhotos);
        $overMedia = self::countMediaBackedPhotos($overPhotos);
        // Ưu tiên gallery đã upload media — không để hotlink crawl đè mất ảnh GCS.
        if ($overMedia > $baseMedia || ($overMedia === $baseMedia && count($overPhotos) > count($basePhotos) && $baseMedia === 0)) {
            $base['photos'] = $overPhotos;
        }

        return $base;
    }

    public static function cleanScrapedLabel(string $text): string
    {
        $text = preg_replace('/\+?\s*Hiển thị giá.*$/iu', '', $text) ?? $text;
        $text = preg_replace('/\s*Hiển thị giá\s*/iu', ' ', $text) ?? $text;

        return trim($text, " \t\n\r\0\x0B+");
    }

    /**
     * Payload public cho tab Tiện ích / Vị trí / điểm đánh giá.
     *
     * @param  array<string, mixed>  $attrs
     * @return array{
     *   amenityGroups: list<array{key: string, label: string, items: list<string>}>,
     *   nearbyGroups: list<array{key: string, label: string, items: list<array<string, mixed>>}>,
     *   reviewScores: list<array{key: string, label: string, score: float, tag: string}>
     * }
     */
    public static function resolvePublicSections(array $attrs): array
    {
        $attrs = self::normalizeStayAttrs($attrs);
        $flatAmenities = self::stringList($attrs['amenities'] ?? null);
        $amenityGroupsRaw = self::normalizeAmenityGroups($attrs['amenity_groups'] ?? null);
        if ($amenityGroupsRaw === null && $flatAmenities !== []) {
            $amenityGroupsRaw = ['popular' => $flatAmenities];
        }

        $nearbyGroupsRaw = self::normalizeNearbyGroups($attrs['nearby_groups'] ?? null);

        return [
            'amenityGroups' => self::displayGroups($flatAmenities, $amenityGroupsRaw),
            'nearbyGroups' => self::nearbyGroups([], $nearbyGroupsRaw),
            'reviewScores' => self::reviewScores($attrs),
        ];
    }

    /**
     * Chuẩn hóa attrs.amenity_groups từ crawler / admin JSON (object, list, chuỗi JSON).
     *
     * @return array<string, list<string>>|null
     */
    public static function normalizeAmenityGroups(mixed $raw): ?array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return null;
        }
        if (is_string($raw)) {
            $decoded = json_decode(trim($raw), true);
            if (! is_array($decoded)) {
                $items = self::stringList($raw);

                return $items !== [] ? ['popular' => $items] : null;
            }
            $raw = $decoded;
        }
        if (! is_array($raw)) {
            return null;
        }

        if (array_is_list($raw)) {
            $out = [];
            foreach ($raw as $i => $block) {
                if (! is_array($block)) {
                    $label = self::itemLabel($block);
                    if ($label !== '') {
                        $out['other_'.($i + 1)] = [$label];
                    }

                    continue;
                }
                $label = trim((string) ($block['label'] ?? $block['name'] ?? $block['title'] ?? ''));
                $items = self::stringList($block['items'] ?? $block['amenities'] ?? null);
                if ($items === []) {
                    continue;
                }
                $key = $label !== '' ? \Illuminate\Support\Str::slug($label) : 'group_'.($i + 1);
                $out[$key !== '' ? $key : 'group_'.($i + 1)] = $items;
            }

            return $out !== [] ? $out : null;
        }

        $out = [];
        foreach ($raw as $key => $items) {
            $list = self::stringList($items);
            if ($list !== []) {
                $out[(string) $key] = $list;
            }
        }

        return $out !== [] ? $out : null;
    }

    /**
     * Chuẩn hóa điểm đánh giá → list tag chung (filter sau này).
     * Chấp nhận object cũ `{staff: 8.6}` hoặc list `[{tag, score}]`.
     *
     * @return list<array{tag: string, score: float}>|null
     */
    public static function normalizeReviewScores(mixed $raw): ?array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return null;
        }
        if (is_string($raw)) {
            $decoded = json_decode(trim($raw), true);
            if (! is_array($decoded) || $decoded === []) {
                return null;
            }
            $raw = $decoded;
        }
        if (! is_array($raw)) {
            return null;
        }

        $map = [];
        if (array_is_list($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $tag = trim((string) ($row['tag'] ?? $row['key'] ?? $row['id'] ?? ''));
                if ($tag === '' || ! isset($row['score']) || ! is_numeric($row['score'])) {
                    continue;
                }
                $score = round((float) $row['score'], 1);
                if ($score <= 0 || $score > 10) {
                    continue;
                }
                $map[$tag] = $score;
            }
        } else {
            foreach ($raw as $key => $value) {
                if (! is_numeric($value)) {
                    continue;
                }
                $tag = trim((string) $key);
                if ($tag === '' || $tag === 'total') {
                    continue;
                }
                $score = round((float) $value, 1);
                if ($score <= 0 || $score > 10) {
                    continue;
                }
                $map[$tag] = $score;
            }
        }

        if ($map === []) {
            return null;
        }

        $labels = config('stay.review_score_tags', config('stay.review_score_labels', []));
        $out = [];
        foreach ($labels as $tag => $_label) {
            if (! isset($map[$tag])) {
                continue;
            }
            $out[] = ['tag' => (string) $tag, 'score' => $map[$tag]];
            unset($map[$tag]);
        }
        foreach ($map as $tag => $score) {
            $out[] = ['tag' => (string) $tag, 'score' => $score];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return list<array{key: string, label: string, score: float, tag: string}>
     */
    public static function reviewScores(array $attrs): array
    {
        $rows = self::normalizeReviewScores($attrs['review_scores'] ?? null) ?? [];
        if ($rows === []) {
            return [];
        }
        $labels = config('stay.review_score_tags', config('stay.review_score_labels', []));
        $out = [];
        foreach ($rows as $row) {
            $tag = (string) ($row['tag'] ?? '');
            if ($tag === '') {
                continue;
            }
            $out[] = [
                'key' => $tag,
                'tag' => $tag,
                'label' => (string) ($labels[$tag] ?? $tag),
                'score' => round((float) ($row['score'] ?? 0), 1),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, list<array<string, mixed>>>|null
     */
    public static function normalizeNearbyGroups(mixed $raw): ?array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return null;
        }
        if (is_string($raw)) {
            $decoded = json_decode(trim($raw), true);
            if (! is_array($decoded)) {
                return null;
            }
            $raw = $decoded;
        }
        if (! is_array($raw)) {
            return null;
        }

        if (array_is_list($raw)) {
            return ['other' => self::normalizeNearbyItems($raw)];
        }

        $out = [];
        foreach ($raw as $key => $items) {
            $normalized = self::normalizeNearbyItems($items);
            if ($normalized !== []) {
                $out[(string) $key] = $normalized;
            }
        }

        return $out !== [] ? $out : null;
    }

    /**
     * @param  array<string, mixed>  $opt
     * @return array<string, mixed>
     */
    public static function mapRoom(array $opt, string $currency, ?callable $formatMoney = null): array
    {
        $attrs = is_array($opt['attrs'] ?? null) ? $opt['attrs'] : [];
        $amenities = array_values(array_filter(
            array_map(fn ($item) => self::cleanScrapedLabel((string) $item), self::stringList($opt['amenities'] ?? null)),
        ));
        if (isset($attrs['bed']) && is_string($attrs['bed'])) {
            $attrs['bed'] = self::cleanScrapedLabel($attrs['bed']);
        }
        if (isset($attrs['bed_label']) && is_string($attrs['bed_label'])) {
            $attrs['bed_label'] = self::cleanScrapedLabel($attrs['bed_label']);
        }
        $price = isset($opt['price_from']) && $opt['price_from'] !== null && $opt['price_from'] !== ''
            ? (float) $opt['price_from']
            : null;

        $beds = self::normalizeBeds($attrs);
        $highlights = array_values(array_filter(
            array_map(fn ($h) => self::cleanScrapedLabel((string) $h), self::stringList($attrs['highlights'] ?? null)),
        ));
        if ($highlights === [] && $amenities !== []) {
            $highlights = array_slice($amenities, 0, 12);
        }

        $sizeSqm = isset($attrs['size_sqm']) ? (int) $attrs['size_sqm'] : null;
        $displayTags = $highlights;
        if ($sizeSqm !== null && $sizeSqm > 0) {
            $sizeTag = $sizeSqm.' m²';
            $hasSize = false;
            foreach ($displayTags as $tag) {
                if (is_string($tag) && preg_match('/\b'.preg_quote((string) $sizeSqm, '/').'\s*m/ui', $tag)) {
                    $hasSize = true;
                    break;
                }
            }
            if (! $hasSize) {
                array_unshift($displayTags, $sizeTag);
            }
        }

        $unitType = (string) ($attrs['unit_type'] ?? $attrs['privacy'] ?? '');
        $unitLabel = $unitType !== ''
            ? (string) (config("stay.unit_types.{$unitType}") ?? $unitType)
            : '';

        $amenityGroups = self::displayGroups(
            $amenities,
            is_array($attrs['amenity_groups'] ?? null) ? $attrs['amenity_groups'] : null,
        );

        $photos = [];
        $rawPhotos = is_array($attrs['photos'] ?? null) ? $attrs['photos'] : [];
        if ($rawPhotos === [] && is_array($opt['photos'] ?? null)) {
            $rawPhotos = $opt['photos'];
        }
        foreach ($rawPhotos as $photo) {
            if (is_string($photo) && $photo !== '') {
                if (! self::shouldExposePublicPhoto($photo)) {
                    continue;
                }
                $photos[] = ['url' => $photo, 'alt' => (string) ($opt['name'] ?? '')];
            } elseif (is_array($photo)) {
                $mediaId = (int) ($photo['media_id'] ?? 0);
                $url = (string) ($photo['url'] ?? $photo['src'] ?? '');
                if (! self::shouldExposePublicPhoto($url, $mediaId)) {
                    continue;
                }
                if ($mediaId > 0) {
                    $media = \App\Models\Media::query()->find($mediaId);
                    $resolved = (string) ($media?->url('lg') ?: $media?->url('card') ?: '');
                    if ($resolved !== '') {
                        $url = $resolved;
                    }
                }
                if ($url === '') {
                    continue;
                }
                $photos[] = [
                    'url' => $url,
                    'alt' => (string) ($photo['alt'] ?? $opt['name'] ?? ''),
                ];
            }
        }

        $formatted = null;
        if ($price !== null && $price > 0) {
            $formatted = $formatMoney ? $formatMoney($price, $currency) : (string) $price;
        }

        $bedLabel = (string) ($attrs['bed'] ?? $attrs['bed_label'] ?? '');
        if ($bedLabel === '' && $beds !== []) {
            $bedLabel = collect($beds)
                ->flatMap(fn ($room) => $room['items'] ?? [])
                ->map(fn ($b) => $b['label'] ?? '')
                ->filter()
                ->implode(' · ');
        }

        $crawlDates = is_array($attrs['crawl_dates'] ?? null) ? $attrs['crawl_dates'] : [];
        $rateOptions = [];
        foreach (is_array($attrs['rate_options'] ?? null) ? $attrs['rate_options'] : [] as $rate) {
            if (! is_array($rate)) {
                continue;
            }
            $rateNight = isset($rate['price_per_night']) && is_numeric($rate['price_per_night'])
                ? (float) $rate['price_per_night']
                : null;
            $rateTotal = isset($rate['price']) && is_numeric($rate['price'])
                ? (float) $rate['price']
                : null;
            $rateStrike = isset($rate['price_strikethrough']) && is_numeric($rate['price_strikethrough'])
                ? (float) $rate['price_strikethrough']
                : null;
            $rateFormatted = null;
            if ($rateNight !== null && $rateNight > 0) {
                $rateFormatted = $formatMoney ? $formatMoney($rateNight, $currency) : (string) $rateNight;
            }
            $totalFormatted = null;
            if ($rateTotal !== null && $rateTotal > 0) {
                $totalFormatted = $formatMoney ? $formatMoney($rateTotal, $currency) : (string) $rateTotal;
            }
            $strikeFormatted = null;
            if ($rateStrike !== null && $rateStrike > 0) {
                $strikeFormatted = $formatMoney ? $formatMoney($rateStrike, $currency) : (string) $rateStrike;
            }
            $breakfast = is_array($rate['breakfast'] ?? null) ? $rate['breakfast'] : [];
            $cancellation = is_array($rate['cancellation'] ?? null) ? $rate['cancellation'] : [];
            $prepayment = is_array($rate['prepayment'] ?? null) ? $rate['prepayment'] : [];
            $rate = StayRateCopy::enrichRateOption($rate, $crawlDates, (string) ($attrs['deal_key'] ?? StayRateCopy::DEFAULT_DEAL_KEY));
            $cancellation = is_array($rate['cancellation'] ?? null) ? $rate['cancellation'] : $cancellation;
            $prepayment = is_array($rate['prepayment'] ?? null) ? $rate['prepayment'] : $prepayment;

            $dealKey = trim((string) ($rate['deal_key'] ?? $attrs['deal_key'] ?? ''));
            $dealLabel = $dealKey !== '' ? StayRateCopy::dealLabel($dealKey) : '';
            $savePercent = isset($rate['save_percent']) ? (int) $rate['save_percent'] : StayRateCopy::savePercent($rateTotal, $rateStrike);

            $mealsDetail = array_values(array_filter(array_map(
                'strval',
                is_array($rate['meals_detail'] ?? null) ? $rate['meals_detail'] : [],
            )));
            $rateOptions[] = [
                'blockId' => (string) ($rate['block_id'] ?? ''),
                'pricePerNight' => $rateNight,
                'priceFormatted' => $rateFormatted,
                'priceTotal' => $rateTotal,
                'priceTotalFormatted' => $totalFormatted,
                'priceStrikeFormatted' => $strikeFormatted,
                'savePercent' => $savePercent,
                'nights' => isset($rate['nights']) ? (int) $rate['nights'] : null,
                'taxesIncluded' => ! empty($rate['taxes_included']),
                'breakfastIncluded' => ! empty($breakfast['included']),
                'breakfastLabel' => (string) ($breakfast['label'] ?? ''),
                'cancellationTitle' => StayRateCopy::normalizeCancellationTitle(
                    (string) ($cancellation['title'] ?? ''),
                    $crawlDates,
                ),
                'cancellationDescription' => (string) ($cancellation['description'] ?? ''),
                'cancellationRefundable' => array_key_exists('refundable', $cancellation)
                    ? (bool) $cancellation['refundable']
                    : null,
                'prepaymentTitle' => StayRateCopy::normalizePrepaymentTitle(
                    (string) ($prepayment['title'] ?? ''),
                ),
                'prepaymentDescription' => (string) ($prepayment['description'] ?? ''),
                'mealsDetail' => $mealsDetail,
                'dealKey' => $dealKey !== '' ? $dealKey : null,
                'dealLabel' => $dealLabel !== '' ? $dealLabel : null,
                'maxRooms' => isset($rate['max_rooms']) ? (int) $rate['max_rooms'] : null,
            ];
        }

        $crawlCheckin = (string) ($crawlDates['checkin'] ?? '');
        $crawlCheckout = (string) ($crawlDates['checkout'] ?? '');
        $crawlNights = isset($crawlDates['nights']) ? (int) $crawlDates['nights'] : null;
        $smoking = self::normalizeSmoking((string) ($attrs['smoking'] ?? ''));
        $dealKeyRoom = trim((string) ($attrs['deal_key'] ?? ''));

        return [
            'code' => (string) ($opt['code'] ?? ''),
            'name' => (string) ($opt['name'] ?? ''),
            'description' => (string) ($opt['description'] ?? $attrs['description'] ?? ''),
            'capacity' => isset($opt['capacity']) ? (int) $opt['capacity'] : null,
            'bedLabel' => $bedLabel,
            'beds' => $beds,
            'sizeSqm' => $sizeSqm,
            'view' => (string) ($attrs['view'] ?? ''),
            'bathroomCount' => isset($attrs['bathroom_count']) ? (int) $attrs['bathroom_count'] : null,
            'bedroomCount' => isset($attrs['bedroom_count']) ? (int) $attrs['bedroom_count'] : (count($beds) ?: null),
            'unitType' => $unitType,
            'unitTypeLabel' => $unitLabel,
            'smoking' => $smoking['label'],
            'smokingAllowed' => $smoking['allowed'],
            'comfortScore' => isset($attrs['comfort_score']) ? (float) $attrs['comfort_score'] : null,
            'comfortReviews' => isset($attrs['comfort_reviews']) ? (int) $attrs['comfort_reviews'] : null,
            'highlights' => $highlights,
            'displayTags' => $displayTags,
            'amenities' => $amenities,
            'amenityGroups' => $amenityGroups,
            'photos' => $photos,
            'priceFrom' => $price,
            'priceFormatted' => $formatted,
            'rateOptions' => $rateOptions,
            'rateCount' => count($rateOptions),
            'crawlCheckin' => $crawlCheckin,
            'crawlCheckout' => $crawlCheckout,
            'crawlNights' => $crawlNights,
            'scarcityActive' => StayRateCopy::scarcityActive($attrs),
            'scarcityTemplate' => StayRateCopy::scarcityTemplate(),
            'dealKey' => $dealKeyRoom !== '' ? $dealKeyRoom : null,
            'dealLabel' => $dealKeyRoom !== '' ? StayRateCopy::dealLabel($dealKeyRoom) : null,
        ];
    }

    /**
     * Chuẩn hoá nhãn hút thuốc — bỏ prefix lặp, suy ra trạng thái cho UI.
     *
     * @return array{label: string, allowed: bool|null}
     */
    public static function normalizeSmoking(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return ['label' => '', 'allowed' => null];
        }

        $label = preg_replace('/^(hút\s*thuốc\s*[:：\-–]\s*)+/iu', '', $raw) ?? $raw;
        $label = trim((string) $label);
        if ($label === '') {
            $label = $raw;
        }

        $lower = mb_strtolower($label);
        if (preg_match('/không\s*hút|no[\s-]?smok|non[\s-]?smok|smoke[\s-]?free/ui', $lower)) {
            return ['label' => 'Không hút thuốc', 'allowed' => false];
        }
        if (preg_match('/được\s*hút|cho\s*phép\s*hút|có\s*thể\s*hút|smoking\s*allowed|^hút\s*thuốc$/ui', $lower)) {
            return ['label' => 'Được hút thuốc', 'allowed' => true];
        }

        return ['label' => $label, 'allowed' => null];
    }

    public static function shouldExposePublicPhoto(?string $url, int $mediaId = 0): bool
    {
        if ($mediaId > 0) {
            return true;
        }
        $url = trim((string) $url);
        if ($url === '') {
            return false;
        }
        $host = (string) parse_url($url, PHP_URL_HOST);
        $host = mb_strtolower($host);
        if ($host !== '' && (str_contains($host, 'bstatic.com') || str_contains($host, 'booking.com'))) {
            return false;
        }

        return preg_match('#^https?://#i', $url) === 1 || str_starts_with($url, '/');
    }

    /** Đếm ảnh đã gắn media (đã upload GCS) — dùng chọn bản attrs giàu hơn. */
    public static function countMediaBackedPhotos(mixed $photos): int
    {
        if (! is_array($photos)) {
            return 0;
        }
        $n = 0;
        foreach ($photos as $photo) {
            if (is_array($photo) && (int) ($photo['media_id'] ?? 0) > 0) {
                $n++;
            }
        }

        return $n;
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return list<array{name: string, items: list<array{type: string, count: int, label: string}>}>
     */
    public static function normalizeBeds(array $attrs): array
    {
        $raw = $attrs['beds'] ?? $attrs['bedrooms'] ?? null;
        if (! is_array($raw) || $raw === []) {
            $label = trim((string) ($attrs['bed'] ?? $attrs['bed_label'] ?? ''));
            if ($label === '') {
                return [];
            }

            return [[
                'name' => 'Phòng ngủ',
                'items' => [['type' => '', 'count' => 1, 'label' => $label]],
            ]];
        }

        $out = [];
        foreach ($raw as $i => $room) {
            if (is_string($room) && trim($room) !== '') {
                $out[] = [
                    'name' => 'Phòng ngủ '.($i + 1),
                    'items' => [['type' => '', 'count' => 1, 'label' => trim($room)]],
                ];
                continue;
            }
            if (! is_array($room)) {
                continue;
            }
            $items = [];
            $list = $room['items'] ?? $room['beds'] ?? null;
            if (is_string($room['label'] ?? null) && $list === null) {
                $items[] = [
                    'type' => (string) ($room['type'] ?? ''),
                    'count' => (int) ($room['count'] ?? 1),
                    'label' => (string) $room['label'],
                ];
            } elseif (is_array($list)) {
                foreach ($list as $bed) {
                    if (is_string($bed) && trim($bed) !== '') {
                        $items[] = ['type' => '', 'count' => 1, 'label' => trim($bed)];
                    } elseif (is_array($bed) && filled($bed['label'] ?? $bed['type'] ?? null)) {
                        $items[] = [
                            'type' => (string) ($bed['type'] ?? ''),
                            'count' => (int) ($bed['count'] ?? 1),
                            'label' => (string) ($bed['label'] ?? $bed['type'] ?? ''),
                        ];
                    }
                }
            }
            if ($items === []) {
                continue;
            }
            $out[] = [
                'name' => (string) ($room['name'] ?? $room['label'] ?? ('Phòng ngủ '.($i + 1))),
                'items' => $items,
            ];
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    public static function stringList(mixed $value): array
    {
        if (is_string($value) && trim($value) !== '') {
            $trimmed = trim($value);
            if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return self::stringList($decoded);
                }
            }

            $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: [])));
            if (count($lines) === 1 && str_contains($lines[0], ',')) {
                return array_values(array_unique(array_filter(array_map('trim', explode(',', $lines[0])))));
            }

            return $lines;
        }
        if (! is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $item) {
            $s = self::itemLabel($item);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  array<string, mixed>  $amenityConfig
     * @param  array<string, string>  $nearbyLabels
     */
    public static function groupLabel(string $key, array $amenityConfig = [], array $nearbyLabels = []): string
    {
        if (isset($amenityConfig[$key]['label'])) {
            return (string) $amenityConfig[$key]['label'];
        }
        if (isset($nearbyLabels[$key])) {
            return (string) $nearbyLabels[$key];
        }
        $label = trim(str_replace([':', '—', '-'], ' ', $key));
        if ($label === '') {
            return $key;
        }

        return mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @return list<array{name: string, distance: string, icon: string, category: string}>
     */
    public static function normalizeNearbyItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }
        $icons = [
            'landmark' => 'compass',
            'beach' => 'waves',
            'nature' => 'tent',
            'transport' => 'plane',
            'dining' => 'utensils',
            'shop' => 'tag',
            'other' => 'map-pin',
        ];
        $out = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $name = trim($item);
                if ($name !== '') {
                    $out[] = ['name' => $name, 'distance' => '', 'icon' => 'map-pin', 'category' => 'other'];
                }
                continue;
            }
            if (! is_array($item)) {
                continue;
            }
            $name = trim((string) ($item['name'] ?? $item['label'] ?? $item['title'] ?? ''));
            if ($name === '') {
                continue;
            }
            $category = (string) ($item['category'] ?? $item['group'] ?? 'other');
            $out[] = [
                'name' => $name,
                'distance' => trim((string) ($item['distance'] ?? $item['dist'] ?? '')),
                'icon' => (string) ($item['icon'] ?? ($icons[$category] ?? 'map-pin')),
                'category' => $category,
            ];
        }

        return $out;
    }

    private static function itemLabel(mixed $item): string
    {
        if (is_string($item) || is_numeric($item)) {
            return trim((string) $item);
        }
        if (! is_array($item)) {
            return '';
        }

        return trim((string) ($item['label'] ?? $item['name'] ?? $item['text'] ?? $item['title'] ?? ''));
    }

    /**
     * Các mục chính sách / nội quy chưa map vào key cố định.
     *
     * @param  array<string, mixed>  $attrs
     * @return list<array{title: string, body: string}>
     */
    public static function policySections(array $attrs): array
    {
        $raw = is_array($attrs['policy_sections'] ?? null) ? $attrs['policy_sections'] : [];
        $out = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            $body = trim((string) ($row['body'] ?? ''));
            if ($title === '' || $body === '') {
                continue;
            }
            $out[] = ['title' => $title, 'body' => $body];
        }

        return $out;
    }

    /**
     * Tất cả dòng chính sách để hiển thị public/admin.
     *
     * @param  array<string, mixed>  $policies  payload policies từ ViewDataService
     * @param  array<string, mixed>  $attrs
     * @return list<array{key: string, icon: string, label: string, value: string, wide?: bool}>
     */
    public static function policyRows(array $policies, array $attrs = []): array
    {
        $labels = config('stay.policy_labels', []);
        $icons = [
            'check_in' => 'clock',
            'check_out' => 'clock',
            'cancellation' => 'calendar',
            'child' => 'users',
            'extra_bed' => 'users',
            'age_restriction' => 'shield',
            'pet' => 'sparkles',
            'smoking' => 'flag',
            'payment' => 'tag',
            'payment_cards' => 'tag',
            'id_required' => 'shield',
        ];
        $wide = ['cancellation', 'child', 'extra_bed', 'pet', 'payment', 'id_required'];

        $rows = [];
        foreach ($labels as $key => $label) {
            $value = match ($key) {
                'check_in', 'check_out' => (string) ($policies[$key] ?? $attrs[$key] ?? ''),
                'age_restriction' => (string) ($policies['age_restriction'] ?? $attrs['age_restriction'] ?? ''),
                'payment_cards' => (string) ($policies['payment_cards'] ?? ''),
                default => (string) ($policies[$key] ?? $attrs["{$key}_policy"] ?? $attrs[$key] ?? ''),
            };
            $value = trim($value);
            if ($value === '') {
                continue;
            }
            $row = [
                'key' => $key,
                'icon' => $icons[$key] ?? 'shield',
                'label' => (string) $label,
                'value' => $value,
            ];
            if (in_array($key, $wide, true)) {
                $row['wide'] = true;
            }
            $rows[] = $row;
        }

        foreach (self::policySections($attrs) as $section) {
            $rows[] = [
                'key' => 'section:'.md5($section['title']),
                'icon' => 'shield',
                'label' => $section['title'],
                'value' => $section['body'],
                'wide' => true,
            ];
        }

        return $rows;
    }
}
