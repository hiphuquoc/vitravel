<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StayCatalog\StayCatalogRebuildService;
use Illuminate\Console\Command;

class StayCatalogRebuildCommand extends Command
{
    protected $signature = 'stay:catalog-rebuild
        {layer=offline : offline|aliases|areas|properties|improve}
        {--dry-run : Không ghi DB}
        {--limit=0 : Giới hạn số hàng}
        {--chunk=40 : Số hàng mỗi lô (tránh OOM attrs/html)}
        {--from-id=0 : Resume từ service/item id}
        {--no-seed : Không seed cây area khi layer=areas}';

    protected $description = 'Tái xây catalog chỗ nghỉ (R1 offline / R2 area / P6 property / R4 improve)';

    public function handle(StayCatalogRebuildService $rebuild): int
    {
        $layer = strtolower((string) $this->argument('layer'));
        $dry = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $chunk = (int) $this->option('chunk');
        $fromId = (int) $this->option('from-id');
        $this->info('stay:catalog-rebuild layer='.$layer.' dry_run='.($dry ? '1' : '0').' chunk='.$chunk.' from_id='.$fromId);
        $report = $rebuild->run(
            $layer,
            $dry,
            $limit,
            ! (bool) $this->option('no-seed'),
            $chunk,
            $fromId,
            function (string $label, int $n, int $id): void {
                $mb = round(memory_get_usage(true) / 1048576, 1);
                $this->line("  {$label} n={$n} id={$id} mem={$mb}MB");
            },
        );
        $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}');

        return self::SUCCESS;
    }
}
