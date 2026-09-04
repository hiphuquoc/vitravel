<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Project;
use App\Models\TourCategory;
use App\Models\TravelStyle;
use App\Models\User;
use App\Services\Purge\EntityPurgeService;
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
use Database\Seeders\TourOnlyContentSeeder;
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
 *   php artisan project:seed hidalat --only=tours
 *   php artisan project:seed hihagiang --fresh-project
 */
class ProjectSeedCommand extends Command
{
    protected $signature = 'project:seed
        {profile : Mã seed / project (vd: vitravel, hicatba)}
        {--domain=* : Domain map Host → project (có thể lặp; domain đầu = primary)}
        {--name= : Tên hiển thị project}
        {--only= : Chỉ chạy nhóm seeder: services | tours (xóa sạch chủ đề/danh mục/chi tiết tour rồi seed lại). Không đụng cụm khác}
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
            $domains = array_values(array_filter(
                array_map(
                    static fn ($d) => is_string($d) ? trim($d) : '',
                    (array) $this->option('domain'),
                ),
                static fn (string $d) => $d !== '',
            ));
            if ($domains !== []) {
                $ensureArgs['--domain'] = $domains;
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
                $this->warn("Đang xóa SẠCH cụm tour (chủ đề + danh mục + SEO + chi tiết tour) của project #{$project->id}…");
                $this->purgeTourCluster($project->id);
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
            // Xóa sạch rồi tạo lại: travel_styles + chi tiết tour + chủ đề/danh mục (+ price table)
            'tours' => [
                TaxonomySeeder::class,
                PriceGuestTypeSeeder::class,
                TourOnlyContentSeeder::class,
                PriceTableSeeder::class,
                TourCategorySeeder::class,
            ],
            'tour' => [
                TaxonomySeeder::class,
                PriceGuestTypeSeeder::class,
                TourOnlyContentSeeder::class,
                PriceTableSeeder::class,
                TourCategorySeeder::class,
            ],
            'tour-taxonomy' => [
                TaxonomySeeder::class,
                PriceGuestTypeSeeder::class,
                TourOnlyContentSeeder::class,
                PriceTableSeeder::class,
                TourCategorySeeder::class,
            ],
            'themes' => [
                TaxonomySeeder::class,
                PriceGuestTypeSeeder::class,
                TourOnlyContentSeeder::class,
                PriceTableSeeder::class,
                TourCategorySeeder::class,
            ],
        ];

        if (! isset($aliases[$only])) {
            throw new \InvalidArgumentException(
                'Giá trị --only không hợp lệ. Dùng: services | tours (xóa sạch chủ đề/danh mục/chi tiết tour rồi seed lại).'
            );
        }

