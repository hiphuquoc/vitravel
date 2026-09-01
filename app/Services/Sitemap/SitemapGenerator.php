<?php

declare(strict_types=1);

namespace App\Services\Sitemap;

use App\Models\Language;
use App\Models\Project;
use App\Models\SeoEntryTranslation;
use App\Services\SeoService;
use App\Support\ProjectContext;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Sinh sitemap XML theo project (multi-tenant), ưu tiên ngôn ngữ.
 *
 * Cây file / URL:
 *   sitemaps/{projectCode}/sitemap.xml                 → index các locale
 *   sitemaps/{projectCode}/sitemap/{lang}.xml          → index types của locale
 *   sitemaps/{projectCode}/sitemap/{lang}/pages.xml    → urlset trang cứng
 *   sitemaps/{projectCode}/sitemap/{lang}/{type}.xml   → index chunks
 *   sitemaps/{projectCode}/sitemap/{lang}/{type}-{n}.xml
 */
final class SitemapGenerator
{
    public function __construct(private readonly SeoService $seo) {}

    /**
     * @return array{locales: int, types: int, files: int, urls: int}
     */
    public function generateForProject(Project $project, ?string $baseUrl = null): array
    {
        return ProjectContext::run($project, function () use ($project, $baseUrl): array {
            $baseUrl = rtrim($baseUrl ?: $this->resolveBaseUrl($project), '/');
            $root = $this->projectRoot($project);
            $disk = Storage::disk((string) config('sitemap.disk', 'local'));
            $disk->makeDirectory($root.'/sitemap');

            $stats = ['locales' => 0, 'types' => 0, 'files' => 0, 'urls' => 0];
            $now = now()->toIso8601String();
            $mainEntries = [];
            $typesSeen = [];

            foreach ($this->activeLocales() as $locale) {
                $languageId = Language::idByCode($locale);
                if (! $languageId) {
                    continue;
                }

                $langDir = $root.'/sitemap/'.$locale;
                $disk->makeDirectory($langDir);
                $langIndexEntries = [];

                // Trang cứng theo từng locale
                $pagesUrls = $this->buildStaticUrlsForLocale($baseUrl, $locale);
                if ($pagesUrls !== []) {
                    $this->writeAtomic($disk, $langDir.'/pages.xml', SitemapXml::urlset($pagesUrls));
                    $stats['files']++;
                    $stats['urls'] += count($pagesUrls);
                    $langIndexEntries[] = [
                        'loc' => $baseUrl.'/sitemap/'.$locale.'/pages.xml',
                        'lastmod' => $now,
                    ];
                }

                foreach ($this->sitemapTypes() as $type) {
                    $pages = $this->writeTypeLanguageChunks(
                        $disk,
                        $root,
                        $baseUrl,
                        $type,
                        $locale,
                        (int) $languageId,
                        $stats,
                    );

                    if ($pages <= 0) {
                        continue;
                    }

                    $typesSeen[$type] = true;

                    $chunkIndexEntries = [];
                    for ($i = 1; $i <= $pages; $i++) {
                        $chunkIndexEntries[] = [
                            'loc' => $baseUrl.'/sitemap/'.$locale.'/'.$type.'-'.$i.'.xml',
                            'lastmod' => $now,
                        ];
                    }

                    // 1 chunk → trỏ thẳng file urlset; nhiều chunk → index
                    if ($pages === 1) {
                        $langIndexEntries[] = [
                            'loc' => $baseUrl.'/sitemap/'.$locale.'/'.$type.'-1.xml',
                            'lastmod' => $now,
                        ];
                    } else {
                        $this->writeAtomic(
                            $disk,
                            $langDir.'/'.$type.'.xml',
                            SitemapXml::sitemapIndex($chunkIndexEntries),
                        );
                        $stats['files']++;
                        $langIndexEntries[] = [
                            'loc' => $baseUrl.'/sitemap/'.$locale.'/'.$type.'.xml',
                            'lastmod' => $now,
                        ];
                    }
                }

                if ($langIndexEntries === []) {
                    continue;
                }

                $this->writeAtomic(
                    $disk,
                    $root.'/sitemap/'.$locale.'.xml',
                    SitemapXml::sitemapIndex($langIndexEntries),
                );
                $stats['files']++;
                $stats['locales']++;
                $mainEntries[] = [
                    'loc' => $baseUrl.'/sitemap/'.$locale.'.xml',
                    'lastmod' => $now,
                ];
            }

            $stats['types'] = count($typesSeen);
            $this->writeAtomic($disk, $root.'/sitemap.xml', SitemapXml::sitemapIndex($mainEntries));
            $stats['files']++;

            return $stats;
        });
    }

