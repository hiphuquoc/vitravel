<?php

namespace Database\Seeders;

use App\Models\CruiseType;
use App\Support\ProjectSeed;
use Illuminate\Database\Seeder;

class CruiseTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProjectSeed::get('cruise_types', []) as $row) {
            CruiseType::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'intro' => $row['intro'] ?? $row['subtitle'] ?? null,
                    'seo_body' => $row['seo_body'] ?? null,
                    'sort' => $row['sort'],
                    'is_active' => true,
                ],
            );
        }
    }
}
