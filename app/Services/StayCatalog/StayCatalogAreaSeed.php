<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\Language;
use App\Models\StayArea;
use App\Models\StayAreaTranslation;
use Illuminate\Support\Facades\DB;

/**
 * Cây khu vực VN phục vụ overlap Cát Bà / Hạ Long (+ Phú Quốc data cũ).
 */
final class StayCatalogAreaSeed
{
    /**
     * @return array{created: int, updated: int}
     */
    public function seed(string $locale = 'vi'): array
    {
        $langId = Language::idByCode($locale);
        $created = 0;
        $updated = 0;

        $tree = $this->tree();
        DB::transaction(function () use ($tree, $langId, &$created, &$updated): void {
            $this->upsertTree($tree, null, $langId, $created, $updated);
            $this->linkRelated('cat-ba', ['ha-long']);
            $this->linkRelated('ha-long', ['cat-ba']);
        });

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * @param  list<array<string, mixed>>  $nodes
     */
    private function upsertTree(array $nodes, ?int $parentId, ?int $langId, int &$created, int &$updated): void
    {
        $sort = 0;
        foreach ($nodes as $node) {
            $sort++;
            $area = StayArea::query()->firstOrNew(['slug' => $node['slug']]);
            $isNew = ! $area->exists;
            $area->fill([
                'parent_id' => $parentId,
                'level' => $node['level'],
                'country_code' => $node['country_code'] ?? 'VN',
                'booking_dest_id' => $node['booking_dest_id'] ?? null,
                'booking_dest_type' => $node['booking_dest_type'] ?? null,
                'lat' => $node['lat'] ?? null,
                'lng' => $node['lng'] ?? null,
                'bbox' => $node['bbox'] ?? null,
                'radius_meters' => $node['radius_meters'] ?? null,
                'aliases' => $node['aliases'] ?? [],
                'sort' => $node['sort'] ?? $sort,
                'is_active' => true,
            ]);
            $area->save();
            $isNew ? $created++ : $updated++;

            if ($langId) {
                StayAreaTranslation::query()->updateOrCreate(
                    ['stay_area_id' => $area->id, 'language_id' => $langId],
                    [
                        'name' => $node['name'],
                        'slug' => $node['slug'],
                        'intro' => $node['intro'] ?? null,
                    ],
                );
            }

            if (! empty($node['children']) && is_array($node['children'])) {
                $this->upsertTree($node['children'], (int) $area->id, $langId, $created, $updated);
            }
        }
    }

    /** @param  list<string>  $relatedSlugs */
    private function linkRelated(string $slug, array $relatedSlugs): void
    {
        $area = StayArea::query()->where('slug', $slug)->first();
        if (! $area) {
            return;
        }
        $ids = StayArea::query()->whereIn('slug', $relatedSlugs)->pluck('id')->all();
        if ($ids !== []) {
            $area->relatedAreas()->syncWithoutDetaching($ids);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tree(): array
    {
        return [[
            'slug' => 'vn',
            'name' => 'Việt Nam',
            'level' => StayArea::LEVEL_COUNTRY,
            'country_code' => 'VN',
            'aliases' => ['vietnam', 'viet nam', 'việt nam'],
            'children' => [
                [
                    'slug' => 'hai-phong',
                    'name' => 'Hải Phòng',
                    'level' => StayArea::LEVEL_REGION,
                    'aliases' => ['hai phong', 'hải phòng'],
                    'children' => [[
                        'slug' => 'cat-ba',
                        'name' => 'Cát Bà',
                        'level' => StayArea::LEVEL_DESTINATION,
                        'booking_dest_id' => '-3712045',
                        'booking_dest_type' => 'city',
                        'lat' => 20.7278,
                        'lng' => 107.0489,
                        'radius_meters' => 18000,
                        'aliases' => ['cat ba', 'cát bà', 'catba', 'dao cat ba', 'đảo cát bà'],
                        'children' => [
                            [
                                'slug' => 'thi-tran-cat-ba',
                                'name' => 'Thị trấn Cát Bà',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['thi tran cat ba', 'thị trấn cát bà'],
                            ],
                            [
                                'slug' => 'viet-hai',
                                'name' => 'Việt Hải',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['viet hai', 'việt hải'],
                            ],
                            [
                                'slug' => 'lan-ha',
                                'name' => 'Lan Hạ',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['lan ha', 'lan hạ', 'vinh lan ha'],
                            ],
                        ],
                    ]],
                ],
                [
                    'slug' => 'quang-ninh',
                    'name' => 'Quảng Ninh',
                    'level' => StayArea::LEVEL_REGION,
                    'aliases' => ['quang ninh', 'quảng ninh'],
                    'children' => [[
                        'slug' => 'ha-long',
                        'name' => 'Hạ Long',
                        'level' => StayArea::LEVEL_DESTINATION,
                        'booking_dest_type' => 'city',
                        'lat' => 20.9500,
                        'lng' => 107.0800,
                        'radius_meters' => 22000,
                        'aliases' => ['ha long', 'hạ long', 'halong', 'ha long bay', 'vịnh hạ long'],
                        'children' => [
                            [
                                'slug' => 'bai-chay',
                                'name' => 'Bãi Cháy',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['bai chay', 'bãi cháy', 'baichay'],
                            ],
                            [
                                'slug' => 'hon-gai',
                                'name' => 'Hòn Gai',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['hon gai', 'hòn gai'],
                            ],
                            [
                                'slug' => 'tuan-chau',
                                'name' => 'Tuần Châu',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['tuan chau', 'tuần châu'],
                            ],
                        ],
                    ]],
                ],
                [
                    'slug' => 'kien-giang',
                    'name' => 'Kiên Giang',
                    'level' => StayArea::LEVEL_REGION,
                    'aliases' => ['kien giang', 'kiên giang'],
                    'children' => [[
                        'slug' => 'phu-quoc',
                        'name' => 'Phú Quốc',
                        'level' => StayArea::LEVEL_DESTINATION,
                        'booking_dest_type' => 'city',
                        'lat' => 10.2899,
                        'lng' => 103.9840,
                        'radius_meters' => 35000,
                        'aliases' => ['phu quoc', 'phú quốc', 'phuquoc', 'dao phu quoc'],
                        'children' => [
                            [
                                'slug' => 'duong-dong',
                                'name' => 'Dương Đông',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['duong dong', 'dương đông'],
                            ],
                            [
                                'slug' => 'duong-to',
                                'name' => 'Dương Tơ',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['duong to', 'dương tơ'],
                            ],
                            [
                                'slug' => 'an-thoi',
                                'name' => 'An Thới',
                                'level' => StayArea::LEVEL_ZONE,
                                'aliases' => ['an thoi', 'an thới'],
                            ],
                        ],
                    ]],
                ],
            ],
        ]];
    }
}
