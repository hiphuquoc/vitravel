<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DetectLocale — URL prefix là nguồn sự thật (Hitour).
 *
 * Ưu tiên:
 *  1. ?locale= / ?lang= (AJAX / listing API)
 *  2. Route {locale} hoặc segment(1) nếu là ngôn ngữ không-default
 *  3. Không có prefix trên URL public → locale mặc định (vi), đồng bộ session
 *     (tránh kẹt session=en khi đã chuyển về /… không prefix)
 *  4. Session chỉ còn fallback cho route nội bộ (api đã dùng ?locale=)
 */
class DetectLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $defaultCode = (string) config('language.default_code', Language::defaultCode());

        $queryLocale = $request->query('locale') ?? $request->query('lang');
        if (is_string($queryLocale) && $queryLocale !== '') {
            if ($this->isKnownLocale($queryLocale)) {
                $langFromQuery = Language::byCode($queryLocale);
                $code = $langFromQuery?->code ?: $queryLocale;
                session(['locale' => $code]);

                return $this->applyLocale($request, $next, $langFromQuery, $code);
            }
        }

        $localeParam = $request->route('locale');
        if (empty($localeParam)) {
            $first = $request->segment(1);
            // Chỉ nhận segment locale khi KHÔNG phải default (vi không có prefix)
            if ($first && $this->isNonDefaultLocale($first)) {
                $localeParam = $first;
            }
        }

        if (! empty($localeParam) && $this->isNonDefaultLocale($localeParam)) {
            $lang = Language::byCode($localeParam);
            $code = $lang?->code ?: $localeParam;
            if (! $lang || $lang->is_active) {
                session(['locale' => $code]);

                return $this->applyLocale($request, $next, $lang, $code);
            }
        }

        // URL không prefix (hoặc prefix không hợp lệ) → ngôn ngữ mặc định
        $lang = Language::default();
        $code = $lang?->code ?: $defaultCode;
        session(['locale' => $code]);

        return $this->applyLocale($request, $next, $lang, $code);
    }

    private function applyLocale(Request $request, Closure $next, ?Language $lang, string $code): Response
    {
        app()->setLocale($code);
        app()->setFallbackLocale((string) config('language.fallback_code', config('language.default_code', 'vi')));

        view()->share('currentLocale', $code);
        view()->share('currentLanguage', $lang);
        try {
            view()->share('availableLocales', Language::active());
        } catch (\Throwable $e) {
            view()->share('availableLocales', collect(config('language.list', []))->map(
                fn ($l, $k) => (object) array_merge($l, ['code' => $l['code'] ?? $k])
            )->values());
        }

        $request->attributes->set('locale', $code);
        $request->attributes->set('language_id', $lang?->id);

        return $next($request);
    }

    private function isNonDefaultLocale(string $code): bool
    {
        if (! $this->isKnownLocale($code)) {
            return false;
        }

        $default = (string) config('language.default_code', 'vi');
        if (strcasecmp($code, $default) === 0) {
            return false;
        }

        try {
            $lang = Language::byCode($code);
            if ($lang && $lang->is_default) {
                return false;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return true;
    }

    /** DB hoặc config/language.list (khi chưa seed). */
    private function isKnownLocale(string $code): bool
    {
        try {
            if (Language::byCode($code)) {
                return true;
            }
        } catch (\Throwable $e) {
            // DB unavailable
        }

        $list = config('language.list', []);

        return isset($list[$code]) && ! empty($list[$code]['is_active'] ?? true);
    }
}