    /**
     * @return list<string>
     */
    public function sitemapTypes(): array
    {
        $configured = config('sitemap.types');
        $all = array_keys(config('seo.types', []));

        if (! is_array($configured) || $configured === []) {
            return $all;
        }

        if (array_is_list($configured)) {
            return array_values(array_intersect($all, array_map('strval', $configured)));
        }

        $out = [];
        foreach ($all as $type) {
            if (($configured[$type] ?? true) === false) {
                continue;
            }
            $out[] = $type;
        }

        return $out;
    }

    public function projectRoot(Project $project): string
    {
        $code = preg_replace('/[^a-z0-9_-]/i', '-', (string) $project->code) ?: 'project';
        $code = trim(strtolower($code), '-') ?: 'project';

        return trim((string) config('sitemap.root', 'sitemaps'), '/').'/'.$code;
    }

    public function resolveBaseUrl(Project $project): string
    {
        // Ưu tiên APP_URL nếu host trùng domain của project (local: vitravel.dev)
        $appUrl = rtrim((string) config('app.url'), '/');
        $appHost = strtolower((string) (parse_url($appUrl, PHP_URL_HOST) ?: ''));
        if ($appHost !== '' && $this->projectOwnsHost($project, $appHost)) {
            $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';

            return $scheme.'://'.$appHost;
        }

        $domain = $project->primary_domain
            ?: $project->domains()->where('is_primary', true)->value('domain')
            ?: $project->domains()->orderBy('id')->value('domain');

        if (is_string($domain) && $domain !== '') {
            $domain = preg_replace('#^https?://#i', '', $domain) ?: $domain;
            $domain = rtrim(strtolower($domain), '/');

            return 'https://'.$domain;
        }

        return $appUrl !== '' ? $appUrl : 'https://localhost';
    }

    public function storagePathFor(Project $project, string $requestPath): string
    {
        $requestPath = ltrim($requestPath, '/');
        if ($requestPath === '' || str_contains($requestPath, '..')) {
            return $this->projectRoot($project).'/sitemap.xml';
        }

        return $this->projectRoot($project).'/'.$requestPath;
    }

    private function projectOwnsHost(Project $project, string $host): bool
    {
        $host = strtolower(preg_replace('/:\d+$/', '', $host) ?: $host);
        if ($host === '') {
            return false;
        }

        if (strtolower((string) ($project->primary_domain ?? '')) === $host) {
            return true;
        }

        return $project->domains()->where('domain', $host)->exists();
    }

    /**
     * @return list<string>
     */
    private function activeLocales(): array
    {
        $fromDb = Language::active()->pluck('code')->filter()->values()->all();
        if ($fromDb !== []) {
            return array_map('strval', $fromDb);
        }

        return collect(config('language.list', []))
            ->filter(fn ($row) => ! empty($row['is_active']) && ! empty($row['code']))
            ->pluck('code')
            ->map(fn ($c) => (string) $c)
            ->values()
            ->all();
    }

    /**
     * @return list<array{loc: string, lastmod: string, changefreq: string, priority: string}>
     */
    private function buildStaticUrlsForLocale(string $baseUrl, string $locale): array
    {
        $paths = config('sitemap.static_paths', []);
        if (! is_array($paths) || $paths === []) {
            return [];
        }

        $defaultLocale = Language::defaultCode();
        $now = now()->toIso8601String();
        $urls = [];

        foreach ($paths as $row) {
            $path = (string) ($row['path'] ?? '');
            if ($path === '') {
                continue;
            }

            $isHome = ($path === '/');
            if ($locale === $defaultLocale) {
                $prefixed = $path;
            } elseif ($isHome) {
                $prefixed = '/'.$locale;
            } else {
                $prefixed = '/'.$locale.$path;
            }

            $loc = $baseUrl.($prefixed === '/' ? '/' : $prefixed);

            $urls[] = [
                'loc' => $loc,
                'lastmod' => $now,
                'changefreq' => (string) ($row['changefreq'] ?? config('sitemap.defaults.changefreq', 'weekly')),
                'priority' => (string) ($row['priority'] ?? config('sitemap.priority_by_type.pages', '0.9')),
            ];
        }

        return $urls;
    }

