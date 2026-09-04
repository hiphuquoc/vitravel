<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Site defaults (meta / Open Graph) — contact & social: config/company.php
    |--------------------------------------------------------------------------
    */
    'site' => [
        // Brand runtime: CompanyProfile (SchemaService). SEO_SITE_* optional only — no COMPANY_*.
        'name' => env('SEO_SITE_NAME', 'ViTravel'),
        'tagline' => env('SEO_SITE_TAGLINE', 'Hài lòng hơn cả mong đợi'),
        'title_suffix' => env('SEO_TITLE_SUFFIX', 'ViTravel'),
        'default_description' => env(
            'SEO_DEFAULT_DESCRIPTION',
            'ViTravel — đại lý du lịch bản địa: tour trọn gói, du thuyền, vé tàu, máy bay, khách sạn/resort, vui chơi và dịch vụ hỗ trợ tại Việt Nam & Đông Nam Á.'
        ),
        'default_og_image' => env('SEO_DEFAULT_OG_IMAGE', null),
        'twitter_site' => env('SEO_TWITTER_SITE', null),
        'locale_default' => 'vi_VN',
        'locales' => [
            'vi' => 'vi_VN',
            'en' => 'en_US',
        ],
        // telephone / email / address / same_as: CompanyProfile / config/company.php fallback (SchemaService)
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
            'label' => 'Điểm đến / khu vực',
            // Trang địa điểm là root SEO — không gắn tours_hub (URL /{slug}, không /tours/{slug}).
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'package_tour' => [
            'label' => 'Gói Tour',
            // Chi tiết: chọn hub / điểm đến / danh mục tour
            'parent_type' => ['tours_hub', 'country', 'tour_category'],
            'parent_relation' => null,
        ],
        'tour_category' => [
            'label' => 'Danh mục Tour',
            // Gắn dưới điểm đến / khu vực (URL /{zone}/{category}); hub chỉ fallback.
            'parent_type' => ['country', 'tours_hub'],
            'parent_relation' => null,
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
            'parent_type' => ['cruises_hub', 'cruise_type'],
            'parent_relation' => null,
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
            // Cùng cấp — chỉ hub, không nest chuyên mục vào chuyên mục
            'parent_type' => 'guide_hub',
            'parent_relation' => null,
        ],
        'article' => [
            'label' => 'Bài viết',
            'parent_type' => ['guide_hub', 'blog_category'],
            'parent_relation' => null,
        ],

        'static_page' => [
            'label' => 'Trang tĩnh',
            'parent_type' => ['static_page'],
            'parent_relation' => null,
        ],

        'team_hub' => [
            'label' => 'Hub Đội ngũ',
            'hub' => true,
            'default_slug' => 'doi-ngu',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'team_member' => [
            'label' => 'Thành viên đội ngũ',
            'parent_type' => 'team_hub',
            'parent_relation' => null,
        ],

        // ── Dịch vụ mở rộng (5 cụm) ──────────────────────────────────────
        'trains_hub' => [
            'label' => 'Hub Vé tàu',
            'hub' => true,
            'default_slug' => 've-tau-cao-toc',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'ferries_hub' => [
            'label' => 'Hub Tàu / Xe ra đảo',
            'hub' => true,
            'default_slug' => 'tau-xe-ra-dao',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'flights_hub' => [
            'label' => 'Hub Vé máy bay',
            'hub' => true,
            'default_slug' => 've-may-bay',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'stays_hub' => [
            'label' => 'Hub Lưu trú',
            'hub' => true,
            'default_slug' => 'luu-tru',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'experiences_hub' => [
            'label' => 'Hub Vui chơi',
            'hub' => true,
            'default_slug' => 've-vui-choi',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'extras_hub' => [
            'label' => 'Hub Dịch vụ khác',
            'hub' => true,
            'default_slug' => 'dich-vu-khac',
            'parent_type' => null,
            'parent_relation' => null,
        ],
        'service_category' => [
            'label' => 'Danh mục dịch vụ',
            'parent_type' => ['trains_hub', 'ferries_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub'],
            'parent_relation' => null,
        ],
        'service' => [
            'label' => 'Dịch vụ / sản phẩm',
            'parent_type' => ['service_category', 'trains_hub', 'ferries_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub'],
            'parent_relation' => 'category',
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
            'default_seo_title' => 'Tour trọn gói Đông Nam Á — :brand',
            'default_seo_description' => 'Tất cả tour trọn gói Việt Nam, Campuchia, Lào, Thái Lan và Bali — thiết kế bởi chuyên gia bản địa.',
        ],
        'cruises_hub' => [
            'template' => 'cruises_hub',
            'seo_type' => 'cruises_hub',
            'default_slug' => 'cruises',
            'default_title' => 'Du thuyền',
            'default_subtitle' => 'Tuyển chọn du thuyền đáng trải nghiệm — Hạ Long, Lan Hạ, Mekong.',
            'default_seo_title' => 'Du thuyền Đông Nam Á — :brand',
            'default_seo_description' => 'Đặt cabin du thuyền Hạ Long, Lan Hạ, Mekong qua chuyên gia bản địa.',
        ],
        'guide_hub' => [
            'template' => 'guide_hub',
            'seo_type' => 'guide_hub',
            'default_slug' => 'cam-nang-du-lich',
            'default_title' => 'Cẩm nang du lịch',
            'default_subtitle' => 'Kinh nghiệm, lịch trình và cảm hứng cho hành trình Đông Nam Á.',
            'default_seo_title' => 'Cẩm nang du lịch Đông Nam Á — :brand',
            'default_seo_description' => 'Bài viết hướng dẫn và cảm hứng du lịch từ đội ngũ :brand.',
        ],
        'team_hub' => [
            'template' => 'team_hub',
            'seo_type' => 'team_hub',
            'default_slug' => 'doi-ngu',
            'default_title' => 'Đội ngũ',
            'default_subtitle' => 'Những người bản địa yêu nghề, trực tiếp thiết kế và chăm chút từng hành trình.',
            'default_seo_title' => 'Đội ngũ :brand — Chuyên gia bản địa',
            'default_seo_description' => 'Gặp gỡ đội ngũ chuyên gia bản địa của :brand — những người trực tiếp thiết kế và đồng hành cùng hành trình của bạn.',
        ],

        'trains_hub' => [
            'template' => 'trains_hub',
            'seo_type' => 'trains_hub',
            'default_slug' => 've-tau-cao-toc',
            'default_title' => 'Vé tàu cao tốc',
            'default_subtitle' => 'Đặt vé tàu SE, giường nằm và ghế mềm — hỗ trợ đổi ngày, giao vé tận nơi.',
            'default_seo_title' => 'Vé tàu cao tốc Việt Nam — :brand',
            'default_seo_description' => 'Đặt vé tàu Hà Nội — Đà Nẵng — Sài Gòn qua :brand. Ghế mềm, giường nằm, hỗ trợ 24/7.',
        ],
        'ferries_hub' => [
            'template' => 'ferries_hub',
            'seo_type' => 'ferries_hub',
            'default_slug' => 'tau-xe-ra-dao',
            'default_title' => 'Vé tàu cao tốc & xe khách',
            'default_subtitle' => 'Tàu cao tốc, phà ô tô và limousine ra đảo — đặt trước, đổi ngày linh hoạt.',
            'default_seo_title' => 'Vé tàu / xe ra đảo — Cát Bà',
            'default_seo_description' => 'Đặt vé tàu cao tốc, phà và limousine Hà Nội / Hải Phòng / Hạ Long — Cát Bà.',
        ],
        'flights_hub' => [
            'template' => 'flights_hub',
            'seo_type' => 'flights_hub',
            'default_slug' => 've-may-bay',
            'default_title' => 'Vé máy bay',
            'default_subtitle' => 'Vé nội địa, quốc tế châu Á và thuê máy bay riêng — báo giá nhanh trong 24 giờ.',
            'default_seo_title' => 'Vé máy bay nội địa & quốc tế — :brand',
            'default_seo_description' => 'Đặt vé máy bay nội địa, châu Á và charter riêng qua chuyên gia bản địa :brand.',
        ],
        'stays_hub' => [
            'template' => 'stays_hub',
            'seo_type' => 'stays_hub',
            'default_slug' => 'luu-tru',
            'default_title' => 'Khách sạn & Resort',
            'default_subtitle' => 'Tuyển chọn resort và khách sạn 5 sao nổi bật — Phú Quốc, Đà Nẵng, Nha Trang, Hạ Long.',
            'default_seo_title' => 'Khách sạn & Resort cao cấp Việt Nam — :brand',
            'default_seo_description' => 'Đặt phòng resort 5 sao Phú Quốc, Đà Nẵng, Nha Trang, Hạ Long qua :brand — giá tốt, hỗ trợ tận nơi.',
        ],
        'experiences_hub' => [
            'template' => 'experiences_hub',
            'seo_type' => 'experiences_hub',
            'default_slug' => 've-vui-choi',
            'default_title' => 'Vé vui chơi & trải nghiệm',
            'default_subtitle' => 'Vinpearl, cáp treo, dù lượn, kayak và thể thao biển — combo tiết kiệm.',
            'default_seo_title' => 'Vé vui chơi & trải nghiệm Việt Nam — :brand',
            'default_seo_description' => 'Vé Vinpearl, Fansipan, Bà Nà, dù lượn, kayak Hạ Long và thể thao biển qua :brand.',
        ],
        'extras_hub' => [
            'template' => 'extras_hub',
            'seo_type' => 'extras_hub',
            'default_slug' => 'dich-vu-khac',
            'default_title' => 'Dịch vụ khác',
            'default_subtitle' => 'Thuê xe, spa, massage, gửi hành lý, hướng dẫn viên riêng, y tế và hỗ trợ khẩn cấp 24/7.',
            'default_seo_title' => 'Dịch vụ hỗ trợ du lịch — :brand',
            'default_seo_description' => 'Thuê xe, spa, HDV riêng, y tế và hotline khẩn cấp 24/7 — đồng hành trọn hành trình với :brand.',
        ],
    ],
];
