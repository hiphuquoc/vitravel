<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ProjectSeed;
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
            CruiseTypeSeeder::class,
            ContentSeeder::class,
            TourCategorySeeder::class,
            HomeSlideSeeder::class,
            HomeSectionSeeder::class,
            ReviewSeeder::class,
            ExperienceVideoSeeder::class,
            HomeFeaturedSeeder::class,
            // Cuối cùng: hub → country/type → package/article + purge redirect hỏng
            SeoHierarchySeeder::class,
        ]);

        $admin = ProjectSeed::meta()['admin'] ?? [];
        User::query()->updateOrCreate(
            ['email' => $admin['email'] ?? 'admin@vitravel.dev'],
            [
                'name' => $admin['name'] ?? 'Admin',
                'password' => Hash::make($admin['password'] ?? 'password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