        return $aliases[$only];
    }

    /**
     * Xóa sạch cụm tour trước khi seed lại (--only=tours):
     * chủ đề/danh mục (+ SEO/FAQ/media) → chi tiết tour packages (+ SEO/itinerary/price/pivots)
     * → travel_styles. Giữ articles / home / services / cruises.
     */
    private function purgeTourCluster(int $projectId): void
    {
        /** @var EntityPurgeService $purge */
        $purge = app(EntityPurgeService::class);

        // 1) Chủ đề / danh mục — Eloquent purge để kèm SEO (DB::table sẽ để orphan SEO)
        $categoryIds = TourCategory::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->pluck('id');

        $catCount = 0;
        foreach ($categoryIds as $categoryId) {
            $category = TourCategory::withoutGlobalScope('project')->find($categoryId);
            if ($category) {
                $purge->purgeTourCategory($category);
                $catCount++;
            }
        }
        $this->line("  · Đã xóa {$catCount} tour_categories (+ SEO)");

        // Orphan SEO còn sót (nếu từng xóa bằng DB::table trước đây)
        $this->purgeOrphanSeo($projectId, 'tour_category');

        // 2) Chi tiết tour (packages type=tour) — không đụng cruise
        $tourIds = Package::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->where('type', Package::TYPE_TOUR)
            ->pluck('id');

        $tourCount = 0;
        foreach ($tourIds as $tourId) {
            $package = Package::withoutGlobalScope('project')->find($tourId);
            if ($package) {
                $purge->purgePackage($package);
                $tourCount++;
            }
        }
        $this->line("  · Đã xóa {$tourCount} packages type=tour (+ SEO/itinerary)");

        $this->purgeOrphanSeo($projectId, 'package');

        // 3) travel_styles của project (TaxonomySeeder tạo lại)
        $styleIds = TravelStyle::withoutGlobalScope('project')
            ->where('project_id', $projectId)
            ->pluck('id');

        if ($styleIds->isNotEmpty()) {
            if (Schema::hasTable('package_travel_style')) {
                DB::table('package_travel_style')->whereIn('travel_style_id', $styleIds)->delete();
            }
            if (Schema::hasTable('travel_style_translations')) {
                DB::table('travel_style_translations')->whereIn('travel_style_id', $styleIds)->delete();
            }
            TravelStyle::withoutGlobalScope('project')->whereIn('id', $styleIds)->delete();
            $this->line('  · Đã xóa '.$styleIds->count().' travel_styles');
        }

        // 4) Dọn pivot/orphan còn lại theo package thuộc project (cruise giữ nguyên)
        if (Schema::hasTable('packages') && Schema::hasColumn('packages', 'project_id')) {
            $remainingPackageIds = DB::table('packages')->where('project_id', $projectId)->pluck('id');
            // Chỉ còn cruise — pivot tour đã cascade; không đụng
            unset($remainingPackageIds);
        }
    }

    /**
     * Xóa SEO entries orphan theo reference_type (sau khi parent đã mất hoặc xóa lệch).
     */
    private function purgeOrphanSeo(int $projectId, string $referenceType): void
    {
        if (! Schema::hasTable('seo_entries') || ! Schema::hasColumn('seo_entries', 'project_id')) {
            return;
        }

        $entryIds = DB::table('seo_entries')
            ->where('project_id', $projectId)
            ->where('reference_type', $referenceType)
            ->pluck('id');

        if ($entryIds->isEmpty()) {
            return;
        }

        // Chỉ xóa entry không còn parent hợp lệ
        $orphanIds = [];
        foreach ($entryIds as $entryId) {
            $row = DB::table('seo_entries')->where('id', $entryId)->first();
            if (! $row) {
                continue;
            }
            $refId = (int) ($row->reference_id ?? 0);
            $exists = match ($referenceType) {
                'tour_category' => Schema::hasTable('tour_categories')
                    && DB::table('tour_categories')->where('id', $refId)->exists(),
                'package' => Schema::hasTable('packages')
                    && DB::table('packages')->where('id', $refId)->exists(),
                default => true,
            };
            if (! $exists) {
                $orphanIds[] = (int) $entryId;
            }
        }

        if ($orphanIds === []) {
            return;
        }

        if (Schema::hasTable('seo_entry_translations')) {
            DB::table('seo_entry_translations')->whereIn('seo_entry_id', $orphanIds)->delete();
        }
        // Gỡ parent_id trỏ tới orphan trước khi xóa
        DB::table('seo_entries')->whereIn('parent_id', $orphanIds)->update(['parent_id' => null]);
        DB::table('seo_entries')->whereIn('id', $orphanIds)->delete();
        $this->line('  · Đã dọn '.count($orphanIds)." SEO orphan ({$referenceType})");
    }

    private function callSilentSeeders(): void
    {
        $only = strtolower(trim((string) $this->option('only')));
        $tourOnly = in_array($only, ['tours', 'tour', 'tour-taxonomy', 'themes'], true);

        if ($tourOnly) {
            PriceTableSeeder::$onlyTourPackages = true;
            PriceTableSeeder::$onlyPackages = true;
        }

        try {
            $seeders = $this->seederList();

            foreach ($seeders as $seeder) {
                $this->info(' → '.class_basename($seeder));
                $instance = $this->laravel->make($seeder);
                $instance->setContainer($this->laravel);
                $instance->setCommand($this);
                $instance->__invoke();
            }
        } finally {
            PriceTableSeeder::$onlyTourPackages = false;
            PriceTableSeeder::$onlyPackages = false;
        }
    }

    private function purgeProjectContent(int $projectId): void
    {
        // Pivots không có project_id — xóa theo package/category của project trước
        if (Schema::hasTable('packages') && Schema::hasColumn('packages', 'project_id')) {
            $packageIds = DB::table('packages')->where('project_id', $projectId)->pluck('id');
            if ($packageIds->isNotEmpty()) {
                foreach (['package_tour_category', 'package_travel_style', 'package_country', 'package_destination', 'package_related', 'article_package'] as $pivot) {
                    if (Schema::hasTable($pivot) && Schema::hasColumn($pivot, 'package_id')) {
                        try {
                            DB::table($pivot)->whereIn('package_id', $packageIds)->delete();
                        } catch (\Throwable $e) {
                            $this->warn("Skip purge pivot {$pivot}: ".$e->getMessage());
                        }
                    }
                }
                // package_related có thể dùng related_package_id
                if (Schema::hasTable('package_related') && Schema::hasColumn('package_related', 'related_package_id')) {
                    try {
                        DB::table('package_related')->whereIn('related_package_id', $packageIds)->delete();
                    } catch (\Throwable $e) {
                        $this->warn('Skip purge package_related related: '.$e->getMessage());
                    }
                }
            }
        }

        if (Schema::hasTable('tour_categories') && Schema::hasColumn('tour_categories', 'project_id')) {
            $categoryIds = DB::table('tour_categories')->where('project_id', $projectId)->pluck('id');
            if ($categoryIds->isNotEmpty() && Schema::hasTable('package_tour_category')) {
                try {
                    DB::table('package_tour_category')->whereIn('tour_category_id', $categoryIds)->delete();
                } catch (\Throwable $e) {
                    $this->warn('Skip purge package_tour_category: '.$e->getMessage());
                }
            }
        }

        // Child tables trước (translations không luôn có project_id trên mọi bản)
        $childFirst = [
            'price_rates',
            'price_periods',
            'price_variant_translations',
            'price_variants',
            'price_tables',
            'price_guest_type_translations',
            'seo_entry_translations',
            'package_translations',
            'package_itinerary_day_translations',
            'package_itinerary_days',
            'package_cabin_type_translations',
            'package_cabin_types',
            'tour_category_translations',
            'travel_style_translations',
            'country_translations',
            'destination_translations',
            'service_option_translations',
            'service_options',
            'service_translations',
            'article_translations',
            'faq_translations',
            'home_slide_translations',
            'home_section_translations',
            'company_profile_translations',
            'blog_category_translations',
            'content_type_tag_translations',
            'keyword_tag_translations',
        ];

        $tables = [
            'price_guest_types',
            'seo_entries',
            'packages',
            'services',
            'service_categories',
            'article_content_type_tag',
            'article_keyword_tag',
            'article_related',
            'articles',
            'comments',
            'home_featured_tours',
            'home_featured_cruises',
            'home_featured_countries',
            'home_featured_review_platforms',
            'home_slides',
            'home_sections',
            'company_profiles',
            'faqs',
            'media',
            'redirect_info',
            'countries',
            'destinations',
            'travel_styles',
            'tour_categories',
            'blog_categories',
            'cruise_types',
            'content_type_tags',
            'keyword_tags',
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

        foreach (array_merge($childFirst, $tables) as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            try {
                if (Schema::hasColumn($table, 'project_id')) {
                    DB::table($table)->where('project_id', $projectId)->delete();

                    continue;
                }
                // Translations gắn parent có project_id
                $this->purgeChildByParentProject($table, $projectId);
            } catch (\Throwable $e) {
                $this->warn("Skip purge {$table}: ".$e->getMessage());
            }
        }
    }

    /**
     * Xóa bảng con không có project_id nhưng parent có (vd: package_itinerary_day_translations).
     */
    private function purgeChildByParentProject(string $table, int $projectId): void
    {
        $map = [
            'package_itinerary_day_translations' => ['package_itinerary_days', 'package_itinerary_day_id'],
            'package_cabin_type_translations' => ['package_cabin_types', 'package_cabin_type_id'],
            'price_variant_translations' => ['price_variants', 'price_variant_id'],
            'price_rates' => ['price_tables', 'price_table_id'],
            'price_periods' => ['price_tables', 'price_table_id'],
            'price_variants' => ['price_tables', 'price_table_id'],
            'seo_entry_translations' => ['seo_entries', 'seo_entry_id'],
            'faq_translations' => ['faqs', 'faq_id'],
            'service_option_translations' => ['service_options', 'service_option_id'],
            'article_content_type_tag' => ['articles', 'article_id'],
            'article_keyword_tag' => ['articles', 'article_id'],
            'article_related' => ['articles', 'article_id'],
        ];

        if (! isset($map[$table])) {
            return;
        }

        [$parentTable, $fk] = $map[$table];
        if (! Schema::hasTable($parentTable) || ! Schema::hasColumn($parentTable, 'project_id')) {
            return;
        }

        $parentIds = DB::table($parentTable)->where('project_id', $projectId)->pluck('id');
        if ($parentIds->isEmpty()) {
            return;
        }

        DB::table($table)->whereIn($fk, $parentIds)->delete();
    }
}
