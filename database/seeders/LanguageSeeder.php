<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('language.list', []) as $cfg) {
            Language::query()->updateOrCreate(
                ['code' => $cfg['code']],
                [
                    'name' => $cfg['name'],
                    'name_native' => $cfg['name_native'] ?? null,
                    'flag' => $cfg['flag'] ?? null,
                    'og_locale' => $cfg['og_locale'] ?? null,
                    'hreflang' => $cfg['hreflang'] ?? $cfg['code'],
                    'dir' => $cfg['dir'] ?? 'ltr',
                    'is_active' => ! empty($cfg['is_active']),
                    'is_default' => ! empty($cfg['is_default']),
                    'sort' => $cfg['sort'] ?? 0,
                ]
            );
        }

        $defaultCode = config('language.default_code', 'vi');
        Language::query()->update(['is_default' => false]);
        Language::query()->where('code', $defaultCode)->update([
            'is_default' => true,
            'is_active' => true,
        ]);

        Language::clearCache();
    }
}
