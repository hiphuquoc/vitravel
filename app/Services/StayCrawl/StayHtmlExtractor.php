<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Support\StayBookingUrl;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Lọc khung HTML chỗ nghỉ (Booking.com) — JSON-LD, Open Graph, section data-testid.
 * Không bypass captcha / anti-bot.
 */
final class StayHtmlExtractor
{
    public const VERSION = 2;

    /**
     * @return array{
     *   blocked: bool,
     *   blocked_reason: ?string,
     *   canonical_url: string,
     *   title: ?string,
     *   json_ld: list<array<string, mixed>>,
     *   open_graph: array<string, string>,
     *   images: list<array{url: string, alt: string}>,
     *   hotel_urls: list<string>,
     *   sections: array<string, string>,
     *   extracted_html: string,
     *   extractor_version: int
     * }
     */
    public function extract(string $html, string $sourceUrl): array
    {
        $maxBytes = (int) config('stay.crawl.max_html_bytes', 1_800_000);
        if (strlen($html) > $maxBytes) {
            $html = substr($html, 0, $maxBytes);
        }

        $blocked = $this->detectBlock($html);
        $dom = $this->loadDom($html);
        $xpath = $dom ? new DOMXPath($dom) : null;

        $jsonLd = $xpath ? $this->jsonLd($xpath) : [];
        $og = $xpath ? $this->openGraph($xpath) : [];
        $canonical = $this->canonicalFrom($xpath, $og, $sourceUrl);
        $images = $xpath ? $this->images($xpath, $canonical) : [];
        $hotelUrls = StayBookingUrl::extractHotelUrlsFromHtml($html, $canonical);
        $sections = $xpath ? $this->sections($xpath) : [];

        $extracted = $this->composeExtractedHtml($canonical, $og, $jsonLd, $sections, $images);

        return [
            'blocked' => $blocked['blocked'],
            'blocked_reason' => $blocked['reason'],
            'canonical_url' => StayBookingUrl::canonicalize($canonical),
            'title' => $og['og:title'] ?? $this->documentTitle($xpath),
            'json_ld' => $jsonLd,
            'open_graph' => $og,
            'images' => $images,
            'hotel_urls' => $hotelUrls,
            'sections' => $sections,
            'extracted_html' => $extracted,
            'extractor_version' => self::VERSION,
        ];
    }

    /** @return array{blocked: bool, reason: ?string} */
    private function detectBlock(string $html): array
    {
        $sample = mb_strtolower(substr($html, 0, 12000));
        foreach ([
            'just a moment' => 'cloudflare_challenge',
            'access denied' => 'access_denied',
            'captcha' => 'captcha',
            'cf-browser-verification' => 'cloudflare_challenge',
            'enable javascript and cookies' => 'js_required',
            'unusual traffic' => 'bot_block',
        ] as $needle => $reason) {
            if (str_contains($sample, $needle)) {
                return ['blocked' => true, 'reason' => $reason];
            }
        }
        if (strlen(trim(strip_tags($html))) < 400) {
            return ['blocked' => true, 'reason' => 'empty_or_shell'];
        }

        return ['blocked' => false, 'reason' => null];
    }

    private function loadDom(string $html): ?DOMDocument
    {
        $dom = new DOMDocument;
        $prev = libxml_use_internal_errors(true);
        $ok = $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $ok ? $dom : null;
    }

