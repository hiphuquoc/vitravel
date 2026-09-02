<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;
use Database\Seeders\ContentSeeder;
use Database\Seeders\CruiseTypeSeeder;
use Database\Seeders\ExperienceVideoSeeder;
use Database\Seeders\HomeFeaturedSeeder;
use Database\Seeders\HomeSectionSeeder;
use Database\Seeders\HomeSlideSeeder;
use Database\Seeders\PriceGuestTypeSeeder;
use Database\Seeders\PriceTableSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ReviewSeeder;
use Database\Seeders\SeoHierarchySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Database\Seeders\TaxonomySeeder;
use Database\Seeders\TourCategorySeeder;
use Database\Seeders\TourPackageStyleSyncSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seed thêm / ghi đè nội dung một project vào DB hiện có (không migrate:fresh).
 *
 * Ví dụ:
 *   php artisan project:seed vitravel --domain=vitravel.test
 *   php artisan project:seed hicatba --domain=hicatba.dev
 */
class ProjectSeedCommand extends Command
{
    protected $signature = 'project:seed
        {profile : Mã seed / project (vd: vitravel, hicatba)}
        {--domain= : Domain map Host → project (vd: hicatba.dev)}
        {--name= : Tên hiển thị project}
        {--only= : Chỉ chạy nhóm seeder: services | tours (chủ đề/danh mục/travel_styles + gắn package). Không đụng cụm khác}
        {--fresh-project : Xóa toàn bộ data của project này rồi seed lại (giữ project khác)}';

    protected $description = 'Seed nội dung một profile (project/seed_{name}.php) vào DB hiện tại';

