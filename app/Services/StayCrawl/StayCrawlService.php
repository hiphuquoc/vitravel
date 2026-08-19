<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Exceptions\StayCrawlAlreadyExistsException;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Models\StayCrawlSource;
use App\Support\ProjectContext;
use App\Support\StayBookingUrl;
use RuntimeException;

final class StayCrawlService
{
    public function __construct(
        private readonly StayCrawlFetcher $fetcher,
        private readonly StayHtmlExtractor $extractor,
        private readonly StayHtmlMapper $mapper,
        private readonly StayCrawlAiService $ai,
        private readonly StayCrawlImporter $importer,
    ) {}

    public function ensureSource(string $url): StayCrawlSource
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST)) ?: 'www.booking.com';

        return StayCrawlSource::query()->firstOrCreate(
            [
                'project_id' => ProjectContext::id(),
                'host' => $host,
            ],
            [
                'user_agent' => (string) config('stay.crawl.user_agent'),
                'delay_ms' => (int) config('stay.crawl.delay_ms', 2500),
                'respect_robots' => true,
            ],
        );
    }

    public function enqueueList(string $listUrl, ?int $categoryId = null, bool $useProxy = false): StayCrawlJob
    {
        $canonical = StayBookingUrl::canonicalize($listUrl);
        $source = $this->ensureSource($canonical);

        return StayCrawlJob::query()->create([
            'source_id' => $source->id,
            'list_url' => $listUrl,
            'canonical_url' => $canonical,
            'service_category_id' => $categoryId,
            'status' => StayCrawlJob::STATUS_PENDING,
            'meta' => ['source' => 'booking.com', 'use_proxy' => $useProxy],
        ]);
    }

    /**
     * @return array{job: StayCrawlJob, urls: list<string>}
     */
    public function crawlList(StayCrawlJob $job, ?string $html = null, bool $respectRobots = false, int $maxPages = 1, bool $useProxy = false): array
    {
        $job->status = StayCrawlJob::STATUS_CRAWLING;
        $job->save();

        $urls = [];
        $pages = max(1, min($maxPages, 5));
        $current = $job->list_url;

        try {
            for ($page = 1; $page <= $pages; $page++) {
                if ($html !== null && $page === 1) {
                    $body = $html;
                    $finalUrl = $current;
                } else {
                    $fetch = $this->fetcher->fetch($current, $respectRobots, $useProxy);
                    if (! $fetch['ok'] && $fetch['html'] === '') {
                        throw new RuntimeException($fetch['reason'] ?? 'Fetch list thất bại');
                    }
                    $body = $fetch['html'];
                    $finalUrl = $fetch['final_url'] ?: $current;
                }
                $extracted = $this->extractor->extract($body, $finalUrl);
                foreach ($extracted['hotel_urls'] as $url) {
                    $urls[] = $url;
                    $this->queueHotelUrl($url, $job);
                }
                $job->pages_crawled = $page;
                $job->save();
                $html = null;
                if ($page < $pages) {
                    break;
                }
            }
            $urls = array_values(array_unique($urls));
            $job->items_found = $job->items()->count();
            $job->status = StayCrawlJob::STATUS_READY;
            $job->error = null;
            $job->save();
        } catch (\Throwable $e) {
            $job->status = StayCrawlJob::STATUS_FAILED;
            $job->error = $e->getMessage();
            $job->save();
            throw $e;
        }

        return ['job' => $job->fresh(), 'urls' => $urls];
    }

    public function queueHotelUrl(string $url, ?StayCrawlJob $job = null, ?string $listUrl = null): StayCrawlItem
    {
        $canonical = StayBookingUrl::canonicalize($url);
        $item = StayCrawlItem::query()->firstOrNew([
            'project_id' => ProjectContext::id(),
            'canonical_url' => $canonical,
        ]);
        if (! $item->exists) {
            $item->source_url = $url;
            $item->status = StayCrawlItem::STATUS_QUEUED;
        }
        if ($job) {
            $item->job_id = $job->id;
            $item->list_url = $job->list_url;
            if (in_array($item->status, [
                StayCrawlItem::STATUS_FAILED,
                StayCrawlItem::STATUS_BLOCKED,
            ], true)) {
                $item->status = StayCrawlItem::STATUS_QUEUED;
                $item->error = null;
                $item->blocked_reason = null;
            }
        } elseif ($listUrl) {
            $item->list_url = $listUrl;
        }
        if ($item->source_url === null || $item->source_url === '') {
            $item->source_url = $url;
        }
        $item->save();

        return $item;
    }

    /**
     * Fetch + extract. $html overrides HTTP (file dump).
     */
    public function crawlDetail(
        StayCrawlItem $item,
        ?string $html = null,
        bool $respectRobots = false,
        bool $keepHtml = true,
        bool $useProxy = false,
    ): StayCrawlItem {
        try {
            $fetchDriver = 'dump';
            $pack = [];
            if ($html === null) {
                $fetch = $this->fetcher->fetch($item->source_url, $respectRobots, $useProxy);
                $fetchDriver = $fetch['driver'] ?? 'browser';
                $pack = is_array($fetch['pack'] ?? null) ? $fetch['pack'] : [];
                $item->http_status = $fetch['status'] ?: null;
                if ($fetch['final_url']) {
                    $item->canonical_url = StayBookingUrl::canonicalize($fetch['final_url']);
                }
                $html = $fetch['html'];
                if ($fetch['reason'] === 'robots_disallow') {
                    $item->status = StayCrawlItem::STATUS_BLOCKED;
                    $item->blocked_reason = 'robots_disallow';
                    $item->error = 'robots.txt không cho phép fetch. Dùng --file= HTML đã lưu hoặc --ignore-robots.';
                    $item->crawled_at = now();
                    $item->save();

                    return $item;
                }
            }

            $extracted = $this->extractor->extract((string) $html, $item->source_url);
            $item->canonical_url = $extracted['canonical_url'] ?: $item->canonical_url;
            $item->extractor_version = $extracted['extractor_version'];
            $item->extracted_html = $extracted['extracted_html'];
            $mapped = [];
            if (! $extracted['blocked']) {
                $mapped = $this->mapper->map((string) $html, $item->source_url, $extracted, $pack);
            }
            $item->raw_json = [
                'title' => $extracted['title'],
                'json_ld' => $extracted['json_ld'],
                'open_graph' => $extracted['open_graph'],
                'images' => $extracted['images'],
                'hotel_urls' => $extracted['hotel_urls'],
                'section_keys' => array_keys($extracted['sections']),
                'fetch_driver' => $fetchDriver,
                'mapper_version' => StayHtmlMapper::VERSION,
                'mapped_keys' => array_keys($mapped),
                'stay_pack' => [
                    'chrome_photos' => count(is_array($pack['photos'] ?? null) ? $pack['photos'] : []),
                    'chrome_rooms' => count(is_array($pack['rooms'] ?? null) ? $pack['rooms'] : []),
                    'mapped_photos' => count(is_array($mapped['attrs']['photos'] ?? null) ? $mapped['attrs']['photos'] : []),
                    'mapped_rooms' => count(is_array($mapped['options'] ?? null) ? $mapped['options'] : []),
                    'error' => $pack['error'] ?? null,
                    'debug' => $pack['debug'] ?? null,
                ],
            ];
            if ($mapped !== []) {
                $item->ai_json = $mapped;
                $item->ai_at = now();
            }
            if ($keepHtml) {
                $item->raw_html = $html;
            }
            $item->crawled_at = now();

            if ($extracted['blocked']) {
                $item->status = StayCrawlItem::STATUS_BLOCKED;
                $item->blocked_reason = $extracted['blocked_reason'];
                $hint = $extracted['blocked_reason'] === 'empty_or_shell'
                    ? 'Chrome chưa lấy được nội dung. Bật proxy hoặc dán HTML đã lưu.'
                    : 'Trang bị chặn / shell: '.$extracted['blocked_reason'];
                $item->error = $hint;
            } else {
                $hasTitle = trim((string) ($mapped['title'] ?? '')) !== '';
                $item->status = $hasTitle ? StayCrawlItem::STATUS_AI_DONE : StayCrawlItem::STATUS_EXTRACTED;
                $item->blocked_reason = null;
                $item->error = $hasTitle ? null : 'Đã lọc HTML nhưng chưa tách được tên chỗ nghỉ.';
            }
            $item->save();
        } catch (\Throwable $e) {
            $item->status = StayCrawlItem::STATUS_FAILED;
            $item->error = $e->getMessage();
            $item->save();
            throw $e;
        }

        return $item;
    }

    /**
     * Map HTML → schema stay (thủ công). Giữ status ai_done để tương thích importer / UI cũ.
     */
    public function mapProcess(StayCrawlItem $item): StayCrawlItem
    {
        $html = (string) ($item->raw_html ?: $item->extracted_html);
        if (trim($html) === '') {
            throw new RuntimeException("Item #{$item->id} chưa có HTML. Chạy crawl detail trước.");
        }
        $fields = $this->mapper->map(
            $html,
            $item->source_url,
            is_array($item->raw_json) ? $item->raw_json : [],
        );
        if (trim((string) ($fields['title'] ?? '')) === '') {
            throw new RuntimeException("Không tách được tên chỗ nghỉ từ HTML (item #{$item->id}).");
        }
        $item->ai_json = $fields;
        $item->ai_at = now();
        $item->status = StayCrawlItem::STATUS_AI_DONE;
        $item->error = null;
        $item->save();

        return $item;
    }

    public function aiProcess(StayCrawlItem $item, string $locale = 'vi', ?string $instructions = null): StayCrawlItem
    {
        if (! $item->extracted_html && empty($item->raw_json)) {
            throw new RuntimeException("Item #{$item->id} chưa extract HTML. Chạy crawl detail trước.");
        }

        $fields = $this->ai->extract(
            sourceUrl: $item->source_url,
            extractedHtml: (string) $item->extracted_html,
            rawJson: is_array($item->raw_json) ? $item->raw_json : [],
            locale: $locale,
            instructions: $instructions,
        );
        $item->ai_json = $fields;
        $item->ai_at = now();
        $item->status = StayCrawlItem::STATUS_AI_DONE;
        $item->error = null;
        $item->save();

        return $item;
    }

    public function importItem(
        StayCrawlItem $item,
        ?int $categoryId = null,
        string $locale = 'vi',
        bool $dryRun = false,
        string $strategy = 'replace',
    ): Service {
        $fields = is_array($item->ai_json) ? $item->ai_json : [];
        if ($fields === []) {
            throw new RuntimeException("Item #{$item->id} chưa map HTML. Chạy crawl detail trước.");
        }
        $categoryId = $categoryId ?: $item->job?->service_category_id;
        $strategy = $strategy === 'improve' ? 'improve' : 'replace';

        return $this->importer->import($item, $fields, $categoryId, $locale, $dryRun, $strategy);
    }

    public function requireStayCategory(int $categoryId): ServiceCategory
    {
        $category = ServiceCategory::query()->find($categoryId);
        if (! $category || $category->cluster !== Service::CLUSTER_STAY) {
            throw new RuntimeException('Chỉ khởi chạy crawler từ danh mục lưu trú (cluster stay).');
        }

        return $category;
    }

    /**
     * Gắn job vào danh mục: URL listing → hàng khách sạn; URL hotel → 1 item.
     *
     * @return array{job: StayCrawlJob, urls: list<string>}
     */
    public function startForCategory(
        int $categoryId,
        string $url,
        ?string $html = null,
        bool $respectRobots = false,
        int $maxPages = 1,
        bool $useProxy = false,
        ?string $rerun = null,
    ): array {
        $this->requireStayCategory($categoryId);
        $rerun = in_array($rerun, ['improve', 'replace'], true) ? $rerun : null;

        if (StayBookingUrl::isHotelPage($url) && $rerun === null) {
            $this->throwIfAlreadyCrawled($url);
        }

        $job = $this->enqueueList($url, $categoryId, $useProxy);
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['rerun'] = $rerun;
        $job->meta = $meta;
        $job->save();

        if (StayBookingUrl::isHotelPage($url)) {
            $item = $this->queueHotelUrl($url, $job);
            if ($rerun) {
                $this->resetItemForRerun($item, $rerun);
            }
            $job->items_found = 1;
            $job->pages_crawled = 0;
            $job->status = StayCrawlJob::STATUS_READY;
            $job->error = null;
            $job->save();

            $item = $this->ingestRemaining(
                $item->fresh() ?? $item,
                $categoryId,
                'vi',
                $html,
                $respectRobots,
                false,
                $useProxy,
            );
            $job = $job->fresh() ?? $job;
            if (in_array($item->status, [StayCrawlItem::STATUS_IMPORTED, StayCrawlItem::STATUS_BLOCKED, StayCrawlItem::STATUS_FAILED], true)
                && $this->processableQuery($job)->count() === 0) {
                $job->status = StayCrawlJob::STATUS_DONE;
                $job->save();
            }

            return ['job' => $job->fresh() ?? $job, 'urls' => [StayBookingUrl::canonicalize($url)]];
        }

        $result = $this->crawlList($job, $html, $respectRobots, $maxPages, $useProxy);
        if ($result['urls'] === [] && $job->items()->count() === 0) {
            $job->status = StayCrawlJob::STATUS_FAILED;
            $job->error = 'Không tìm thấy URL khách sạn. Thử bật proxy (Chrome) hoặc dán HTML đã lưu.';
            $job->save();
            throw new RuntimeException($job->error);
        }
        $this->applyRerunToJob($result['job']->fresh() ?? $result['job'], $rerun);

        return $result;
    }

    /**
     * Cào tiếp 1 chỗ nghỉ của job (fetch → map HTML → draft con của danh mục).
     *
     * @return array{
     *     done: bool,
     *     item: StayCrawlItem|null,
     *     remaining: int,
     *     imported: int,
     *     blocked: int,
     *     failed: int,
     *     total: int,
     *     job: StayCrawlJob
     * }
     */
    public function processNext(
        StayCrawlJob $job,
        string $locale = 'vi',
        ?string $html = null,
        bool $respectRobots = false,
        bool $useProxy = false,
    ): array {
        if ($job->service_category_id) {
            $this->requireStayCategory((int) $job->service_category_id);
        }

        $useProxy = $useProxy || (bool) data_get($job->meta, 'use_proxy', false);

        $item = $this->nextProcessableItem($job, $html !== null && $html !== '');
        if (! $item) {
            $job->status = StayCrawlJob::STATUS_DONE;
            $job->save();

            return $this->jobProgress($job, null, true);
        }

        $item = $this->ingestRemaining(
            $item,
            $job->service_category_id,
            $locale,
            $html,
            $respectRobots,
            false,
            $useProxy,
        );

        $remaining = $this->processableQuery($job)->count();
        if ($remaining === 0) {
            $job->status = StayCrawlJob::STATUS_DONE;
            $job->save();
        }

        return $this->jobProgress($job->fresh() ?? $job, $item, $remaining === 0);
    }

    public function ingestRemaining(
        StayCrawlItem $item,
        ?int $categoryId = null,
        string $locale = 'vi',
        ?string $html = null,
        bool $respectRobots = false,
        bool $dryRun = false,
        bool $useProxy = false,
    ): StayCrawlItem {
        $categoryId = $categoryId ?: $item->job?->service_category_id;
        $useProxy = $useProxy || (bool) data_get($item->job?->meta, 'use_proxy', false);

        if ($item->status === StayCrawlItem::STATUS_IMPORTED) {
            return $item;
        }

        try {
            $needsFetch = in_array($item->status, [
                StayCrawlItem::STATUS_QUEUED,
                StayCrawlItem::STATUS_FAILED,
                StayCrawlItem::STATUS_BLOCKED,
                StayCrawlItem::STATUS_FETCHED,
            ], true) || ($html !== null && $html !== '');

            if ($needsFetch && $item->status !== StayCrawlItem::STATUS_AI_DONE) {
                $item = $this->crawlDetail($item, $html, $respectRobots, true, $useProxy);
            }

            if ($item->status === StayCrawlItem::STATUS_BLOCKED) {
                return $item;
            }

            if ($item->status === StayCrawlItem::STATUS_EXTRACTED) {
                $item = $this->mapProcess($item);
            }

            if ($item->status === StayCrawlItem::STATUS_AI_DONE) {
                $strategy = (string) data_get($item->job?->meta, 'rerun', 'replace');
                $this->importItem($item, $categoryId, $locale, $dryRun, $strategy === 'improve' ? 'improve' : 'replace');
                $item = $item->fresh(['job', 'service.seoEntry.translations']) ?? $item;
            }
        } catch (\Throwable $e) {
            $item->status = StayCrawlItem::STATUS_FAILED;
            $item->error = $e->getMessage();
            $item->save();
        }

        return $item;
    }

    private function nextProcessableItem(StayCrawlJob $job, bool $retryBlocked): ?StayCrawlItem
    {
        $item = $this->processableQuery($job)->orderBy('id')->first();
        if ($item) {
            return $item;
        }
        if (! $retryBlocked) {
            return null;
        }

        return $job->items()
            ->where('status', StayCrawlItem::STATUS_BLOCKED)
            ->orderBy('id')
            ->first();
    }

    private function processableQuery(StayCrawlJob $job)
    {
        return $job->items()->whereIn('status', [
            StayCrawlItem::STATUS_QUEUED,
            StayCrawlItem::STATUS_EXTRACTED,
            StayCrawlItem::STATUS_AI_DONE,
            StayCrawlItem::STATUS_FETCHED,
            StayCrawlItem::STATUS_FAILED,
        ]);
    }

    /**
     * @return array{
     *     done: bool,
     *     item: StayCrawlItem|null,
     *     remaining: int,
     *     imported: int,
     *     blocked: int,
     *     failed: int,
     *     total: int,
     *     job: StayCrawlJob
     * }
     */
    private function jobProgress(StayCrawlJob $job, ?StayCrawlItem $item, bool $done): array
    {
        $job->loadCount([
            'items',
            'items as imported_count' => fn ($q) => $q->where('status', StayCrawlItem::STATUS_IMPORTED),
            'items as blocked_count' => fn ($q) => $q->where('status', StayCrawlItem::STATUS_BLOCKED),
            'items as failed_count' => fn ($q) => $q->where('status', StayCrawlItem::STATUS_FAILED),
        ]);

        return [
            'done' => $done,
            'item' => $item,
            'remaining' => $this->processableQuery($job)->count(),
            'imported' => (int) $job->imported_count,
            'blocked' => (int) $job->blocked_count,
            'failed' => (int) $job->failed_count,
            'total' => (int) ($job->items_count ?? $job->items()->count()),
            'job' => $job,
        ];
    }

    private function throwIfAlreadyCrawled(string $url): void
    {
        $item = StayCrawlItem::query()
            ->with('service.seoEntry.translations')
            ->where('canonical_url', StayBookingUrl::canonicalize($url))
            ->first();
        if (! $item || ! $this->itemIsAlreadyCrawled($item)) {
            return;
        }

        throw new StayCrawlAlreadyExistsException(
            'URL này đã cào trước đó. Chọn Cải thiện (bổ sung box thiếu) hoặc Xóa sạch rồi cào lại.',
            $this->existingPayload(collect([$item])),
        );
    }

    private function applyRerunToJob(StayCrawlJob $job, ?string $rerun): void
    {
        $done = $job->items()->get()->filter(fn (StayCrawlItem $i) => $this->itemIsAlreadyCrawled($i));
        if ($done->isEmpty()) {
            return;
        }
        if ($rerun === null) {
            throw new StayCrawlAlreadyExistsException(
                'Một số chỗ nghỉ trong danh sách đã cào. Chọn Cải thiện, Xóa sạch rồi cào lại, hoặc hủy.',
                $this->existingPayload($done),
            );
        }
        foreach ($done as $item) {
            $this->resetItemForRerun($item, $rerun);
        }
    }

    private function itemIsAlreadyCrawled(StayCrawlItem $item): bool
    {
        return in_array($item->status, [
            StayCrawlItem::STATUS_IMPORTED,
            StayCrawlItem::STATUS_AI_DONE,
            StayCrawlItem::STATUS_EXTRACTED,
        ], true);
    }

    public function resetItemForRerun(StayCrawlItem $item, string $rerun): StayCrawlItem
    {
        if ($rerun === 'replace' && $item->service_id) {
            $service = Service::query()->withTrashed()->find($item->service_id);
            if ($service) {
                $this->importer->purgeCrawledService($service);
            }
            $item->service_id = null;
        }
        $item->status = StayCrawlItem::STATUS_QUEUED;
        $item->raw_html = null;
        $item->extracted_html = null;
        $item->raw_json = null;
        $item->ai_json = null;
        $item->error = null;
        $item->blocked_reason = null;
        $item->http_status = null;
        $item->imported_at = null;
        $item->ai_at = null;
        $item->crawled_at = null;
        $item->save();

        return $item;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, StayCrawlItem>  $items
     * @return array<string, mixed>
     */
    private function existingPayload($items): array
    {
        return [
            'count' => $items->count(),
            'items' => $items->map(function (StayCrawlItem $item) {
                $service = $item->service;
                $slug = null;
                if ($service) {
                    $service->loadMissing('seoEntry.translations');
                    $trans = $service->seoEntry?->translationExact()
                        ?? $service->seoEntry?->translations?->first();
                    $slug = $trans?->slug_full;
                }

                return [
                    'id' => $item->id,
                    'source_url' => $item->source_url,
                    'status' => $item->status,
                    'service_id' => $item->service_id,
                    'slug_full' => $slug,
                ];
            })->values()->all(),
        ];
    }
}