    /** @return list<array<string, mixed>> */
    private function jsonLd(DOMXPath $xpath): array
    {
        $out = [];
        foreach ($xpath->query('//script[@type="application/ld+json"]') ?: [] as $node) {
            $raw = trim($node->textContent ?? '');
            if ($raw === '') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                continue;
            }
            $items = isset($decoded['@graph']) && is_array($decoded['@graph']) ? $decoded['@graph'] : [$decoded];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $type = $item['@type'] ?? '';
                $types = is_array($type) ? $type : [$type];
                $hit = false;
                foreach ($types as $t) {
                    if (is_string($t) && preg_match('/Hotel|LodgingBusiness|Product|Apartment|Resort|Place/i', $t)) {
                        $hit = true;
                        break;
                    }
                }
                if ($hit || isset($item['amenityFeature']) || isset($item['starRating'])) {
                    $out[] = $item;
                }
            }
        }

        return $out;
    }

    /** @return array<string, string> */
    private function openGraph(DOMXPath $xpath): array
    {
        $out = [];
        foreach ($xpath->query('//meta[starts-with(@property,"og:") or starts-with(@name,"og:")]') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $key = $node->getAttribute('property') ?: $node->getAttribute('name');
            $val = trim($node->getAttribute('content'));
            if ($key !== '' && $val !== '') {
                $out[$key] = $val;
            }
        }

        return $out;
    }

    private function canonicalFrom(?DOMXPath $xpath, array $og, string $sourceUrl): string
    {
        if ($xpath) {
            $node = $xpath->query('//link[@rel="canonical"]/@href')?->item(0);
            if ($node && trim($node->nodeValue ?? '') !== '') {
                return trim($node->nodeValue);
            }
        }
        if (! empty($og['og:url'])) {
            return (string) $og['og:url'];
        }

        return $sourceUrl;
    }

    private function documentTitle(?DOMXPath $xpath): ?string
    {
        if (! $xpath) {
            return null;
        }
        $node = $xpath->query('//title')?->item(0);

        return $node ? trim(preg_replace('/\s+/', ' ', $node->textContent ?? '') ?? '') : null;
    }

    /**
     * @return list<array{url: string, alt: string}>
     */
    private function images(DOMXPath $xpath, string $baseUrl): array
    {
        $max = (int) config('stay.crawl.max_images', 40);
        $seen = [];
        $out = [];
        foreach ($xpath->query('//img[@src or @data-src or @srcset]') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $src = $node->getAttribute('src') ?: $node->getAttribute('data-src');
            if ($src === '' && $node->getAttribute('srcset') !== '') {
                $src = trim(explode(' ', explode(',', $node->getAttribute('srcset'))[0])[0]);
            }
            $abs = $this->absUrl($src, $baseUrl);
            if ($abs === null || isset($seen[$abs]) || ! $this->isStayImage($abs)) {
                continue;
            }
            $seen[$abs] = true;
            $out[] = [
                'url' => $abs,
                'alt' => trim($node->getAttribute('alt')),
            ];
            if (count($out) >= $max) {
                break;
            }
        }

        return $out;
    }

    private function isStayImage(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        if (str_contains($path, '/static/img/') || str_contains($path, 'transparent')) {
            return false;
        }

        return str_contains($path, '/xdata/images/hotel/')
            || str_contains($path, '/xdata/images/max');
    }

    private function absUrl(string $src, string $baseUrl): ?string
    {
        $src = trim($src);
        if ($src === '' || str_starts_with($src, 'data:')) {
            return null;
        }
        if (str_starts_with($src, '//')) {
            return 'https:'.$src;
        }
        if (preg_match('#^https?://#i', $src)) {
            return $src;
        }
        $base = parse_url($baseUrl);
        if (! is_array($base) || empty($base['host'])) {
            return null;
        }
        $origin = ($base['scheme'] ?? 'https').'://'.$base['host'];

        return str_starts_with($src, '/') ? $origin.$src : $origin.'/'.$src;
    }

    /** @return array<string, string> */
    private function sections(DOMXPath $xpath): array
    {
        $map = [
            'header' => [
                '//*[@data-testid="property-header"]',
                '//*[@id="hp_hotel_name"]',
                '//h2[contains(@class,"pp-header")]',
            ],
            'description' => [
                '//*[@data-testid="property-description"]',
                '//*[@id="property_description_section"]',
                '//*[@id="summary"]',
                '//div[contains(@class,"hp_desc_main_content")]',
            ],
            'facilities' => [
                '//*[@data-testid="property-facilities-block-container"]',
                '//*[@data-testid="facility-group-container"]',
                '//*[@data-testid="property-most-popular-facilities-wrapper"]',
                '//*[@data-testid="property-facilities-group"]',
                '//*[@id="hp_facilities_box"]',
                '//*[@id="facilities"]',
                '//div[contains(@class,"hotel-facilities")]',
            ],
            'highlights' => [
                '//*[contains(@class,"property-highlights")]',
                '//*[contains(@class,"k2-hp--highlights")]',
            ],
            'address' => [
                '//*[@data-testid="PropertyHeaderAddressDesktop-wrapper"]',
            ],
            'rooms' => [
                '//*[@data-testid="PropertyRoomsList"]',
                '//*[@id="maxotelRoomArea"]',
                '//*[@id="rooms_table"]',
                '//table[contains(@class,"roomstable")]',
                '//div[contains(@class,"roomtable")]',
            ],
            'house_rules' => [
                '//*[@data-testid="house-rules"]',
                '//*[@data-testid="PropertyImportantInfo-wrapper"]',
                '//*[@id="hotelPoliciesInc"]',
                '//div[contains(@class,"hp-policies")]',
            ],
            'reviews' => [
                '//*[@data-testid="review-score-component"]',
                '//*[contains(@class,"review-score")]',
                '//*[@id="review_list_score"]',
            ],
            'nearby' => [
                '//*[@id="surroundings_block"]',
                '//*[@data-testid="location-block-container"]',
                '//*[@data-testid="NearbyPlaces"]',
                '//*[@id="surroundings"]',
                '//div[contains(@class,"hp_location_block")]',
            ],
        ];

        $out = [];
        foreach ($map as $key => $queries) {
            foreach ($queries as $q) {
                $node = $xpath->query($q)?->item(0);
                if (! $node instanceof DOMElement) {
                    continue;
                }
                $html = $this->innerHtml($node);
                $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
                if (mb_strlen($text) < 8) {
                    continue;
                }
                $out[$key] = $this->stripChrome($html);
                break;
            }
        }

        return $out;
    }

    private function innerHtml(DOMElement $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument?->saveHTML($child) ?: '';
        }

        return $html !== '' ? $html : ($node->ownerDocument?->saveHTML($node) ?: '');
    }

    private function stripChrome(string $html): string
    {
        $html = preg_replace('#<(script|style|noscript|svg|iframe|button)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#\s(class|style|id|data-[a-z0-9_-]+)="[^"]*"#i', '', $html) ?? $html;

        return trim($html);
    }

    /**
     * @param  list<array<string, mixed>>  $jsonLd
     * @param  array<string, string>  $og
     * @param  array<string, string>  $sections
     * @param  list<array{url: string, alt: string}>  $images
     */
    private function composeExtractedHtml(
        string $canonical,
        array $og,
        array $jsonLd,
        array $sections,
        array $images,
    ): string {
        $max = (int) config('stay.crawl.max_extract_chars', 90_000);
        $parts = [];
        $parts[] = '<section data-vt="source"><p>Source URL: '.e($canonical).'</p></section>';
        if ($og !== []) {
            $parts[] = '<section data-vt="open-graph"><pre>'.e(json_encode($og, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '').'</pre></section>';
        }
        if ($jsonLd !== []) {
            $parts[] = '<section data-vt="json-ld"><pre>'.e(json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '').'</pre></section>';
        }
        foreach ($sections as $name => $html) {
            $parts[] = '<section data-vt="'.e($name).'"><h2>'.e($name).'</h2>'.$html.'</section>';
        }
        if ($images !== []) {
            $lis = '';
            foreach (array_slice($images, 0, 40) as $img) {
                $lis .= '<li>'.e($img['url']).($img['alt'] !== '' ? ' — '.e($img['alt']) : '').'</li>';
            }
            $parts[] = '<section data-vt="images"><h2>images</h2><ul>'.$lis.'</ul></section>';
        }
        $blob = implode("\n", $parts);
        if (mb_strlen($blob) > $max) {
            $blob = mb_substr($blob, 0, $max)."\n<!-- truncated -->";
        }

        return $blob;
    }
}
