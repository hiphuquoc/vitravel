<?php

/**
 * Shared taxonomy builders for hub seed profiles.
 *
 * RULES (sync with project/README.md §3):
 * - ZONES (→ countries): phân khu theo insight search/book; hub zone đứng đầu;
 *   GEO = chỗ nghỉ/vé riêng; ket-hop-* = combo only. SEO country = root (không trang cha).
 * - DANH MỤC = type `region` → khu vực / combo (zoneSlug = vùng hoặc ket-hop-*). Không chia theo số ngày.
 * - CHỦ ĐỀ   = type `theme`  → gắn hub zone:
 *     (A) thời lượng: tour-trong-ngay, tour-2-ngay-1-dem, tour-3-ngay-2-dem, tour-4-ngay-3-dem, tour-tu-5-ngay
 *     (B) tính chất / insight địa phương — KHÔNG clone tên zone GEO.
 * - Package ↔ category: nhiều–nhiều qua packageSlugs[] (+ minDays/maxDays cho theme thời lượng, project-wide).
 * - travel_styles: mã khớp chủ đề (filter card), không tạo trang SEO riêng.
 * - stay/experience/other: bắt buộc zone_slug = GEO hoặc hub; ferry/flight/train: KHÔNG zone_slug.
 */

declare(strict_types=1);

/**
 * @return array<string, array{vi: string, en: string}>
 */
function hub_duration_travel_styles(): array
{
    return [
        'day-trip' => ['vi' => 'Tour trong ngày', 'en' => 'Day trip'],
        '2n1d' => ['vi' => '2 ngày 1 đêm', 'en' => '2 days 1 night'],
        '3n2d' => ['vi' => '3 ngày 2 đêm', 'en' => '3 days 2 nights'],
        '4n3d' => ['vi' => '4 ngày 3 đêm', 'en' => '4 days 3 nights'],
        '5-plus-days' => ['vi' => 'Từ 5 ngày', 'en' => '5+ days'],
    ];
}

/**
 * @return array<string, string>
 */
function hub_duration_style_labels(): array
{
    return [
        'day-trip' => 'Tour trong ngày',
        '2n1d' => '2 ngày 1 đêm',
        '3n2d' => '3 ngày 2 đêm',
        '4n3d' => '4 ngày 3 đêm',
        '5-plus-days' => 'Từ 5 ngày',
    ];
}

/**
 * 5 chủ đề thời lượng chuẩn — gắn hub zone.
 *
 * @param  array<string, list<string>>  $byBucket  keys: day|2n1d|3n2d|4n3d|5plus
 * @return list<array<string, mixed>>
 */
