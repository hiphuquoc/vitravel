<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Support\SampleData;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            'Việt Nam' => 'VN',
            'Úc' => 'AU',
            'Pháp' => 'FR',
            'Ý' => 'IT',
            'Mỹ' => 'US',
            'Anh' => 'GB',
            'Đức' => 'DE',
            'Hàn Quốc' => 'KR',
            'Nhật Bản' => 'JP',
        ];

        foreach (SampleData::testimonials() as $sort => $row) {
            Review::query()->updateOrCreate(
                [
                    'author_name' => $row['name'],
                    'content' => $row['quote'],
                ],
                [
                    'author_country' => $row['country'],
                    'author_country_code' => $flags[$row['country']] ?? 'VN',
                    'rating' => max(1, min(5, (int) round((float) $row['rating']))),
                    'question_title' => $row['trip'] ?? null,
                    'photos_count' => (int) ($row['photos'] ?? 0),
                    'reviewed_on' => now()->subDays(10 + $sort * 7)->toDateString(),
                    'is_featured' => true,
                    'show_on_home' => true,
                    'status' => 'published',
                    'sort' => $sort,
                    'reviewable_type' => 'company',
                    'reviewable_id' => null,
                ]
            );
        }
    }
}
