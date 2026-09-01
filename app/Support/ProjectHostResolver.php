<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Resolve project theo Host (domain) — không dùng cookie / ?project=.
 * Dùng cho sitemap, robots, và các endpoint phải gắn chặt với domain.
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
            $byPrimary = Project::query()
                ->active()
                ->where('primary_domain', $host)
                ->first();

            if ($byPrimary) {
                return $byPrimary;
            }
        }

        return self::fallback();
    }

    public static function resolveFromRequest(Request $request): ?Project
    {
        return self::resolve($request->getHost());
    }

    private static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));

        return preg_replace('/:\d+$/', '', $host) ?: $host;
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
