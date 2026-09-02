<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Package;
use App\Support\SampleData;

/**
 * Chỉ seed zones/countries + chi tiết tour (packages type=tour).
 * Dùng với project:seed --only=tours — không đụng articles / brand / cruises / home.
 */
class TourOnlyContentSeeder extends ContentSeeder
{
    public function run(): void
    {
        $this->seo = app(\App\Services\SeoService::class);
        $this->viId = \App\Models\Language::idByCode('vi');
        $this->enId = \App\Models\Language::idByCode('en');
        $map = \App\Support\ProjectSeed::get('content_tag_map', []);
        $this->contentTagMap = is_array($map) ? $map : [];

        $this->seedCountries();
        $this->seedPackages(SampleData::tours(), Package::TYPE_TOUR);
    }
}
