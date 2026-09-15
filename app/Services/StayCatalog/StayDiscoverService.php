<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\Language;
use App\Models\ServiceCategory;
use App\Models\StayArea;
use App\Models\StayCrawlJob;
use App\Models\StayTaxon;
use App\Models\StayTaxonTranslation;
use App\Services\StayCrawl\StayCrawlFetcher;
use App\Services\StayCrawl\StayCrawlService;
use App\Support\StayBookingUrl;
use App\Support\StayCatalog\StayFilterPolicy;
use App\Support\StayCatalog\StayFilterSidebarParser;
use App\Support\StayCatalog\StayIdentity;
use App\Support\StayCatalog\StayText;
use Illuminate\Support\Str;
use RuntimeException;

final class StayDiscoverService
{
    public function __construct(
        private readonly StayCrawlService $crawl,
        private readonly StayCrawlFetcher $fetcher,
        private readonly StayFilterSidebarParser $parser,
        private readonly StayAreaResolver $areas,
    ) {}

    /**
     * Tạo job discover — không spawn list-crawl, không tạo danh mục.
     */
    public function start(string $listUrl, bool $useProxy = false, ?int $areaId = null): StayCrawlJob
    {
        if (! StayBookingUrl::isSearchPage($listUrl) && ! StayBookingUrl::isBookingHost($listUrl)) {
            throw new RuntimeException('URL discover phải là listing Booking (searchresults / city / region).');
        }
        $canonical = StayBookingUrl::canonicalize($listUrl);
        $identity = StayIdentity::fromUrl($listUrl);
        $area = $areaId
            ? StayArea::query()->find($areaId)
            : $this->areas->resolve($identity['ss'], $identity['dest_id'], null, null, $identity['booking_cc']);

        $source = $this->crawl->ensureSource($canonical);
        $job = StayCrawlJob::query()->create([
            'source_id' => $source->id,
            'list_url' => $listUrl,
            'canonical_url' => $canonical,
            'service_category_id' => null,
            'job_type' => StayCrawlJob::TYPE_AREA_DISCOVER,
            'stay_area_id' => $area?->id,
            'status' => StayCrawlJob::STATUS_PENDING,
            'meta' => [
                'source' => 'booking.com',
                'use_proxy' => $useProxy,
                'discover' => [
                    'dest' => [
                        'ss' => $identity['ss'],
                        'dest_id' => $identity['dest_id'],
                        'dest_type' => $identity['dest_type'],
                    ],
                    'spawned' => false,
                    'confirmed_at' => null,
                ],
            ],
        ]);

        if ($area && ! $area->list_url) {
            $area->list_url = $listUrl;
            $area->booking_dest_id = $area->booking_dest_id ?: $identity['dest_id'];
            $area->booking_dest_type = $area->booking_dest_type ?: $identity['dest_type'];
            $area->save();
        }

        $this->spawnDiscoverProcess($job, $useProxy);

        return $job->fresh() ?? $job;
    }

    public function runDiscover(StayCrawlJob $job, bool $useProxy = false, ?string $html = null): StayCrawlJob
    {
        $job->status = StayCrawlJob::STATUS_CRAWLING;
        $job->save();

        $pack = [];
        $finalUrl = $job->list_url;
        if ($html === null) {
            $fetch = $this->fetcher->fetch($job->list_url, false, $useProxy, ['mode' => 'list_discover']);
            if (! $fetch['ok'] && $fetch['html'] === '' && ($fetch['pack'] ?? []) === []) {
                throw new RuntimeException($fetch['reason'] ?? 'Discover filter thất bại');
            }
            $html = $fetch['html'];
            $pack = is_array($fetch['pack'] ?? null) ? $fetch['pack'] : [];
            $finalUrl = $fetch['final_url'] ?: $job->list_url;
        }

        $fromPack = is_array($pack['filters'] ?? null) ? $pack['filters'] : [];
        $filters = $fromPack !== []
            ? $this->parser->decoratePack($fromPack, $job->list_url)
            : $this->parser->parse((string) $html, $job->list_url)['filters'];

        $dest = is_array($pack['dest'] ?? null) ? $pack['dest'] : StayIdentity::fromUrl($job->list_url);
        $expand = data_get($pack, 'debug.expand', []);
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['discover'] = array_merge(is_array($meta['discover'] ?? null) ? $meta['discover'] : [], [
            'dest' => [
                'ss' => $dest['ss'] ?? data_get($meta, 'discover.dest.ss'),
                'dest_id' => $dest['dest_id'] ?? data_get($meta, 'discover.dest.dest_id'),
                'dest_type' => $dest['dest_type'] ?? data_get($meta, 'discover.dest.dest_type'),
                'label' => $dest['label'] ?? ($dest['ss'] ?? null),
            ],
            'filters' => $filters,
            'expand' => $expand,
            'final_url' => $finalUrl,
            'spawned' => false,
            'rerun_notes' => $meta['discover']['rerun_notes'] ?? null,
        ]);
        $job->meta = $meta;
        $job->items_found = count($filters);
        $job->status = StayCrawlJob::STATUS_REVIEW;
        $job->save();

        return $job;
    }

