<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ProjectSeed;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            ServiceCatalogSeeder::class,
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
        // Plain password — User::$casts['password' => 'hashed'] sẽ hash 1 lần (không Hash::make trước).
        User::query()->updateOrCreate(
            ['email' => $admin['email'] ?? 'admin@vitravel.dev'],
            [
                'name' => $admin['name'] ?? 'Admin',
                'password' => $admin['password'] ?? '111111',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
