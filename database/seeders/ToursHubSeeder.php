<?php

namespace Database\Seeders;

use App\Services\SeoService;
use Illuminate\Database\Seeder;

/**
 * @deprecated Dùng SeoHierarchySeeder (rebuild toàn cây). Giữ để gọi lẻ vẫn đồng bộ tours tree.
 */
class ToursHubSeeder extends Seeder
{
    public function run(): void
    {
        $seo = app(SeoService::class);
        $seo->rebuildToursSeoTree('vi');
        if (\App\Models\Language::idByCode('en')) {
            $seo->rebuildToursSeoTree('en');
        }
    }
}
