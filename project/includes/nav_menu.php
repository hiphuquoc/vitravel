<?php

declare(strict_types=1);

/**
 * Sinh cấu trúc nav_menu mặc định từ nav + service_clusters trong seed dự án.
 *
 * @param  array<string, mixed>  $seedData
 * @return array{main: list<array<string, mixed>>, more: list<array<string, mixed>>, cta: list<array<string, mixed>>}
 */
function vitravel_build_nav_menu(array $seedData): array
{
    $nav = is_array($seedData['nav'] ?? null) ? $seedData['nav'] : [];
    $clusters = is_array($seedData['service_clusters'] ?? null) ? $seedData['service_clusters'] : [];

    $pick = static function (mixed $val, string $fallback): array {
        if (is_array($val)) {
            return $val;
        }
        if (is_string($val) && $val !== '') {
            return ['vi' => $val, 'en' => $val];
        }

        return ['vi' => $fallback, 'en' => $fallback];
    };

    $cruise = is_array($nav['cruise'] ?? null) ? $nav['cruise'] : [];
    $tours = is_array($nav['tours'] ?? null) ? $nav['tours'] : [];

    $main = [
        [
            'kind' => 'tours_menu',
            'key' => 'tours',
            'sort' => 1,
            'label' => $pick($tours['label'] ?? null, 'Tour trọn gói'),
            'lead_label' => ['vi' => 'Tất cả tour', 'en' => 'All tours'],
            'meta' => ['vi' => 'Xem toàn bộ hành trình', 'en' => 'Browse all itineraries'],
        ],
        [
            'kind' => 'cruise_menu',
            'key' => 'cruise',
            'sort' => 2,
            'label' => $pick($cruise['label'] ?? null, 'Du thuyền'),
            'lead_label' => $pick($cruise['all_label'] ?? null, 'Tất cả du thuyền'),
            'meta' => $pick($cruise['all_meta'] ?? null, 'Xem toàn bộ lịch trình du thuyền'),
        ],
    ];

    $sortBase = 3;
    foreach ($clusters as $row) {
        if (! is_array($row)) {
            continue;
        }
        $code = (string) ($row['code'] ?? '');
        if ($code === '') {
            continue;
        }

        $main[] = [
            'kind' => 'service_cluster',
            'key' => 'svc_'.$code,
            'reference' => $code,
            'sort' => (int) ($row['sort'] ?? $sortBase),
            'show_in_main_bar' => ! in_array($code, ['train', 'flight'], true),
            'label' => $pick($row['nav_label'] ?? null, $code),
            'lead_label' => [
                'vi' => 'Tất cả '.mb_strtolower((string) ($row['nav_label'] ?? $code)),
                'en' => 'All '.strtolower((string) ($row['nav_label'] ?? $code)),
            ],
            'meta' => $pick($row['label'] ?? null, ''),
        ];
        $sortBase++;
    }

    usort($main, static fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

    $aboutGroup = $pick($nav['about_group'] ?? null, 'Về chúng tôi');

    $more = [
        [
            'kind' => 'heading',
            'key' => 'about_group',
            'sort' => 1,
            'label' => $aboutGroup,
        ],
        ['kind' => 'route_link', 'key' => 'about', 'reference' => 'about', 'sort' => 2, 'label' => ['vi' => 'Về chúng tôi', 'en' => 'About us']],
        ['kind' => 'route_link', 'key' => 'contact', 'reference' => 'contact', 'sort' => 3, 'label' => ['vi' => 'Liên hệ', 'en' => 'Contact']],
        ['kind' => 'route_link', 'key' => 'team', 'reference' => 'team', 'sort' => 4, 'label' => ['vi' => 'Đội ngũ', 'en' => 'Our team']],
        ['kind' => 'route_link', 'key' => 'reviews', 'reference' => 'reviews', 'sort' => 5, 'label' => ['vi' => 'Cảm nhận khách hàng', 'en' => 'Guest reviews']],
        ['kind' => 'route_link', 'key' => 'gallery', 'reference' => 'gallery', 'sort' => 6, 'label' => ['vi' => 'Thư viện ảnh', 'en' => 'Photo gallery']],
        ['kind' => 'route_link', 'key' => 'videos', 'reference' => 'videos', 'sort' => 7, 'label' => ['vi' => 'Video trải nghiệm', 'en' => 'Experience videos']],
        [
            'kind' => 'heading',
            'key' => 'blogs_heading',
            'sort' => 8,
            'label' => ['vi' => 'Blogs', 'en' => 'Blog'],
        ],
        [
            'kind' => 'blog_menu',
            'key' => 'blogs',
            'sort' => 9,
            'label' => ['vi' => 'Tất cả bài viết', 'en' => 'All articles'],
        ],
    ];

    $cta = [
        [
            'kind' => 'cta_link',
            'key' => 'customize',
            'reference' => 'customize',
            'sort' => 1,
            'label' => ['vi' => 'Tour riêng', 'en' => 'Custom tour'],
        ],
    ];

    return [
        'main' => $main,
        'more' => $more,
        'cta' => $cta,
    ];
}
