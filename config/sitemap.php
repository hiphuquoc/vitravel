<?php

declare(strict_types=1);

/**
 * Sitemap đa dự án (multi-tenant).
 *
 * Mỗi project ghi file riêng: storage/app/sitemaps/{projectCode}/…
 * (disk `sitemap`, KHÔNG dùng disk `local` = storage/app/private)
 *
 * Cây URL (ưu tiên ngôn ngữ):
 *   /sitemap.xml
 *     → /sitemap/vi.xml
 *     → /sitemap/en.xml
 *          → /sitemap/vi/pages.xml
 *          → /sitemap/vi/service_stay-1.xml           (khách sạn)
 *          → /sitemap/vi/service_category_stay-1.xml   (danh mục khách sạn)
 *
 * Serve theo Host → ProjectContext. Generate: php artisan sitemap:generate
 */
return [

    'max_urls_per_file' => (int) env('SITEMAP_MAX_URLS', 1000),

    'disk' => env('SITEMAP_DISK', 'sitemap'),

    /** Thư mục gốc trên disk (mỗi project là 1 subfolder). Disk `sitemap` đã trỏ storage/app/sitemaps. */
    'root' => env('SITEMAP_ROOT', ''),

    /**
     * Tách service + service_category theo cluster (stay, train, flight, …).
     */
    'split_service_by_cluster' => filter_var(env('SITEMAP_SPLIT_SERVICE_BY_CLUSTER', true), FILTER_VALIDATE_BOOLEAN),

    'cache_max_age' => (int) env('SITEMAP_CACHE_MAX_AGE', 3600),

    /** URL gốc trong XML toàn cục (vd: https://culaocham.net). Để trống = tự chọn từ domain project. */
    'canonical_base_url' => env('SITEMAP_CANONICAL_BASE_URL', ''),

    /**
     * Override theo project code (ưu tiên sau canonical_base_url).
     *
     * @var array<string, string>
     */
    'canonical_base_url_by_project' => [
        // 'culaocham' => env('SITEMAP_CULAOCHAM_BASE_URL', 'https://culaocham.net'),
    ],

    /** Ưu tiên domain không có www khi sinh loc trong sitemap. */
    'prefer_non_www' => filter_var(env('SITEMAP_PREFER_NON_WWW', true), FILTER_VALIDATE_BOOLEAN),

    /** Tự generate khi /sitemap.xml chưa có — chỉ bật khi web user có quyền ghi storage. */
    'generate_on_miss' => filter_var(env('SITEMAP_GENERATE_ON_MISS', false), FILTER_VALIDATE_BOOLEAN),

    /**
     * SEO types đưa vào sitemap (null = tất cả keys trong config/seo.php types).
     * Có thể tắt từng type bằng false trong map dưới.
     *
     * @var array<string, bool>|null
     */
    'types' => null,

    /**
     * Trang cứng (route named) — bổ sung dù chưa có SeoEntry.
     * path = path default locale (không prefix); non-default sẽ thêm /{locale}.
     *
     * @var list<array{path: string, changefreq?: string, priority?: string}>
     */
    'static_paths' => [
        ['path' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
        ['path' => '/ve-chung-toi', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['path' => '/lien-he', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['path' => '/thiet-ke-tour-rieng', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['path' => '/doi-ngu', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ['path' => '/cam-nhan-khach-hang', 'changefreq' => 'weekly', 'priority' => '0.6'],
        ['path' => '/thu-vien-khoanh-khac', 'changefreq' => 'weekly', 'priority' => '0.5'],
        ['path' => '/video-trai-nghiem', 'changefreq' => 'weekly', 'priority' => '0.5'],
    ],

    'defaults' => [
        'changefreq' => 'weekly',
        'priority' => '0.8',
    ],

    /** Ưu tiên theo SEO type (fallback defaults). */
    'priority_by_type' => [
        'tours_hub' => '0.9',
        'cruises_hub' => '0.9',
        'guide_hub' => '0.9',
        'stays_hub' => '0.9',
        'trains_hub' => '0.8',
        'ferries_hub' => '0.8',
        'flights_hub' => '0.8',
        'experiences_hub' => '0.8',
        'extras_hub' => '0.7',
        'team_hub' => '0.7',
        'country' => '0.8',
        'cruise_type' => '0.8',
        'service_category' => '0.7',
        'service_category_stay' => '0.75',
        'service_category_train' => '0.7',
        'service_category_ferry' => '0.7',
        'service_category_flight' => '0.7',
        'service_category_experience' => '0.7',
        'service_category_other' => '0.65',
        'tour_category' => '0.7',
        'blog_category' => '0.7',
        'package_tour' => '0.8',
        'package_cruise' => '0.8',
        'service' => '0.7',
        'service_stay' => '0.8',
        'service_train' => '0.7',
        'service_ferry' => '0.7',
        'service_flight' => '0.7',
        'service_experience' => '0.7',
        'service_other' => '0.6',
        'article' => '0.6',
        'team_member' => '0.5',
        'static_page' => '0.5',
        'pages' => '0.9',
    ],
];
