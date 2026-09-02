<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PriceTable;
use App\Models\Project;
use App\Models\Service;
use App\Services\PriceTableService;
use App\Support\PriceTableSample;
use App\Support\ProjectContext;
use App\Support\ProjectSeed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Bảng giá mẫu cho package + service.
 * An toàn: bỏ qua chương trình đã có rate; không ghi đè bảng giá admin đã nhập.
 *
 *   php artisan db:seed --class=PriceTableSeeder
 *
 * Tuỳ chọn static (set trước khi gọi từ project:seed --only=tours):
 *   PriceTableSeeder::$onlyPackages = true;  // chỉ tour/cruise packages, không đụng services
 *   PriceTableSeeder::$onlyTourPackages = true; // chỉ packages type=tour
 */
class PriceTableSeeder extends Seeder
{
    /** Chỉ seed bảng giá cho Package (bỏ Service). */
    public static bool $onlyPackages = false;

    /** Chỉ packages type=tour (ngầm bật $onlyPackages). */
    public static bool $onlyTourPackages = false;

    public function run(): void
    {
        $current = ProjectContext::get();
        if ($current) {
            $this->seedForProject($current);

            return;
        }

        $projects = Project::query()->orderBy('id')->get();
        if ($projects->isEmpty()) {
            $this->command?->warn('Chưa có project — bỏ qua PriceTableSeeder.');

            return;
        }

        foreach ($projects as $project) {
            ProjectContext::run($project, function () use ($project) {
                $profile = trim((string) ($project->seed_profile ?: $project->code));
                if ($profile !== '') {
                    ProjectSeed::useProfile($profile);
                }

                try {
                    $this->seedForProject($project);
                } finally {
                    ProjectSeed::clearProfile();
                }
            });
        }
    }

    private function seedForProject(Project $project): void
    {
        $this->call(PriceGuestTypeSeeder::class);

        $defaults = $this->defaults();
        $overrides = $this->overridesByCode();
        $prices = app(PriceTableService::class);
        $filled = 0;
        $skipped = 0;

        $onlyTours = self::$onlyTourPackages;
        $onlyPackages = self::$onlyPackages || $onlyTours;

        $packagesQuery = Package::query()
            ->with(['cabinTypes.translations', 'priceTable.periods.rates']);
        if ($onlyTours) {
            $packagesQuery->where('type', Package::TYPE_TOUR);
        }

        foreach ($packagesQuery->get() as $package) {
            if ($this->seedPriceable($prices, $package, $defaults, $overrides[$package->code ?? ''] ?? null)) {
                $filled++;
            } else {
                $skipped++;
            }
        }

        if (! $onlyPackages) {
            $services = Service::query()
                ->with(['options.translations', 'priceTable.periods.rates'])
                ->get();
            foreach ($services as $service) {
                if ($this->seedPriceable($prices, $service, $defaults, $overrides[$service->code ?? ''] ?? null)) {
                    $filled++;
                } else {
                    $skipped++;
                }
            }
        }

        $scope = $onlyTours ? 'tours' : ($onlyPackages ? 'packages' : 'all');
        $this->command?->info(
            "Price tables [{$project->code}/{$scope}]: +{$filled} mới, {$skipped} bỏ qua (đã có giá / không dựng được)."
        );
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>|null  $explicit
     */
    private function seedPriceable(PriceTableService $prices, Model $priceable, array $defaults, ?array $explicit): bool
    {
        if ($this->hasRates($priceable)) {
            return false;
        }

        $payload = PriceTableSample::payload($priceable, $defaults, $explicit);
        if ($payload === null) {
            return false;
        }

        try {
            DB::transaction(function () use ($prices, $priceable, $payload) {
                $prices->sync($priceable, $payload, 'vi');
            });
        } catch (\Throwable $e) {
            $label = (string) ($priceable->getAttribute('code') ?: $priceable->getKey());
            $this->command?->warn('Bỏ qua bảng giá '.$priceable->getMorphClass().'#'.$label.': '.$e->getMessage());

            return false;
        }

        return true;
    }

    private function hasRates(Model $priceable): bool
    {
        /** @var PriceTable|null $table */
        $table = $priceable->relationLoaded('priceTable')
            ? $priceable->getRelation('priceTable')
            : $priceable->priceTable()->with('periods.rates')->first();

        if (! $table) {
            return false;
        }

        foreach ($table->periods as $period) {
            if ($period->rates->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        $fromSeed = ProjectSeed::get('price_table_defaults', []);

        return is_array($fromSeed) ? $fromSeed : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function overridesByCode(): array
    {
        $out = [];
        foreach (['tours', 'cruises', 'services'] as $key) {
            $rows = ProjectSeed::get($key, []);
            if (! is_array($rows)) {
                continue;
            }
            foreach ($rows as $row) {
                if (! is_array($row) || empty($row['price_table']) || ! is_array($row['price_table'])) {
                    continue;
                }
                $code = (string) ($row['tourCode'] ?? $row['code'] ?? $row['slug'] ?? '');
                if ($code === '') {
                    continue;
                }
                $out[$code] = $row['price_table'];
            }
        }

        return $out;
    }
}
