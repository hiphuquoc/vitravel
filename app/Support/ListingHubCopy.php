<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Đoạn SEO cuối trang hub từ project/seed_*.php → key `listing_hubs`.
 */
final class ListingHubCopy
{
    public static function seoBody(string $hubKey, ?string $locale = null): string
    {
        try {
            $raw = ProjectSeed::get("listing_hubs.{$hubKey}", []);
        } catch (\Throwable) {
            return '';
        }
        if (! is_array($raw) || $raw === []) {
            return '';
        }

        $picked = LocaleContent::pick($raw, $locale, $raw['vi'] ?? []);
        if (! is_array($picked)) {
            return '';
        }

        $body = trim((string) ($picked['seo_body'] ?? ''));

        return $body !== '' ? apply_site_brand($body) : '';
    }
}
