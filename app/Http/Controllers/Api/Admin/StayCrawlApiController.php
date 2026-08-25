<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\StayCrawlAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Jobs\ProcessStayCrawlItemJob;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Services\StayCrawl\StayCrawlBrowser;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\ApiResponse;
use App\Support\StayBookingUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class StayCrawlApiController extends Controller
{
    public function __construct(
        private readonly StayCrawlService $crawl,
        private readonly StayCrawlBrowser $browser,
    ) {}

    public function status(): JsonResponse
    {
        $ready = $this->browser->readiness();

        return ApiResponse::success([
            'driver' => (string) config('stay.crawl.driver', 'browser'),
            'browser_ready' => $ready['ready'],
            'node_bin' => $ready['node'],
            'puppeteer_installed' => $ready['puppeteer'],
            'script_ok' => $ready['script'],
            'ready_hint' => $ready['hint'],
            'proxy_configured' => $this->browser->proxyConfigured(),
            'proxy_enabled_default' => (bool) config('stay.crawl.proxy.enabled', false),
            'headless' => (bool) config('stay.crawl.headless', true),
            'headed' => ! (bool) config('stay.crawl.headless', true),
            'slow_mo' => (int) config('stay.crawl.slow_mo', 0),
            'chrome_bin' => trim((string) config('stay.crawl.chrome_bin', '')) ?: null,
        ]);
    }

    public function jobs(Request $request): JsonResponse
    {
        $q = StayCrawlJob::query()
            ->with(['category:id,name'])
            ->withCount([
                'items',
                'items as done_items_count' => fn ($iq) => $iq->whereIn('status', [StayCrawlItem::STATUS_IMPORTED, StayCrawlItem::STATUS_AI_DONE]),
                'items as failed_items_count' => fn ($iq) => $iq->whereIn('status', [StayCrawlItem::STATUS_FAILED, StayCrawlItem::STATUS_BLOCKED]),
                'items as queued_items_count' => fn ($iq) => $iq->whereIn('status', [StayCrawlItem::STATUS_QUEUED, StayCrawlItem::STATUS_EXTRACTED, StayCrawlItem::STATUS_FETCHED]),
            ])
            ->latest('id');

        if ($catId = (int) $request->input('service_category_id')) {
            $q->where('service_category_id', $catId);
        }

        if ($status = (string) $request->input('status', '')) {
            if ($status !== 'all' && $status !== '') {
                $q->where('status', $status);
            }
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $q->where(function ($sq) use ($search) {
                if (is_numeric($search)) {
                    $sq->where('id', (int) $search);
                } else {
                    $sq->where('list_url', 'like', "%{$search}%")
                       ->orWhere('canonical_url', 'like', "%{$search}%")
                       ->orWhereHas('category', function ($cq) use ($search) {
                           $cq->where('name', 'like', "%{$search}%");
                       });
                }
            });
        }

        $per = min(100, max(1, (int) $request->input('per_page', 20)));
        $paginator = $q->paginate($per);

        return ApiResponse::success([
            'items' => $paginator->getCollection()->map(fn (StayCrawlJob $j) => $this->mapJob($j))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function job(Request $request, int $id): JsonResponse
    {
        $job = StayCrawlJob::query()->withCount('items')->findOrFail($id);

        // Auto-link item if single hotel crawl job has 0 items linked
        if ($job->items_count === 0 && ! empty($job->list_url) && str_contains($job->list_url, '/hotel/')) {
            $canon = StayBookingUrl::canonicalize($job->list_url);
            $matchedItem = StayCrawlItem::query()
                ->where('canonical_url', $canon)
                ->orWhere('source_url', $job->list_url)
                ->first();
            if ($matchedItem) {
                $matchedItem->job_id = $job->id;
                $matchedItem->save();
                $job->loadCount('items');
            }
        }
        
        // Tự động đồng bộ tiến độ từ stream listing nếu có
        $this->crawl->syncListProgressFromStream($job);
        
        $query = $job->items()
            ->select([
                'id', 'project_id', 'job_id', 'source_url', 'canonical_url',
                'status', 'http_status', 'blocked_reason', 'service_id',
                'error', 'crawled_at', 'ai_at', 'imported_at',
            ])
            ->with([
                'service:id,code,cluster,status',
                'service.seoEntry:id,parent_id,reference_type,reference_id',
                'service.seoEntry.translations:id,seo_entry_id,language_id,slug,slug_full',
            ])
            ->latest('id');
        
        if ($status = $request->input('status')) {
            if ($status === 'done') {
                $query->whereIn('status', [StayCrawlItem::STATUS_IMPORTED, StayCrawlItem::STATUS_AI_DONE]);
            } elseif ($status === 'failed') {
                $query->whereIn('status', [StayCrawlItem::STATUS_FAILED, StayCrawlItem::STATUS_BLOCKED]);
            } elseif ($status === 'queued') {
                $query->whereIn('status', [StayCrawlItem::STATUS_QUEUED, StayCrawlItem::STATUS_EXTRACTED, StayCrawlItem::STATUS_FETCHED]);
            } else {
                $query->where('status', $status);
            }
        }

        $limit = min(500, max(1, (int) $request->input('limit', $request->input('per_page', 250))));
        $items = $query->limit($limit)->get();

        // Thống kê nhanh theo từng nhóm trạng thái
        $counts = [
            'total' => $job->items()->count(),
            'done' => $job->items()->whereIn('status', [StayCrawlItem::STATUS_IMPORTED, StayCrawlItem::STATUS_AI_DONE])->count(),
            'failed' => $job->items()->where('status', StayCrawlItem::STATUS_FAILED)->count(),
            'blocked' => $job->items()->where('status', StayCrawlItem::STATUS_BLOCKED)->count(),
            'queued' => $job->items()->whereIn('status', [StayCrawlItem::STATUS_QUEUED, StayCrawlItem::STATUS_EXTRACTED, StayCrawlItem::STATUS_FETCHED])->count(),
        ];

        return ApiResponse::success([
            'job' => $this->mapJob($job),
            'items' => $items->map(fn (StayCrawlItem $i) => $this->mapItem($i))->values(),
            'stats' => $counts,
        ]);
    }

    public function retryItem(Request $request, int $id): JsonResponse
    {
        $item = StayCrawlItem::query()->with(['job', 'service'])->findOrFail($id);
        $rerun = $request->input('rerun'); // 'replace' | 'improve'
        $from = $request->input('from', 'basic'); // 'basic' | 'gallery' | 'rooms' | 'rooms_modals'

        if ($rerun === 'replace' || $rerun === 'improve') {
            $this->crawl->resetItemForRerun($item, $rerun, $from);
        } else {
            $item->status = StayCrawlItem::STATUS_QUEUED;
            $item->error = null;
            $item->blocked_reason = null;
            $item->save();
        }

        // Đẩy thẳng vào hàng đợi Laravel Queue (bảng jobs)
        ProcessStayCrawlItemJob::dispatch(
            (int) $item->id,
            'vi',
            false,
            false,
        );

        if ($item->job) {
            $job = $item->job;
            if ($job->status === StayCrawlJob::STATUS_DONE) {
                $job->status = StayCrawlJob::STATUS_READY;
                $job->save();
            }
            $this->crawl->touchQueueMeta($job, [
                'item_id' => $item->id,
                'phase' => 'queue',
                'message' => "Đã kích hoạt lại khách sạn #{$item->id} vào hàng đợi Laravel queue",
            ]);
        }

        return ApiResponse::success([
            'item' => $this->mapItem($item->fresh()),
            'message' => 'Đã đưa khách sạn vào hàng đợi xử lý',
        ]);
    }

    public function resetItemStatus(Request $request, int $id): JsonResponse
    {
        $item = StayCrawlItem::query()->findOrFail($id);
        $status = $request->input('status', StayCrawlItem::STATUS_QUEUED);
        $item->status = $status;
        $item->error = null;
        $item->blocked_reason = null;
        $item->save();

        return ApiResponse::success([
            'item' => $this->mapItem($item->fresh()),
            'message' => 'Đã đặt lại trạng thái khách sạn',
        ]);
    }

    public function retryFailed(Request $request, int $id): JsonResponse
    {
        $job = StayCrawlJob::query()->findOrFail($id);
        $failedItems = $job->items()
            ->whereIn('status', [
                StayCrawlItem::STATUS_FAILED,
                StayCrawlItem::STATUS_BLOCKED,
                StayCrawlItem::STATUS_QUEUED,
            ])
            ->get();

        $retriedCount = 0;
        foreach ($failedItems as $item) {
            // Xóa sạch dữ liệu / service lỗi trước đó để cào lại từ đầu (replace mode)
            $this->crawl->resetItemForRerun($item, 'replace', 'basic');
            ProcessStayCrawlItemJob::dispatch(
                (int) $item->id,
                'vi',
                false,
                false,
            );
            $retriedCount++;
        }

        if ($job->status === StayCrawlJob::STATUS_DONE && $retriedCount > 0) {
            $job->status = StayCrawlJob::STATUS_READY;
            $job->save();
        }

        $this->crawl->touchQueueMeta($job, [
            'phase' => 'queue',
            'message' => "Đã kích hoạt lại {$retriedCount} URL vào hàng đợi Laravel queue",
        ]);

        return ApiResponse::success([
            'retried_count' => $retriedCount,
            'message' => "Đã kích hoạt lại {$retriedCount} URL lỗi vào hàng đợi",
        ]);
    }

    public function deleteJob(int $id): JsonResponse
    {
        $job = StayCrawlJob::findOrFail($id);
        $job->items()->delete();
        $job->delete();

        return ApiResponse::success(null, 'Đã xóa job crawler #' . $id . ' và các khách sạn liên quan.');
    }

    public function deleteItem(int $id): JsonResponse
    {
        $item = StayCrawlItem::findOrFail($id);
        $item->delete();

        return ApiResponse::success(null, 'Đã xóa mục crawler #' . $id . '.');
    }

    public function bulkDeleteItems(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1|max:500',
            'item_ids.*' => 'integer|exists:stay_crawl_items,id',
        ]);

        $count = StayCrawlItem::whereIn('id', $validated['item_ids'])->delete();

        return ApiResponse::success(['deleted_count' => $count], "Đã xóa {$count} mục khách sạn.");
    }

    public function bulkRetryItems(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1|max:500',
            'item_ids.*' => 'integer|exists:stay_crawl_items,id',
            'rerun' => 'nullable|in:improve,replace',
            'from' => 'nullable|in:basic,gallery,rooms,rooms_modals',
        ]);

        $items = StayCrawlItem::whereIn('id', $validated['item_ids'])->with(['job'])->get();
        $retried = 0;
        $touchedJobs = [];

        foreach ($items as $item) {
            $rerun = $validated['rerun'] ?? null;
            $from = $validated['from'] ?? 'basic';

            if ($rerun === 'replace' || $rerun === 'improve') {
                $this->crawl->resetItemForRerun($item, $rerun, $from);
            } else {
                $item->status = StayCrawlItem::STATUS_QUEUED;
                $item->error = null;
                $item->blocked_reason = null;
                $item->save();
            }

            // ĐẨY TRỰC TIẾP VÀO BẢNG JOBS TRONG CƠ SỞ DỮ LIỆU
            ProcessStayCrawlItemJob::dispatch(
                (int) $item->id,
                'vi',
                false,
                false,
            );

            if ($item->job) {
                $job = $item->job;
                if ($job->status === StayCrawlJob::STATUS_DONE) {
                    $job->status = StayCrawlJob::STATUS_READY;
                    $job->save();
                }
                $touchedJobs[$job->id] = $job;
            }

            $retried++;
        }

        foreach ($touchedJobs as $job) {
            $this->crawl->touchQueueMeta($job, [
                'phase' => 'queue',
                'message' => "Đã đẩy {$retried} khách sạn vào hàng đợi Laravel queue",
            ]);
        }

        return ApiResponse::success(['retried_count' => $retried], "Đã đưa {$retried} khách sạn vào hàng đợi xử lý.");
    }

    public function bulkResetStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1|max:500',
            'item_ids.*' => 'integer|exists:stay_crawl_items,id',
            'status' => 'required|string|max:32',
        ]);

        $count = StayCrawlItem::whereIn('id', $validated['item_ids'])->update([
            'status' => $validated['status'],
        ]);

        return ApiResponse::success(['updated_count' => $count], "Đã cập nhật trạng thái {$count} khách sạn.");
    }



    public function items(Request $request): JsonResponse
    {
        $q = StayCrawlItem::query()
            ->select([
                'id', 'project_id', 'job_id', 'source_url', 'canonical_url',
                'status', 'http_status', 'blocked_reason', 'service_id',
                'error', 'crawled_at', 'ai_at', 'imported_at', 'raw_json', 'ai_json',
            ])
            ->with(['service.seoEntry.translations'])
            ->latest('id');
        if ($job = $request->input('job_id')) {
            $q->where('job_id', (int) $job);
        }
        if ($catId = (int) $request->input('service_category_id')) {
            $q->whereHas('job', fn ($j) => $j->where('service_category_id', $catId));
        }
        if ($status = $request->input('status')) {
            $q->where('status', $status);
        }
        $per = min(50, max(1, (int) $request->input('per_page', 20)));
        $paginator = $q->paginate($per);

        return ApiResponse::success([
            'items' => $paginator->getCollection()->map(fn (StayCrawlItem $i) => $this->mapItem($i))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function showItem(int $id): JsonResponse
    {
        $item = StayCrawlItem::query()->findOrFail($id);

        return ApiResponse::success([
            'item' => $this->mapItem($item, true),
        ]);
    }

    public function enqueueList(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'url' => 'required|url|max:2000',
                'service_category_id' => 'nullable|integer|exists:service_categories,id',
                'html' => 'nullable|string',
                'max_pages' => 'nullable|integer|min:1|max:80',
                'ignore_robots' => 'nullable|boolean',
                'use_proxy' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        if (! empty($validated['service_category_id'])) {
            try {
                $this->crawl->requireStayCategory((int) $validated['service_category_id']);
            } catch (RuntimeException $e) {
                return ApiResponse::error($e->getMessage(), 'INVALID_CATEGORY', 422);
            }
        }

        $useProxy = $this->useProxyFlag($validated);
        $job = $this->crawl->enqueueList($validated['url'], $validated['service_category_id'] ?? null, $useProxy);
        $html = $validated['html'] ?? null;
        $result = $this->crawl->crawlList(
            $job,
            is_string($html) && $html !== '' ? $html : null,
            $this->respectRobotsFlag($validated),
            (int) ($validated['max_pages'] ?? 1),
            $useProxy,
        );

        return ApiResponse::success([
            'job' => $this->mapJob($result['job']),
            'urls' => $result['urls'],
        ], 'Đã lưu URL chỗ nghỉ từ danh mục');
    }

    public function fromCategory(Request $request): JsonResponse
    {
        @set_time_limit(480);
        try {
            $validated = $request->validate([
                'service_category_id' => 'required|integer',
                'url' => 'required|url|max:2000',
                'html' => 'nullable|string',
                'max_pages' => 'nullable|integer|min:1|max:80',
                'ignore_robots' => 'nullable|boolean',
                'use_proxy' => 'nullable|boolean',
                'rerun' => 'nullable|in:improve,replace',
                'from' => 'nullable|in:basic,gallery,rooms,rooms_modals',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $this->crawl->requireStayCategory((int) $validated['service_category_id']);
            $html = $validated['html'] ?? null;
            $rerun = $validated['rerun']
                ?? $request->input('rerun')
                ?? $request->query('rerun')
                ?? $request->header('X-Stay-Crawl-Rerun');
            $from = $validated['from']
                ?? $request->input('from')
                ?? $request->query('from')
                ?? $request->header('X-Stay-Crawl-From');
            $result = $this->crawl->startForCategory(
                (int) $validated['service_category_id'],
                $validated['url'],
                is_string($html) && $html !== '' ? $html : null,
                $this->respectRobotsFlag($validated),
                (int) ($validated['max_pages'] ?? 1),
                $this->useProxyFlag($validated),
                is_string($rerun) ? $rerun : null,
                is_string($from) ? $from : null,
            );
        } catch (StayCrawlAlreadyExistsException $e) {
            return ApiResponse::error($e->getMessage(), 'STAY_CRAWL_EXISTS', 409, $e->details());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 'CRAWL_LIST_FAILED', 422);
        }

        $job = $result['job'];
        $autoWork = ! \App\Support\StayBookingUrl::isHotelPage($validated['url'])
            || $job->items()->count() > 1;
        $queued = ['dispatched' => 0, 'item_ids' => []];
        if ($autoWork && $job->items()->count() > 0) {
            $queued = $this->crawl->dispatchItemQueue(
                $job->fresh() ?? $job,
                'vi',
                $this->useProxyFlag($validated),
                $this->respectRobotsFlag($validated),
            );
            $job = $job->fresh() ?? $job;
        }

        $items = $job->items()
            ->select([
                'id', 'project_id', 'job_id', 'source_url', 'canonical_url',
                'status', 'http_status', 'blocked_reason', 'service_id',
                'error', 'crawled_at', 'ai_at', 'imported_at', 'raw_json', 'ai_json',
            ])
            ->with(['service.seoEntry.translations'])
            ->latest('id')
            ->limit(50)
            ->get();
        $queueName = (string) config('stay.crawl.queue', 'default');

        return ApiResponse::success([
            'job' => $this->mapJob($job->loadCount('items')),
            'urls' => $result['urls'],
            'items' => $items->map(fn (StayCrawlItem $i) => $this->mapItem($i))->values(),
            'worker' => data_get($job->meta, 'worker'),
            'queue' => data_get($job->meta, 'queue'),
            'queued' => $queued,
            'worker_hint' => $autoWork
                ? 'Đã đẩy từng URL vào Laravel queue (ProcessStayCrawlItemJob). Chạy Supervisor/queue:work — sống sót sau reboot. Có thể đóng tab.'
                : 'Gọi process-next để cào từng bước (hotel đơn).',
            'queue_hint' => $autoWork
                ? 'php artisan queue:work --queue='.$queueName.' --sleep=3 --tries=3 --timeout=1200'
                : null,
        ], 'Đã lấy danh sách chỗ nghỉ — đang tạo trang con');
    }

    public function processNext(Request $request, int $id): JsonResponse
    {
        @set_time_limit(60);
        $job = StayCrawlJob::query()->findOrFail($id);
        try {
            $validated = $request->validate([
                'locale' => 'nullable|string|max:12',
                'html' => 'nullable|string',
                'ignore_robots' => 'nullable|boolean',
                'use_proxy' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $locale = $validated['locale'] ?? 'vi';
        $html = $validated['html'] ?? null;
        $html = is_string($html) && $html !== '' ? $html : null;

        // 1. Nếu tiến trình cào danh sách (listing) đang chạy nền: Đồng bộ stream URLs và trả về ngay
        $listSync = $this->crawl->syncListProgressFromStream($job);
        if ($listSync['running']) {
            $msg = $listSync['message'] ?: 'Đang tải danh sách chỗ nghỉ từ Booking.com (Chrome realtime)…';
            $snap = $this->crawl->httpStepSnapshot($job);
            $snap['phase'] = 'listing';
            $snap['message'] = $msg;
            $snap['busy'] = true;
            $snap['stream'] = $listSync['stream'];
            $snap['urls_found'] = $listSync['urls_found'];
            return $this->stepResponse($snap, $locale, $msg);
        }

        if ($this->crawl->isWorkerAlive($job)) {
            $busyMsg = $this->crawl->isWorkerPaused($job)
                ? 'Worker đang tạm dừng (paused) — gọi resume để tiếp tục'
                : (data_get($job->meta, 'worker.mode') === 'laravel_queue'
                    ? 'Laravel queue đang xử lý từng URL — không cần poll process-next (cần queue:work / Supervisor)'
                    : 'Worker nền đang chạy (stay-crawl:work) — không cần poll process-next');

            return $this->stepResponse($this->crawl->httpStepSnapshot($job), $locale, $busyMsg);
        }

        if (! $this->crawl->isStepRunning($job)) {
            $this->crawl->clearStaleStep($job);
            $snap = $this->crawl->httpStepSnapshot($job);
            if (! $snap['done']) {
                $this->crawl->markStepRunning($job);
                $this->crawl->spawnHttpStep($job, [
                    'locale' => $locale,
                    'html' => $html,
                    'respectRobots' => $this->respectRobotsFlag($validated),
                    'useProxy' => $this->useProxyFlag($validated),
                ]);
            }
        }

        return $this->stepResponse($this->crawl->httpStepSnapshot($job), $locale, 'Chrome đang chạy nền');
    }

    /** Khởi động / đảm bảo worker nền cho job dài ngày. */
    public function startWork(Request $request, int $id): JsonResponse
    {
        $job = StayCrawlJob::query()->findOrFail($id);
        try {
            $validated = $request->validate([
                'locale' => 'nullable|string|max:12',
                'ignore_robots' => 'nullable|boolean',
                'use_proxy' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $this->crawl->resumeWorker($job, [
            'locale' => $validated['locale'] ?? 'vi',
            'useProxy' => $this->useProxyFlag($validated),
            'respectRobots' => $this->respectRobotsFlag($validated),
        ]);

        $fresh = $job->fresh() ?? $job;
        $queueName = (string) config('stay.crawl.queue', 'default');

        return ApiResponse::success([
            'job' => $this->mapJob($fresh),
            'worker' => data_get($fresh->meta, 'worker'),
            'queue' => data_get($fresh->meta, 'queue'),
            'worker_hint' => 'php artisan queue:work --queue='.$queueName.' --timeout=1200',
        ], 'Đã đẩy URL còn lại vào Laravel queue');
    }

    public function pauseWork(int $id): JsonResponse
    {
        $job = StayCrawlJob::query()->findOrFail($id);
        $this->crawl->pauseWorker($job, 'api_pause');
        $fresh = $job->fresh() ?? $job;

        return ApiResponse::success([
            'job' => $this->mapJob($fresh),
            'worker' => data_get($fresh->meta, 'worker'),
        ], 'Đã tạm dừng worker (hoàn tất bước đang chạy rồi nghỉ)');
    }

    public function resumeWork(Request $request, int $id): JsonResponse
    {
        return $this->startWork($request, $id);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function stepResponse(array $result, string $locale, string $busyMessage): JsonResponse
    {
        $item = $result['item'] instanceof StayCrawlItem ? $result['item'] : null;
        $service = $item?->service;
        $busy = (bool) ($result['busy'] ?? false);

        return ApiResponse::success([
            'done' => (bool) ($result['done'] ?? false),
            'busy' => $busy,
            'remaining' => $result['remaining'] ?? 0,
            'imported' => $result['imported'] ?? 0,
            'blocked' => $result['blocked'] ?? 0,
            'failed' => $result['failed'] ?? 0,
            'total' => $result['total'] ?? 0,
            'job' => $this->mapJob($result['job']),
            'item' => $item ? $this->mapItem($item) : null,
            'service' => $service ? $this->mapServiceSeo($service, $locale) : null,
            'phase' => $result['phase'] ?? null,
            'message' => $busy ? $busyMessage : ($result['message'] ?? null),
            'last_step' => $result['last_step'] ?? null,
        ], $busy
            ? $busyMessage
            : (($result['done'] ?? false) ? 'Đã xử lý xong danh sách' : ($result['message'] ?? 'Đã xử lý 1 bước crawler')));
    }

    public function enqueueHotel(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'url' => 'required|url|max:2000',
                'job_id' => 'nullable|integer|exists:stay_crawl_jobs,id',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $job = ! empty($validated['job_id']) ? StayCrawlJob::query()->find($validated['job_id']) : null;
        $item = $this->crawl->queueHotelUrl($validated['url'], $job);

        return ApiResponse::success(['item' => $this->mapItem($item)], 'Đã lưu URL crawler');
    }

    public function crawlDetail(Request $request, int $id): JsonResponse
    {
        @set_time_limit(480);
        $item = StayCrawlItem::query()->findOrFail($id);
        try {
            $validated = $request->validate([
                'html' => 'nullable|string',
                'ignore_robots' => 'nullable|boolean',
                'use_proxy' => 'nullable|boolean',
                'keep_html' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $html = $validated['html'] ?? null;
        $item = $this->crawl->crawlDetail(
            $item,
            is_string($html) && $html !== '' ? $html : null,
            $this->respectRobotsFlag($validated),
            (bool) ($validated['keep_html'] ?? true),
            $this->useProxyFlag($validated),
        );

        return ApiResponse::success(['item' => $this->mapItem($item)], 'Đã extract HTML');
    }

    public function mapProcess(int $id): JsonResponse
    {
        $item = StayCrawlItem::query()->findOrFail($id);
        $item = $this->crawl->mapProcess($item);

        return ApiResponse::success(['item' => $this->mapItem($item, true)], 'Đã map HTML');
    }

    public function aiProcess(int $id): JsonResponse
    {
        return $this->mapProcess($id);
    }

    public function import(Request $request, int $id): JsonResponse
    {
        $item = StayCrawlItem::query()->findOrFail($id);
        try {
            $validated = $request->validate([
                'service_category_id' => 'nullable|integer|exists:service_categories,id',
                'locale' => 'nullable|string|max:12',
                'dry_run' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $service = $this->crawl->importItem(
            $item,
            $validated['service_category_id'] ?? null,
            $validated['locale'] ?? 'vi',
            (bool) ($validated['dry_run'] ?? false),
        );

        return ApiResponse::success([
            'item' => $this->mapItem($item->fresh(), true),
            ...$this->mapServiceSeo($service, $validated['locale'] ?? 'vi'),
        ], 'Đã tạo draft chỗ nghỉ');
    }

    public function ingest(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'url' => 'required|url|max:2000',
                'html' => 'nullable|string',
                'service_category_id' => 'nullable|integer|exists:service_categories,id',
                'locale' => 'nullable|string|max:12',
                'instructions' => 'nullable|string|max:4000',
                'ignore_robots' => 'nullable|boolean',
                'use_proxy' => 'nullable|boolean',
                'dry_run' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $item = $this->crawl->queueHotelUrl($validated['url']);
        $html = $validated['html'] ?? null;
        $item = $this->crawl->ingestRemaining(
            $item,
            $validated['service_category_id'] ?? null,
            $validated['locale'] ?? 'vi',
            is_string($html) && $html !== '' ? $html : null,
            $this->respectRobotsFlag($validated),
            (bool) ($validated['dry_run'] ?? false),
            $this->useProxyFlag($validated),
        );
        $steps = 0;
        while ($this->crawl->itemNeedsEnrich($item) && $steps < 40) {
            $step = $this->crawl->enrichNext($item, $this->useProxyFlag($validated));
            $item = $step['item'];
            $steps++;
        }
        if ($item->status === StayCrawlItem::STATUS_BLOCKED) {
            return ApiResponse::error(
                $item->error ?: 'Booking.com chặn fetch. Dán HTML đã lưu vào trường html.',
                'CRAWL_BLOCKED',
                422,
                ['item' => $this->mapItem($item)],
            );
        }
        if ($item->status === StayCrawlItem::STATUS_FAILED || ! $item->service_id) {
            return ApiResponse::error(
                $item->error ?: 'Ingest thất bại.',
                'CRAWL_FAILED',
                422,
                ['item' => $this->mapItem($item)],
            );
        }
        $service = Service::query()->findOrFail((int) $item->service_id);

        return ApiResponse::success([
            'item' => $this->mapItem($item->fresh(), true),
            ...$this->mapServiceSeo($service, $validated['locale'] ?? 'vi'),
        ], 'Ingest xong — draft + gallery/phòng (nếu Chrome lấy được)');
    }

    /** @return array<string, mixed> */
    private function mapJob(StayCrawlJob $job): array
    {
        return [
            'id' => $job->id,
            'list_url' => $job->list_url,
            'canonical_url' => $job->canonical_url,
            'status' => $job->status,
            'pages_crawled' => $job->pages_crawled,
            'items_found' => $job->items_found,
            'items_count' => $job->items_count ?? null,
            'service_category_id' => $job->service_category_id,
            'category' => ($job->relationLoaded('category') && $job->category) ? [
                'id' => $job->category->id,
                'name' => $job->category->name,
            ] : null,
            'stats' => [
                'total' => (int) ($job->items_count ?? 0),
                'done' => (int) ($job->done_items_count ?? 0),
                'failed' => (int) ($job->failed_items_count ?? 0),
                'queued' => (int) ($job->queued_items_count ?? 0),
            ],
            'error' => $job->error,
            'rerun' => data_get($job->meta, 'rerun'),
            'rerun_from' => data_get($job->meta, 'rerun_from'),
            'list' => data_get($job->meta, 'list'),
            'worker' => data_get($job->meta, 'worker'),
            'worker_alive' => $this->crawl->isWorkerAlive($job),
            'worker_paused' => $this->crawl->isWorkerPaused($job),
            'created_at' => $job->created_at?->toIso8601String(),
            'updated_at' => $job->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function mapItem(StayCrawlItem $item, bool $full = false): array
    {
        $payload = [
            'id' => $item->id,
            'job_id' => $item->job_id,
            'source_url' => $item->source_url,
            'canonical_url' => $item->canonical_url,
            'status' => $item->status,
            'http_status' => $item->http_status,
            'blocked_reason' => $item->blocked_reason,
            'service_id' => $item->service_id,
            'error' => $item->error,
            'crawled_at' => $item->crawled_at?->toIso8601String(),
            'ai_at' => $item->ai_at?->toIso8601String(),
            'imported_at' => $item->imported_at?->toIso8601String(),
            'has_extracted' => (bool) ($item->crawled_at || filled($item->extracted_html ?? null)),
            'has_ai' => (bool) ($item->ai_at || (is_array($item->ai_json ?? null) && $item->ai_json !== [])),
            'slug_full' => $this->itemSlugFull($item),
            'enrich' => is_array($item->raw_json['enrich'] ?? null) ? $item->raw_json['enrich'] : null,
        ];
        if ($full) {
            $payload['raw_json'] = $item->raw_json;
            $payload['ai_json'] = $item->ai_json;
            $payload['extracted_html'] = $item->extracted_html;
        }

        return $payload;
    }

    /**
     * @return array{service_id: int, code: string, service_category_id: int|null, slug_full: string|null, level: int|null, parent_id: int|null}
     */
    private function mapServiceSeo(Service $service, string $locale): array
    {
        $service->loadMissing('seoEntry.translations');
        $entry = $service->seoEntry;
        $trans = $entry?->translationExact($locale);

        return [
            'service_id' => $service->id,
            'code' => $service->code,
            'service_category_id' => $service->service_category_id,
            'slug_full' => $trans?->slug_full,
            'level' => $entry?->level,
            'parent_id' => $entry?->parent_id,
        ];
    }

    private function itemSlugFull(StayCrawlItem $item): ?string
    {
        if (! $item->relationLoaded('service') || ! $item->service) {
            return null;
        }
        $item->service->loadMissing('seoEntry.translations');
        $entry = $item->service->seoEntry;
        $trans = $entry?->translationExact() ?? $entry?->translations?->first();

        return $trans?->slug_full;
    }

    /** @param  array<string, mixed>  $validated */
    private function useProxyFlag(array $validated): bool
    {
        if (array_key_exists('use_proxy', $validated) && $validated['use_proxy'] !== null) {
            return (bool) $validated['use_proxy'];
        }

        return (bool) config('stay.crawl.proxy.enabled', false);
    }

    /** @param  array<string, mixed>  $validated */
    private function respectRobotsFlag(array $validated): bool
    {
        return ! (bool) ($validated['ignore_robots'] ?? true);
    }
}