    /**
     * @param  array{locales: int, types: int, files: int, urls: int}  $stats
     */
    private function writeTypeLanguageChunks(
        $disk,
        string $root,
        string $baseUrl,
        string $type,
        string $locale,
        int $languageId,
        array &$stats,
    ): int {
        if (! Schema::hasTable('seo_entry_translations') || ! Schema::hasTable('seo_entries')) {
            return 0;
        }

        $max = max(1, (int) config('sitemap.max_urls_per_file', 1000));
        $langDir = $root.'/sitemap/'.$locale;
        $disk->makeDirectory($langDir);

        $changefreq = (string) config('sitemap.defaults.changefreq', 'weekly');
        $priority = (string) (
            config("sitemap.priority_by_type.{$type}")
            ?? config('sitemap.defaults.priority', '0.8')
        );

        $page = 0;
        $buffer = [];
        $flush = function () use (
            &$buffer,
            &$page,
            &$stats,
            $disk,
            $langDir,
            $type,
        ): void {
            if ($buffer === []) {
                return;
            }
            $page++;
            $this->writeAtomic($disk, $langDir.'/'.$type.'-'.$page.'.xml', SitemapXml::urlset($buffer));
            $stats['files']++;
            $stats['urls'] += count($buffer);
            $buffer = [];
        };

        SeoEntryTranslation::query()
            ->withoutGlobalScopes()
            ->where('project_id', ProjectContext::id())
            ->where('language_id', $languageId)
            ->where('status', 'published')
            ->whereNotNull('slug_full')
            ->where('slug_full', '!=', '')
            ->whereHas('seoEntry', function ($q) use ($type): void {
                $q->withoutGlobalScopes()
                    ->where('type', $type)
                    ->where('is_indexable', true);
            })
            ->orderBy('id')
            ->select(['id', 'slug_full', 'updated_at', 'published_at'])
            ->chunkById(500, function ($rows) use (
                &$buffer,
                $flush,
                $max,
                $baseUrl,
                $locale,
                $changefreq,
                $priority,
            ): void {
                foreach ($rows as $row) {
                    $slugFull = ltrim((string) $row->slug_full, '/');
                    if ($slugFull === ''
                        || str_starts_with($slugFull, '__trashed-')
                        || str_starts_with($slugFull, '__orphaned-')) {
                        continue;
                    }

                    $path = $this->seo->publicUrl($row, $locale);
                    if ($path === '#' || $path === '') {
                        continue;
                    }

                    $lastmod = $this->formatLastmod($row->updated_at ?? $row->published_at);

                    $buffer[] = [
                        'loc' => $baseUrl.'/'.ltrim($path, '/'),
                        'lastmod' => $lastmod,
                        'changefreq' => $changefreq,
                        'priority' => $priority,
                    ];

                    if (count($buffer) >= $max) {
                        $flush();
                    }
                }
            });

        $flush();

        return $page;
    }

    private function formatLastmod(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }

        if (is_string($value) && $value !== '') {
            $ts = strtotime($value);

            return $ts ? date('c', $ts) : now()->toIso8601String();
        }

        return now()->toIso8601String();
    }

    private function writeAtomic($disk, string $relativePath, string $contents): void
    {
        $dir = dirname($relativePath);
        if ($dir !== '.' && $dir !== '') {
            $disk->makeDirectory($dir);
        }

        $tmp = $dir.'/.tmp-'.basename($relativePath).'.'.uniqid('', true);
        $disk->put($tmp, $contents);

        try {
            $absoluteTmp = $disk->path($tmp);
            $absoluteFinal = $disk->path($relativePath);
            if (is_file($absoluteTmp)) {
                @rename($absoluteTmp, $absoluteFinal);
                if (is_file($absoluteTmp)) {
                    $disk->put($relativePath, $contents);
                    $disk->delete($tmp);
                }

                return;
            }
        } catch (\Throwable) {
            // cloud disk
        }

        $disk->put($relativePath, $contents);
        $disk->delete($tmp);
    }
}
