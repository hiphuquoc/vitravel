<?php

namespace Database\Seeders;

use App\Models\ExperienceVideo;
use App\Models\ExperienceVideoTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class ExperienceVideoSeeder extends Seeder
{
    public function run(): void
    {
        $vi = Language::idByCode('vi');
        $en = Language::idByCode('en');

        $rows = [
            [
                'youtube_id' => 'LXb3EKWsInQ',
                'duration' => '12:40',
                'tag' => 'Xuyên Việt',
                'sort' => 0,
                'show_on_home' => true,
                'vi' => [
                    'title' => 'Hành trình xuyên Việt 14 ngày cùng gia đình chị Sarah',
                    'description' => 'Những khoảnh khắc chân thật trên cung đường dài cùng ViTravel.',
                ],
                'en' => [
                    'title' => '14-day Vietnam journey with Sarah’s family',
                    'description' => 'Authentic moments along the road with ViTravel.',
                ],
            ],
            [
                'youtube_id' => 'sNPnbDn9b4k',
                'duration' => '08:15',
                'tag' => 'Vịnh Lan Hạ',
                'sort' => 1,
                'show_on_home' => true,
                'vi' => [
                    'title' => 'Một đêm trên du thuyền vịnh Lan Hạ',
                    'description' => 'Bình minh trên vịnh — góc nhìn từ boong tàu.',
                ],
                'en' => [
                    'title' => 'A night on a Lan Ha Bay cruise',
                    'description' => 'Sunrise on the bay from the deck.',
                ],
            ],
            [
                'youtube_id' => 'C0DPdy98e4c',
                'duration' => '10:02',
                'tag' => 'Sa Pa',
                'sort' => 2,
                'show_on_home' => true,
                'vi' => [
                    'title' => 'Trekking mùa lúa chín Sa Pa — nhật ký bằng hình',
                    'description' => 'Leo núi, bản làng và mùa vàng.',
                ],
                'en' => [
                    'title' => 'Sa Pa rice season trekking diary',
                    'description' => 'Trails, villages, and golden terraces.',
                ],
            ],
            [
                'youtube_id' => 'ScMzIvxBSi4',
                'duration' => '06:47',
                'tag' => 'Angkor',
                'sort' => 3,
                'show_on_home' => true,
                'vi' => [
                    'title' => 'Bình minh Angkor Wat qua ống kính khách hàng',
                    'description' => 'Khoảnh khắc thiêng liêng trước đền tháp.',
                ],
                'en' => [
                    'title' => 'Angkor Wat sunrise through a guest’s lens',
                    'description' => 'A sacred moment before the temples.',
                ],
            ],
        ];

        foreach ($rows as $row) {
            $video = ExperienceVideo::query()->updateOrCreate(
                ['youtube_id' => $row['youtube_id']],
                [
                    'duration' => $row['duration'],
                    'tag' => $row['tag'],
                    'sort' => $row['sort'],
                    'show_on_home' => $row['show_on_home'],
                    'status' => 'published',
                    'published_at' => now()->subDays(10 - $row['sort']),
                ]
            );

            if ($vi) {
                ExperienceVideoTranslation::query()->updateOrCreate(
                    ['experience_video_id' => $video->id, 'language_id' => $vi],
                    $row['vi']
                );
            }
            if ($en) {
                ExperienceVideoTranslation::query()->updateOrCreate(
                    ['experience_video_id' => $video->id, 'language_id' => $en],
                    $row['en']
                );
            }
        }
    }
}
