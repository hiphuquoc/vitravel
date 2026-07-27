<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => 'vi',
                'name' => 'Tiếng Việt',
                'name_native' => 'Tiếng Việt',
                'flag' => '/images/flags/vi.svg',
                'og_locale' => 'vi_VN',
                'hreflang' => 'vi',
                'dir' => 'ltr',
                'is_active' => true,
                'is_default' => true,
                'sort' => 0,
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'name_native' => 'English',
                'flag' => '/images/flags/en.svg',
                'og_locale' => 'en_US',
                'hreflang' => 'en',
                'dir' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'sort' => 1,
            ],
        ];

        foreach ($rows as $row) {
            Language::query()->updateOrCreate(['code' => $row['code']], $row);
        }

        Language::clearCache();
    }
}
