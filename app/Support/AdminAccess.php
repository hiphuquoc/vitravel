<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Multi-project admin RBAC helpers.
 */
final class AdminAccess
{
    public static function isSuperAdmin(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    /** User được vào console (active + super admin hoặc có ≥1 project). */
    public static function canAccessConsole(User $user): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if (self::isSuperAdmin($user)) {
            return true;
        }

        if (! Schema::hasTable('project_user')) {
            return false;
        }

        return $user->projects()->where('projects.is_active', true)->exists();
    }

    public static function canAccessProject(User $user, Project $project): bool
    {
        if (self::isSuperAdmin($user)) {
            return true;
        }

        if (! Schema::hasTable('project_user')) {
            return false;
        }

        return $user->projects()->where('projects.id', $project->id)->exists();
    }

    /**
     * @return list<string>
     */
    public static function permissionsFor(User $user, ?Project $project = null): array
    {
        $all = array_keys(config('admin_permissions.permissions', []));

        if (self::isSuperAdmin($user)) {
            return $all;
        }

        if (! $project) {
            return [];
        }

        $pivot = $user->projects()
            ->where('projects.id', $project->id)
            ->first()?->pivot;

        if (! $pivot) {
            return [];
        }

        $role = (string) ($pivot->role ?: 'viewer');
        $granted = self::expandRolePermissions($role);

        $overrides = $pivot->permissions ?? null;
        if (is_string($overrides)) {
            $decoded = json_decode($overrides, true);
            $overrides = is_array($decoded) ? $decoded : null;
        }

        if (is_array($overrides)) {
            $extra = array_values(array_filter(
                (array) ($overrides['grant'] ?? []),
                fn ($p) => is_string($p) && $p !== ''
            ));
            $deny = array_values(array_filter(
                (array) ($overrides['deny'] ?? []),
                fn ($p) => is_string($p) && $p !== ''
            ));
            $granted = array_values(array_unique(array_merge($granted, $extra)));
            if ($deny !== []) {
                $granted = array_values(array_diff($granted, $deny));
            }
        }

        // Quản lý tài khoản admin chỉ dành cho siêu quản trị hệ thống.
        // Vai trò dự án (kể cả owner/*) không xem/sửa user — kể cả chính mình — trong module Người dùng.
        // Hồ sơ cá nhân dùng PUT /auth/me (/account), không qua /users.
        $granted = array_values(array_diff($granted, ['users.view', 'users.manage']));

        return array_values(array_intersect($granted, $all));
    }

    public static function can(User $user, string $permission, ?Project $project = null): bool
    {
        if ($permission === '' || $permission === '*') {
            return self::isSuperAdmin($user);
        }

        if (self::isSuperAdmin($user)) {
            return true;
        }

        return in_array($permission, self::permissionsFor($user, $project), true);
    }

    /**
     * Resolve required permission for an admin API request path.
     */
    public static function permissionForRequest(Request $request): ?string
    {
        $path = trim($request->path(), '/');
        // api/v1/admin/...
        if (Str::startsWith($path, 'api/v1/admin/')) {
            $path = substr($path, strlen('api/v1/admin/'));
        }

        $method = strtoupper($request->method());
        $overrides = config('admin_permissions.route_overrides', []);

        foreach ($overrides as $pattern => $permission) {
            [$patMethod, $patPath] = array_pad(explode(' ', (string) $pattern, 2), 2, '');
            if (strtoupper((string) $patMethod) !== $method) {
                continue;
            }
            $patPath = trim((string) $patPath, '/');
            if ($patPath !== '' && (Str::startsWith($path, $patPath) || $path === $patPath)) {
                return (string) $permission;
            }
        }

        $segment = explode('/', $path)[0] ?? '';
        if ($segment === '' || in_array($segment, ['auth', 'projects'], true)) {
            return null;
        }

        $modules = config('admin_permissions.route_modules', []);
        $module = $modules[$segment] ?? null;
        if (! $module) {
            return null;
        }

        // meta endpoints under a resource → view
        if (Str::contains($path, '/meta') || $segment === 'meta') {
            return $module === 'dashboard' ? 'dashboard.view' : $module.'.view';
        }

        $action = match ($method) {
            'GET', 'HEAD' => 'view',
            'POST' => $module === 'ai' ? 'use' : ($module === 'media' ? 'manage' : ($module === 'cache' ? 'update' : 'create')),
            'PUT', 'PATCH' => $module === 'media' ? 'manage' : 'update',
            'DELETE' => $module === 'media' ? 'manage' : ($module === 'cache' ? 'update' : 'delete'),
            default => 'view',
        };

        if ($module === 'ai') {
            return 'ai.use';
        }

        return $module.'.'.$action;
    }

