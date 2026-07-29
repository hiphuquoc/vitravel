<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
            'detectLocale' => \App\Http\Middleware\DetectLocale::class,
            'detectCurrency' => \App\Http\Middleware\DetectCurrency::class,
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
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
