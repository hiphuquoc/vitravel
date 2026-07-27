<?php

namespace Database\Seeders;

use App\Models\HomeSlide;
use App\Models\HomeSlideTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class HomeSlideSeeder extends Seeder
{
    public function run(): void
    {
        $viId = Language::idByCode('vi');
        $enId = Language::idByCode('en');

        $rows = [
            [
                'sort' => 0,
                'text_align' => 'center',
                'link_url' => '/tours/viet-nam',
                'vi' => [
                    'title' => 'Miền Bắc Việt Nam',
                    'title_accent' => 'theo cách của bạn',
                    'description' => 'Tour trọn gói & hành trình riêng qua Hạ Long, Sa Pa, Ninh Bình, Hà Giang — thiết kế bởi chuyên gia bản địa.',
                    'button_label' => 'Khám phá tour Việt Nam',
                    'image_alt' => 'Vịnh Hạ Long lúc hoàng hôn',
                ],
                'en' => [
                    'title' => 'Northern Vietnam',
                    'title_accent' => 'your way',
                    'description' => 'Fully inclusive tours & private journeys through Halong, Sapa, Ninh Binh and Ha Giang — designed by local experts.',
                    'button_label' => 'Explore Vietnam tours',
                    'image_alt' => 'Halong Bay at sunset',
                ],
            ],
            [
                'sort' => 1,
                'text_align' => 'center',
                'link_url' => '/tours/campuchia',
                'vi' => [
                    'title' => 'Campuchia huyền bí',
                    'title_accent' => 'Angkor & Tonlé Sap',
                    'description' => 'Khám phá đền Angkor, Phnom Penh và làng nổi — hành trình Đông Dương đầy cảm xúc.',
                    'button_label' => 'Xem tour Campuchia',
                    'image_alt' => 'Angkor Wat bình minh',
                ],
                'en' => [
                    'title' => 'Mystical Cambodia',
                    'title_accent' => 'Angkor & Tonlé Sap',
                    'description' => 'Discover Angkor temples, Phnom Penh and floating villages — an emotional Indochina journey.',
                    'button_label' => 'View Cambodia tours',
                    'image_alt' => 'Angkor Wat at sunrise',
                ],
            ],
        ];

        foreach ($rows as $row) {
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
