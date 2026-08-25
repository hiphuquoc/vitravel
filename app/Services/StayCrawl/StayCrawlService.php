<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Exceptions\StayCrawlAlreadyExistsException;
use App\Jobs\ProcessStayCrawlItemJob;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Models\StayCrawlSource;
use App\Support\ProjectContext;
use App\Support\StayBookingUrl;
use App\Support\StayCrawlDates;
use RuntimeException;

final class StayCrawlService
{
    public function __construct(
        private readonly StayCrawlFetcher $fetcher,
        private readonly StayHtmlExtractor $extractor,
        private readonly StayHtmlMapper $mapper,
        private readonly StayCrawlAiService $ai,
        private readonly StayCrawlImporter $importer,
        private readonly StayCrawlEnricher $enricher,
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
    /**
     * Lấy đủ URL từ listing (Chrome scroll + «Tải thêm kết quả»).
     * Không dùng max_pages từ UI — mặc định 1 phiên Chrome đầy đủ.
     * Offset trang chỉ khi config stay.crawl.list_offset_extra_pages > 0 (legacy).
     *
     * @return array{job: StayCrawlJob, urls: list<string>}
     */
    public function crawlList(StayCrawlJob $job, ?string $html = null, bool $respectRobots = false, int $maxPages = 1, bool $useProxy = false): array
    {
        $job->status = StayCrawlJob::STATUS_CRAWLING;
        $job->save();

        $pageSize = max(10, min(50, (int) config('stay.crawl.list_page_size', 25)));
        // UI không còn «số trang» — luôn 1 lần Chrome load-more đủ; offset phụ chỉ qua config.
        $extraOffsetPages = max(0, (int) config('stay.crawl.list_offset_extra_pages', 0));
        $pages = 1 + $extraOffsetPages;
        $urls = [];
        $seen = [];
        $current = StayCrawlDates::applyToUrl($job->list_url);
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['list'] = [
            'max_pages' => $pages,
            'page_size' => $pageSize,
            'pages_done' => 0,
            'offset' => StayBookingUrl::searchOffset($current),
            'stopped_reason' => null,
            'load_more' => null,
            'mode' => 'full_load_more',
        ];
        $job->meta = $meta;
        $job->save();

        try {
            for ($page = 1; $page <= $pages; $page++) {
                $pack = [];
                if ($html !== null && $page === 1) {
                    $body = $html;
                    $finalUrl = $current;
                } else {
                    $streamFile = data_get($job->meta, 'stream_file') ?: storage_path('app/tmp/stay_list_stream_'.$job->id.'.json');
                    $fetch = $this->fetcher->fetch($current, $respectRobots, $useProxy, [
                        'mode' => 'list',
                        'progress_stream_path' => $streamFile,
                    ]);
                    if (! $fetch['ok'] && $fetch['html'] === '') {
                        throw new RuntimeException($fetch['reason'] ?? 'Fetch list thất bại');
                    }
                    $body = $fetch['html'];
                    $finalUrl = $fetch['final_url'] ?: $current;
                    $pack = is_array($fetch['pack'] ?? null) ? $fetch['pack'] : [];
                }

                $extracted = $this->extractor->extract($body, $finalUrl);
                $fromPack = [];
                foreach (is_array($pack['hotel_urls'] ?? null) ? $pack['hotel_urls'] : [] as $u) {
                    if (is_string($u) && $u !== '') {
                        $fromPack[] = $u;
                    }
                }
                $hotelUrls = array_values(array_unique(array_merge(
                    $fromPack,
                    is_array($extracted['hotel_urls'] ?? null) ? $extracted['hotel_urls'] : [],
                )));

                $newOnPage = 0;
                foreach ($hotelUrls as $url) {
                    $canon = StayBookingUrl::canonicalize($url);
                    if (isset($seen[$canon])) {
                        continue;
                    }
                    $seen[$canon] = true;
                    $urls[] = $url;
                    $this->queueHotelUrl($url, $job);
                    $newOnPage++;
                }

                $meta = is_array($job->meta) ? $job->meta : [];
                $listMeta = is_array($meta['list'] ?? null) ? $meta['list'] : [];
                $listMeta['pages_done'] = $page;
                $listMeta['offset'] = StayBookingUrl::searchOffset($current);
                $listMeta['last_page_new'] = $newOnPage;
                $listMeta['urls_queued'] = count($seen);
                $listMeta['pack_urls'] = count($fromPack);
                $listMeta['html_urls'] = count($extracted['hotel_urls'] ?? []);
                if (is_array($pack['debug'] ?? null)) {
                    $listMeta['load_more'] = [
                        'clicks' => $pack['debug']['load_more_clicks'] ?? null,
                        'scrolls' => $pack['debug']['scrolls'] ?? null,
                        'stopped' => $pack['debug']['stopped'] ?? null,
                        'cards_end' => $pack['debug']['cards_end'] ?? null,
                        'hotel_url_count' => $pack['debug']['hotel_url_count'] ?? null,
                        'network_hint' => $pack['debug']['network']['hint'] ?? null,
                    ];
                }
                $meta['list'] = $listMeta;
                $job->meta = $meta;
                $job->pages_crawled = $page;
                $job->items_found = $job->items()->count();
                $job->save();

                $html = null;

                $loadMoreDone = in_array(
                    (string) ($pack['debug']['stopped'] ?? ''),
                    ['complete', 'exhausted'],
                    true,
                );
                if ($page === 1 && ($loadMoreDone || count($fromPack) > 0 || $newOnPage > 0)) {
                    $listMeta['stopped_reason'] = $loadMoreDone
                        ? 'chrome_load_more_complete'
                        : 'chrome_list_page';
                    $meta['list'] = $listMeta;
                    $job->meta = $meta;
                    $job->save();
                    // Mặc định dừng sau 1 phiên Chrome đầy đủ (không offset thêm).
                    if ($extraOffsetPages <= 0) {
                        break;
                    }
                }

                if ($page >= $pages) {
                    break;
                }
                if ($newOnPage === 0) {
                    $listMeta['stopped_reason'] = 'empty_page';
                    $meta['list'] = $listMeta;
                    $job->meta = $meta;
                    $job->save();
                    break;
                }

                $next = StayBookingUrl::nextSearchPageUrl($current, $pageSize);
                if ($next === null || $next === $current) {
                    $listMeta['stopped_reason'] = 'no_next_url';
                    $meta['list'] = $listMeta;
                    $job->meta = $meta;
                    $job->save();
                    break;
                }
                $current = StayCrawlDates::applyToUrl($next);
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
        $projectId = $job?->project_id ?: ProjectContext::id();

        $query = StayCrawlItem::query();
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        $item = $query->where('canonical_url', $canonical)->first();

        if (! $item) {
            $item = new StayCrawlItem([
                'project_id' => $projectId,
                'canonical_url' => $canonical,
                'source_url' => $url,
                'status' => StayCrawlItem::STATUS_QUEUED,
            ]);
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

            // TỰ ĐỘNG GÁN THÊM DANH MỤC CHO KHÁCH SẠN ĐÃ CÀO NẾU XUẤT HIỆN Ở DANH MỤC NÀY
            if ($job->service_category_id) {
                $service = $item->service_id
                    ? \App\Models\Service::withoutGlobalScopes()->find($item->service_id)
                    : null;

                if (! $service) {
                    $svcQuery = \App\Models\Service::withoutGlobalScopes()
                        ->where('cluster', \App\Models\Service::CLUSTER_STAY);
                    if ($projectId) {
                        $svcQuery->where('project_id', $projectId);
                    }
                    $service = $svcQuery->where(function ($q) use ($canonical, $url) {
                        $q->where('attrs->crawl->canonical_url', $canonical)
                          ->orWhere('attrs->crawl->source_url', $url);
                    })->first();
                }

                if ($service && (int) $job->service_category_id > 0) {
                    if (! $item->service_id) {
                        $item->service_id = $service->id;
                    }
                    $service->categories()->syncWithoutDetaching([(int) $job->service_category_id]);
                    if (! $service->service_category_id) {
                        $service->service_category_id = (int) $job->service_category_id;
                        $service->saveQuietly();
                    }
                }
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
                $fetch = $this->fetcher->fetch($item->source_url, $respectRobots, $useProxy, ['mode' => 'basic']);
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
            // --- Supplement gallery via HTTP if initial extraction has few images ---
            $mappedPhotos = is_array($mapped['attrs']['photos'] ?? null) ? $mapped['attrs']['photos'] : [];
            $httpGalleryCount = 0;
            if (count($mappedPhotos) < 15 && count($extracted['images']) < 15) {
                try {
                    $galleryHtml = $this->fetcher->fetchGalleryHtml($item->source_url, $useProxy);
                    if ($galleryHtml !== '') {
                        $galleryPhotos = $this->extractor->extractGalleryImages($galleryHtml);
                        $httpGalleryCount = count($galleryPhotos);
                        if ($galleryPhotos !== []) {
                            // Merge HTTP gallery photos into extracted images
                            $seenIds = [];
                            foreach ($extracted['images'] as $img) {
                                if (preg_match('#/(\d{5,})\.jpe?g#i', $img['url'] ?? '', $m)) {
                                    $seenIds[$m[1]] = true;
                                }
                            }
                            foreach ($galleryPhotos as $gp) {
                                if (preg_match('#/(\d{5,})\.jpe?g#i', $gp['url'], $m) && ! isset($seenIds[$m[1]])) {
                                    $extracted['images'][] = $gp;
                                    $seenIds[$m[1]] = true;
                                }
                            }
                            // Re-run mapper with enriched images if we had few mapped photos
                            if ($mappedPhotos === [] || count($mappedPhotos) < count($galleryPhotos)) {
                                $mapped = $this->mapper->map((string) $html, $item->source_url, $extracted, $pack);
                            }
                        }
                    }
                } catch (\Throwable) {
                    // HTTP gallery fallback failed — continue with original data
                }
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
                    'http_gallery_photos' => $httpGalleryCount,
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
        $html = (string) ($item->raw_html ?: $item->extracted_html);
        if (trim($html) !== '') {
            $mapped = $this->mapper->map(
                $html,
                $item->source_url,
                is_array($item->raw_json) ? $item->raw_json : [],
            );
            if (is_array($mapped['attrs'] ?? null)) {
                $fields['attrs'] = \App\Support\StayFacilities::overlayRicherStayAttrs(
                    is_array($fields['attrs'] ?? null) ? $fields['attrs'] : [],
                    $mapped['attrs'],
                );
            }
        }
        $categoryId = $categoryId ?: $item->job?->service_category_id;
        $strategy = $strategy === 'improve' ? 'improve' : 'replace';

        return $this->importer->import($item, $fields, $categoryId, $locale, $dryRun, $strategy);
    }

    /**
     * @return array{phase: string, item: StayCrawlItem, message: string}
     */
    public function enrichNext(StayCrawlItem $item, bool $useProxy = false): array
    {
        return $this->enricher->runNext($item, $useProxy);
    }

    public function itemNeedsEnrich(StayCrawlItem $item): bool
    {
        return $this->enricher->needsEnrich($item);
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
        ?string $from = null,
    ): array {
        $this->requireStayCategory($categoryId);
        $rerun = in_array($rerun, ['improve', 'replace'], true) ? $rerun : null;
        $from = StayCrawlEnricher::normalizeFrom($from);

        if (StayBookingUrl::isHotelPage($url) && $rerun === null) {
            $this->throwIfAlreadyCrawled($url);
        }

        $job = $this->enqueueList($url, $categoryId, $useProxy);
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['rerun'] = $rerun;
        $meta['rerun_from'] = $rerun === 'improve' ? $from : null;
        $job->meta = $meta;
        $job->save();

        if (StayBookingUrl::isHotelPage($url)) {
            $item = $this->queueHotelUrl($url, $job);
            if ($rerun) {
                $this->resetItemForRerun($item, $rerun, $from);
            }
            $job->items_found = 1;
            $job->pages_crawled = 0;
            $job->status = StayCrawlJob::STATUS_READY;
            $job->error = null;
            $job->save();

            return ['job' => $job->fresh() ?? $job, 'urls' => [StayBookingUrl::canonicalize($url)]];
        }

        // max_pages UI đã bỏ — crawlList luôn tải đủ listing (Chrome load-more).
        $result = $this->crawlList($job, $html, $respectRobots, $maxPages, $useProxy);
        if ($result['urls'] === [] && $job->items()->count() === 0) {
            $job->status = StayCrawlJob::STATUS_FAILED;
            $job->error = 'Không tìm thấy URL khách sạn. Thử bật proxy (Chrome) hoặc dán HTML đã lưu.';
            $job->save();
            throw new RuntimeException($job->error);
        }
        $this->applyRerunToJob($result['job']->fresh() ?? $result['job'], $rerun, $from);

        return $result;
    }

    /**
     * Pipeline crawler đơn cho 1 item: ingest + mọi bước enrich đến xong / fail / blocked.
     */
    public function processItemFully(
        StayCrawlItem $item,
        string $locale = 'vi',
        bool $useProxy = false,
        bool $respectRobots = false,
    ): StayCrawlItem {
        $job = $item->job;
        if (! $job) {
            throw new RuntimeException('Item không gắn stay_crawl_job.');
        }
        if ($job->service_category_id) {
            $this->requireStayCategory((int) $job->service_category_id);
        }
        $useProxy = $useProxy || (bool) data_get($job->meta, 'use_proxy', false);

        $guard = 0;
        $retriedFailed = false;
        while ($guard++ < 120) {
            $item = $item->fresh(['job', 'service.seoEntry.translations']) ?? $item;

            if ($this->itemNeedsEnrich($item)) {
                $this->enrichNext($item, $useProxy);
                continue;
            }

            if (in_array($item->status, [
                StayCrawlItem::STATUS_QUEUED,
                StayCrawlItem::STATUS_EXTRACTED,
                StayCrawlItem::STATUS_AI_DONE,
                StayCrawlItem::STATUS_FETCHED,
                StayCrawlItem::STATUS_FAILED,
            ], true)) {
                if ($item->status === StayCrawlItem::STATUS_FAILED) {
                    if ($retriedFailed) {
                        break;
                    }
                    $retriedFailed = true;
                }
                $item = $this->ingestRemaining(
                    $item,
                    $job->service_category_id,
                    $locale,
                    null,
                    $respectRobots,
                    false,
                    $useProxy,
                );
                if (in_array($item->status, [
                    StayCrawlItem::STATUS_BLOCKED,
                    StayCrawlItem::STATUS_FAILED,
                ], true) && ! $this->itemNeedsEnrich($item)) {
                    break;
                }
                continue;
            }

            break;
        }

        return $item->fresh(['job', 'service.seoEntry.translations']) ?? $item;
    }

    /**
     * Đưa từng URL của job vào Laravel queue (ProcessStayCrawlItemJob).
     *
     * @return array{dispatched: int, item_ids: list<int>}
     */
    public function dispatchItemQueue(
        StayCrawlJob $job,
        string $locale = 'vi',
        bool $useProxy = false,
        bool $respectRobots = false,
        ?StayCrawlItem $onlyItem = null,
    ): array {
        $useProxy = $useProxy || (bool) data_get($job->meta, 'use_proxy', false);
        $ids = [];

        $itemsQuery = $onlyItem !== null
            ? collect([$onlyItem])
            : $job->items()->orderBy('id')->cursor();

        foreach ($itemsQuery as $item) {
            /** @var StayCrawlItem $item */
            $needs = in_array($item->status, [
                StayCrawlItem::STATUS_QUEUED,
                StayCrawlItem::STATUS_EXTRACTED,
                StayCrawlItem::STATUS_AI_DONE,
                StayCrawlItem::STATUS_FETCHED,
                StayCrawlItem::STATUS_FAILED,
                StayCrawlItem::STATUS_BLOCKED,
            ], true) || $this->itemNeedsEnrich($item);
            if (! $needs && $onlyItem === null) {
                continue;
            }
            
            // Đánh dấu rõ ràng trạng thái QUEUED trên database ngay khi đẩy vào queue
            $item->status = StayCrawlItem::STATUS_QUEUED;
            $item->error = null;
            $item->blocked_reason = null;
            $item->save();

            ProcessStayCrawlItemJob::dispatch(
                (int) $item->id,
                $locale,
                $useProxy,
                $respectRobots,
            );
            $ids[] = (int) $item->id;
        }

        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['queue'] = [
            'dispatched_at' => now()->toIso8601String(),
            'dispatched' => count($ids),
            'driver' => (string) config('queue.default'),
            'queue' => (string) config('stay.crawl.queue', 'default'),
            'hint' => 'php artisan queue:work --queue='.config('stay.crawl.queue', 'default'),
        ];
        $meta['worker'] = array_merge(is_array($meta['worker'] ?? null) ? $meta['worker'] : [], [
            'running' => true,
            'mode' => 'laravel_queue',
            'paused' => false,
            'heartbeat_at' => now()->toIso8601String(),
            'message' => 'Đã đẩy '.count($ids).' URL vào queue — chạy queue:work / Supervisor',
        ]);
        $job->meta = $meta;
        $job->save();

        return ['dispatched' => count($ids), 'item_ids' => $ids];
    }

    /** @param  array<string, mixed>  $patch */
    public function touchQueueMeta(StayCrawlJob $job, array $patch = []): void
    {
        $job->refresh();
        $meta = is_array($job->meta) ? $job->meta : [];
        $q = is_array($meta['queue'] ?? null) ? $meta['queue'] : [];
        $meta['queue'] = array_merge($q, $patch, [
            'last_at' => now()->toIso8601String(),
        ]);
        $w = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
        
        $activeWorkers = is_array($w['active_items'] ?? null) ? $w['active_items'] : [];
        if (! empty($patch['item_id'])) {
            $activeWorkers[(string) $patch['item_id']] = [
                'item_id' => $patch['item_id'],
                'updated_at' => now()->toIso8601String(),
                'message' => $patch['message'] ?? 'Đang xử lý',
            ];
        }

        // Lọc bỏ các worker đã cũ > 10 phút
        $nowTs = time();
        foreach ($activeWorkers as $k => $aw) {
            $ts = ! empty($aw['updated_at']) ? strtotime($aw['updated_at']) : 0;
            if ($nowTs - $ts > 600) {
                unset($activeWorkers[$k]);
            }
        }

        $meta['worker'] = array_merge($w, [
            'running' => true,
            'mode' => 'laravel_queue',
            'heartbeat_at' => now()->toIso8601String(),
            'phase' => $patch['phase'] ?? ($w['phase'] ?? 'queue'),
            'message' => $patch['message'] ?? ($w['message'] ?? null),
            'item_id' => $patch['item_id'] ?? ($w['item_id'] ?? null),
            'active_items' => $activeWorkers,
            'active_count' => count($activeWorkers),
        ]);
        $job->meta = $meta;
        $job->save();
    }

    public function removeItemActive(StayCrawlJob $job, int $itemId): void
    {
        $job->refresh();
        $meta = is_array($job->meta) ? $job->meta : [];
        $w = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
        $activeWorkers = is_array($w['active_items'] ?? null) ? $w['active_items'] : [];
        unset($activeWorkers[(string) $itemId]);

        $meta['worker'] = array_merge($w, [
            'heartbeat_at' => now()->toIso8601String(),
            'active_items' => $activeWorkers,
            'active_count' => count($activeWorkers),
        ]);
        $job->meta = $meta;
        $job->save();
    }

    public function refreshJobCompletion(StayCrawlJob $job): void
    {
        $job->refresh();
        $remaining = $this->remainingCount($job);
        if ($remaining === 0) {
            $job->status = StayCrawlJob::STATUS_DONE;
            $meta = is_array($job->meta) ? $job->meta : [];
            $w = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
            $meta['worker'] = array_merge($w, [
                'running' => false,
                'stop_reason' => 'completed',
                'heartbeat_at' => now()->toIso8601String(),
                'message' => 'Queue hoàn tất',
            ]);
            $job->meta = $meta;
            $job->save();

            return;
        }

        $meta = is_array($job->meta) ? $job->meta : [];
        $w = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
        $meta['worker'] = array_merge($w, [
            'running' => true,
            'mode' => 'laravel_queue',
            'heartbeat_at' => now()->toIso8601String(),
            'remaining' => $remaining,
        ]);
        $job->meta = $meta;
        $job->save();
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
     *     phase: string|null,
     *     message: string|null,
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

        $enrichItem = $this->nextEnrichItem($job);
        if ($enrichItem) {
            try {
                $step = $this->enricher->runNext($enrichItem, $useProxy);
            } catch (\Throwable $e) {
                $step = $this->failEnrichStep($enrichItem, $e->getMessage());
            }
            $remaining = $this->remainingCount($job);
            if ($remaining === 0) {
                $job->status = StayCrawlJob::STATUS_DONE;
                $job->save();
            }

            return $this->jobProgress($job->fresh() ?? $job, $step['item'], $remaining === 0, $step['phase'], $step['message']);
        }

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

        $remaining = $this->remainingCount($job);
        if ($remaining === 0) {
            $job->status = StayCrawlJob::STATUS_DONE;
            $job->save();
        }

        $basicMessage = $item->service_id
            ? 'Đã tạo chỗ nghỉ (published)'
            : ($item->error ?: 'Đã cào HTML');

        return $this->jobProgress($job->fresh() ?? $job, $item, $remaining === 0, 'basic', $basicMessage);
    }

    public function isStepRunning(StayCrawlJob $job): bool
    {
        $job->refresh();
        if ($this->isWorkerAlive($job)) {
            return true;
        }
        if (! (bool) data_get($job->meta, 'step_running', false)) {
            return false;
        }
        $started = (string) data_get($job->meta, 'step_started_at', '');
        if ($started === '') {
            return true;
        }
        $ts = strtotime($started);
        // Khớp timeout Chrome gallery (~7–8 phút). Quá hạn → coi như chết để spawn lại.
        return $ts !== false && (time() - $ts) < 480;
    }

    /** Worker nền (stay-crawl:work) còn heartbeat trong cửa sổ an toàn. */
    public function isWorkerAlive(StayCrawlJob $job): bool
    {
        $job->refresh();
        if (! (bool) data_get($job->meta, 'worker.running', false)) {
            return false;
        }

        // Laravel queue: còn URL cần xử lý → coi như “busy” (tránh process-next spawn Chrome trùng).
        if (data_get($job->meta, 'worker.mode') === 'laravel_queue') {
            return $this->remainingCount($job) > 0;
        }

        $hb = (string) data_get($job->meta, 'worker.heartbeat_at', '');
        if ($hb === '') {
            return true;
        }
        $ts = strtotime($hb);
        $stale = max(90, (int) config('stay.crawl.worker_heartbeat_stale_sec', 120));

        return $ts !== false && (time() - $ts) < $stale;
    }

    public function isWorkerPaused(StayCrawlJob $job): bool
    {
        return (bool) data_get($job->meta, 'worker.paused', false);
    }

    /**
     * @param  array{locale?: string, useProxy?: bool, respectRobots?: bool}  $opts
     */
    public function spawnWorker(StayCrawlJob $job, array $opts = []): void
    {
        if ($this->isWorkerAlive($job)) {
            return;
        }

        $php = $this->phpCliBinary();
        $artisan = base_path('artisan');
        $setsid = $this->safeIsExecutable('/usr/bin/setsid') ? '/usr/bin/setsid' : '';
        $nohup = $this->safeIsExecutable('/usr/bin/nohup') ? '/usr/bin/nohup' : 'nohup';
        $parts = array_values(array_filter([
            $setsid,
            $nohup,
            escapeshellarg($php),
            escapeshellarg($artisan),
            'stay-crawl:work',
            (string) $job->id,
            '--locale='.escapeshellarg((string) ($opts['locale'] ?? 'vi')),
        ]));
        if (! empty($opts['useProxy']) || (bool) data_get($job->meta, 'use_proxy', false)) {
            $parts[] = '--proxy';
        }
        if (! empty($opts['respectRobots'])) {
            $parts[] = '--respect-robots';
        }
        $log = storage_path('logs/stay-crawl-work-'.$job->id.'.log');
        $stamp = now()->toIso8601String();
        @file_put_contents($log, "{$stamp} SPAWN stay-crawl:work {$job->id}\n", FILE_APPEND);
        $cmd = 'cd '.escapeshellarg(base_path()).' && '.implode(' ', $parts)
            .' >> '.escapeshellarg($log).' 2>&1 < /dev/null & echo $!';
        $pid = trim((string) shell_exec($cmd));

        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['worker'] = array_merge(is_array($meta['worker'] ?? null) ? $meta['worker'] : [], [
            'running' => true,
            'paused' => false,
            'pid' => is_numeric($pid) ? (int) $pid : null,
            'spawned_at' => $stamp,
            'heartbeat_at' => $stamp,
            'log' => 'storage/logs/stay-crawl-work-'.$job->id.'.log',
            'stop_reason' => null,
        ]);
        $job->meta = $meta;
        $job->save();
        @file_put_contents($log, "{$stamp} SPAWN_PID=".($pid !== '' ? $pid : 'unknown')."\n", FILE_APPEND);
    }

    public function pauseWorker(StayCrawlJob $job, string $reason = 'paused'): void
    {
        $meta = is_array($job->meta) ? $job->meta : [];
        $worker = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
        $worker['paused'] = true;
        $worker['pause_reason'] = $reason;
        $worker['paused_at'] = now()->toIso8601String();
        $meta['worker'] = $worker;
        $job->meta = $meta;
        $job->save();
        @file_put_contents(storage_path('app/stay-crawl-pause-'.$job->id), $reason."\n".now()->toIso8601String()."\n");
    }

    public function resumeWorker(StayCrawlJob $job, array $opts = []): void
    {
        $meta = is_array($job->meta) ? $job->meta : [];
        $worker = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
        $worker['paused'] = false;
        $worker['pause_reason'] = null;
        $worker['resumed_at'] = now()->toIso8601String();
        $meta['worker'] = $worker;
        $job->meta = $meta;
        $job->save();
        $pauseFile = storage_path('app/stay-crawl-pause-'.$job->id);
        if (is_file($pauseFile)) {
            @unlink($pauseFile);
        }

        // Ưu tiên Laravel queue (bền sau reboot). CLI stay-crawl:work vẫn dùng được thủ công.
        $this->dispatchItemQueue(
            $job->fresh() ?? $job,
            (string) ($opts['locale'] ?? 'vi'),
            (bool) ($opts['useProxy'] ?? false),
            (bool) ($opts['respectRobots'] ?? false),
        );
    }

    /**
     * @param  array<string, mixed>  $patch
     */
    public function touchWorkerHeartbeat(StayCrawlJob $job, array $patch = []): void
    {
        $job->refresh();
        $meta = is_array($job->meta) ? $job->meta : [];
        $worker = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
        $worker = array_merge($worker, $patch, [
            'running' => true,
            'heartbeat_at' => now()->toIso8601String(),
        ]);
        $meta['worker'] = $worker;
        $job->meta = $meta;
        $job->save();
    }

    public function stopWorkerMeta(StayCrawlJob $job, string $reason): void
    {
        $job->refresh();
        $meta = is_array($job->meta) ? $job->meta : [];
        $worker = is_array($meta['worker'] ?? null) ? $meta['worker'] : [];
        $worker['running'] = false;
        $worker['stop_reason'] = $reason;
        $worker['stopped_at'] = now()->toIso8601String();
        $worker['heartbeat_at'] = now()->toIso8601String();
        $meta['worker'] = $worker;
        $job->meta = $meta;
        $job->save();
    }

    /**
     * Bước nền bị kẹt (step_running quá hạn) — clear để poll tiếp tục.
     */
    public function clearStaleStep(StayCrawlJob $job): bool
    {
        $job->refresh();
        if (! (bool) data_get($job->meta, 'step_running', false)) {
            return false;
        }
        if ($this->isStepRunning($job)) {
            return false;
        }
        $this->failStep($job, 'Bước nền quá hạn / Chrome kẹt profile — sẽ chạy lại bước tiếp.');

        return true;
    }

    public function markStepRunning(StayCrawlJob $job): void
    {
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['step_running'] = true;
        $meta['step_started_at'] = now()->toIso8601String();
        $job->meta = $meta;
        $job->save();
    }

    /**
     * @param  array{done?: bool, item?: StayCrawlItem|null, remaining?: int, imported?: int, blocked?: int, failed?: int, phase?: string|null, message?: string|null}  $result
     */
    public function finishStep(StayCrawlJob $job, array $result): void
    {
        $job->refresh();
        $meta = is_array($job->meta) ? $job->meta : [];
        $seq = (int) ($meta['step_seq'] ?? 0) + 1;
        $item = $result['item'] ?? null;
        $meta['step_running'] = false;
        $meta['step_started_at'] = null;
        $meta['step_seq'] = $seq;
        $meta['last_step'] = [
            'seq' => $seq,
            'phase' => $result['phase'] ?? null,
            'message' => $result['message'] ?? null,
            'done' => (bool) ($result['done'] ?? false),
            'imported' => (int) ($result['imported'] ?? 0),
            'blocked' => (int) ($result['blocked'] ?? 0),
            'failed' => (int) ($result['failed'] ?? 0),
            'remaining' => (int) ($result['remaining'] ?? 0),
            'item_id' => $item?->id,
            'source_url' => $item?->source_url,
            'item_status' => $item?->status,
            'blocked_reason' => $item?->blocked_reason,
            'error' => $item?->error,
        ];
        $job->meta = $meta;
        $job->save();
    }

    public function failStep(StayCrawlJob $job, string $error): void
    {
        $this->finishStep($job, [
            'done' => false,
            'item' => null,
            'remaining' => $this->remainingCount($job),
            'imported' => 0,
            'blocked' => 0,
            'failed' => 0,
            'phase' => 'error',
            'message' => 'Luồng Chrome lỗi: '.$error,
        ]);
    }

    /**
     * @return array{
     *     done: bool,
     *     busy: bool,
     *     item: StayCrawlItem|null,
     *     remaining: int,
     *     imported: int,
     *     blocked: int,
     *     failed: int,
     *     total: int,
     *     phase: string|null,
     *     message: string|null,
     *     last_step: array<string, mixed>|null,
     *     job: StayCrawlJob
     * }
     */
    public function httpStepSnapshot(StayCrawlJob $job): array
    {
        $job->refresh();
        $busy = $this->isStepRunning($job);
        $last = is_array($job->meta['last_step'] ?? null) ? $job->meta['last_step'] : null;
        $item = null;
        if (is_array($last) && ! empty($last['item_id'])) {
            $item = StayCrawlItem::query()
                ->with('service.seoEntry.translations')
                ->find((int) $last['item_id']);
        }
        $remaining = $this->remainingCount($job);
        $progress = $this->jobProgress(
            $job,
            $item,
            ! $busy && $remaining === 0,
            is_array($last) ? ($last['phase'] ?? null) : null,
            is_array($last) ? ($last['message'] ?? null) : null,
        );
        $progress['busy'] = $busy;
        $progress['last_step'] = $last;

        return $progress;
    }

    /**
     * Chạy 1 bước Chrome ngoài request HTTP (tránh Nginx cắt ~60s).
     *
     * @param  array{locale: string, html: ?string, respectRobots: bool, useProxy: bool}  $opts
     */
    public function spawnHttpStep(StayCrawlJob $job, array $opts): void
    {
        $htmlFile = '';
        $html = $opts['html'] ?? null;
        if (is_string($html) && $html !== '') {
            $dir = storage_path('app/tmp');
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $htmlFile = $dir.'/stay_step_'.$job->id.'.html';
            file_put_contents($htmlFile, $html);
        }

        $php = $this->phpCliBinary();
        $artisan = base_path('artisan');
        $setsid = $this->safeIsExecutable('/usr/bin/setsid') ? '/usr/bin/setsid' : '';
        $nohup = $this->safeIsExecutable('/usr/bin/nohup') ? '/usr/bin/nohup' : 'nohup';
        $parts = array_values(array_filter([
            $setsid,
            $nohup,
            escapeshellarg($php),
            escapeshellarg($artisan),
            'stay-crawl:step',
            (string) $job->id,
            '--locale='.escapeshellarg((string) ($opts['locale'] ?? 'vi')),
        ]));
        if (! empty($opts['useProxy'])) {
            $parts[] = '--proxy';
        }
        if (! empty($opts['respectRobots'])) {
            $parts[] = '--respect-robots';
        }
        if ($htmlFile !== '') {
            $parts[] = '--html-file='.escapeshellarg($htmlFile);
        }
        $log = storage_path('logs/stay-crawl-step-'.$job->id.'.log');
        $stamp = now()->toIso8601String();
        @file_put_contents(
            $log,
            "{$stamp} SPAWN stay-crawl:step {$job->id} php={$php}\n",
            FILE_APPEND,
        );
        $cmd = 'cd '.escapeshellarg(base_path()).' && '.implode(' ', $parts)
            .' >> '.escapeshellarg($log).' 2>&1 < /dev/null & echo $!';
        $pid = trim((string) shell_exec($cmd));
        @file_put_contents(
            $log,
            "{$stamp} SPAWN_PID=".($pid !== '' ? $pid : 'unknown')."\n",
            FILE_APPEND,
        );
    }

    /**
     * Wrapper an toàn cho is_executable() — tránh lỗi open_basedir trên aaPanel/cPanel.
     * Khi bị chặn, fallback dùng shell `test -x`.
     */
    private function safeIsExecutable(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        // Suppress open_basedir warning
        $result = @is_executable($path);
        if ($result) {
            return true;
        }

        // Nếu open_basedir chặn, fallback qua shell
        $check = @shell_exec('test -x ' . escapeshellarg($path) . ' && echo 1 2>/dev/null');
        if (trim((string) $check) === '1') {
            return true;
        }

        return false;
    }

    private function phpCliBinary(): string
    {
        $candidates = array_filter([
            trim((string) env('STAY_CRAWL_PHP', '')),
            PHP_BINDIR.'/php',
            '/usr/bin/php',
            '/usr/local/bin/php',
            PHP_SAPI === 'cli' ? PHP_BINARY : '',
        ]);
        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && $this->safeIsExecutable($path) && ! str_contains($path, 'php-fpm')) {
                return $path;
            }
        }

        throw new RuntimeException('Không tìm thấy php CLI để chạy crawler nền (stay-crawl:step).');
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
                if (! $dryRun && $item->status === StayCrawlItem::STATUS_IMPORTED) {
                    $this->enricher->markPending($item);
                    $item = $item->fresh(['job', 'service.seoEntry.translations']) ?? $item;
                }
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

    private function nextEnrichItem(StayCrawlJob $job): ?StayCrawlItem
    {
        foreach ($job->items()->where('status', StayCrawlItem::STATUS_IMPORTED)->orderBy('id')->get() as $item) {
            if ($this->enricher->needsEnrich($item)) {
                return $item;
            }
        }

        return null;
    }

    private function remainingCount(StayCrawlJob $job): int
    {
        $enrich = 0;
        foreach ($job->items()->where('status', StayCrawlItem::STATUS_IMPORTED)->get() as $item) {
            if ($this->enricher->needsEnrich($item)) {
                $enrich++;
            }
        }

        return $this->processableQuery($job)->count() + $enrich;
    }

    /**
     * @return array{phase: string, item: StayCrawlItem, message: string}
     */
    private function failEnrichStep(StayCrawlItem $item, string $error): array
    {
        $raw = is_array($item->raw_json) ? $item->raw_json : [];
        $enrich = is_array($raw['enrich'] ?? null) ? $raw['enrich'] : [];
        if (($enrich['gallery'] ?? 'done') !== 'done') {
            $enrich['gallery'] = 'done';
            $enrich['gallery_error'] = $error;
            $phase = 'gallery';
        } elseif (($enrich['rooms_total'] ?? null) === null) {
            $enrich['rooms'] = 'done';
            $enrich['rooms_error'] = $error;
            $phase = 'rooms_list';
        } else {
            $enrich['rooms_next'] = (int) ($enrich['rooms_next'] ?? 0) + 1;
            $enrich['last_room_error'] = $error;
            if ((int) $enrich['rooms_next'] >= (int) ($enrich['rooms_total'] ?? 0)) {
                $enrich['rooms'] = 'done';
            }
            $phase = 'room';
        }
        $raw['enrich'] = $enrich;
        $item->raw_json = $raw;
        $item->error = $error;
        $item->save();

        return [
            'phase' => $phase,
            'item' => $item,
            'message' => 'Luồng phụ lỗi: '.$error,
        ];
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
     *     phase: string|null,
     *     message: string|null,
     *     job: StayCrawlJob
     * }
     */
    private function jobProgress(
        StayCrawlJob $job,
        ?StayCrawlItem $item,
        bool $done,
        ?string $phase = null,
        ?string $message = null,
    ): array {
        $job->loadCount([
            'items',
            'items as imported_count' => fn ($q) => $q->where('status', StayCrawlItem::STATUS_IMPORTED),
            'items as blocked_count' => fn ($q) => $q->where('status', StayCrawlItem::STATUS_BLOCKED),
            'items as failed_count' => fn ($q) => $q->where('status', StayCrawlItem::STATUS_FAILED),
        ]);

        return [
            'done' => $done,
            'item' => $item,
            'remaining' => $this->remainingCount($job),
            'imported' => (int) $job->imported_count,
            'blocked' => (int) $job->blocked_count,
            'failed' => (int) $job->failed_count,
            'total' => (int) ($job->items_count ?? $job->items()->count()),
            'phase' => $phase,
            'message' => $message,
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

    private function applyRerunToJob(StayCrawlJob $job, ?string $rerun, string $from = 'basic'): void
    {
        $done = $job->items()->get()->filter(fn (StayCrawlItem $i) => $this->itemIsAlreadyCrawled($i));
        if ($done->isEmpty()) {
            return;
        }
        
        // Nếu người dùng chọn cào lại / cải thiện thì reset các item đã xong
        if ($rerun === 'replace' || $rerun === 'improve') {
            foreach ($done as $item) {
                $this->resetItemForRerun($item, $rerun, $from);
            }
        }
        // Mặc định hoặc rerun === null/skip: Giữ nguyên các khách sạn đã cào trước đó, không xóa/đè
        
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['rerun'] = $rerun;
        $meta['rerun_from'] = $rerun === 'improve' ? StayCrawlEnricher::normalizeFrom($from) : null;
        $meta['skipped_existing_count'] = $done->count();
        $job->meta = $meta;
        if ($job->status === StayCrawlJob::STATUS_DONE) {
            $job->status = StayCrawlJob::STATUS_READY;
        }
        $job->error = null;
        $job->save();
    }

    private function itemIsAlreadyCrawled(StayCrawlItem $item): bool
    {
        return in_array($item->status, [
            StayCrawlItem::STATUS_IMPORTED,
            StayCrawlItem::STATUS_AI_DONE,
            StayCrawlItem::STATUS_EXTRACTED,
        ], true);
    }

    /**
     * @param  string  $from  basic|gallery|rooms|rooms_modals (chỉ áp dụng khi improve)
     */
    public function resetItemForRerun(StayCrawlItem $item, string $rerun, string $from = 'basic'): StayCrawlItem
    {
        $from = StayCrawlEnricher::normalizeFrom($from);

        if ($rerun === 'replace' && $item->service_id) {
            $service = Service::query()->withTrashed()->find($item->service_id);
            if ($service) {
                $this->importer->purgeCrawledService($service);
            }
            $item->service_id = null;
        }

        if ($rerun === 'improve' && $from !== 'basic') {
            $result = $this->enricher->resetFrom($item, $from);
            if ($result['ok']) {
                $job = $item->job;
                if ($job && $job->status === StayCrawlJob::STATUS_DONE) {
                    $job->status = StayCrawlJob::STATUS_READY;
                    $job->error = null;
                    $meta = is_array($job->meta) ? $job->meta : [];
                    $meta['rerun'] = 'improve';
                    $meta['rerun_from'] = $result['from'];
                    $job->meta = $meta;
                    $job->save();
                }

                return $item->fresh(['job', 'service']) ?? $item;
            }
            // Chưa có draft → fallback full basic
            $from = 'basic';
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

    /**
     * Khởi chạy tiến trình Chrome cào danh sách listing ở background (tránh Nginx 60s timeout).
     */
    public function spawnListProcess(StayCrawlJob $job, array $opts = []): void
    {
        $htmlFile = '';
        $html = $opts['html'] ?? null;
        if (is_string($html) && $html !== '') {
            $dir = storage_path('app/tmp');
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $htmlFile = $dir.'/stay_list_'.$job->id.'.html';
            file_put_contents($htmlFile, $html);
        }

        $php = $this->phpCliBinary();
        $artisan = base_path('artisan');
        $setsid = $this->safeIsExecutable('/usr/bin/setsid') ? '/usr/bin/setsid' : '';
        $nohup = $this->safeIsExecutable('/usr/bin/nohup') ? '/usr/bin/nohup' : 'nohup';
        $parts = array_values(array_filter([
            $setsid,
            $nohup,
            escapeshellarg($php),
            escapeshellarg($artisan),
            'stay-crawl:list',
            (string) $job->id,
            '--locale='.escapeshellarg((string) ($opts['locale'] ?? 'vi')),
        ]));
        if (! empty($opts['useProxy'])) {
            $parts[] = '--proxy';
        }
        if (! empty($opts['respectRobots'])) {
            $parts[] = '--respect-robots';
        }
        if ($htmlFile !== '') {
            $parts[] = '--html-file='.escapeshellarg($htmlFile);
        }
        if (! empty($opts['maxPages'])) {
            $parts[] = '--max-pages='.(int) $opts['maxPages'];
        }

        $log = storage_path('logs/stay-crawl-list-'.$job->id.'.log');
        $stamp = now()->toIso8601String();
        @file_put_contents(
            $log,
            "{$stamp} SPAWN stay-crawl:list {$job->id} php={$php}
",
            FILE_APPEND,
        );
        $cmd = 'cd '.escapeshellarg(base_path()).' && '.implode(' ', $parts)
            .' >> '.escapeshellarg($log).' 2>&1 < /dev/null & echo $!';
        $pid = trim((string) shell_exec($cmd));
        @file_put_contents(
            $log,
            "{$stamp} SPAWN_PID=".($pid !== '' ? $pid : 'unknown')."
",
            FILE_APPEND,
        );

        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['list_process'] = [
            'running' => true,
            'pid' => $pid !== '' ? (int) $pid : null,
            'started_at' => now()->toIso8601String(),
            'log' => 'storage/logs/stay-crawl-list-'.$job->id.'.log',
        ];
        $job->meta = $meta;
        $job->status = StayCrawlJob::STATUS_CRAWLING;
        $job->save();
    }

    /**
     * Cào listing ở background và stream URL trực tiếp vào database ngay khi tìm thấy.
     */
    public function crawlListBackground(
        StayCrawlJob $job,
        ?string $html = null,
        bool $respectRobots = false,
        int $maxPages = 1,
        bool $useProxy = false,
    ): array {
        $streamFile = storage_path('app/tmp/stay_list_stream_'.$job->id.'.json');
        @unlink($streamFile);

        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['stream_file'] = $streamFile;
        $job->meta = $meta;
        $job->save();

        try {
            $result = $this->crawlList($job, $html, $respectRobots, $maxPages, $useProxy);
            $fresh = $result['job']->fresh() ?? $result['job'];
            $meta = is_array($fresh->meta) ? $fresh->meta : [];
            $meta['list_process']['running'] = false;
            $meta['list_process']['finished_at'] = now()->toIso8601String();
            $fresh->meta = $meta;
            $fresh->status = StayCrawlJob::STATUS_READY;
            $fresh->save();

            // Tự động đẩy queue để xử lý song song các URLs đã tìm thấy
            $this->dispatchItemQueue($fresh, 'vi', $useProxy, $respectRobots);

            return $result;
        } catch (\Throwable $e) {
            $this->failListStep($job->fresh() ?? $job, $e->getMessage());
            throw $e;
        }
    }

    public function failListStep(StayCrawlJob $job, string $error): void
    {
        $job->refresh();
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['list_process']['running'] = false;
        $meta['list_process']['error'] = $error;
        $job->meta = $meta;
        $job->status = StayCrawlJob::STATUS_FAILED;
        $job->error = $error;
        $job->save();
    }

    /**
     * Đồng bộ tiến độ stream từ file sidecar của Chrome Node script vào DB realtime.
     */
    public function syncListProgressFromStream(StayCrawlJob $job): array
    {
        $streamFile = storage_path('app/tmp/stay_list_stream_'.$job->id.'.json');
        $running = (bool) data_get($job->meta, 'list_process.running', false);

        // Kiểm tra xem process có bị treo quá 15 phút không
        if ($running) {
            $started = (string) data_get($job->meta, 'list_process.started_at', '');
            if ($started !== '' && (time() - strtotime($started)) > 900) {
                $running = false;
                $this->failListStep($job, 'Tiến trình cào danh sách vượt quá thời gian tối đa (15 phút).');
            }
        }

        $streamData = null;
        if (is_file($streamFile)) {
            $raw = @file_get_contents($streamFile);
            if (is_string($raw) && $raw !== '') {
                $streamData = @json_decode($raw, true);
            }
        }

        if (is_array($streamData)) {
            // Nạp URL mới vào database theo thời gian thực
            $urls = is_array($streamData['urls'] ?? null) ? $streamData['urls'] : [];
            $newAdded = 0;
            foreach ($urls as $url) {
                if (! is_string($url) || $url === '') {
                    continue;
                }
                $canon = StayBookingUrl::canonicalize($url);
                $exists = $job->items()->where('canonical_url', $canon)->exists();
                if (! $exists) {
                    $this->queueHotelUrl($url, $job);
                    $newAdded++;
                }
            }
            if ($newAdded > 0) {
                $job->items_found = $job->items()->count();
                $job->save();
            }

            if (($streamData['phase'] ?? '') === 'listing_done') {
                $running = false;
            }
        }

        return [
            'running' => $running,
            'stream' => $streamData,
            'urls_found' => (int) ($job->items()->count()),
            'message' => is_array($streamData) ? ($streamData['message'] ?? null) : 'Đang khởi động Chrome…',
        ];
    }

}
