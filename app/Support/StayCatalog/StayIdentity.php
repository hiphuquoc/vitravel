<?php

declare(strict_types=1);

namespace App\Support\StayCatalog;

use App\Support\StayBookingUrl;

/**
 * Identity chỗ nghỉ Booking: /hotel/{cc}/{slug}.html → source_hotel_key.
 */
final class StayIdentity
{
    public const SOURCE_BOOKING = 'booking.com';

    /**
     * @return array{
     *   source: string,
     *   booking_cc: ?string,
     *   slug: ?string,
     *   source_hotel_key: ?string,
     *   canonical_url: string,
     *   dest_id: ?string,
     *   dest_type: ?string,
     *   ss: ?string
     * }
     */
    public static function fromUrl(string $url): array
    {
        $canonical = StayBookingUrl::canonicalize($url);
        $path = (string) parse_url($url, PHP_URL_PATH);
        $cc = null;
        $slug = StayBookingUrl::hotelSlug($url);
        if (preg_match('#/hotel/([a-z]{2})/#i', $path, $m)) {
            $cc = strtolower($m[1]);
        }
        $key = ($cc && $slug) ? $cc.':'.$slug : null;

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return [
            'source' => self::SOURCE_BOOKING,
            'booking_cc' => $cc,
            'slug' => $slug,
            'source_hotel_key' => $key,
            'canonical_url' => $canonical,
            'dest_id' => isset($query['dest_id']) ? (string) $query['dest_id'] : null,
            'dest_type' => isset($query['dest_type']) ? (string) $query['dest_type'] : null,
            'ss' => isset($query['ss']) ? (string) $query['ss'] : null,
        ];
    }

    public static function hotelKey(string $url): ?string
    {
        return self::fromUrl($url)['source_hotel_key'];
    }

    public static function withNflt(string $listUrl, string $nflt): string
    {
        $parts = parse_url(trim($listUrl));
        if (! is_array($parts) || empty($parts['host'])) {
            return trim($listUrl);
        }
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $query['nflt'] = $nflt;
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'];
        $path = (string) ($parts['path'] ?? '/');
        $qs = http_build_query($query);

        return $scheme.'://'.$host.$path.($qs !== '' ? '?'.$qs : '');
    }
}
