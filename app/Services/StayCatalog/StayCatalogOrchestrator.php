<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlService;

/**
 * P11: điều phối discover / list-crawl — không đốt hết slot Chrome.
 */
final class StayCatalogOrchestrator
{
    public function __construct(
        private readonly StayCrawlService $crawl,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        $base = StayCrawlJob::withoutGlobalScopes();
        $listPending = (clone $base)
            ->where('job_type', StayCrawlJob::TYPE_LIST_FILTER)
            ->where('status', StayCrawlJob::STATUS_PENDING)
            ->count();
        $listCrawling = (clone $base)
            ->whereIn('job_type', [StayCrawlJob::TYPE_LIST_FILTER, StayCrawlJob::TYPE_LISTING])
            ->where('status', StayCrawlJob::STATUS_CRAWLING)
            ->count();
        $recent = (clone $base)
            ->where('job_type', StayCrawlJob::TYPE_LIST_FILTER)
            ->latest('id')
            ->limit(80)
            ->get(['id', 'meta']);
        $skipped = 0;
        $taxonAttached = 0;
        foreach ($recent as $job) {
            $skipped += (int) data_get($job->meta, 'skipped_existing', 0);
            $taxonAttached += (int) data_get($job->meta, 'taxon_attached', 0);
        }

        return [
            'discover_pending' => (clone $base)
                ->where('job_type', StayCrawlJob::TYPE_AREA_DISCOVER)
                ->where('status', StayCrawlJob::STATUS_PENDING)
                ->count(),
            'discover_crawling' => (clone $base)
                ->where('job_type', StayCrawlJob::TYPE_AREA_DISCOVER)
                ->where('status', StayCrawlJob::STATUS_CRAWLING)
                ->count(),
            'discover_review' => (clone $base)
                ->where('job_type', StayCrawlJob::TYPE_AREA_DISCOVER)
                ->where('status', StayCrawlJob::STATUS_REVIEW)
                ->count(),
            'list_pending_spawn' => $listPending,
            'list_crawling' => $listCrawling,
            'list_done' => (clone $base)
                ->where('job_type', StayCrawlJob::TYPE_LIST_FILTER)
                ->where('status', StayCrawlJob::STATUS_DONE)
                ->count(),
            'list_spawn_max' => $this->spawnMax(),
            'chrome_slots_free' => max(0, $this->spawnMax() - $listCrawling),
            'recent_skipped_existing' => $skipped,
            'recent_taxon_attached' => $taxonAttached,
        ];
    }

    public function runningListCount(): int
    {
        return StayCrawlJob::withoutGlobalScopes()
            ->whereIn('job_type', [StayCrawlJob::TYPE_LIST_FILTER, StayCrawlJob::TYPE_LISTING])
            ->where('status', StayCrawlJob::STATUS_CRAWLING)
            ->count();
    }

    public function spawnSlots(): int
    {
        return max(0, $this->spawnMax() - $this->runningListCount());
    }

    /**
     * Spawn list-filter đang pending cho đến khi hết slot Chrome.
     *
     * @return array{spawned: int, remaining: int, slots: int}
     */
    public function drainPendingLists(): array
    {
        $slots = $this->spawnSlots();
        $spawned = 0;
        if ($slots <= 0) {
            return [
                'spawned' => 0,
                'remaining' => $this->pendingListCount(),
                'slots' => 0,
            ];
        }

        $candidates = StayCrawlJob::withoutGlobalScopes()
            ->where('job_type', StayCrawlJob::TYPE_LIST_FILTER)
            ->where('status', StayCrawlJob::STATUS_PENDING)
            ->orderBy('id')
            ->limit($slots + 8)
            ->get();

        foreach ($candidates as $job) {
            if ($spawned >= $slots) {
                break;
            }
            if (data_get($job->meta, 'list_process.running')) {
                continue;
            }
            $this->crawl->spawnListProcess($job, [
                'useProxy' => (bool) data_get($job->meta, 'use_proxy', false),
            ]);
            $spawned++;
        }

        return [
            'spawned' => $spawned,
            'remaining' => $this->pendingListCount(),
            'slots' => $this->spawnSlots(),
        ];
    }

    public function pendingListCount(): int
    {
        return StayCrawlJob::withoutGlobalScopes()
            ->where('job_type', StayCrawlJob::TYPE_LIST_FILTER)
            ->where('status', StayCrawlJob::STATUS_PENDING)
            ->count();
    }

    private function spawnMax(): int
    {
        $catalog = (int) config('stay.catalog.list_spawn_max', 1);
        $chrome = (int) config('stay.crawl.max_concurrent_crawlers', 3);

        return max(1, min($catalog > 0 ? $catalog : 1, max(1, $chrome)));
    }
}
