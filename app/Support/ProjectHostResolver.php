<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Resolve project theo Host (domain) — không dùng cookie / ?project=.
 * Hỗ trợ alias www ↔ non-www.
 */
final class ProjectHostResolver
{
    public static function resolve(?string $host = null): ?Project
    {
        if (! Schema::hasTable('projects')) {
            return null;
        }

        $host = self::normalizeHost($host ?? (string) request()->getHost());
        if ($host === '') {
            return self::fallback();
        }

        foreach (self::hostAliases($host) as $tryHost) {
            $project = self::findByHost($tryHost);
            if ($project) {
                return $project;
            }
        }

        return self::fallback();
    }

    public static function resolveFromRequest(Request $request): ?Project
    {
        return self::resolve($request->getHost());
    }

    /**
     * Base URL canonical cho sitemap.
     * Ưu tiên domain production (bỏ .dev/.local/.test) rồi bỏ www.
     */
    public static function canonicalBaseUrl(Project $project): string
    {
        $hosts = self::rankHostsForCanonical(self::collectProjectHosts($project));
        if ($hosts === []) {
            $appUrl = rtrim((string) config('app.url'), '/');

            return $appUrl !== '' ? $appUrl : 'https://localhost';
        }

        return 'https://'.$hosts[0];
    }

    public static function isDevHost(string $host): bool
    {
        $host = self::normalizeHost($host);
        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1') {
            return true;
        }

        foreach (['.dev', '.local', '.test', '.localhost', '.invalid', '.example', '.lan'] as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sắp xếp host cho sitemap: production trước, giữ thứ tự primary → domains, ưu tiên non-www.
     *
     * @param  list<string>  $hosts
     * @return list<string>
     */
    public static function rankHostsForCanonical(array $hosts): array
    {
        $hosts = array_values(array_unique(array_filter(array_map(
            static fn (string $host): string => self::normalizeHost($host),
            $hosts,
        ))));

        if ($hosts === []) {
            return [];
        }

        $production = array_values(array_filter(
            $hosts,
            static fn (string $host): bool => ! self::isDevHost($host),
        ));
        $candidates = $production !== [] ? $production : $hosts;

        if (! (bool) config('sitemap.prefer_non_www', true)) {
            return $candidates;
        }

        $order = array_flip($hosts);
        usort($candidates, static function (string $a, string $b) use ($order): int {
            $aWww = str_starts_with($a, 'www.') ? 1 : 0;
            $bWww = str_starts_with($b, 'www.') ? 1 : 0;
            if ($aWww !== $bWww) {
                return $aWww <=> $bWww;
            }

            return ($order[$a] ?? 999) <=> ($order[$b] ?? 999);
        });

        return $candidates;
    }

    /**
     * @return list<string>
     */
    public static function hostAliases(string $host): array
    {
        $host = self::normalizeHost($host);
        if ($host === '') {
            return [];
        }

        $aliases = [$host];
        if (str_starts_with($host, 'www.')) {
            $aliases[] = substr($host, 4);
        } else {
            $aliases[] = 'www.'.$host;
        }

        return array_values(array_unique(array_filter($aliases)));
    }

    /**
     * @return list<string>
     */
    public static function collectProjectHosts(Project $project): array
    {
        $hosts = [];
        if (filled($project->primary_domain)) {
            $hosts[] = self::normalizeHost((string) $project->primary_domain);
        }

        if (Schema::hasTable('project_domains')) {
            foreach ($project->domains()->pluck('domain') as $domain) {
                $hosts[] = self::normalizeHost((string) $domain);
            }
        }

        return array_values(array_unique(array_filter($hosts)));
    }

    private static function findByHost(string $host): ?Project
    {
        if (Schema::hasTable('project_domains')) {
            $domain = ProjectDomain::query()
                ->where('domain', $host)
                ->with(['project' => fn ($q) => $q->where('is_active', true)])
                ->first();

            if ($domain?->project) {
                return $domain->project;
            }
        }

        if (Schema::hasColumn('projects', 'primary_domain')) {
            return Project::query()
                ->active()
                ->where('primary_domain', $host)
                ->first();
        }

        return null;
    }

    private static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('#^https?://#i', '', $host) ?: $host;
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;

        return rtrim($host, '/');
    }

    private static function fallback(): ?Project
    {
        $defaultCode = trim((string) config('project.default_code', ''));
        if ($defaultCode !== '') {
            $byCode = Project::query()->active()->where('code', $defaultCode)->first()
                ?? Project::query()->active()->where('seed_profile', $defaultCode)->first();
            if ($byCode) {
                return $byCode;
            }
        }

        return Project::query()->active()->orderBy('id')->first();
    }
}
