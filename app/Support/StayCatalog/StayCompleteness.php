<?php

declare(strict_types=1);

namespace App\Support\StayCatalog;

use App\Models\Service;

/**
 * Điểm đủ dữ liệu chỗ nghỉ — dùng rebuild / improve chọn lọc.
 *
 * @phpstan-type Flags array{
 *   identity: bool,
 *   geo: bool,
 *   type: bool,
 *   amenities: bool,
 *   poi: bool,
 *   gallery: bool,
 *   rooms: bool,
 *   rates: bool,
 *   score: int
 * }
 */
final class StayCompleteness
{
    /**
     * @param  array<string, mixed>  $attrs
     * @return Flags
     */
    public static function fromAttrs(array $attrs, ?string $sourceHotelKey = null, int $roomCount = 0): array
    {
        $crawl = is_array($attrs['crawl'] ?? null) ? $attrs['crawl'] : [];
        $identity = ($sourceHotelKey !== null && $sourceHotelKey !== '')
            || filled($crawl['source_hotel_key'] ?? null)
            || filled($crawl['canonical_url'] ?? null);
        $geo = is_numeric($attrs['lat'] ?? null) && is_numeric($attrs['lng'] ?? null);
        $type = filled($attrs['property_type'] ?? null);
        $amenities = (is_array($attrs['amenity_groups'] ?? null) && $attrs['amenity_groups'] !== [])
            || (is_array($attrs['amenities'] ?? null) && $attrs['amenities'] !== []);
        $poi = is_array($attrs['nearby_groups'] ?? null) && $attrs['nearby_groups'] !== [];
        $photos = $attrs['photos'] ?? [];
        $gallery = is_array($photos) && $photos !== [];
        $rooms = $roomCount > 0;
        $rates = false;
        if ($rooms && is_array($attrs['options'] ?? null)) {
            foreach ($attrs['options'] as $opt) {
                if (is_array($opt) && ! empty($opt['attrs']['rate_options'])) {
                    $rates = true;
                    break;
                }
            }
        }

        $flags = compact('identity', 'geo', 'type', 'amenities', 'poi', 'gallery', 'rooms', 'rates');
        $score = (int) round(100 * count(array_filter($flags)) / max(1, count($flags)));
        $flags['score'] = $score;

        return $flags;
    }

    /**
     * @return Flags
     */
    public static function forService(Service $service): array
    {
        $attrs = is_array($service->attrs) ? $service->attrs : [];
        $key = is_array($attrs['crawl'] ?? null) ? (string) ($attrs['crawl']['source_hotel_key'] ?? '') : '';

        $flags = self::fromAttrs(
            $attrs,
            $key !== '' ? $key : null,
            $service->relationLoaded('options') ? $service->options->count() : $service->options()->count(),
        );

        if (! $flags['rates'] && $service->relationLoaded('options')) {
            foreach ($service->options as $opt) {
                $optAttrs = is_array($opt->attrs) ? $opt->attrs : [];
                if (! empty($optAttrs['rate_options'])) {
                    $flags['rates'] = true;
                    break;
                }
            }
            $flags['score'] = (int) round(100 * count(array_filter([
                $flags['identity'], $flags['geo'], $flags['type'], $flags['amenities'],
                $flags['poi'], $flags['gallery'], $flags['rooms'], $flags['rates'],
            ])) / 8);
        }

        return $flags;
    }
}
