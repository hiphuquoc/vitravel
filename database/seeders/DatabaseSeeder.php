<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            TaxonomySeeder::class,
            ContentSeeder::class,
            TourCategorySeeder::class,
            HomeSlideSeeder::class,
            HomeSectionSeeder::class,
            ReviewSeeder::class,
            ExperienceVideoSeeder::class,
            HomeFeaturedSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'admin@vitravel.dev'],
            [
                'name' => 'Admin ViTravel',
                'password' => Hash::make('vitravel@admin2026'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
