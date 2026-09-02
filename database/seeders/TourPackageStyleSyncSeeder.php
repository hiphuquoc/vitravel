<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Package;
use App\Models\TravelStyle;
use App\Support\ProjectSeed;
use App\Support\SampleData;
use Illuminate\Database\Seeder;

/**
 * Chỉ đồng bộ package ↔ travel_styles từ tours/cruises trong seed.
 * Dùng với --only=tours để không chạy full ContentSeeder (tránh đụng bài viết / brand / FAQ…).
 */
class TourPackageStyleSyncSeeder extends Seeder
{
    public function run(): void
    {
        $this->syncStyles(SampleData::tours());
        $this->syncStyles(SampleData::cruises());
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function syncStyles(array $rows): void
    {
        foreach ($rows as $row) {
            $slug = $row['slug'] ?? null;
            if (! is_string($slug) || $slug === '') {
                continue;
            }

            $package = Package::query()
                ->whereHas('seoEntry.translations', fn ($q) => $q->where('slug', $slug))
                ->first();

            if (! $package) {
                continue;
            }

            $codes = $row['styles'] ?? [];
            if (! is_array($codes)) {
                continue;
            }

            $styleIds = TravelStyle::query()
                ->whereIn('code', $codes)
                ->pluck('id');

            $package->travelStyles()->sync($styleIds);
        }
    }
}
