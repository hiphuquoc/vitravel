<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AdminApiToken as AdminApiTokenModel;
use App\Models\User;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdminApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->bearerToken();

        if (! $header) {
            return ApiResponse::error('Thiếu token xác thực.', 'UNAUTHENTICATED', 401);
        }

        $hashed = hash('sha256', $header);
        $token = AdminApiTokenModel::query()
            ->where('token', $hashed)
            ->first();

        if (! $token) {
            return ApiResponse::error('Token không hợp lệ.', 'UNAUTHENTICATED', 401);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            return ApiResponse::error('Token đã hết hạn.', 'TOKEN_EXPIRED', 401);
        }

        $user = $token->user;

        if (! $user instanceof User || ! $user->is_active || ! $user->isAdmin()) {
            return ApiResponse::error('Không có quyền truy cập.', 'FORBIDDEN', 403);
        }

        $token->forceFill(['last_used_at' => now()])->save();

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('accessToken', $token);

        return $next($request);
    }
}
