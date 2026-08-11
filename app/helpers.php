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

if (! function_exists('admin_app_url')) {
    /**
     * Absolute URL tới Admin Console (host riêng — ADMIN_APP_URL).
     */
    function admin_app_url(string $path = '/'): string
    {
        $base = rtrim((string) config('app.admin_url', env('ADMIN_APP_URL', 'https://admin.vitravel.dev')), '/');
        $path = '/'.ltrim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        return $base.($path === '/' ? '/' : $path);
    }
}

if (! function_exists('company')) {
    /**
     * Thông tin dự án runtime (DB company_profiles qua CompanyProfile::contact()).
     * Key dạng chấm: contact.email, name, social, …
     */
    function company(?string $key = null, mixed $default = null): mixed
    {
        $contact = \App\Models\CompanyProfile::contact();
        $nested = [
            'name' => $contact['name'],
            'legal_name' => $contact['legal_name'],
            'tagline' => $contact['tagline'],
            'slogan' => $contact['slogan'],
            'license_number' => $contact['license'],
            'contact' => [
                'email' => $contact['email'],
                'phone' => $contact['phone'],
                'whatsapp' => $contact['whatsapp'],
                'zalo' => $contact['zalo'],
                'hotline_label' => $contact['hotline_label'],
            ],
            'address' => $contact['address'],
            'social' => collect($contact['social'])->keyBy('key')->map(fn ($row) => [
                'label' => $row['label'],
                'icon' => $row['icon'],
                'url' => $row['url'],
            ])->all(),
            'schema' => $contact['schema'],
            'footer' => [
                'copyright' => $contact['footer_copyright'],
                'show_dmca_badge' => $contact['show_dmca_badge'],
            ],
        ];

        if ($key === null) {
            return $nested;
        }

        return data_get($nested, $key, $default);
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
                'tours.index', 'guide.country', 'guide.zone' => ['country' => $parameters],
                'cruises.index' => ['type' => $parameters],
                default => [$parameters],
            };
        } else {
            $params = (array) $parameters;
        }

        if (isset($params['zone']) && ! isset($params['country'])) {
            $params['country'] = $params['zone'];
        }

        // Chuỗi rỗng ≠ đủ tham số cho route SEO (tránh UrlGenerationException).
        foreach (['country', 'slug', 'type', 'category', 'cluster'] as $key) {
            if (array_key_exists($key, $params) && ! filled($params[$key])) {
                unset($params[$key]);
            }
        }

        $seoPath = app(\App\Services\SeoService::class)->namedSeoPath($name, $params);
        if (is_string($seoPath) && $seoPath !== '') {
            $path = '/'.ltrim($seoPath, '/');
            if (! is_default_locale()) {
                $path = '/'.current_locale().($path === '/' ? '' : $path);
            }

            return $absolute ? url($path) : $path;
        }

        $seoNames = [
            'tours.hub', 'tours.index', 'tours.show',
            'cruises.hub', 'cruises.index', 'cruises.show',
            'guide.index', 'guide.country', 'guide.zone', 'guide.show',
            'services.hub', 'services.index', 'services.show',
        ];

        try {
            $path = route($name, $params === [] ? $parameters : $params, false);
        } catch (\Illuminate\Routing\Exceptions\UrlGenerationException $e) {
            if (in_array($name, $seoNames, true)) {
                return $absolute ? url('/') : '/';
            }
            throw $e;
        }

        if (! is_default_locale()) {
            $locale = current_locale();
            if (! preg_match('#^/'.preg_quote($locale, '#').'(/|$)#', $path)
                && ! preg_match('#^/(api|currency|up)(/|$)#', $path)) {
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

if (! function_exists('blog_rich_text')) {
    /**
     * Allowlisted inline HTML for article block text (admin TipTap → public Blade).
     */
    function blog_rich_text(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $clean = strip_tags($html, '<strong><b><em><i><u><a><br><span>');

        return (string) preg_replace_callback(
            '/<a\s+([^>]*?)>/i',
            static function (array $m): string {
                $attrs = $m[1];
                if (! preg_match('/href\s*=\s*(["\'])(.*?)\1/i', $attrs, $href)) {
                    return '<a>';
                }
                $url = html_entity_decode($href[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (! preg_match('~^(https?:|mailto:|/|#)~i', $url)) {
                    return '<a>';
                }

                return '<a href="'.e($url).'" rel="noopener noreferrer">';
            },
            $clean
        );
    }
}

if (! function_exists('rich_body_html')) {
    /**
     * Sanitize block-level HTML for public detail bodies (itinerary day, etc.).
     * Accepts TipTap HTML, legacy plain text, or article JSON blocks.
     */
    function rich_body_html(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }

        if (str_starts_with($raw, '[')) {
            $blocks = json_decode($raw, true);
            if (is_array($blocks)) {
                $raw = article_blocks_to_html($blocks);
            }
        }

        if ($raw === '' || $raw === '<p></p>') {
            return '';
        }

        // Gỡ citation markdown do AI web_search (đã lưu trước đó).
        $raw = (string) preg_replace('/\s*\(\[[^\]]*]\(\s*https?:\/\/[^)]+\)\s*\)/iu', '', $raw);
        $raw = (string) preg_replace('/\s*\[[^\]]*]\(\s*https?:\/\/[^)]+\)/iu', '', $raw);
        $raw = (string) preg_replace('/\s*\(\s*https?:\/\/[^)]*(?:utm_source=openai|chatgpt\.com)[^)]*\)/iu', '', $raw);

        if (! str_contains($raw, '<')) {
            return '<p>'.nl2br(e($raw), false).'</p>';
        }

        $clean = strip_tags(
            $raw,
            '<p><br><strong><b><em><i><u><a><ul><ol><li><h2><h3><blockquote><span><figure><figcaption><img>'
        );

        $clean = (string) preg_replace_callback(
            '/<a\s+([^>]*?)>/i',
            static function (array $m): string {
                $attrs = $m[1];
                if (! preg_match('/href\s*=\s*(["\'])(.*?)\1/i', $attrs, $href)) {
                    return '<a>';
                }
                $url = html_entity_decode($href[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (! preg_match('~^(https?:|mailto:|/|#)~i', $url)) {
                    return '<a>';
                }

                return '<a href="'.e($url).'" rel="noopener noreferrer">';
            },
            $clean
        );

        return (string) preg_replace_callback(
            '/<img\s+([^>]*?)\/?>/i',
            static function (array $m): string {
                $attrs = $m[1];
                if (! preg_match('/src\s*=\s*(["\'])(.*?)\1/i', $attrs, $src)) {
                    return '';
                }
                $url = html_entity_decode($src[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (! preg_match('~^(https?:|/|#)~i', $url)) {
                    return '';
                }
                $alt = '';
                if (preg_match('/alt\s*=\s*(["\'])(.*?)\1/i', $attrs, $altMatch)) {
                    $alt = html_entity_decode($altMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                return '<img src="'.e($url).'" alt="'.e($alt).'" loading="lazy">';
            },
            $clean
        );
    }
}

if (! function_exists('article_blocks_to_html')) {
    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    function article_blocks_to_html(array $blocks): string
    {
        $parts = [];

        foreach ($blocks as $block) {
            if (! is_array($block) || ! isset($block['type'])) {
                continue;
            }

            $type = (string) $block['type'];

            switch ($type) {
                case 'p':
                    $parts[] = '<p>'.blog_rich_text((string) ($block['text'] ?? '')).'</p>';
                    break;
                case 'h2':
                    $id = trim((string) ($block['id'] ?? ''));
                    $parts[] = $id !== ''
                        ? '<h2 id="'.e($id).'">'.blog_rich_text((string) ($block['text'] ?? '')).'</h2>'
                        : '<h2>'.blog_rich_text((string) ($block['text'] ?? '')).'</h2>';
                    break;
                case 'h3':
                    $id = trim((string) ($block['id'] ?? ''));
                    $parts[] = $id !== ''
                        ? '<h3 id="'.e($id).'">'.blog_rich_text((string) ($block['text'] ?? '')).'</h3>'
                        : '<h3>'.blog_rich_text((string) ($block['text'] ?? '')).'</h3>';
                    break;
                case 'ul':
                case 'ol':
                    $items = is_array($block['items'] ?? null) ? $block['items'] : [];
                    $lis = '';
                    foreach ($items as $item) {
                        $lis .= '<li>'.blog_rich_text((string) $item).'</li>';
                    }
                    $parts[] = '<'.$type.'>'.$lis.'</'.$type.'>';
                    break;
                case 'image':
                    $caption = (string) ($block['caption'] ?? '');
                    $src = trim((string) ($block['src'] ?? ''));
                    $img = $src !== ''
                        ? '<img src="'.e($src).'" alt="'.e($caption).'" loading="lazy">'
                        : '';
                    $parts[] = '<figure>'.$img.'<figcaption>'.e($caption).'</figcaption></figure>';
                    break;
                default:
                    break;
            }
        }

        return implode('', $parts);
    }
}
