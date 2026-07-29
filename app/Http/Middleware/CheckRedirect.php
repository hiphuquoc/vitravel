<?php

namespace App\Http\Middleware;

use App\Models\SeoRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRedirect — 301 từ bảng redirect_info (Hitour pattern).
 */
class CheckRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $first = $request->segment(1);
            if (in_array($first, ['he-thong', 'api', 'currency', 'build', 'storage', 'up'], true)) {
                return $next($request);
            }

            if (! Schema::hasTable('redirect_info')) {
                return $next($request);
            }

            $path = '/'.rawurldecode($request->path());
            $pathNoSlash = ltrim($path, '/');

            $info = SeoRedirect::query()
                ->whereRaw('url_old COLLATE utf8mb4_bin IN (?, ?)', [$path, $pathNoSlash])
                ->first();

            if (! empty($info) && ! empty($info->url_new)) {
                $newUrl = $info->url_new;
                if (! preg_match('#^https?://#i', $newUrl) && ! str_starts_with($newUrl, '/')) {
                    $newUrl = '/'.$newUrl;
                }

                return Redirect::to($newUrl, 301);
            }
        } catch (\Throwable $e) {
            Log::warning('CheckRedirect middleware failed: '.$e->getMessage());
        }

        return $next($request);
    }
}
