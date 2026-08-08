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
 * Bỏ qua self-redirect và chuỗi vòng để tránh ERR_TOO_MANY_REDIRECTS.
 */
class CheckRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $first = $request->segment(1);
            // /he-thong/* → routes/admin.php (redirect ADMIN_APP_URL); api/assets bỏ qua SEO redirect
            if (in_array($first, ['he-thong', 'api', 'currency', 'build', 'storage', 'up'], true)) {
                return $next($request);
            }

            if (! Schema::hasTable('redirect_info')) {
                return $next($request);
            }

            $path = '/'.rawurldecode(ltrim($request->path(), '/'));
            if ($path === '//') {
                $path = '/';
            }
            $pathNoSlash = ltrim($path, '/');

            $info = SeoRedirect::query()
                ->whereRaw('url_old COLLATE utf8mb4_bin IN (?, ?)', [$path, $pathNoSlash])
                ->first();

            if (empty($info) || empty($info->url_new)) {
                return $next($request);
            }

            $newUrl = (string) $info->url_new;
            if (! preg_match('#^https?://#i', $newUrl) && ! str_starts_with($newUrl, '/')) {
                $newUrl = '/'.$newUrl;
            }

            // Absolute URL: chỉ so path với request hiện tại
            $newPath = $newUrl;
            if (preg_match('#^https?://#i', $newUrl)) {
                $parts = parse_url($newUrl);
                $newPath = ($parts['path'] ?? '/') ?: '/';
            }

            $normCurrent = '/'.trim($path, '/');
            $normNew = '/'.trim($newPath, '/');
            if ($normCurrent === $normNew) {
                // Self-redirect → bỏ qua (tránh ERR_TOO_MANY_REDIRECTS)
                return $next($request);
            }

            // Chuỗi 1 bước: nếu đích cũng redirect về chính path hiện tại → vòng lặp
            $bounce = SeoRedirect::query()
                ->whereRaw('url_old COLLATE utf8mb4_bin IN (?, ?)', [$newPath, ltrim($newPath, '/')])
                ->value('url_new');
            if (is_string($bounce) && $bounce !== '') {
                $bouncePath = preg_match('#^https?://#i', $bounce)
                    ? ((parse_url($bounce, PHP_URL_PATH) ?: '/'))
                    : (str_starts_with($bounce, '/') ? $bounce : '/'.$bounce);
                if ('/'.trim($bouncePath, '/') === $normCurrent) {
                    return $next($request);
                }
            }

            return Redirect::to($newUrl, 301);
        } catch (\Throwable $e) {
            Log::warning('CheckRedirect middleware failed: '.$e->getMessage());
        }

        return $next($request);
    }
}
