<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Token nội bộ cho GET /api/v1/catalog/* — không mở public khi chưa chốt ToS.
 */
class CatalogApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = trim((string) config('stay.catalog.api_token', ''));
        if ($expected === '') {
            return ApiResponse::error(
                'Catalog API chưa bật (thiếu STAY_CATALOG_API_TOKEN).',
                'CATALOG_API_DISABLED',
                403,
            );
        }
        $got = (string) ($request->header('X-Catalog-Token') ?: $request->bearerToken() ?: '');
        if (! hash_equals($expected, $got)) {
            return ApiResponse::error('Token catalog không hợp lệ.', 'UNAUTHENTICATED', 401);
        }

        return $next($request);
    }
}
