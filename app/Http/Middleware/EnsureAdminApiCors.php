<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Đảm bảo CORS cho Admin API khi admin host ≠ Laravel host.
 * Bổ sung HandleCors (tránh thiếu Access-Control-* dù preflight 204).
 */
final class EnsureAdminApiCors
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/v1/admin', 'api/v1/admin/*')) {
            return $next($request);
        }

        $origin = (string) $request->headers->get('Origin', '');
        $allowOrigin = $this->resolveAllowedOrigin($origin);

        if ($request->isMethod('OPTIONS')) {
            return response('', 204, $this->corsHeaders($allowOrigin, $request));
        }

        $response = $next($request);

        if ($allowOrigin !== null) {
            foreach ($this->corsHeaders($allowOrigin, $request) as $key => $value) {
                if (! $response->headers->has($key)) {
                    $response->headers->set($key, $value);
                }
            }
        }

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function corsHeaders(?string $allowOrigin, Request $request): array
    {
        if ($allowOrigin === null) {
            return [
                'Vary' => 'Origin',
            ];
        }

        $reqHeaders = (string) $request->headers->get('Access-Control-Request-Headers', '');
        $allowHeaders = $reqHeaders !== ''
            ? $reqHeaders
            : 'Content-Type, Accept, Authorization, X-Project-Code, X-Project-Id, X-Requested-With';

        return [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => $allowHeaders,
            'Access-Control-Max-Age' => '86400',
            'Vary' => 'Origin',
        ];
    }

    private function resolveAllowedOrigin(string $origin): ?string
    {
        if ($origin === '') {
            return null;
        }

        $allowed = config('cors.allowed_origins', []);
        if (! is_array($allowed)) {
            $allowed = [];
        }

        $adminApp = rtrim((string) config('app.admin_url', ''), '/');
        if ($adminApp !== '') {
            $allowed[] = $adminApp;
        }

        $allowed = array_values(array_unique(array_filter(array_map('strval', $allowed))));

        if (in_array($origin, $allowed, true) || in_array('*', $allowed, true)) {
            return $origin;
        }

        // admin.*.vitravel.net / .dev (+ www)
        if (preg_match('#^https://(www\.)?admin\.[a-z0-9.-]+$#i', $origin)) {
            return $origin;
        }

        return null;
    }
}
