<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Project;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ProjectContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Xử lý 1 URL chỗ nghỉ (cùng pipeline crawler: fetch → map → draft → gallery → phòng).
 * Cho phép chạy song song đa luồng (2, 3, 4 worker) — mỗi worker bốc 1 item khác nhau chạy đồng thời.
 * WithoutOverlapping được đánh theo itemId để tránh 2 worker cùng bốc 1 khách sạn, nhưng các khách sạn khác nhau chạy song song 100%.
 */
final class ProcessStayCrawlItemJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 3;

    public int $backoff = 30;

    /** Tránh dispatch trùng cùng item khi resume. */
    public int $uniqueFor = 1800;

    public function __construct(
        public int $itemId,
        public string $locale = 'vi',
        public bool $useProxy = false,
        public bool $respectRobots = false,
    ) {
        $this->onQueue((string) config('stay.crawl.queue', 'default'));
    }

    public function uniqueId(): string
    {
        return 'stay-crawl-item-'.$this->itemId;
    }

    /** @return list<object> */
    public function middleware(): array
    {
        // Khóa riêng cho từng item để các worker bốc các item khác nhau chạy song song không phải chờ nhau
        return [
            (new WithoutOverlapping('stay-crawl-item-lock-'.$this->itemId))
                ->releaseAfter(30)
                ->expireAfter(1800),
        ];
    }

    public function handle(StayCrawlService $crawl): void
    {
        $item = StayCrawlItem::query()->with(['job.category'])->find($this->itemId);
        if (! $item || ! $item->job) {
            return;
        }

        $job = $item->job;
        if (! $this->bindProject($job)) {
            Log::warning('ProcessStayCrawlItemJob: missing project', ['item_id' => $this->itemId]);

            return;
        }

        $useProxy = $this->useProxy || (bool) data_get($job->meta, 'use_proxy', false);
        @set_time_limit(0);

        $crawl->touchQueueMeta($job, [
            'item_id' => $item->id,
            'phase' => 'queue',
            'message' => 'Worker đang xử lý khách sạn #'.$item->id,
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
                'item_id' => $this->itemId,
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
            $crawl->refreshJobCompletion($job->fresh() ?? $job);
        }
    }

    public function failed(?Throwable $e): void
    {
        Log::error('ProcessStayCrawlItemJob permanently failed', [
            'item_id' => $this->itemId,
            'error' => $e?->getMessage(),
        ]);
        $item = StayCrawlItem::query()->with('job')->find($this->itemId);
        if ($item?->job) {
            app(StayCrawlService::class)->refreshJobCompletion($item->job);
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
