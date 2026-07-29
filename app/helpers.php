<?php

use App\Models\Language;
use App\Models\Media;
use App\Models\SeoEntryTranslation;
use App\Services\MediaService;
use App\Services\SeoService;
use App\Services\ViewDataService;

if (! function_exists('view_data')) {
    function view_data(): ViewDataService
    {
        return app(ViewDataService::class);
    }
}

if (! function_exists('company')) {
    /**
     * Đọc config/company.php (hoặc key con). Contact runtime: CompanyProfile::contact().
     */
    function company(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('company');
        }

        return config('company.'.$key, $default);
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app()->getLocale();
    }
}

if (! function_exists('current_language')) {
    function current_language(): ?Language
    {
        return Language::byCode(current_locale());
    }
}

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return (string) (config('language.default_code') ?: Language::defaultCode());
    }
}

if (! function_exists('is_default_locale')) {
    function is_default_locale(): bool
    {
        return current_locale() === default_locale();
    }
}

if (! function_exists('locale_url')) {
    /**
     * Chuyển URL hiện tại sang locale khác (language switcher).
     * Trả về path nội bộ (vd `/en/tours`, `/`).
     */
    function locale_url(string $targetLocale, $entityOrSeo = null, ?string $fallback = null): string
    {
        $path = '/'.ltrim(request()->path(), '/');
        $segs = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));
        if (! empty($segs)) {
            $first = Language::byCode($segs[0]);
            if ($first) {
                array_shift($segs);
            }
        }

        $defaultCode = default_locale();
        $targetLang = Language::byCode($targetLocale);
        $isTargetDefault = $targetLang ? (bool) $targetLang->is_default : ($targetLocale === $defaultCode);
        $newPrefix = $isTargetDefault ? '' : '/'.$targetLocale;
        $rest = implode('/', $segs);

        if ($rest === '') {
            return $newPrefix === '' ? '/' : $newPrefix;
        }

        return $newPrefix.'/'.$rest;
    }
}

if (! function_exists('locale_route')) {
    /**
     * Named route with locale URL prefix for non-default locales.
     * SEO listing names resolve via current slug_full (Hitour catch-all).
     * Skips prefix for admin/api/currency paths.
     */
    function locale_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        if (! is_array($parameters) && is_string($parameters)) {
            $params = match ($name) {
                'tours.index' => ['country' => $parameters],
                'cruises.index' => ['type' => $parameters],
                default => [$parameters],
            };
        } else {
            $params = (array) $parameters;
        }

        $seoPath = app(SeoService::class)->namedSeoPath($name, $params);
        if ($seoPath !== null) {
            $path = '/'.ltrim($seoPath, '/');
            if (! is_default_locale()) {
                $path = '/'.current_locale().($path === '/' ? '' : $path);
            }

            return $absolute ? url($path) : $path;
        }

        $path = route($name, $params === [] ? $parameters : $params, false);
        if (! is_default_locale()) {
            $locale = current_locale();
            if (! preg_match('#^/'.preg_quote($locale, '#').'(/|$)#', $path)
                && ! preg_match('#^/(he-thong|api|currency|up)(/|$)#', $path)) {
                $path = '/'.$locale.($path === '/' ? '' : $path);
            }
        }

        return $absolute ? url($path) : $path;
    }
}

if (! function_exists('locale_switch_url')) {
    /**
     * URL giữ nguyên trang hiện tại, đổi locale (URL subfolder strategy).
     */
    function locale_switch_url(string $locale): string
    {
        return locale_url($locale);
    }
}

if (! function_exists('seo_url')) {
    /**
     * Public URL from slug_full with locale prefix (Hitour pattern).
     */
    function seo_url(string $slugFull, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $prefix = $locale === default_locale() ? '' : '/'.$locale;

        return $prefix.'/'.ltrim($slugFull, '/');
    }
}

if (! function_exists('seo_public_url')) {
    function seo_public_url(?SeoEntryTranslation $translation, ?string $locale = null): string
    {
        return app(SeoService::class)->publicUrl($translation, $locale ?? app()->getLocale());
    }
}

if (! function_exists('media_url')) {
    /**
     * URL ảnh theo variant (thumb|card|lg|full hoặc alias avatar|hero|banner…).
     */
    function media_url(?Media $media, ?string $variant = null): ?string
    {
        return app(MediaService::class)->publicUrl($media, $variant);
    }
}

if (! function_exists('media_srcset')) {
    function media_srcset(?Media $media, ?array $variants = null): ?string
    {
        return app(MediaService::class)->srcset($media, $variants);
    }
}

if (! function_exists('media_payload')) {
    /**
     * @return array{src: ?string, srcset: ?string, width: ?int, height: ?int, alt: ?string, variant: string}
     */
    function media_payload(?Media $media, string $variant = 'card'): array
    {
        return app(MediaService::class)->imagePayload($media, $variant);
    }
}

if (! function_exists('media_sizes')) {
    /** Preset sizes= từ config/media.php sizes_presets. */
    function media_sizes(string $preset): ?string
    {
        $presets = config('media.sizes_presets', []);

        return $presets[$preset] ?? null;
    }
}

if (! function_exists('schema')) {
    function schema(): \App\Services\SchemaService
    {
        return app(\App\Services\SchemaService::class);
    }
}

if (! function_exists('schema_ld')) {
    /**
     * Render a <script type="application/ld+json"> block (safe for </script> breakout).
     *
     * @param  array<string, mixed>|null  $payload
     */
    function schema_ld(?array $payload): string
    {
        if ($payload === null || $payload === []) {
            return '';
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT
        );

        if ($json === false) {
            return '';
        }

        return '<script type="application/ld+json">'.$json.'</script>';
    }
}
