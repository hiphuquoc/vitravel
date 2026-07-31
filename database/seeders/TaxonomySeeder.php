<?php

namespace Database\Seeders;

use App\Models\ContentTypeTag;
use App\Models\ContentTypeTagTranslation;
use App\Models\Language;
use App\Models\ReviewPlatform;
use App\Models\TravelStyle;
use App\Models\TravelStyleTranslation;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $vi = Language::idByCode('vi');
        $en = Language::idByCode('en');

        $styles = ProjectSeed::get('travel_styles', []);

        $sort = 0;
        foreach ($styles as $code => $labels) {
            $style = TravelStyle::query()->updateOrCreate(
                ['code' => $code],
                ['sort' => $sort++, 'is_active' => true]
            );

            if ($vi) {
                TravelStyleTranslation::query()->updateOrCreate(
                    ['travel_style_id' => $style->id, 'language_id' => $vi],
                    ['name' => $labels['vi'], 'slug' => Str::slug($labels['vi'])]
                );
            }
            if ($en) {
                TravelStyleTranslation::query()->updateOrCreate(
                    ['travel_style_id' => $style->id, 'language_id' => $en],
                    ['name' => $labels['en'], 'slug' => Str::slug($labels['en'])]
                );
            }
        }

        $contentTags = ProjectSeed::get('content_tags', []);

        $sort = 0;
        foreach ($contentTags as $code => $labels) {
            $tag = ContentTypeTag::query()->updateOrCreate(
                ['code' => $code],
                ['sort' => $sort++, 'is_active' => true]
            );

            if ($vi) {
                ContentTypeTagTranslation::query()->updateOrCreate(
                    ['content_type_tag_id' => $tag->id, 'language_id' => $vi],
                    ['label' => $labels['vi'], 'slug' => Str::slug($labels['vi'])]
                );
            }
            if ($en) {
                ContentTypeTagTranslation::query()->updateOrCreate(
                    ['content_type_tag_id' => $tag->id, 'language_id' => $en],
                    ['label' => $labels['en'], 'slug' => Str::slug($labels['en'])]
                );
            }
        }

        foreach (ProjectSeed::get('review_platforms', []) as $platform) {
            ReviewPlatform::query()->updateOrCreate(
                ['code' => $platform['code']],
                array_merge($platform, ['is_active' => true, 'show_on_home' => true])
            );
        }
    }
}