    /**
     * Cào lại filter, giữ ghi chú review / tick tay nếu cùng nflt.
     */
    public function rerun(StayCrawlJob $job, bool $useProxy = false): StayCrawlJob
    {
        if ($job->job_type !== StayCrawlJob::TYPE_AREA_DISCOVER) {
            throw new RuntimeException('Job không phải discover.');
        }
        $prev = is_array(data_get($job->meta, 'discover.filters')) ? data_get($job->meta, 'discover.filters') : [];
        $notes = data_get($job->meta, 'discover.review_notes');
        $overrides = [];
        foreach ($prev as $row) {
            if (! is_array($row) || empty($row['nflt'])) {
                continue;
            }
            $overrides[(string) $row['nflt']] = [
                'create_page' => $row['create_page'] ?? $row['create_page_default'] ?? null,
                'crawl' => $row['crawl'] ?? $row['crawl_default'] ?? null,
            ];
        }
        $job = $this->runDiscover($job, $useProxy);
        $meta = is_array($job->meta) ? $job->meta : [];
        $filters = is_array($meta['discover']['filters'] ?? null) ? $meta['discover']['filters'] : [];
        foreach ($filters as $i => $row) {
            $nflt = (string) ($row['nflt'] ?? '');
            if ($nflt !== '' && isset($overrides[$nflt])) {
                if ($overrides[$nflt]['create_page'] !== null) {
                    $filters[$i]['create_page'] = (bool) $overrides[$nflt]['create_page'];
                }
                if ($overrides[$nflt]['crawl'] !== null) {
                    $filters[$i]['crawl'] = (bool) $overrides[$nflt]['crawl'];
                }
            }
        }
        $meta['discover']['filters'] = $filters;
        $meta['discover']['review_notes'] = $notes;
        $job->meta = $meta;
        $job->save();

        return $job;
    }

    /**
     * @param  list<array<string, mixed>>|null  $overrides
     * @return array{job: StayCrawlJob, pages: int, lists: int, spawned_now: int, queued: int}
     */
    public function confirm(StayCrawlJob $job, ?array $overrides = null): array
    {
        if ($job->status !== StayCrawlJob::STATUS_REVIEW) {
            throw new RuntimeException('Chỉ xác nhận job đang ở trạng thái review.');
        }
        if (data_get($job->meta, 'discover.spawned')) {
            throw new RuntimeException('Job này đã xác nhận — không spawn lại.');
        }

        $filters = is_array(data_get($job->meta, 'discover.filters')) ? data_get($job->meta, 'discover.filters') : [];
        if (is_array($overrides)) {
            $byNflt = [];
            foreach ($overrides as $row) {
                if (is_array($row) && ! empty($row['nflt'])) {
                    $byNflt[(string) $row['nflt']] = $row;
                }
            }
            foreach ($filters as $i => $row) {
                $nflt = (string) ($row['nflt'] ?? '');
                if ($nflt !== '' && isset($byNflt[$nflt])) {
                    if (array_key_exists('create_page', $byNflt[$nflt])) {
                        $filters[$i]['create_page'] = (bool) $byNflt[$nflt]['create_page'];
                    }
                    if (array_key_exists('crawl', $byNflt[$nflt])) {
                        $filters[$i]['crawl'] = (bool) $byNflt[$nflt]['crawl'];
                    }
                }
            }
        } else {
            foreach ($filters as $i => $row) {
                $filters[$i]['create_page'] = (bool) ($row['create_page'] ?? $row['create_page_default'] ?? false);
                $filters[$i]['crawl'] = (bool) ($row['crawl'] ?? $row['crawl_default'] ?? false);
            }
        }

        $area = $job->stay_area_id ? StayArea::query()->find($job->stay_area_id) : null;
        $areaName = $area?->displayName() ?: (string) data_get($job->meta, 'discover.dest.ss', '');
        $pages = 0;
        $lists = 0;
        $spawnedNow = 0;
        $childIds = [];
        $orchestrator = app(StayCatalogOrchestrator::class);
        $slots = $orchestrator->spawnSlots();
        $useProxy = (bool) data_get($job->meta, 'use_proxy', false);

        foreach ($filters as $i => $row) {
            if (($row['policy'] ?? '') === StayFilterPolicy::IGNORE && empty($row['crawl'])) {
                continue;
            }
            $create = (bool) ($row['create_page'] ?? false);
            $doCrawl = (bool) ($row['crawl'] ?? false);
            $taxon = $this->upsertTaxon($row, $area, $create, $areaName);
            $filters[$i]['taxon_id'] = $taxon->id;
            $filters[$i]['service_category_id'] = $taxon->service_category_id;
            if ($create) {
                $pages++;
            }
            if ($doCrawl && ($row['policy'] ?? '') !== StayFilterPolicy::IGNORE) {
                $url = (string) ($row['filter_url'] ?? '');
                if ($url === '') {
                    $url = StayIdentity::withNflt($job->list_url, (string) $row['nflt']);
                }
                $child = $this->crawl->enqueueList($url, $taxon->service_category_id, $useProxy);
                $child->job_type = StayCrawlJob::TYPE_LIST_FILTER;
                $child->stay_area_id = $area?->id;
                $meta = is_array($child->meta) ? $child->meta : [];
                $meta['parent_discover_id'] = $job->id;
                $meta['area_id'] = $area?->id;
                $meta['taxon_id'] = $taxon->id;
                $meta['nflt'] = $row['nflt'] ?? null;
                $meta['spawn_queued'] = true;
                $child->meta = $meta;
                $child->status = StayCrawlJob::STATUS_PENDING;
                $child->save();
                if ($spawnedNow < $slots) {
                    $this->crawl->spawnListProcess($child, ['useProxy' => $useProxy]);
                    $spawnedNow++;
                }
                $childIds[] = $child->id;
                $lists++;
            }
        }

        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['discover']['filters'] = $filters;
        $meta['discover']['spawned'] = true;
        $meta['discover']['confirmed_at'] = now()->toIso8601String();
        $meta['discover']['child_job_ids'] = $childIds;
        $meta['discover']['pages_created'] = $pages;
        $meta['discover']['lists_spawned'] = $lists;
        $meta['discover']['lists_spawned_now'] = $spawnedNow;
        $meta['discover']['lists_queued'] = max(0, $lists - $spawnedNow);
        $job->meta = $meta;
        $job->status = StayCrawlJob::STATUS_DONE;
        $job->save();

        return [
            'job' => $job,
            'pages' => $pages,
            'lists' => $lists,
            'spawned_now' => $spawnedNow,
            'queued' => max(0, $lists - $spawnedNow),
        ];
    }

