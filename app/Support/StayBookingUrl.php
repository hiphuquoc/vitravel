<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Chuẩn hoá URL Booking.com — luôn giữ URL gốc để replay crawler.
 */
final class StayBookingUrl
{
    public static function isBookingHost(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        return $host === 'booking.com';
    }

    public static function isHotelPage(string $url): bool
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        return (bool) preg_match('#/hotel/[a-z]{2}/[^/]+\.html#i', $path);
    }

    public static function isSearchPage(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return str_contains($path, 'searchresults')
            || str_contains($path, '/city/')
            || str_contains($path, '/region/')
            || str_contains($path, '/district/')
            || str_contains($path, '/landmark/');
    }

    /**
     * URL canonical (bỏ tracking query, locale infix .en-gb).
     * URL gốc (source_url) vẫn được lưu riêng trên item.
     */
    public static function canonicalize(string $url): string
    {
        $parts = parse_url(trim($url));
        if (! is_array($parts) || empty($parts['host'])) {
            return trim($url);
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = strtolower($parts['host']);
        if (! str_starts_with($host, 'www.') && $host === 'booking.com') {
            $host = 'www.booking.com';
        }
        $path = (string) ($parts['path'] ?? '/');
        $path = preg_replace('/\.(en-gb|en-us|vi|fr|de|zh-cn|ko|ja)(\.html)$/i', '$2', $path) ?? $path;

        return $scheme.'://'.$host.$path;
    }

    public static function hotelSlug(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (! preg_match('#/hotel/[a-z]{2}/([^/]+)\.html#i', $path, $m)) {
            return null;
        }
        $slug = preg_replace('/\.(en-gb|en-us|vi|fr|de)$/i', '', $m[1]) ?? $m[1];

        return strtolower($slug);
    }

    /**
     * @return list<string>
     */
    public static function extractHotelUrlsFromHtml(string $html, string $baseUrl = 'https://www.booking.com'): array
    {
        $found = [];
        if (preg_match_all('#(?:https?:)?//(?:www\.)?booking\.com/hotel/[a-z]{2}/[^"\'?\s<>]+\.html#i', $html, $m)) {
            foreach ($m[0] as $raw) {
                $abs = str_starts_with($raw, '//') ? 'https:'.$raw : $raw;
                $found[] = self::canonicalize($abs);
            }
        }
        if (preg_match_all('#(?:href|content)=["\'](/hotel/[a-z]{2}/[^"\'?\s>]+\.html)#i', $html, $m)) {
            foreach ($m[1] as $path) {
                $found[] = self::canonicalize(rtrim($baseUrl, '/').$path);
            }
        }

        return array_values(array_unique(array_filter($found)));
    }
}
