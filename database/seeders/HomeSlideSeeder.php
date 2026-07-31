<?php

namespace Database\Seeders;

use App\Models\HomeSlide;
use App\Models\HomeSlideTranslation;
use App\Models\Language;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;

class HomeSlideSeeder extends Seeder
{
    public function run(): void
    {
        $viId = Language::idByCode('vi');
        $enId = Language::idByCode('en');

        foreach (ProjectSeed::get('home_slides', []) as $row) {
            $slide = HomeSlide::query()->updateOrCreate(
                ['sort' => $row['sort']],
                [
                    'text_align' => $row['text_align'],
                    'link_url' => $row['link_url'],
                    'is_active' => true,
                ],
            );

            if ($viId) {
                HomeSlideTranslation::query()->updateOrCreate(
                    ['home_slide_id' => $slide->id, 'language_id' => $viId],
                    $row['vi'],
                );
            }

            if ($enId) {
                HomeSlideTranslation::query()->updateOrCreate(
                    ['home_slide_id' => $slide->id, 'language_id' => $enId],
                    $row['en'],
                );
            }
        }
    }
}
