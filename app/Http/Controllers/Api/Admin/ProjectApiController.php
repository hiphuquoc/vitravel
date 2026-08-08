<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Support\ApiResponse;
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
            'domains' => $project->domains->map(fn ($d) => [
                'domain' => $d->domain,
                'is_primary' => (bool) $d->is_primary,
            ])->values()->all(),
        ];
    }
}
