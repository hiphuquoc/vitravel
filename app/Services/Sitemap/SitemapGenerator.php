<?php

declare(strict_types=1);

namespace App\Services\Sitemap;

use App\Models\Language;
use App\Models\Project;
use App\Models\SeoEntryTranslation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\SeoService;
use App\Support\ProjectContext;
use App\Support\ProjectHostResolver;
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
            $this->ensureStorageRoot();

            $baseUrl = rtrim($baseUrl ?: $this->resolveBaseUrl($project), '/');
            $root = $this->projectRoot($project);
            $disk = $this->disk();
            $this->ensureRelativeDir($disk, $root.'/sitemap');

            $stats = ['locales' => 0, 'types' => 0, 'files' => 0, 'urls' => 0];
            $now = now()->toIso8601String();
            $mainEntries = [];
            $typesSeen = [];

        foreach ($this->activeLocales() as $locale) {
            $languageId = Language::idByCode($locale);

            $langDir = $root.'/sitemap/'.$locale;
            $this->ensureRelativeDir($disk, $langDir);
            $langIndexEntries = [];

            // Trang cứng theo từng locale (không phụ thuộc language_id DB)
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

            if (! $languageId) {
                if ($langIndexEntries !== []) {
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

                continue;
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
            return $this->expandSitemapTypes($all);
        }

        if (array_is_list($configured)) {
            return $this->expandSitemapTypes(array_values(array_intersect($all, array_map('strval', $configured))));
        }

        $out = [];
        foreach ($all as $type) {
            if (($configured[$type] ?? true) === false) {
                continue;
            }
            $out[] = $type;
        }

        return $this->expandSitemapTypes($out);
    }

    /**
     * @param  list<string>  $types
     * @return list<string>
     */
    private function expandSitemapTypes(array $types): array
    {
        if (! (bool) config('sitemap.split_service_by_cluster', true)) {
            return $types;
        }

        $clusters = array_keys(config('services_catalog.clusters', []));
        $expanded = [];

        foreach ($types as $type) {
            if ($type === 'service') {
                foreach ($clusters as $cluster) {
                    $expanded[] = 'service_'.$cluster;
                }

                continue;
            }

            if ($type === 'service_category') {
                foreach ($clusters as $cluster) {
                    $expanded[] = 'service_category_'.$cluster;
                }

                continue;
            }

            $expanded[] = $type;
        }

        return $expanded;
    }

    /**
     * @return array{seo_type: string, cluster: ?string, reference_class: ?class-string}
     */
    private function resolveSitemapTypeFilter(string $type): array
    {
        $clusters = config('services_catalog.clusters', []);

        if (str_starts_with($type, 'service_category_')) {
            $cluster = substr($type, strlen('service_category_'));
            if (isset($clusters[$cluster])) {
                return [
                    'seo_type' => 'service_category',
                    'cluster' => $cluster,
                    'reference_class' => ServiceCategory::class,
                ];
            }
        }

        if (str_starts_with($type, 'service_')) {
            $cluster = substr($type, strlen('service_'));
            if (isset($clusters[$cluster])) {
                return [
                    'seo_type' => 'service',
                    'cluster' => $cluster,
                    'reference_class' => Service::class,
                ];
            }
        }

        return ['seo_type' => $type, 'cluster' => null, 'reference_class' => null];
    }

    public function projectRoot(Project $project): string
    {
        $code = preg_replace('/[^a-z0-9_-]/i', '-', (string) $project->code) ?: 'project';
        $code = trim(strtolower($code), '-') ?: 'project';
        $root = trim((string) config('sitemap.root', ''), '/');

        return $root !== '' ? $root.'/'.$code : $code;
    }

    /**
     * Đường dẫn đọc file (ưu tiên path mới, fallback path cũ khi migrate).
     *
     * @return list<string>
     */
    public function storagePathCandidates(Project $project, string $requestPath): array
    {
        $requestPath = ltrim($requestPath, '/');
        $primary = $this->storagePathFor($project, $requestPath);
        $code = basename(str_replace('\\', '/', $this->projectRoot($project)));

        return array_values(array_unique([
            $primary,
            'sitemaps/'.$code.'/'.$requestPath,
        ]));
    }

    public function resolveBaseUrl(Project $project): string
    {
        $override = trim((string) config('sitemap.canonical_base_url', ''));
        if ($override !== '') {
            return rtrim($override, '/');
        }

        $byProject = config('sitemap.canonical_base_url_by_project', []);
        if (is_array($byProject)) {
            $code = (string) $project->code;
            $projectOverride = trim((string) ($byProject[$code] ?? ''));
            if ($projectOverride !== '') {
                return rtrim($projectOverride, '/');
            }
        }

        // Local: APP_URL khi trùng domain project (vd. vitravel.dev). Production: luôn từ DB.
        if (app()->environment('local')) {
            $appUrl = rtrim((string) config('app.url'), '/');
            $appHost = strtolower((string) (parse_url($appUrl, PHP_URL_HOST) ?: ''));
            if ($appHost !== '' && $this->projectOwnsHost($project, $appHost)) {
                $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';

                return $scheme.'://'.$appHost;
            }
        }

        return ProjectHostResolver::canonicalBaseUrl($project);
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
        foreach (ProjectHostResolver::hostAliases($host) as $alias) {
            if (in_array($alias, ProjectHostResolver::collectProjectHosts($project), true)) {
                return true;
            }
        }

        return false;
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
        $this->ensureRelativeDir($disk, $langDir);

        $changefreq = (string) config('sitemap.defaults.changefreq', 'weekly');
        $priority = (string) (
            config("sitemap.priority_by_type.{$type}")
            ?? (str_starts_with($type, 'service_category_')
                ? config('sitemap.priority_by_type.service_category')
                : (str_starts_with($type, 'service_')
                    ? config('sitemap.priority_by_type.service')
                    : null))
            ?? config('sitemap.defaults.priority', '0.8')
        );

        ['seo_type' => $seoType, 'cluster' => $cluster, 'reference_class' => $referenceClass] = $this->resolveSitemapTypeFilter($type);

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
            ->whereHas('seoEntry', function ($q) use ($seoType, $cluster, $referenceClass): void {
                $q->withoutGlobalScopes()
                    ->where('type', $seoType)
                    ->where('is_indexable', true);

                if ($cluster !== null && $referenceClass !== null) {
                    $q->where('reference_type', (new $referenceClass)->getMorphClass())
                        ->whereHasMorph(
                            'reference',
                            [$referenceClass],
                            function ($rq) use ($cluster, $referenceClass): void {
                                $rq->withoutGlobalScopes()->where('cluster', $cluster);
                                if ($referenceClass === ServiceCategory::class) {
                                    $rq->where('is_active', true);
                                }
                            },
                        );
                }
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
            $this->ensureRelativeDir($disk, $dir);
        }

        $tmp = $dir.'/.tmp-'.basename($relativePath).'.'.uniqid('', true);
        $disk->put($tmp, $contents);

        try {
            $absoluteTmp = $disk->path($tmp);
            $absoluteFinal = $disk->path($relativePath);
            if (is_file($absoluteTmp)) {
                @rename($absoluteTmp, $absoluteFinal);
                @chmod($absoluteFinal, 0664);
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

    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk((string) config('sitemap.disk', 'sitemap'));
    }

    public function ensureStorageRoot(): void
    {
        $root = storage_path('app/sitemaps');
        $this->ensureAbsoluteDir($root);
    }

    private function ensureRelativeDir(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $relativePath): void
    {
        try {
            $absolute = $disk->path($relativePath);
            $this->ensureAbsoluteDir($absolute);
        } catch (\Throwable) {
            $disk->makeDirectory($relativePath);
        }
    }

    private function ensureAbsoluteDir(string $absolutePath): void
    {
        if (is_dir($absolutePath)) {
            @chmod($absolutePath, 0775);

            return;
        }

        if (! @mkdir($absolutePath, 0775, true) && ! is_dir($absolutePath)) {
            throw new \RuntimeException(
                "Không tạo được thư mục sitemap: {$absolutePath}. "
                .'Chạy: sudo chown -R $USER:www-data storage && chmod -R 775 storage/app/sitemaps'
            );
        }

        @chmod($absolutePath, 0775);
    }
}
