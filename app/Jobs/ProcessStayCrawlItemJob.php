<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Project;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlLimiter;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ProjectContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Xu ly 1 URL cho nghi qua Supervisor (fetch -> map -> draft -> gallery -> phong).
 * Toi uu tuyet doi cho da luong Supervisor:
 * - Su dung StayCrawlLimiter khong che cung so luong thuc thi (co Redis dung Redis Funnel, khong co Redis tu dong fallback sang Cache Slot Lock).
 * - Kiem tra optimistic locking truc tiep tren model StayCrawlItem trong database.
 */
final class ProcessStayCrawlItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 2;

    public int $backoff = 10;

    public function __construct(
        public int $itemId,
        public string $locale = 'vi',
        public bool $useProxy = false,
        public bool $respectRobots = false,
    ) {
        $this->onQueue((string) config('stay.crawl.queue', 'crawler'));
    }

    public function handle(StayCrawlService $crawl): void
    {
        // Optimistic check: Neu item da hoan tat hoac khong ton tai thi skip
        $item = StayCrawlItem::query()->with(['job.category'])->find($this->itemId);
        if (! $item || ! $item->job) {
            return;
        }

        // Neu item da co trang hoan tat roi va khong co nhu cau enrich thi bo qua
        if ($item->status === StayCrawlItem::STATUS_IMPORTED && ! $crawl->itemNeedsEnrich($item)) {
            return;
        }

        $job = $item->job;
        if (! $this->bindProject($job)) {
            Log::warning('ProcessStayCrawlItemJob: missing project', ['item_id' => $this->itemId]);

            return;
        }

        $useProxy = $this->useProxy || (bool) data_get($job->meta, 'use_proxy', false);
        @set_time_limit(0);

        // Khong che so luong xu ly chrome dong thoi an toan
        StayCrawlLimiter::run(
            function () use ($item, $job, $crawl, $useProxy) {
                // Chuyen trang thai item sang FETCHED (dang xu ly) ngay tuc thi
                $item->status = StayCrawlItem::STATUS_FETCHED;
                $item->error = null;
                $item->save();

                $crawl->touchQueueMeta($job, [
                    'item_id' => $item->id,
                    'phase' => 'queue',
                    'message' => 'Worker dang xu ly khach san #' . $item->id,
                ]);

                try {
                    $crawl->processItemFully(
                        $item,
                        $this->locale,
                        $useProxy,
                        $this->respectRobots,
                    );
                } catch (Throwable $e) {
                    Log::error('ProcessStayCrawlItemJob failed', [
                        'item_id' => $item->id,
                        'error' => $e->getMessage(),
                    ]);
                    $item->refresh();
                    if ($item->status !== StayCrawlItem::STATUS_BLOCKED) {
                        $item->status = StayCrawlItem::STATUS_FAILED;
                        $item->error = $e->getMessage();
                        $item->save();
                    }
                    throw $e;
                } finally {
                    $crawl->removeItemActive($job, $this->itemId);
                    $crawl->refreshJobCompletion($job->fresh() ?? $job);
                }
            },
            max(1, (int) config('stay.crawl.max_concurrent_crawlers', 3)),
            60,
            600,
            function () {
                // Neu he thong dang qua tai khong lay duoc slot, release lai vao queue sau 15 seconds
                return $this->release(15);
            }
        );
    }

    public function failed(?Throwable $e): void
    {
        Log::error('ProcessStayCrawlItemJob permanently failed', [
            'item_id' => $this->itemId,
            'error' => $e?->getMessage(),
        ]);
        $item = StayCrawlItem::query()->with('job')->find($this->itemId);
        if ($item) {
            $item->status = StayCrawlItem::STATUS_FAILED;
            $item->error = $e?->getMessage() ?? 'Job that bai sau so lan thu lai';
            $item->save();
            if ($item->job) {
                app(StayCrawlService::class)->refreshJobCompletion($item->job);
            }
        }
    }

    private function bindProject(StayCrawlJob $job): bool
    {
        $projectId = (int) ($job->project_id ?: $job->category?->project_id);
        if ($projectId <= 0) {
            return false;
        }
        $project = Project::query()->find($projectId);
        if (! $project) {
            return false;
        }
        ProjectContext::set($project);

        return true;
    }
}
