<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminApiToken;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 300;

    public function login(Request $request): JsonResponse
    {
        $throttleKey = 'admin-api-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return ApiResponse::error(
                'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau.',
                'RATE_LIMIT',
                429,
                ['retry_after' => $seconds],
            );
        }

        try {
            $validated = $request->validate([
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6|max:100',
                'device_name' => 'nullable|string|max:120',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $user = User::query()->where('email', trim($validated['email']))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return ApiResponse::error('Email hoặc mật khẩu không chính xác.', 'INVALID_CREDENTIALS', 401);
        }

        if (! $user->is_active) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return ApiResponse::error('Tài khoản đã bị vô hiệu hóa.', 'ACCOUNT_DISABLED', 403);
        }

        if (! $user->isAdmin()) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return ApiResponse::error('Không có quyền truy cập quản trị.', 'FORBIDDEN', 403);
        }

        $this->ensureTokenTable();

        RateLimiter::clear($throttleKey);

        try {
            $device = Str::limit(
                $validated['device_name'] ?? 'admin-console',
                180,
                ''
            );
            $plainToken = Str::random(64);

            $user->adminApiTokens()->where('name', $device)->delete();

            AdminApiToken::query()->create([
                'user_id' => $user->id,
                'name' => $device,
                'token' => hash('sha256', $plainToken),
                'expires_at' => now()->addDays(30),
                'last_used_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);

            $message = 'Không tạo được phiên đăng nhập.';
            if (config('app.debug')) {
                $message .= ' '.$e->getMessage();
            } else {
                $message .= ' Chạy: php artisan migrate --force';
            }

            return ApiResponse::error($message, 'TOKEN_CREATE_FAILED', 500);
        }

        return ApiResponse::success([
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_in_days' => 30,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Đăng nhập thành công');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('accessToken');
        if ($token instanceof AdminApiToken) {
            $token->delete();
        }

        return ApiResponse::success(null, 'Đã đăng xuất');
    }

    private function ensureTokenTable(): void
    {
        if (Schema::hasTable('admin_api_tokens')) {
            return;
        }

        Schema::create('admin_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 191)->default('admin-console');
            $table->string('token', 64)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index(['user_id', 'name']);
        });
    }
}
