<?php

declare(strict_types=1);

namespace App\Services\Sitemap;

/**
 * XML builders cho sitemap index / urlset (Google sitemap protocol).
 */
final class SitemapXml
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    public static function sitemapIndex(array $entries): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $lines[] = '  <sitemap>';
            $lines[] = '    <loc>'.self::escape((string) $entry['loc']).'</loc>';
            if (! empty($entry['lastmod'])) {
                $lines[] = '    <lastmod>'.self::escape((string) $entry['lastmod']).'</lastmod>';
            }
            $lines[] = '  </sitemap>';
        }

        $lines[] = '</sitemapindex>';

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  list<array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>  $urls
     */
    public static function urlset(array $urls): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.self::escape((string) $url['loc']).'</loc>';
            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>'.self::escape((string) $url['lastmod']).'</lastmod>';
            }
            if (! empty($url['changefreq'])) {
                $lines[] = '    <changefreq>'.self::escape((string) $url['changefreq']).'</changefreq>';
            }
            if (! empty($url['priority'])) {
                $lines[] = '    <priority>'.self::escape((string) $url['priority']).'</priority>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }

    public static function urlsetOpen(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    }

    public static function urlsetClose(): string
    {
        return '</urlset>'."\n";
    }

    /**
     * @param  array{loc: string, lastmod?: string, changefreq?: string, priority?: string}  $url
     */
    public static function urlEntry(array $url): string
    {
        $xml = "  <url>\n";
        $xml .= '    <loc>'.self::escape((string) $url['loc'])."</loc>\n";
        if (! empty($url['lastmod'])) {
            $xml .= '    <lastmod>'.self::escape((string) $url['lastmod'])."</lastmod>\n";
        }
        if (! empty($url['changefreq'])) {
            $xml .= '    <changefreq>'.self::escape((string) $url['changefreq'])."</changefreq>\n";
        }
        if (! empty($url['priority'])) {
            $xml .= '    <priority>'.self::escape((string) $url['priority'])."</priority>\n";
        }
        $xml .= "  </url>\n";

        return $xml;
    }
}
