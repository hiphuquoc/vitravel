<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 300;

    public function loginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return view('layouts.loginForm');
    }

    public function loginAdmin(Request $request): JsonResponse
    {
        $throttleKey = 'admin-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = (int) ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => "Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau {$minutes} phút.",
                'type' => 'rate_limit',
                'retry_after' => $seconds,
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6|max:100',
        ], [
            'email.required' => 'Vui lòng nhập email',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'type' => 'validation',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $loginValue = trim($request->string('email')->toString());
        $user = User::where('email', $loginValue)->first();
        $remember = $request->boolean('remember', false);

        if ($user && Hash::check($request->password, $user->password)) {
            if (! $user->is_active) {
                RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn đã bị vô hiệu hóa.',
                    'type' => 'unauthorized',
                ], 403);
            }

            if ($user->hasRole('admin')) {
                Auth::login($user, $remember);
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();

                return response()->json([
                    'success' => true,
                    'message' => 'Đăng nhập thành công! Đang chuyển hướng...',
                    'redirect_url' => route('admin.dashboard'),
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ]);
            }

            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn không có quyền truy cập khu vực quản trị.',
                'type' => 'unauthorized',
            ], 403);
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
        $attemptsLeft = self::MAX_ATTEMPTS - RateLimiter::attempts($throttleKey);

        $message = 'Email hoặc mật khẩu không chính xác.';
        if ($attemptsLeft > 0 && $attemptsLeft <= 3) {
            $message .= " Bạn còn {$attemptsLeft} lần thử.";
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'type' => 'credentials',
            'attempts_left' => $attemptsLeft,
        ], 401);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.loginForm');
    }
}
