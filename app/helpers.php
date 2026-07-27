<?php

use App\Models\Language;
use App\Models\SeoEntryTranslation;
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
