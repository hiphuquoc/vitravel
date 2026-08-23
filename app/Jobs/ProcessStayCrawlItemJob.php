<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Project;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ProjectContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Xử lý 1 URL chỗ nghỉ qua Supervisor (fetch → map → draft → gallery → phòng).
 * Thiết kế an toàn 100% cho đa luồng Supervisor (2, 4, 8 cores):
 * - Không implement ShouldBeUnique tĩnh ở cấp class (tránh xung đột lock khi dispatch lại).
 * - Sử dụng WithoutOverlapping theo 'stay-item-proc-'.$this->itemId với release(10) và expireAfter(1800).
 * - Ghi nhận active worker độc lập cho từng item vào Job Meta để không bị ghi đè.
 */
final class ProcessStayCrawlItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public int $itemId,
        public string $locale = 'vi',
        public bool $useProxy = false,
        public bool $respectRobots = false,
    ) {
        $this->onQueue((string) config('stay.crawl.queue', 'default'));
    }

    /** @return list<object> */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('stay-item-proc-'.$this->itemId))
                ->releaseAfter(10)
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

        // Cập nhật trạng thái item sang running / crawling
        $item->status = StayCrawlItem::STATUS_FETCHED;
        $item->error = null;
        $item->save();

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
        if ($item) {
            $item->status = StayCrawlItem::STATUS_FAILED;
            $item->error = $e?->getMessage() ?? 'Job thất bại sau số lần thử lại';
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
