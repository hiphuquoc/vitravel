<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'admin.api' => \App\Http\Middleware\AuthenticateAdminApi::class,
            'detectLocale' => \App\Http\Middleware\DetectLocale::class,
            'detectCurrency' => \App\Http\Middleware\DetectCurrency::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->encryptCookies(except: [
            'app_currency',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\DetectLocale::class,
            \App\Http\Middleware\DetectCurrency::class,
            \App\Http\Middleware\CheckRedirect::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return \App\Support\ApiResponse::fromValidation($e);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            if ($status < 400 || $status > 599) {
                $status = 500;
            }

            $message = config('app.debug')
                ? ($e->getMessage() ?: class_basename($e))
                : ($status >= 500 ? 'Lỗi máy chủ. Thử lại sau.' : ($e->getMessage() ?: 'Đã xảy ra lỗi.'));

            return \App\Support\ApiResponse::error(
                $message,
                'SERVER_ERROR',
                $status,
            );
        });
    })->create();
