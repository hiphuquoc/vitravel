<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Site defaults (meta / Open Graph) — contact & social: config/company.php
    |--------------------------------------------------------------------------
    */
    'site' => [
        'name' => env('SEO_SITE_NAME', env('COMPANY_NAME', 'ViTravel')),
        'tagline' => env('SEO_SITE_TAGLINE', env('COMPANY_TAGLINE', 'Hài lòng hơn cả mong đợi')),
        'title_suffix' => env('SEO_TITLE_SUFFIX', env('COMPANY_NAME', 'ViTravel')),
        'default_description' => env(
            'SEO_DEFAULT_DESCRIPTION',
            'ViTravel — đại lý du lịch bản địa thiết kế tour trọn gói, du thuyền và hành trình riêng tại Việt Nam, Campuchia, Lào, Thái Lan và Bali.'
        ),
        'default_og_image' => env('SEO_DEFAULT_OG_IMAGE', env('COMPANY_LOGO', null)),
        'twitter_site' => env('SEO_TWITTER_SITE', null),
        'locale_default' => 'vi_VN',
        'locales' => [
            'vi' => 'vi_VN',
            'en' => 'en_US',
        ],
        // telephone / email / address / same_as: lấy từ config/company.php (SchemaService merge)
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO entity types — Hitour parent → slug_full (KHÔNG hardcode path theo type)
    |--------------------------------------------------------------------------
    | parent_type: string|list — SEO type(s) hợp lệ làm trang cha (admin select)
    | parent_relation: Eloquent relation trên model → tự resolve parent nếu không chọn UI
    | hub: true = trang gốc (level 1, parent null), slug_full = /{slug}
    |
    | Quy tắc slug_full duy nhất (SeoService):
    |   có parent → {parent.slug_full}/{slug}
    |   không parent (hub/root) → /{slug}
    */
    'types' => [
        'tours_hub' => [
            'label' => 'Hub Tour',
            'hub' => true,
            'default_slug' => 'tours',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'country' => [
            'label' => 'Quốc gia (Tour)',
            'parent_type' => 'tours_hub',
            'parent_relation' => null,
        ],
        'package_tour' => [
            'label' => 'Gói Tour',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],
        'tour_category' => [
            'label' => 'Chủ đề Tour',
            'parent_type' => 'country',
            'parent_relation' => 'country',
        ],

        'cruises_hub' => [
            'label' => 'Hub Du thuyền',
            'hub' => true,
            'default_slug' => 'cruises',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'cruise_type' => [
            'label' => 'Loại du thuyền',
            'parent_type' => 'cruises_hub',
            'parent_relation' => null,
        ],
        'package_cruise' => [
            'label' => 'Gói Cruise',
            'parent_type' => 'cruise_type',
            'parent_relation' => 'cruiseType',
        ],

        'guide_hub' => [
            'label' => 'Hub Cẩm nang',
            'hub' => true,
            'default_slug' => 'cam-nang-du-lich',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'blog_category' => [
            'label' => 'Chuyên mục Blog',
            // Có thể chọn hub hoặc chuyên mục khác để phân tầng
            'parent_type' => ['guide_hub', 'blog_category'],
            'parent_relation' => null,
        ],
        'article' => [
            'label' => 'Bài viết',
            'parent_type' => 'blog_category',
            'parent_relation' => 'blogCategory',
        ],

        'static_page' => [
            'label' => 'Trang tĩnh',
            'parent_type' => ['static_page'],
            'parent_relation' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hub StaticPage templates → SEO type
    |--------------------------------------------------------------------------
    */
    'hubs' => [
        'tours_hub' => [
            'template' => 'tours_hub',
            'seo_type' => 'tours_hub',
            'default_slug' => 'tours',
            'default_title' => 'Tour trọn gói',
            'default_subtitle' => 'Mọi hành trình Đông Nam Á — chọn điểm đến, thời lượng và phong cách phù hợp với bạn.',
            'default_seo_title' => 'Tour trọn gói Đông Nam Á — ViTravel',
            'default_seo_description' => 'Tất cả tour trọn gói Việt Nam, Campuchia, Lào, Thái Lan và Bali — thiết kế bởi chuyên gia bản địa.',
        ],
        'cruises_hub' => [
            'template' => 'cruises_hub',
            'seo_type' => 'cruises_hub',
            'default_slug' => 'cruises',
            'default_title' => 'Du thuyền',
            'default_subtitle' => 'Tuyển chọn du thuyền đáng trải nghiệm — Hạ Long, Lan Hạ, Mekong.',
            'default_seo_title' => 'Du thuyền Đông Nam Á — ViTravel',
            'default_seo_description' => 'Đặt cabin du thuyền Hạ Long, Lan Hạ, Mekong qua chuyên gia bản địa.',
        ],
        'guide_hub' => [
            'template' => 'guide_hub',
            'seo_type' => 'guide_hub',
            'default_slug' => 'cam-nang-du-lich',
            'default_title' => 'Cẩm nang du lịch',
            'default_subtitle' => 'Kinh nghiệm, lịch trình và cảm hứng cho hành trình Đông Nam Á.',
            'default_seo_title' => 'Cẩm nang du lịch Đông Nam Á — ViTravel',
            'default_seo_description' => 'Bài viết hướng dẫn và cảm hứng du lịch từ đội ngũ ViTravel.',
        ],
    ],
];
