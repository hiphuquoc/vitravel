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
     * Base URL canonical cho sitemap (ưu tiên bỏ www nếu có cả hai).
     */
    public static function canonicalBaseUrl(Project $project): string
    {
        $hosts = self::collectProjectHosts($project);
        if ($hosts === []) {
            $appUrl = rtrim((string) config('app.url'), '/');

            return $appUrl !== '' ? $appUrl : 'https://localhost';
        }

        $preferNonWww = (bool) config('sitemap.prefer_non_www', true);
        if ($preferNonWww) {
            foreach ($hosts as $host) {
                if (! str_starts_with($host, 'www.')) {
                    return 'https://'.$host;
                }
            }
        }

        return 'https://'.$hosts[0];
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
