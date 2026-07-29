<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\CruiseType;
use App\Services\SeoService;
use Illuminate\Database\Seeder;

/**
 * Gắn cây SEO Hitour: hubs → children (country / cruise_type / blog_category).
 */
class SeoHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $seo = app(SeoService::class);
        $locale = 'vi';

        // Tours: countries under tours hub
        $seo->attachCountriesToToursHub($locale);

        // Cruises: ensure each cruise type has SEO under cruises hub, then attach
        $cruisesHub = $seo->ensureCruisesHub($locale);
        CruiseType::query()->each(function (CruiseType $type) use ($seo, $locale, $cruisesHub) {
            $seo->ensureSeoFor($type, 'cruise_type', $locale, [
                'slug' => $type->slug,
                'title' => $type->name,
                'seo_title' => $type->name,
                'status' => 'published',
                'parent_id' => $cruisesHub->id,
            ]);
        });
        $seo->attachCruiseTypesToCruisesHub($locale);

        // Guide: ensure each blog category under guide hub, then attach
        $guideHub = $seo->ensureGuideHub($locale);
        BlogCategory::query()->with('translations')->each(function (BlogCategory $cat) use ($seo, $locale, $guideHub) {
            $trans = $cat->translation($locale) ?? $cat->translation();
            $slug = $trans?->slug ?: \Illuminate\Support\Str::slug((string) ($trans?->name ?? 'category-'.$cat->id));
            $title = $trans?->name ?? $slug;

            $seo->ensureSeoFor($cat, 'blog_category', $locale, [
                'slug' => $slug,
                'title' => $title,
                'seo_title' => $title,
                'status' => 'published',
                'parent_id' => $guideHub->id,
            ]);
        });
        $seo->attachBlogCategoriesToGuideHub($locale);
    }
}