function hub_duration_themes(string $hubZone, array $byBucket, string $placeVi, string $placeEn): array
{
    $defs = [
        [
            'slug' => 'tour-trong-ngay',
            'sort' => 0,
            'minDays' => 1,
            'maxDays' => 1,
            'bucket' => 'day',
            'name' => ['vi' => 'Tour trong ngày', 'en' => 'Day tours'],
            'subtitle' => [
                'vi' => '1 ngày, nửa ngày hoặc tour tối — không qua đêm tại '.$placeVi.'.',
                'en' => 'Full day, half-day or evening — no overnight in '.$placeEn.'.',
            ],
            'seo_body' => [
                'vi' => 'Chủ đề theo thời lượng chương trình — khác danh mục theo từng khu vực / combo.',
                'en' => 'Program-duration theme — distinct from GEO/combo category pages.',
            ],
        ],
        [
            'slug' => 'tour-2-ngay-1-dem',
            'sort' => 1,
            'minDays' => 2,
            'maxDays' => 2,
            'bucket' => '2n1d',
            'name' => ['vi' => 'Tour 2 ngày 1 đêm', 'en' => '2 days 1 night'],
            'subtitle' => [
                'vi' => 'Cuối tuần ngắn — một đêm nghỉ tại '.$placeVi.'.',
                'en' => 'Short weekend — one overnight in '.$placeEn.'.',
            ],
            'seo_body' => [
                'vi' => 'Lọc theo số ngày 2N1D — không trùng trang danh mục vùng.',
                'en' => '2N1D duration filter — not a GEO category duplicate.',
            ],
        ],
        [
            'slug' => 'tour-3-ngay-2-dem',
            'sort' => 2,
            'minDays' => 3,
            'maxDays' => 3,
            'bucket' => '3n2d',
            'name' => ['vi' => 'Tour 3 ngày 2 đêm', 'en' => '3 days 2 nights'],
            'subtitle' => [
                'vi' => 'Khám phá vừa đủ — hai đêm tại '.$placeVi.'.',
                'en' => 'Enough depth — two nights in '.$placeEn.'.',
            ],
            'seo_body' => [
                'vi' => 'Gói 3N2D phổ biến — chủ đề thời lượng, không phải danh mục GEO.',
                'en' => 'Popular 3N2D packages — duration theme, not GEO.',
            ],
        ],
        [
            'slug' => 'tour-4-ngay-3-dem',
            'sort' => 3,
            'minDays' => 4,
            'maxDays' => 4,
            'bucket' => '4n3d',
            'name' => ['vi' => 'Tour 4 ngày 3 đêm', 'en' => '4 days 3 nights'],
            'subtitle' => [
                'vi' => 'Khám phá sâu hơn — ba đêm trải nghiệm '.$placeVi.'.',
                'en' => 'Deeper exploration — three nights in '.$placeEn.'.',
            ],
            'seo_body' => [
                'vi' => 'Lịch 4N3D — lọc theo thời lượng chương trình.',
                'en' => '4N3D itineraries — program-duration filter.',
            ],
        ],
        [
            'slug' => 'tour-tu-5-ngay',
            'sort' => 4,
            'minDays' => 5,
            'maxDays' => null,
            'bucket' => '5plus',
            'name' => ['vi' => 'Tour từ 5 ngày', 'en' => '5+ day tours'],
            'subtitle' => [
                'vi' => 'Tour dài ngày và combo nhiều điểm đến từ '.$placeVi.'.',
                'en' => 'Extended tours and multi-stop combos from '.$placeEn.'.',
            ],
            'seo_body' => [
                'vi' => 'Tour dài / combo — chủ đề thời lượng, không trùng danh mục vùng.',
                'en' => 'Long tours & combos — duration insight, not a GEO page.',
            ],
        ],
    ];

    $out = [];
    foreach ($defs as $def) {
        $bucket = $def['bucket'];
        unset($def['bucket']);
        $def['zoneSlug'] = $hubZone;
        $def['type'] = 'theme';
        $def['packageSlugs'] = array_values($byBucket[$bucket] ?? []);
        $def['faqs'] = [];
        $out[] = $def;
    }

    return $out;
}

/**
 * @param  list<string>  $packageSlugs
 * @return array<string, mixed>
 */
function hub_region_category(
    string $zoneSlug,
    string $slug,
    int $sort,
    string $nameVi,
    string $nameEn,
    string $subVi,
    string $subEn,
    string $seoVi,
    string $seoEn,
    array $packageSlugs,
): array {
    return [
        'zoneSlug' => $zoneSlug,
        'slug' => $slug,
        'type' => 'region',
        'sort' => $sort,
        'packageSlugs' => array_values($packageSlugs),
        'name' => ['vi' => $nameVi, 'en' => $nameEn],
        'subtitle' => ['vi' => $subVi, 'en' => $subEn],
        'seo_body' => ['vi' => $seoVi, 'en' => $seoEn],
        'faqs' => [],
    ];
}

/**
 * @param  list<string>  $packageSlugs
 * @return array<string, mixed>
 */
function hub_insight_theme(
    string $hubZone,
    string $slug,
    int $sort,
    string $nameVi,
    string $nameEn,
    string $subVi,
    string $subEn,
    string $seoVi,
    string $seoEn,
    array $packageSlugs,
): array {
    return [
        'zoneSlug' => $hubZone,
        'slug' => $slug,
        'type' => 'theme',
        'sort' => $sort,
        'packageSlugs' => array_values($packageSlugs),
        'name' => ['vi' => $nameVi, 'en' => $nameEn],
        'subtitle' => ['vi' => $subVi, 'en' => $subEn],
        'seo_body' => ['vi' => $seoVi, 'en' => $seoEn],
        'faqs' => [],
    ];
}
