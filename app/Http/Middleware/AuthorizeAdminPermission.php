<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminAccess;
use App\Support\ApiResponse;
use App\Support\ProjectContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce permission for admin API routes (after auth + optional project resolve).
 */
class AuthorizeAdminPermission
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Thiếu token xác thực.', 'UNAUTHENTICATED', 401);
        }

        $needed = $permission ?: AdminAccess::permissionForRequest($request);
        if ($needed === null || $needed === '') {
            return $next($request);
        }

        $project = ProjectContext::get() ?? $request->attributes->get('currentProject');

        if (! AdminAccess::can($user, $needed, $project instanceof \App\Models\Project ? $project : null)) {
            return ApiResponse::error(
                'Bạn không có quyền thực hiện thao tác này.',
                'FORBIDDEN',
                403,
                ['permission' => $needed],
            );
        }

        return $next($request);
    }
}
