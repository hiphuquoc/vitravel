<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminApiToken;
use App\Models\Project;
use App\Models\User;
use App\Support\AdminAccess;
use App\Support\ApiResponse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

        if (! AdminAccess::canAccessConsole($user)) {
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
            'user' => $this->userPayload($user),
            'projects' => AdminAccess::projectsPayload($user),
            'current_project' => null,
        ], 'Đăng nhập thành công');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $payload = $this->userPayload($user);
        $payload['projects'] = AdminAccess::projectsPayload($user);
        $payload['current_project'] = null;
        $payload['permissions'] = [];
        $payload['project_role'] = null;

        $project = $this->resolveProjectFromRequest($request, $user);
        if ($project) {
            $payload['current_project'] = [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'primary_domain' => $project->primary_domain,
            ];
            $payload['permissions'] = AdminAccess::permissionsFor($user, $project);
            $payload['project_role'] = collect($payload['projects'])
                ->firstWhere('id', $project->id)['role'] ?? null;
        }

        return ApiResponse::success($payload);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:120',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'current_password' => 'nullable|string|min:6|max:100',
                'password' => 'nullable|string|min:6|max:100|confirmed',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        if (! empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $user->password)) {
                return ApiResponse::error(
                    'Mật khẩu hiện tại không đúng.',
                    'INVALID_CURRENT_PASSWORD',
                    422,
                    ['current_password' => ['Mật khẩu hiện tại không đúng.']],
                );
            }
            $user->password = $validated['password'];
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        $payload = $this->userPayload($user->fresh());
        $payload['projects'] = AdminAccess::projectsPayload($user);

        return ApiResponse::success($payload, 'Đã cập nhật hồ sơ');
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => config('admin_permissions.system_roles.'.$user->role, $user->role),
            'is_active' => (bool) $user->is_active,
            'is_super_admin' => AdminAccess::isSuperAdmin($user),
        ];
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('accessToken');
        if ($token instanceof AdminApiToken) {
            $token->delete();
        }

        return ApiResponse::success(null, 'Đã đăng xuất');
    }

    /**
     * Soft-resolve project from admin headers for /auth/me permissions.
     */
    private function resolveProjectFromRequest(Request $request, User $user): ?Project
    {
        if (! Schema::hasTable('projects')) {
            return null;
        }

        $code = trim((string) $request->header(
            (string) config('project.admin_header_code', 'X-Project-Code'),
            ''
        ));
        $id = trim((string) $request->header(
            (string) config('project.admin_header_id', 'X-Project-Id'),
            ''
        ));

        $project = null;
        if ($id !== '' && ctype_digit($id)) {
            $project = Project::query()->active()->where('id', (int) $id)->first();
        } elseif ($code !== '') {
            $project = Project::query()->active()->where('code', $code)->first();
        }

        if ($project && AdminAccess::canAccessProject($user, $project)) {
            return $project;
        }

        return null;
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
