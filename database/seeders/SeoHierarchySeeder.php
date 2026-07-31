<?php

namespace Database\Seeders;

use App\Services\SeoService;
use Illuminate\Database\Seeder;

/**
 * Bước cuối pipeline seed: gắn hub → country/cruise_type/blog_category → package/tour_category/article
 * và rebuild slug_full theo cha (tránh 404 khi listing có data nhưng URL chưa đúng cây SEO).
 *
 * Idempotent — chạy lại an toàn: `php artisan db:seed --class=SeoHierarchySeeder`
 */
class SeoHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $seo = app(SeoService::class);
        $seo->rebuildPublicSeoTree(['vi', 'en']);
        $seo->purgeBadRedirects();
    }
}
