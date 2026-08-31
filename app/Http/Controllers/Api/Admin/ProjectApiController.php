<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\AI\SeoPromptRules;
use App\Support\AdminAccess;
use App\Support\ApiResponse;
use App\Support\ProjectContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProjectApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $projects = $this->accessibleQuery($user)
            ->orderBy('name')
            ->get()
            ->map(fn (Project $p) => $this->serialize($p))
            ->values()
            ->all();

        return ApiResponse::success(['projects' => $projects]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $project = $this->accessibleQuery($user)->where('projects.id', $id)->first();
        if (! $project) {
            return ApiResponse::error('Dự án không tồn tại.', 'NOT_FOUND', 404);
        }

        return ApiResponse::success($this->serialize($project));
    }

    public function settings(Request $request): JsonResponse
    {
        $project = $this->resolveCurrentProject($request);
        if ($project instanceof JsonResponse) {
            return $project;
        }

        return ApiResponse::success($this->settingsPayload($project));
    }

    public function updateSettings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $project = $this->resolveCurrentProject($request);
        if ($project instanceof JsonResponse) {
            return $project;
        }

        if (! AdminAccess::can($user, 'settings.update', $project)) {
            return ApiResponse::error('Không có quyền sửa cài đặt dự án.', 'FORBIDDEN', 403);
        }

        $validated = $request->validate([
            'ai_brief' => ['nullable', 'string', 'max:'.SeoPromptRules::PROJECT_BRIEF_MAX],
        ]);

        $config = is_array($project->config) ? $project->config : [];
        $brief = trim((string) ($validated['ai_brief'] ?? ''));
        if ($brief === '') {
            unset($config['ai_brief']);
        } else {
            $config['ai_brief'] = SeoPromptRules::clipProjectBrief($brief);
        }
        $project->config = $config;
        $project->save();

        ProjectContext::set($project);

        return ApiResponse::success($this->settingsPayload($project), 'Đã lưu cài đặt dự án.');
    }

    private function resolveCurrentProject(Request $request): Project|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $project = ProjectContext::get();
        if (! $project) {
            return ApiResponse::error('Thiếu project context (header X-Project-Code).', 'PROJECT_REQUIRED', 400);
        }
        if (! $this->accessibleQuery($user)->where('projects.id', $project->id)->exists()) {
            return ApiResponse::error('Không có quyền truy cập dự án này.', 'FORBIDDEN', 403);
        }

        return $project;
    }

    /** @return array{id: int, code: string, name: string, ai_brief: string} */
    private function settingsPayload(Project $project): array
    {
        $config = is_array($project->config) ? $project->config : [];

        return [
            'id' => $project->id,
            'code' => $project->code,
            'name' => $project->name,
            'ai_brief' => trim((string) ($config['ai_brief'] ?? '')),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    private function accessibleQuery(User $user)
    {
        if ($user->isAdmin() || ! Schema::hasTable('project_user')) {
            return Project::query()->active();
        }

        return $user->projects()->where('projects.is_active', true);
    }

    private function serialize(Project $project): array
    {
        $project->loadMissing('domains');

        return [
            'id' => $project->id,
            'code' => $project->code,
            'name' => $project->name,
            'primary_domain' => $project->primary_domain,
            'seed_profile' => $project->seed_profile,
            'is_active' => (bool) $project->is_active,
            'media_prefix' => $project->media_prefix,
            'ai_brief' => trim((string) (is_array($project->config) ? ($project->config['ai_brief'] ?? '') : '')),
            'domains' => $project->domains->map(fn ($d) => [
                'domain' => $d->domain,
                'is_primary' => (bool) $d->is_primary,
            ])->values()->all(),
        ];
    }
}
