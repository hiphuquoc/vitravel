<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminAccess;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catalog chỗ nghỉ — chỉ users.role ∈ {admin, super_admin}.
 */
class RequireSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Thiếu token xác thực.', 'UNAUTHENTICATED', 401);
        }
        if (! AdminAccess::isSuperAdmin($user)) {
            return ApiResponse::error(
                'Chỉ siêu quản trị được thao tác catalog chỗ nghỉ.',
                'FORBIDDEN',
                403,
            );
        }

        return $next($request);
    }
}
