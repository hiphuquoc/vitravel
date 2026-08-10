<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\User;
use App\Support\AdminAccess;
use App\Support\ApiResponse;
use App\Support\ProjectContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResolveAdminProject
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('projects')) {
            return $next($request);
        }

        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Thiếu token xác thực.', 'UNAUTHENTICATED', 401);
        }

        $headerCode = trim((string) $request->header(
            (string) config('project.admin_header_code', 'X-Project-Code'),
            ''
        ));
        $headerId = trim((string) $request->header(
            (string) config('project.admin_header_id', 'X-Project-Id'),
            ''
        ));

        if ($headerCode === '' && $headerId === '' && $request->isMethod('GET')) {
            $headerCode = trim((string) $request->query('project', ''));
            if ($headerCode === '' && $request->filled('project_id')) {
                $headerId = (string) $request->query('project_id');
            }
        }

        $project = null;

        if ($headerId !== '' || $headerCode !== '') {
            $project = $this->loadRequestedProject($headerId, $headerCode);
            if (! $project) {
                return ApiResponse::error('Dự án không tồn tại hoặc không hoạt động.', 'PROJECT_NOT_FOUND', 404);
            }
            if (! $this->userCanAccess($user, $project)) {
                return ApiResponse::error('Không có quyền truy cập dự án này.', 'PROJECT_FORBIDDEN', 403);
            }
        } else {
            $project = $this->softResolve($user);
            $requireHeader = (bool) config('project.require_admin_project_header', false);
            if (! $project && $requireHeader) {
                return $this->projectRequiredResponse($user);
            }
            if (! $project) {
                return $this->projectRequiredResponse($user);
            }
        }

        ProjectContext::set($project);
        \App\Support\ProjectSeed::flush();
        $request->attributes->set('currentProject', $project);

        return $next($request);
    }

    private function loadRequestedProject(string $headerId, string $headerCode): ?Project
    {
        $query = Project::query()->active();

        if ($headerId !== '' && ctype_digit($headerId)) {
            return $query->where('id', (int) $headerId)->first();
        }

        if ($headerCode !== '') {
            return $query->where('code', $headerCode)->first();
        }

        return null;
    }

    private function softResolve(User $user): ?Project
    {
        $accessible = $this->accessibleProjects($user);

        if ($accessible->count() === 1) {
            return $accessible->first();
        }

        $systemCount = Project::query()->active()->count();
        if ($systemCount === 1) {
            return Project::query()->active()->orderBy('id')->first();
        }

        $defaultCode = trim((string) config('project.default_code', ''));
        if ($defaultCode !== '') {
            $default = $accessible->firstWhere('code', $defaultCode)
                ?? Project::query()->active()->where('code', $defaultCode)->first();

            if ($default && $this->userCanAccess($user, $default)) {
                return $default;
            }
        }

        return null;
    }

    private function userCanAccess(User $user, Project $project): bool
    {
        return AdminAccess::canAccessProject($user, $project);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Project>
     */
    private function accessibleProjects(User $user)
    {
        if (AdminAccess::isSuperAdmin($user)) {
            return Project::query()->active()->orderBy('name')->get();
        }

        return $user->projects()->where('projects.is_active', true)->orderBy('projects.name')->get();
    }

    private function projectRequiredResponse(User $user): Response
    {
        $list = $this->accessibleProjects($user)->map(fn (Project $p) => [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'primary_domain' => $p->primary_domain,
        ])->values()->all();

        return ApiResponse::error(
            'Cần chọn dự án (header X-Project-Code hoặc X-Project-Id).',
            'PROJECT_REQUIRED',
            400,
            ['projects' => $list],
        );
    }
}