    /**
     * @return list<string>
     */
    public static function expandRolePermissions(string $role): array
    {
        $map = config('admin_permissions.role_permissions', []);
        $raw = $map[$role] ?? $map['viewer'] ?? [];
        $all = array_keys(config('admin_permissions.permissions', []));

        if (in_array('*', $raw, true)) {
            return $all;
        }

        $out = [];
        foreach ($raw as $item) {
            if (! is_string($item) || $item === '') {
                continue;
            }
            if (str_ends_with($item, '.*')) {
                $prefix = substr($item, 0, -1); // "packages."
                foreach ($all as $perm) {
                    if (str_starts_with($perm, $prefix)) {
                        $out[] = $perm;
                    }
                }
                continue;
            }
            $out[] = $item;
        }

        return array_values(array_unique($out));
    }

    /**
     * @return list<array{id: int, code: string, name: string, primary_domain: ?string, role: ?string, permissions: list<string>}>
     */
    public static function projectsPayload(User $user): array
    {
        if (! Schema::hasTable('projects')) {
            return [];
        }

        if (self::isSuperAdmin($user)) {
            return Project::query()->active()->orderBy('name')->get()->map(function (Project $p) use ($user) {
                $perms = self::permissionsFor($user, $p);

                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'primary_domain' => $p->primary_domain,
                    'role' => 'owner',
                    'permissions' => $perms,
                ];
            })->values()->all();
        }

        return $user->projects()
            ->where('projects.is_active', true)
            ->orderBy('projects.name')
            ->get()
            ->map(function (Project $p) use ($user) {
                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'primary_domain' => $p->primary_domain,
                    'role' => (string) ($p->pivot->role ?? 'viewer'),
                    'permissions' => self::permissionsFor($user, $p),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Grouped permission catalog for UI checkboxes.
     *
     * @return list<array{module: string, label: string, items: list<array{key: string, label: string}>}>
     */
    public static function catalogGrouped(): array
    {
        $perms = config('admin_permissions.permissions', []);
        $groups = [];

        foreach ($perms as $key => $label) {
            $module = explode('.', (string) $key)[0] ?? 'other';
            // Không cho gán users.* qua vai trò dự án — chỉ quản trị hệ thống.
            if ($module === 'users') {
                continue;
            }
            if (! isset($groups[$module])) {
                $groups[$module] = [
                    'module' => $module,
                    'label' => self::moduleLabel($module),
                    'items' => [],
                ];
            }
            $groups[$module]['items'][] = [
                'key' => (string) $key,
                'label' => (string) $label,
            ];
        }

        return array_values($groups);
    }

    public static function moduleLabel(string $module): string
    {
        return match ($module) {
            'dashboard' => 'Tổng quan',
            'packages' => 'Tour / Du thuyền',
            'tour_categories' => 'Danh mục tour',
            'cruise_types' => 'Loại du thuyền',
            'travel_styles' => 'Phong cách',
            'countries' => 'Điểm đến',
            'services' => 'Dịch vụ',
            'service_categories' => 'Danh mục dịch vụ',
            'content' => 'Nội dung',
            'brand' => 'Thương hiệu',
            'leads' => 'Leads',
            'media' => 'Media',
            'settings' => 'Cài đặt',
            'users' => 'Người dùng',
            'ai' => 'AI',
            default => Str::headline($module),
        };
    }
}
