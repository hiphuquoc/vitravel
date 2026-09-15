<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StayCatalog\StayCatalogOrchestrator;
use Illuminate\Console\Command;

class StayCatalogDrainListsCommand extends Command
{
    protected $signature = 'stay-catalog:drain-lists';

    protected $description = 'Spawn list-crawl filter đang chờ khi còn slot Chrome (P11)';

    public function handle(StayCatalogOrchestrator $orchestrator): int
    {
        $out = $orchestrator->drainPendingLists();
        $this->line(json_encode($out, JSON_UNESCAPED_UNICODE) ?: '{}');

        return self::SUCCESS;
    }
}
