<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO entity types & layered URL rules (Hitour pattern)
    |--------------------------------------------------------------------------
    */
    'types' => [
        'country' => [
            'label' => 'Quốc gia',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'package_tour' => [
            'label' => 'Gói Tour',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'package_cruise' => [
            'label' => 'Gói Cruise',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'tour_category' => [
            'label' => 'Danh mục Tour',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'article' => [
            'label' => 'Bài viết',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'blog_category' => [
            'label' => 'Chuyên mục Blog',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'static_page' => [
            'label' => 'Trang tĩnh',
            'parent_type' => null,
            'parent_relation' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug full builders per locale — return path starting with /
    |--------------------------------------------------------------------------
    */
    'slug_full_builders' => [
        'country' => fn (string $locale, string $slug, ?string $parentSlugFull = null, array $context = []): string => '/tours/'.($context['country_code'] ?? $slug),
        'package_tour' => fn (string $locale, string $slug, ?string $parentSlugFull = null, array $context = []): string => rtrim((string) $parentSlugFull, '/').'/'.$slug,
        'tour_category' => fn (string $locale, string $slug, ?string $parentSlugFull = null, array $context = []): string => rtrim((string) $parentSlugFull, '/').'/'.$slug,
        'package_cruise' => fn (string $locale, string $slug, ?string $parentSlugFull = null, array $context = []): string => '/cruises/'.($context['country_code'] ?? 'vn').'/'.$slug,
        'article' => fn (string $locale, string $slug, ?string $parentSlugFull = null, array $context = []): string => '/cam-nang-du-lich/'.($context['country_code'] ?? 'vn').'/'.$slug,
        'blog_category' => fn (string $locale, string $slug, ?string $parentSlugFull = null, array $context = []): string => '/cam-nang-du-lich/'.$slug,
        'static_page' => fn (string $locale, string $slug, ?string $parentSlugFull = null, array $context = []): string => '/'.$slug,
    ],
];
