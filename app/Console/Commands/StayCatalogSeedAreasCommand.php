<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StayCatalog\StayCatalogAreaSeed;
use Illuminate\Console\Command;

class StayCatalogSeedAreasCommand extends Command
{
    protected $signature = 'stay:catalog-seed-areas {--locale=vi}';

    protected $description = 'Seed cây stay_areas (VN, Cát Bà, Hạ Long, Phú Quốc)';

    public function handle(StayCatalogAreaSeed $seed): int
    {
        $out = $seed->seed((string) $this->option('locale'));
        $this->info('stay_areas created='.$out['created'].' updated='.$out['updated']);

        return self::SUCCESS;
    }
}