    public function handle(): int
    {
        if (! Schema::hasTable('projects')) {
            $this->error('Chưa có bảng projects. Chạy: php artisan migrate');

            return self::FAILURE;
        }

        $profile = (string) $this->argument('profile');
        $profile = trim($profile);
        if ($profile === '') {
            $this->error('Thiếu profile.');

            return self::FAILURE;
        }

        ProjectSeed::useProfile($profile);

        try {
            $path = ProjectSeed::path();
            if (! is_file($path)) {
                $this->error("Không thấy file seed: {$path}");

                return self::FAILURE;
            }

            $this->info("Seed file: {$path}");

            $code = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($profile)) ?: $profile;
            $code = trim($code, '-') ?: $profile;

            $ensureArgs = ['profile' => $profile];
            if ($this->option('domain')) {
                $ensureArgs['--domain'] = (string) $this->option('domain');
            }
            if ($this->option('name')) {
                $ensureArgs['--name'] = (string) $this->option('name');
            }

            Artisan::call('project:ensure', $ensureArgs);
            $this->output->write(Artisan::output());

            $project = Project::query()->where('code', $code)->first();
            if (! $project) {
                $this->error("Không tạo được project code={$code}");

                return self::FAILURE;
            }

            if ($this->option('fresh-project')) {
                $this->warn("Đang xóa data project #{$project->id} ({$project->code})…");
                $this->purgeProjectContent($project->id);
            }

            ProjectContext::set($project);
            $this->info("ProjectContext → {$project->code} (#{$project->id})");

            $only = strtolower(trim((string) $this->option('only')));
            if (in_array($only, ['tours', 'tour', 'tour-taxonomy', 'themes'], true)) {
                $this->warn("Đang xóa taxonomy tour cũ (categories + pivots + travel_styles) của project #{$project->id}…");
                $this->purgeTourTaxonomy($project->id);
            }

            try {
                $this->callSilentSeeders();
            } catch (\InvalidArgumentException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            if ($this->option('only')) {
                $this->info("Xong (only={$this->option('only')}). Project {$project->code}.");

                return self::SUCCESS;
            }

            $admin = ProjectSeed::meta()['admin'] ?? [];
            $user = User::query()->updateOrCreate(
                ['email' => $admin['email'] ?? 'admin@vitravel.dev'],
                [
                    'name' => $admin['name'] ?? 'Admin',
                    'password' => $admin['password'] ?? '111111',
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );
            // Keep seed admin as system admin (full access).
            if ($user->role !== 'admin' && $user->role !== 'super_admin') {
                $user->forceFill(['role' => 'admin'])->save();
            }
            ProjectSeeder::attachAdmin($user);

            $this->info("Xong. Project {$project->code} đã có dữ liệu.");
            $this->line('Admin: chọn dự án trong topbar, hoặc gửi header X-Project-Code: '.$project->code);
            $this->line('Public: domain trong project_domains, hoặc ?project='.$project->code.' (khi PROJECT_PUBLIC_QUERY_OVERRIDE).');

            return self::SUCCESS;
        } finally {
            ProjectSeed::clearProfile();
            ProjectContext::clear();
        }
    }

    /**
     * @return list<class-string>
     */
    private function seederList(): array
    {
        $all = [
            TaxonomySeeder::class,
            PriceGuestTypeSeeder::class,
            CruiseTypeSeeder::class,
            ContentSeeder::class,
            ServiceCatalogSeeder::class,
            PriceTableSeeder::class,
            TourCategorySeeder::class,
            HomeSlideSeeder::class,
            HomeSectionSeeder::class,
            ReviewSeeder::class,
            ExperienceVideoSeeder::class,
            HomeFeaturedSeeder::class,
            SeoHierarchySeeder::class,
        ];

        $only = strtolower(trim((string) $this->option('only')));
        if ($only === '') {
            return $all;
        }

        $aliases = [
            'services' => [ServiceCatalogSeeder::class],
            'service' => [ServiceCatalogSeeder::class],
            'stay' => [ServiceCatalogSeeder::class],
            'catalog' => [ServiceCatalogSeeder::class],
            // Chủ đề + danh mục tour + travel_styles + gắn package (không đụng bài viết/home/services catalogue)
            'tours' => [
                TaxonomySeeder::class,
                TourPackageStyleSyncSeeder::class,
                TourCategorySeeder::class,
            ],
            'tour' => [
                TaxonomySeeder::class,
                TourPackageStyleSyncSeeder::class,
                TourCategorySeeder::class,
            ],
            'tour-taxonomy' => [
                TaxonomySeeder::class,
                TourPackageStyleSyncSeeder::class,
                TourCategorySeeder::class,
            ],
            'themes' => [
                TaxonomySeeder::class,
                TourPackageStyleSyncSeeder::class,
                TourCategorySeeder::class,
            ],
        ];

        if (! isset($aliases[$only])) {
            throw new \InvalidArgumentException(
                'Giá trị --only không hợp lệ. Dùng: services | tours (chủ đề/danh mục/travel_styles).'
            );
        }

        return $aliases[$only];
    }

    /**
     * Xóa danh mục/chủ đề tour + pivot + travel_styles của 1 project trước khi seed lại cụm taxonomy.
     * Giữ nguyên packages, articles, services, home…
     */
    private function purgeTourTaxonomy(int $projectId): void
    {
        if (Schema::hasTable('package_tour_category') && Schema::hasTable('tour_categories')) {
            $categoryIds = DB::table('tour_categories')->where('project_id', $projectId)->pluck('id');
            if ($categoryIds->isNotEmpty()) {
                DB::table('package_tour_category')->whereIn('tour_category_id', $categoryIds)->delete();
            }
        }

        foreach ([
            'tour_category_translations',
            'tour_categories',
            'travel_style_translations',
            'travel_styles',
            'package_travel_style',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            try {
                if ($table === 'package_travel_style') {
                    // Pivot không có project_id — xóa theo package thuộc project
                    if (Schema::hasTable('packages') && Schema::hasColumn('packages', 'project_id')) {
                        $packageIds = DB::table('packages')->where('project_id', $projectId)->pluck('id');
                        if ($packageIds->isNotEmpty()) {
                            DB::table('package_travel_style')->whereIn('package_id', $packageIds)->delete();
                        }
                    }

                    continue;
                }
                if ($table === 'tour_category_translations') {
                    if (Schema::hasTable('tour_categories') && Schema::hasColumn('tour_categories', 'project_id')) {
                        $ids = DB::table('tour_categories')->where('project_id', $projectId)->pluck('id');
                        if ($ids->isNotEmpty()) {
                            DB::table('tour_category_translations')->whereIn('tour_category_id', $ids)->delete();
                        }
                    }

                    continue;
                }
                if ($table === 'travel_style_translations') {
                    if (Schema::hasTable('travel_styles') && Schema::hasColumn('travel_styles', 'project_id')) {
                        $ids = DB::table('travel_styles')->where('project_id', $projectId)->pluck('id');
                        if ($ids->isNotEmpty()) {
                            DB::table('travel_style_translations')->whereIn('travel_style_id', $ids)->delete();
                        }
                    }

                    continue;
                }
                if (Schema::hasColumn($table, 'project_id')) {
                    DB::table($table)->where('project_id', $projectId)->delete();
                }
            } catch (\Throwable $e) {
                $this->warn("Skip purge tour taxonomy {$table}: ".$e->getMessage());
            }
        }

        // FAQ gắn tour category
        if (Schema::hasTable('faqs') && Schema::hasColumn('faqs', 'project_id')) {
            try {
                $faqIds = DB::table('faqs')
                    ->where('project_id', $projectId)
                    ->where('faqable_type', 'like', '%TourCategory%')
                    ->pluck('id');
                if ($faqIds->isNotEmpty()) {
                    if (Schema::hasTable('faq_translations')) {
                        DB::table('faq_translations')->whereIn('faq_id', $faqIds)->delete();
                    }
                    DB::table('faqs')->whereIn('id', $faqIds)->delete();
                }
            } catch (\Throwable $e) {
                $this->warn('Skip purge tour category faqs: '.$e->getMessage());
            }
        }
    }

    private function callSilentSeeders(): void
    {
        $seeders = $this->seederList();

        foreach ($seeders as $seeder) {
            $this->info(' → '.class_basename($seeder));
            $instance = $this->laravel->make($seeder);
            $instance->setContainer($this->laravel);
            $instance->setCommand($this);
            $instance->__invoke();
        }
    }

    private function purgeProjectContent(int $projectId): void
    {
        $tables = [
            'price_rates',
            'price_periods',
            'price_variant_translations',
            'price_variants',
            'price_tables',
            'price_guest_type_translations',
            'price_guest_types',
            'seo_entry_translations',
            'seo_entries',
            'package_translations',
            'package_itinerary_day_translations',
            'package_itinerary_days',
            'package_cabin_type_translations',
            'package_cabin_types',
            'package_tour_category',
            'package_travel_style',
            'package_destination',
            'package_related',
            'package_country',
            'packages',
            'service_option_translations',
            'service_options',
            'service_translations',
            'services',
            'service_categories',
            'article_translations',
            'article_content_type_tag',
            'article_keyword_tag',
            'article_package',
            'article_related',
            'articles',
            'comments',
            'home_featured_tours',
            'home_featured_cruises',
            'home_featured_countries',
            'home_featured_review_platforms',
            'home_slide_translations',
            'home_slides',
            'home_section_translations',
            'home_sections',
            'company_profile_translations',
            'company_profiles',
            'faqs',
            'faq_translations',
            'media',
            'redirect_info',
            'countries',
            'country_translations',
            'destinations',
            'destination_translations',
            'travel_styles',
            'travel_style_translations',
            'tour_categories',
            'tour_category_translations',
            'blog_categories',
            'blog_category_translations',
            'cruise_types',
            'content_type_tags',
            'content_type_tag_translations',
            'keyword_tags',
            'keyword_tag_translations',
            'static_pages',
            'reviews',
            'review_platforms',
            'experience_albums',
            'experience_videos',
            'offices',
            'team_members',
            'company_values',
            'reasons_to_choose_us',
            'reference_persons',
            'usps',
            'hero_pills',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'project_id')) {
                continue;
            }
            try {
                \Illuminate\Support\Facades\DB::table($table)->where('project_id', $projectId)->delete();
            } catch (\Throwable $e) {
                $this->warn("Skip purge {$table}: ".$e->getMessage());
            }
        }
    }
}
