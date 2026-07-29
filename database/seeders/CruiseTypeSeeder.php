<?php

namespace Database\Seeders;

use App\Models\CruiseType;
use Illuminate\Database\Seeder;

class CruiseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['slug' => 'du-thuyen-ha-long', 'name' => 'Du thuyền Hạ Long', 'sort' => 10],
            ['slug' => 'du-thuyen-mekong', 'name' => 'Du thuyền Mekong', 'sort' => 20],
            ['slug' => 'du-thuyen-lan-ha', 'name' => 'Du thuyền Lan Hạ', 'sort' => 30],
        ];

        foreach ($rows as $row) {
            CruiseType::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'sort' => $row['sort'],
                    'is_active' => true,
                ],
            );
        }
    }
}