    public function spawnDiscoverProcess(StayCrawlJob $job, bool $useProxy = false): void
    {
        $php = $this->crawlPhp();
        $artisan = base_path('artisan');
        $setsid = is_executable('/usr/bin/setsid') ? '/usr/bin/setsid' : '';
        $nohup = is_executable('/usr/bin/nohup') ? '/usr/bin/nohup' : 'nohup';
        $parts = array_values(array_filter([
            $setsid,
            $nohup,
            escapeshellarg($php),
            escapeshellarg($artisan),
            'stay-catalog:discover',
            (string) $job->id,
        ]));
        if ($useProxy) {
            $parts[] = '--proxy';
        }
        $log = storage_path('logs/stay-catalog-discover-'.$job->id.'.log');
        $cmd = 'cd '.escapeshellarg(base_path()).' && '.implode(' ', $parts)
            .' >> '.escapeshellarg($log).' 2>&1 < /dev/null & echo $!';
        $pid = trim((string) shell_exec($cmd));
        $meta = is_array($job->meta) ? $job->meta : [];
        $meta['discover_process'] = [
            'running' => true,
            'pid' => $pid !== '' ? (int) $pid : null,
            'started_at' => now()->toIso8601String(),
            'log' => 'storage/logs/stay-catalog-discover-'.$job->id.'.log',
        ];
        $job->meta = $meta;
        $job->status = StayCrawlJob::STATUS_CRAWLING;
        $job->save();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertTaxon(array $row, ?StayArea $area, bool $createPage, string $areaName): StayTaxon
    {
        $code = (string) ($row['nflt'] ?? $row['code'] ?? '');
        $taxon = StayTaxon::query()->firstOrNew([
            'source' => 'booking.com',
            'code' => $code,
            'stay_area_id' => $area?->id,
        ]);
        $taxon->group = (string) ($row['group'] ?? $taxon->group ?: 'other');
        $taxon->filter_url = (string) ($row['filter_url'] ?? $taxon->filter_url);
        $taxon->create_page = $createPage;
        $taxon->is_active = true;
        $taxon->meta = [
            'count' => (int) ($row['count'] ?? 0),
            'policy' => $row['policy'] ?? null,
        ];
        $taxon->save();

        $label = (string) ($row['label'] ?? $code);
        $langId = Language::idByCode('vi');
        if ($langId) {
            StayTaxonTranslation::query()->updateOrCreate(
                ['stay_taxon_id' => $taxon->id, 'language_id' => $langId],
                [
                    'name' => $label,
                    'slug' => Str::slug($label) ?: Str::slug($code),
                ],
            );
        }

        if ($createPage && ! $taxon->service_category_id) {
            $pageName = trim($label.($areaName !== '' ? ' '.$areaName : ''));
            $slug = Str::slug($pageName) ?: Str::slug($label.'-'.$code);
            $existing = ServiceCategory::query()
                ->where('cluster', 'stay')
                ->where('slug', $slug)
                ->first();
            if (! $existing) {
                $existing = ServiceCategory::query()->create([
                    'cluster' => 'stay',
                    'slug' => $slug,
                    'name' => $pageName !== '' ? $pageName : $label,
                    'is_active' => true,
                    'sort' => 0,
                ]);
            }
            $taxon->service_category_id = $existing->id;
            $taxon->save();
        }

        return $taxon;
    }

    private function crawlPhp(): string
    {
        $configured = trim((string) config('stay.crawl.php_bin', ''));
        if ($configured !== '') {
            return $configured;
        }

        return PHP_BINARY ?: 'php';
    }
}
