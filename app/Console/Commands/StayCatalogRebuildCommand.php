<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StayCatalog\StayCatalogRebuildService;
use Illuminate\Console\Command;

class StayCatalogRebuildCommand extends Command
{
    protected $signature = 'stay:catalog-rebuild
        {layer=offline : offline|areas|properties|improve}
        {--dry-run : Không ghi DB}
        {--limit=0 : Giới hạn số hàng}
        {--no-seed : Không seed cây area khi layer=areas}';

    protected $description = 'Tái xây catalog chỗ nghỉ (R1 offline / R2 area / P6 property / R4 improve)';

    public function handle(StayCatalogRebuildService $rebuild): int
    {
        $layer = strtolower((string) $this->argument('layer'));
        $dry = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $this->info("stay:catalog-rebuild layer={$layer} dry_run=".($dry ? '1' : '0'));
        $report = $rebuild->run($layer, $dry, $limit, ! (bool) $this->option('no-seed'));
        $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}');

        return self::SUCCESS;
    }
}
