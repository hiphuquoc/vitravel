<?php

namespace Database\Seeders;

use App\Models\ContentTypeTag;
use App\Models\ContentTypeTagTranslation;
use App\Models\Language;
use App\Models\ReviewPlatform;
use App\Models\TravelStyle;
use App\Models\TravelStyleTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $vi = Language::idByCode('vi');
        $en = Language::idByCode('en');

        $styles = [
            'long-duration' => ['vi' => 'Tour dài ngày', 'en' => 'Long duration'],
            'heritage-rich' => ['vi' => 'Nhiều di sản', 'en' => 'Heritage rich'],
            'nature-homestay' => ['vi' => 'Thiên nhiên & homestay', 'en' => 'Nature & homestay'],
            'culture-history' => ['vi' => 'Văn hoá & lịch sử', 'en' => 'Culture & history'],
            'balanced' => ['vi' => 'Kỳ nghỉ cân bằng', 'en' => 'Balanced'],
            'beach' => ['vi' => 'Nghỉ dưỡng biển', 'en' => 'Beach'],
            'honeymoon' => ['vi' => 'Trăng mật', 'en' => 'Honeymoon'],
            'family' => ['vi' => 'Gia đình', 'en' => 'Family'],
            'trekking' => ['vi' => 'Trekking & khám phá', 'en' => 'Trekking'],
            'multi-country-combo' => ['vi' => 'Tour kết hợp nhiều nước', 'en' => 'Multi-country combo'],
            'small-group' => ['vi' => 'Nhóm nhỏ', 'en' => 'Small group'],
        ];

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

        $contentTags = [
            'where-to-eat' => ['vi' => 'Ăn uống ở đâu?', 'en' => 'Where to eat & drink?'],
            'where-to-stay' => ['vi' => 'Ở đâu?', 'en' => 'Where to stay?'],
            'what-to-do' => ['vi' => 'Làm gì & xem gì?', 'en' => 'What to do & see?'],
            'travel-tips' => ['vi' => 'Mẹo du lịch', 'en' => 'Travel tips'],
            'trip-report' => ['vi' => 'Cảm nhận chuyến đi', 'en' => 'How was the trip?'],
            'which-tour' => ['vi' => 'Chọn tour nào?', 'en' => 'Which tour to choose?'],
        ];

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

        $platforms = [
            [
                'code' => 'tripadvisor',
                'name' => 'Tripadvisor',
                'rating' => 4.9,
                'review_count' => 320,
                'sort' => 0,
                'quote' => 'Xếp hạng 5/5 từ hơn 900 đánh giá — Giải thưởng Travelers\' Choice 3 năm liên tiếp.',
                'link_label' => 'Đọc đánh giá trên Tripadvisor',
                'url' => 'https://www.tripadvisor.com',
            ],
            [
                'code' => 'google',
                'name' => 'Google',
                'rating' => 4.8,
                'review_count' => 210,
                'sort' => 1,
                'quote' => '4.9/5 trên Google Maps với hơn 600 nhận xét từ du khách khắp thế giới.',
                'link_label' => 'Xem đánh giá trên Google',
                'url' => 'https://www.google.com/maps',
            ],
            [
                'code' => 'trustpilot',
                'name' => 'Trustpilot',
                'rating' => 4.7,
                'review_count' => 95,
                'sort' => 2,
                'quote' => 'Điểm "Xuất sắc" trên Trustpilot — 96% khách hàng chấm 5 sao.',
                'link_label' => 'Đọc đánh giá trên Trustpilot',
                'url' => 'https://www.trustpilot.com',
            ],
        ];

        foreach ($platforms as $platform) {
            ReviewPlatform::query()->updateOrCreate(
                ['code' => $platform['code']],
                array_merge($platform, ['is_active' => true, 'show_on_home' => true])
            );
        }
    }
}
