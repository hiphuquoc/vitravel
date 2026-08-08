<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\ProjectDomain;
use App\Support\ProjectContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResolveProjectFromHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/v1/admin', 'api/v1/admin/*')) {
            return $next($request);
        }

        if (! Schema::hasTable('projects')) {
            return $next($request);
        }

        $allowOverride = (bool) config('project.allow_public_query_override', false);

        if ($allowOverride) {
            view()->share(
                'publicProjects',
                Project::query()->active()->orderBy('name')->get()
            );
        }

        $project = $this->resolve($request, $allowOverride);

        if ($project) {
            ProjectContext::set($project);
            view()->share('currentProject', $project);
        }

        return $next($request);
    }

    private function resolve(Request $request, bool $allowOverride): ?Project
    {
        // a) ?project= / ?_project= (code) → cookie + use
        if ($allowOverride) {
            $queryCode = trim((string) ($request->query('project') ?? $request->query('_project') ?? ''));
            if ($queryCode !== '') {
                $byQuery = $this->findActiveByCode($queryCode);
                if ($byQuery) {
                    $this->queueProjectCookie($byQuery->code);

                    return $byQuery;
                }
            }

            // b) cookie vt_project
            $cookieName = (string) config('project.public_project_cookie', 'vt_project');
            $cookieCode = trim((string) $request->cookie($cookieName, ''));
            if ($cookieCode !== '') {
                $byCookie = $this->findActiveByCode($cookieCode);
                if ($byCookie) {
                    return $byCookie;
                }
            }
        }

        // c) Host → project_domains
        $host = strtolower((string) $request->getHost());
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;

        if ($host !== '' && Schema::hasTable('project_domains')) {
            $domain = ProjectDomain::query()
                ->where('domain', $host)
                ->with(['project' => fn ($q) => $q->where('is_active', true)])
                ->first();

            if ($domain?->project) {
                return $domain->project;
            }
        }

        // d) PROJECT_DEFAULT_CODE
        $defaultCode = trim((string) config('project.default_code', ''));
        if ($defaultCode !== '') {
            $byDefault = $this->findActiveByCode($defaultCode);
            if ($byDefault) {
                return $byDefault;
            }
        }

        // e) first active
        return Project::query()->active()->orderBy('id')->first();
    }

    private function findActiveByCode(string $code): ?Project
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        return Project::query()->active()->where('code', $code)->first()
            ?? Project::query()->active()->where('seed_profile', $code)->first();
    }

    private function queueProjectCookie(string $code): void
    {
        $name = (string) config('project.public_project_cookie', 'vt_project');

        Cookie::queue(cookie(
            $name,
            $code,
            60 * 24 * 30,
            '/',
            null,
            null,
            false, // httpOnly false — JS ok
            false,
            'lax'
        ));
    }
}
