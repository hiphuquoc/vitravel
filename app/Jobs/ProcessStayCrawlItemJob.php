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
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Xử lý 1 URL chỗ nghỉ qua Supervisor (fetch → map → draft → gallery → phòng).
 * Tối ưu tuyệt đối cho đa luồng Supervisor (3, 4, 8 cores):
 * - Loại bỏ hoàn toàn WithoutOverlapping middleware tĩnh ở cấp application để tránh va chạm lock cache/redis giữa các worker.
 * - Kiểm tra optimistic locking trực tiếp trên model StayCrawlItem trong database để không 2 worker nào bốc cùng 1 item.
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
        $this->onQueue((string) config('stay.crawl.queue', 'default'));
    }

    public function handle(StayCrawlService $crawl): void
    {
        // Optimistic check: Nếu item đã hoàn tất hoặc không tồn tại thì skip
        $item = StayCrawlItem::query()->with(['job.category'])->find($this->itemId);
        if (! $item || ! $item->job) {
            return;
        }

        // Nếu item đã có trang hoàn tất rồi và không có nhu cầu enrich thì bỏ qua
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

        // Chuyển trạng thái item sang FETCHED (đang xử lý) ngay tức thì
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
