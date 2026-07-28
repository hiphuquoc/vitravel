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

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app()->getLocale();
    }
}

if (! function_exists('locale_switch_url')) {
    /**
     * URL giữ nguyên trang hiện tại, đổi query ?lang= để switch locale (lưu session).
     */
    function locale_switch_url(string $locale): string
    {
        return url()->current().'?'.http_build_query(array_merge(
            request()->except(['lang', 'locale']),
            ['lang' => $locale],
        ));
    }
}

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return Language::defaultCode();
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
