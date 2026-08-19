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
                    'label' => (string) $key,
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
                $items = $groups[$key] ?? null;
                if (! is_array($items) || $items === []) {
                    continue;
                }
                $out[] = ['key' => (string) $key, 'label' => (string) $label, 'items' => array_values($items)];
            }
            foreach ($groups as $key => $items) {
                if (isset($labels[$key]) || ! is_array($items) || $items === []) {
                    continue;
                }
                $out[] = ['key' => (string) $key, 'label' => (string) $key, 'items' => array_values($items)];
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
     * @param  array<string, mixed>  $attrs
     * @return list<array{key: string, label: string, score: float}>
     */
    public static function reviewScores(array $attrs): array
    {
        $raw = is_array($attrs['review_scores'] ?? null) ? $attrs['review_scores'] : [];
        if ($raw === []) {
            return [];
        }
        $labels = config('stay.review_score_labels', []);
        $out = [];
        foreach ($labels as $key => $label) {
            if (! isset($raw[$key]) || ! is_numeric($raw[$key])) {
                continue;
            }
            $out[] = [
                'key' => (string) $key,
                'label' => (string) $label,
                'score' => round((float) $raw[$key], 1),
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $opt
     * @return array<string, mixed>
     */
    public static function mapRoom(array $opt, string $currency, ?callable $formatMoney = null): array
    {
        $attrs = is_array($opt['attrs'] ?? null) ? $opt['attrs'] : [];
        $amenities = self::stringList($opt['amenities'] ?? null);
        $price = isset($opt['price_from']) && $opt['price_from'] !== null && $opt['price_from'] !== ''
            ? (float) $opt['price_from']
            : null;

        $beds = self::normalizeBeds($attrs);
        $highlights = self::stringList($attrs['highlights'] ?? null);
        if ($highlights === [] && $amenities !== []) {
            $highlights = array_slice($amenities, 0, 8);
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
                $photos[] = ['url' => $photo, 'alt' => (string) ($opt['name'] ?? '')];
            } elseif (is_array($photo) && filled($photo['url'] ?? $photo['src'] ?? null)) {
                $photos[] = [
                    'url' => (string) ($photo['url'] ?? $photo['src']),
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

        return [
            'code' => (string) ($opt['code'] ?? ''),
            'name' => (string) ($opt['name'] ?? ''),
            'description' => (string) ($opt['description'] ?? $attrs['description'] ?? ''),
            'capacity' => isset($opt['capacity']) ? (int) $opt['capacity'] : null,
            'bedLabel' => $bedLabel,
            'beds' => $beds,
            'sizeSqm' => isset($attrs['size_sqm']) ? (int) $attrs['size_sqm'] : null,
            'view' => (string) ($attrs['view'] ?? ''),
            'bathroomCount' => isset($attrs['bathroom_count']) ? (int) $attrs['bathroom_count'] : null,
            'bedroomCount' => isset($attrs['bedroom_count']) ? (int) $attrs['bedroom_count'] : (count($beds) ?: null),
            'unitType' => $unitType,
            'unitTypeLabel' => $unitLabel,
            'smoking' => (string) ($attrs['smoking'] ?? ''),
            'comfortScore' => isset($attrs['comfort_score']) ? (float) $attrs['comfort_score'] : null,
            'comfortReviews' => isset($attrs['comfort_reviews']) ? (int) $attrs['comfort_reviews'] : null,
            'highlights' => $highlights,
            'amenities' => $amenities,
            'amenityGroups' => $amenityGroups,
            'photos' => $photos,
            'priceFrom' => $price,
            'priceFormatted' => $formatted,
        ];
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
            return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: [])));
        }
        if (! is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $item) {
            $s = trim((string) $item);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }
}
