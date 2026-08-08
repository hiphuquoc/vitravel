<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Support\AdminAccess;
use App\Support\ApiResponse;
use App\Support\ProjectContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserApiController extends Controller
{
    public function meta(Request $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! AdminAccess::isSuperAdmin($actor)) {
            return ApiResponse::error('Chỉ quản trị hệ thống mới xem được người dùng.', 'FORBIDDEN', 403);
        }

        $projects = $this->manageableProjects($actor)->map(fn (Project $p) => [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
        ])->values()->all();

        return ApiResponse::success([
            'system_roles' => $this->systemRoleOptions($actor),
            'project_roles' => collect(config('admin_permissions.project_roles', []))
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'permission_groups' => AdminAccess::catalogGrouped(),
            'projects' => $projects,
            'can_assign_super_admin' => AdminAccess::isSuperAdmin($actor),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $project = ProjectContext::get();

        if (! AdminAccess::isSuperAdmin($actor)) {
            return ApiResponse::error('Chỉ quản trị hệ thống mới xem được người dùng.', 'FORBIDDEN', 403);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $role = trim((string) $request->query('role', ''));

        $query = User::query()->with(['projects' => fn ($q) => $q->orderBy('name')]);

        if (! AdminAccess::isSuperAdmin($actor)) {
            $projectIds = $this->manageableProjects($actor)->pluck('id');
            $query->where(function ($q) use ($projectIds) {
                $q->whereHas('projects', fn ($p) => $p->whereIn('projects.id', $projectIds))
                    ->orWhereIn('role', ['admin', 'super_admin']);
            });
            // Hide other super admins' emails from project admins? Keep list but no edit later.
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($role !== '') {
            $query->where('role', $role);
        }

        if ($project && ! AdminAccess::isSuperAdmin($actor)) {
            $query->whereHas('projects', fn ($p) => $p->where('projects.id', $project->id));
        } elseif ($project && $request->boolean('current_project_only')) {
            $query->whereHas('projects', fn ($p) => $p->where('projects.id', $project->id));
        }

        $paginator = $query->orderByDesc('id')->paginate($perPage);

        $items = collect($paginator->items())->map(fn (User $u) => $this->mapList($u))->values()->all();

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! AdminAccess::isSuperAdmin($actor)) {
            return ApiResponse::error('Chỉ quản trị hệ thống mới xem được người dùng.', 'FORBIDDEN', 403);
        }

        $user = User::query()->with(['projects' => fn ($q) => $q->orderBy('name')])->find($id);
        if (! $user) {
            return ApiResponse::error('Không tìm thấy người dùng.', 'NOT_FOUND', 404);
        }

        if (! $this->actorCanSee($actor, $user)) {
            return ApiResponse::error('Không có quyền xem người dùng này.', 'FORBIDDEN', 403);
        }

        return ApiResponse::success($this->mapDetail($user));
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! AdminAccess::isSuperAdmin($actor)) {
            return ApiResponse::error('Chỉ quản trị hệ thống mới quản lý người dùng.', 'FORBIDDEN', 403);
        }

        try {
            $validated = $this->validatePayload($request, null, $actor);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $user = DB::transaction(function () use ($validated) {
                $user = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role' => $validated['role'],
                    'is_active' => $validated['is_active'],
                ]);
                $this->syncProjects($user, $validated['projects'] ?? []);

                return $user->fresh(['projects']);
            });
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error('Không tạo được người dùng.', 'USER_CREATE_FAILED', 500);
        }

        return ApiResponse::success($this->mapDetail($user), 'Đã tạo người dùng', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! AdminAccess::isSuperAdmin($actor)) {
            return ApiResponse::error('Chỉ quản trị hệ thống mới quản lý người dùng.', 'FORBIDDEN', 403);
        }

        $user = User::query()->with('projects')->find($id);
        if (! $user) {
            return ApiResponse::error('Không tìm thấy người dùng.', 'NOT_FOUND', 404);
        }

        if (! $this->actorCanManage($actor, $user)) {
            return ApiResponse::error('Không có quyền sửa người dùng này.', 'FORBIDDEN', 403);
        }

        try {
            $validated = $this->validatePayload($request, $user, $actor);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            DB::transaction(function () use ($user, $validated, $actor) {
                $user->name = $validated['name'];
                $user->email = $validated['email'];
                $user->is_active = $validated['is_active'];

                if (AdminAccess::isSuperAdmin($actor)) {
                    $user->role = $validated['role'];
                }

                if (! empty($validated['password'])) {
                    $user->password = $validated['password'];
                }

                $user->save();
                $this->syncProjects($user, $validated['projects'] ?? []);
            });
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error('Không cập nhật được người dùng.', 'USER_UPDATE_FAILED', 500);
        }

        return ApiResponse::success($this->mapDetail($user->fresh(['projects'])), 'Đã cập nhật người dùng');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! AdminAccess::isSuperAdmin($actor)) {
            return ApiResponse::error('Chỉ quản trị hệ thống mới quản lý người dùng.', 'FORBIDDEN', 403);
        }

        $user = User::query()->find($id);
        if (! $user) {
            return ApiResponse::error('Không tìm thấy người dùng.', 'NOT_FOUND', 404);
        }

        if ($user->id === $actor->id) {
            return ApiResponse::error('Không thể tự xóa tài khoản đang đăng nhập.', 'FORBIDDEN', 403);
        }

        if (! $this->actorCanManage($actor, $user)) {
            return ApiResponse::error('Không có quyền xóa người dùng này.', 'FORBIDDEN', 403);
        }

        if (AdminAccess::isSuperAdmin($user) && ! AdminAccess::isSuperAdmin($actor)) {
            return ApiResponse::error('Không thể xóa quản trị hệ thống.', 'FORBIDDEN', 403);
        }

        $user->adminApiTokens()->delete();
        $user->projects()->detach();
        $user->delete();

        return ApiResponse::success(null, 'Đã xóa người dùng');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?User $existing, User $actor): array
    {
        $systemRoles = array_keys(config('admin_permissions.system_roles', []));
        $projectRoles = array_keys(config('admin_permissions.project_roles', []));
        $permissionKeys = array_keys(config('admin_permissions.permissions', []));

        $rules = [
            'name' => 'required|string|max:120',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($existing?->id),
            ],
            'password' => ($existing ? 'nullable' : 'required').'|string|min:6|max:100',
            'is_active' => 'sometimes|boolean',
            'role' => ['sometimes', 'string', Rule::in($systemRoles)],
            'projects' => 'nullable|array',
            'projects.*.project_id' => 'required|integer|exists:projects,id',
            'projects.*.role' => ['required', 'string', Rule::in($projectRoles)],
            'projects.*.permissions' => 'nullable|array',
            'projects.*.permissions.grant' => 'nullable|array',
            'projects.*.permissions.grant.*' => [
                'string',
                Rule::in($permissionKeys),
                Rule::notIn(['users.view', 'users.manage']),
            ],
            'projects.*.permissions.deny' => 'nullable|array',
            'projects.*.permissions.deny.*' => [
                'string',
                Rule::in($permissionKeys),
                Rule::notIn(['users.view', 'users.manage']),
            ],
        ];

        $validated = $request->validate($rules);
        $validated['is_active'] = array_key_exists('is_active', $validated)
            ? (bool) $validated['is_active']
            : ($existing?->is_active ?? true);

        $role = $validated['role'] ?? $existing?->role ?? 'staff';
        if (! AdminAccess::isSuperAdmin($actor)) {
            $role = 'staff';
            // Project admin cannot elevate to system admin.
        }
        if (in_array($role, ['admin', 'super_admin'], true) && ! AdminAccess::isSuperAdmin($actor)) {
            throw ValidationException::withMessages([
                'role' => 'Chỉ siêu quản trị mới gán quyền hệ thống.',
            ]);
        }
        $validated['role'] = $role;

        $projects = $validated['projects'] ?? [];
        $manageableIds = $this->manageableProjects($actor)->pluck('id')->all();

        foreach ($projects as $row) {
            $pid = (int) $row['project_id'];
            if (! AdminAccess::isSuperAdmin($actor) && ! in_array($pid, $manageableIds, true)) {
                throw ValidationException::withMessages([
                    'projects' => 'Không được gán dự án ngoài phạm vi quản lý.',
                ]);
            }
        }

        // Staff must belong to ≥1 project unless super admin.
        if (! in_array($validated['role'], ['admin', 'super_admin'], true) && $projects === []) {
            throw ValidationException::withMessages([
                'projects' => 'Nhân sự cần được gán ít nhất một dự án.',
            ]);
        }

        $validated['projects'] = $projects;

        return $validated;
    }

    /**
     * @param  list<array{project_id: int, role: string, permissions?: array<string, mixed>|null}>  $projects
     */
    private function syncProjects(User $user, array $projects): void
    {
        if (! Schema::hasTable('project_user')) {
            return;
        }

        $sync = [];
        foreach ($projects as $row) {
            $pid = (int) $row['project_id'];
            $perms = $row['permissions'] ?? null;
            if (is_array($perms)) {
                $grant = array_values(array_unique(array_filter((array) ($perms['grant'] ?? []))));
                $deny = array_values(array_unique(array_filter((array) ($perms['deny'] ?? []))));
                $perms = ($grant === [] && $deny === []) ? null : ['grant' => $grant, 'deny' => $deny];
            } else {
                $perms = null;
            }

            $sync[$pid] = [
                'role' => (string) ($row['role'] ?? 'editor'),
                'permissions' => $perms ? json_encode($perms, JSON_UNESCAPED_UNICODE) : null,
            ];
        }

        // When casting JSON on pivot, Laravel may want array — use detach/attach manually if needed.
        $user->projects()->detach();
        foreach ($sync as $pid => $attrs) {
            $user->projects()->attach($pid, $attrs);
        }
    }

    private function actorCanSee(User $actor, User $target): bool
    {
        if (AdminAccess::isSuperAdmin($actor)) {
            return true;
        }

        if ($actor->id === $target->id) {
            return true;
        }

        $actorProjectIds = $this->manageableProjects($actor)->pluck('id');
        if ($actorProjectIds->isEmpty()) {
            return false;
        }

        return $target->projects()->whereIn('projects.id', $actorProjectIds)->exists();
    }

    private function actorCanManage(User $actor, User $target): bool
    {
        if (AdminAccess::isSuperAdmin($actor)) {
            return true;
        }

        if (AdminAccess::isSuperAdmin($target)) {
            return false;
        }

        return $this->actorCanSee($actor, $target);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Project>
     */
    private function manageableProjects(User $actor)
    {
        if (AdminAccess::isSuperAdmin($actor)) {
            return Project::query()->active()->orderBy('name')->get();
        }

        return $actor->projects()
            ->where('projects.is_active', true)
            ->wherePivotIn('role', ['owner', 'admin'])
            ->orderBy('projects.name')
            ->get();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function systemRoleOptions(User $actor): array
    {
        $all = config('admin_permissions.system_roles', []);
        if (! AdminAccess::isSuperAdmin($actor)) {
            $all = array_intersect_key($all, array_flip(['staff']));
        }

        return collect($all)
            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapList(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => config('admin_permissions.system_roles.'.$user->role, $user->role),
            'is_active' => (bool) $user->is_active,
            'is_super_admin' => AdminAccess::isSuperAdmin($user),
            'projects_count' => $user->projects->count(),
            'projects' => $user->projects->map(fn (Project $p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'role' => (string) ($p->pivot->role ?? 'viewer'),
            ])->values()->all(),
            'created_at' => optional($user->created_at)?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDetail(User $user): array
    {
        $base = $this->mapList($user);
        $base['projects'] = $user->projects->map(function (Project $p) use ($user) {
            $raw = $p->pivot->permissions ?? null;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $raw = is_array($decoded) ? $decoded : null;
            }

            return [
                'project_id' => $p->id,
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'role' => (string) ($p->pivot->role ?? 'viewer'),
                'permissions' => [
                    'grant' => array_values((array) ($raw['grant'] ?? [])),
                    'deny' => array_values((array) ($raw['deny'] ?? [])),
                ],
                'effective_permissions' => AdminAccess::permissionsFor($user, $p),
            ];
        })->values()->all();

        return $base;
    }
}
