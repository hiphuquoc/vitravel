<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StayArea;
use App\Models\StayAreaTranslation;
use App\Models\StayCategoryBinding;
use App\Models\StayCrawlItem;
use App\Models\StayCrawlJob;
use App\Models\StayProperty;
use App\Models\StayTaxon;
use App\Services\StayCatalog\StayCatalogAreaSeed;
use App\Services\StayCatalog\StayCatalogOrchestrator;
use App\Services\StayCatalog\StayCatalogProjectionService;
use App\Services\StayCatalog\StayCatalogRebuildService;
use App\Services\StayCatalog\StayCategoryBindService;
use App\Services\StayCatalog\StayDiscoverService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class CatalogAdminApiController extends Controller
{
    public function __construct(
        private readonly StayDiscoverService $discover,
        private readonly StayCatalogRebuildService $rebuild,
        private readonly StayCategoryBindService $bindings,
        private readonly StayCatalogProjectionService $projections,
        private readonly StayCatalogAreaSeed $areaSeed,
    ) {}

    public function dashboard(): JsonResponse
    {
        $stats = $this->rebuild->stats();
        $jobs = StayCrawlJob::withoutGlobalScopes()
            ->whereIn('job_type', [StayCrawlJob::TYPE_AREA_DISCOVER, StayCrawlJob::TYPE_LIST_FILTER, StayCrawlJob::TYPE_LISTING])
            ->latest('id')
            ->limit(8)
            ->get(['id', 'job_type', 'status', 'list_url', 'items_found', 'created_at']);

        return ApiResponse::success([
            'stats' => $stats,
            'orchestrator' => app(StayCatalogOrchestrator::class)->metrics(),
            'recent_jobs' => $jobs,
            'review_count' => StayCrawlJob::withoutGlobalScopes()
                ->where('job_type', StayCrawlJob::TYPE_AREA_DISCOVER)
                ->where('status', StayCrawlJob::STATUS_REVIEW)
                ->count(),
        ]);
    }

    public function discover(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2000'],
            'use_proxy' => ['sometimes', 'boolean'],
            'stay_area_id' => ['nullable', 'integer'],
        ]);
        $job = $this->discover->start(
            trim($validated['url']),
            (bool) ($validated['use_proxy'] ?? false),
            isset($validated['stay_area_id']) ? (int) $validated['stay_area_id'] : null,
        );

        return ApiResponse::success([
            'job' => $this->serializeDiscover($job),
            'spawned_list' => false,
        ], 'Đã tạo job discover — chờ Chrome bung filter, chưa tạo trang.');
    }

    public function showDiscover(int $id): JsonResponse
    {
        $job = $this->discoverJob($id);

        return ApiResponse::success(['job' => $this->serializeDiscover($job)]);
    }

    public function rerunDiscover(int $id, Request $request): JsonResponse
    {
        $job = $this->discover->rerun($this->discoverJob($id), $request->boolean('use_proxy'));

        return ApiResponse::success(['job' => $this->serializeDiscover($job)], 'Đã cào lại filter.');
    }

    public function saveDiscoverReview(int $id, Request $request): JsonResponse
    {
        $job = $this->discoverJob($id);
        $meta = is_array($job->meta) ? $job->meta : [];
        $filters = is_array($request->input('filters')) ? $request->input('filters') : null;
        if (is_array($filters)) {
            $current = is_array(data_get($meta, 'discover.filters')) ? data_get($meta, 'discover.filters') : [];
            $byNflt = [];
            foreach ($filters as $row) {
                if (is_array($row) && ! empty($row['nflt'])) {
                    $byNflt[(string) $row['nflt']] = $row;
                }
            }
            foreach ($current as $i => $row) {
                $nflt = (string) ($row['nflt'] ?? '');
                if ($nflt === '' || ! isset($byNflt[$nflt])) {
                    continue;
                }
                if (array_key_exists('create_page', $byNflt[$nflt])) {
                    $current[$i]['create_page'] = (bool) $byNflt[$nflt]['create_page'];
                }
                if (array_key_exists('crawl', $byNflt[$nflt])) {
                    $current[$i]['crawl'] = (bool) $byNflt[$nflt]['crawl'];
                }
            }
            $meta['discover']['filters'] = $current;
        }
        if ($request->exists('review_notes')) {
            $meta['discover']['review_notes'] = $request->input('review_notes');
        }
        $job->meta = $meta;
        $job->save();

        return ApiResponse::success(['job' => $this->serializeDiscover($job)]);
    }

    public function confirmDiscover(int $id, Request $request): JsonResponse
    {
        try {
            $result = $this->discover->confirm(
                $this->discoverJob($id),
                is_array($request->input('filters')) ? $request->input('filters') : null,
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 'DISCOVER_CONFIRM_FAILED', 422);
        }

        return ApiResponse::success([
            'job' => $this->serializeDiscover($result['job']),
            'pages' => $result['pages'],
            'lists' => $result['lists'],
            'spawned_now' => $result['spawned_now'] ?? $result['lists'],
            'queued' => $result['queued'] ?? 0,
        ], 'Đã tạo trang; list-crawl spawn tuần tự theo slot Chrome.');
    }

    public function rebuildStats(): JsonResponse
    {
        return ApiResponse::success($this->rebuild->stats());
    }

    public function rebuild(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'layer' => ['required', 'in:offline,areas,properties,improve,r1,r2,r4,r7'],
            'dry_run' => ['sometimes', 'boolean'],
            'limit' => ['sometimes', 'integer', 'min:0', 'max:20000'],
        ]);
        $report = $this->rebuild->run(
            $validated['layer'],
            (bool) ($validated['dry_run'] ?? false),
            (int) ($validated['limit'] ?? 0),
        );

        return ApiResponse::success($report);
    }

    public function areas(): JsonResponse
    {
        $items = StayArea::query()
            ->with(['translations', 'relatedAreas:id,slug'])
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (StayArea $a) => $this->serializeArea($a));

        return ApiResponse::success(['items' => $items]);
    }

    public function storeArea(Request $request): JsonResponse
    {
        $validated = $request->validate($this->areaRules());
        $area = StayArea::query()->create($this->areaFill($validated));
        $this->saveAreaTranslation($area, $validated);

        return ApiResponse::success(['area' => $this->serializeArea($area->fresh(['translations', 'relatedAreas']) ?? $area)], 'Đã tạo khu vực');
    }

    public function updateArea(int $id, Request $request): JsonResponse
    {
        $area = StayArea::query()->findOrFail($id);
        $validated = $request->validate($this->areaRules($id));
        $area->fill($this->areaFill($validated));
        $area->save();
        $this->saveAreaTranslation($area, $validated);
        if (isset($validated['related_area_ids']) && is_array($validated['related_area_ids'])) {
            $area->relatedAreas()->sync(array_map('intval', $validated['related_area_ids']));
        }

        return ApiResponse::success(['area' => $this->serializeArea($area->fresh(['translations', 'relatedAreas']) ?? $area)]);
    }

    public function seedAreas(): JsonResponse
    {
        $out = $this->areaSeed->seed();

        return ApiResponse::success($out, 'Đã seed cây khu vực.');
    }

    public function taxons(Request $request): JsonResponse
    {
        $q = StayTaxon::query()->with(['translations', 'area'])->orderBy('group')->orderBy('id');
        if ($group = trim((string) $request->input('group', ''))) {
            $q->where('group', $group);
        }
        if ($areaId = (int) $request->input('stay_area_id')) {
            $q->where('stay_area_id', $areaId);
        }
        $paginator = $q->paginate(min(max($request->integer('per_page', 50), 1), 200));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (StayTaxon $t) => $this->serializeTaxon($t)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function updateTaxon(int $id, Request $request): JsonResponse
    {
        $taxon = StayTaxon::query()->findOrFail($id);
        $taxon->fill($request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'create_page' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'integer'],
        ]));
        $taxon->save();
        if ($request->filled('name')) {
            $langId = Language::idByCode('vi');
            if ($langId) {
                $taxon->translations()->updateOrCreate(
                    ['language_id' => $langId],
                    ['name' => (string) $request->input('name')],
                );
            }
        }

        return ApiResponse::success(['taxon' => $this->serializeTaxon($taxon->fresh(['translations', 'area']) ?? $taxon)]);
    }

    public function properties(Request $request): JsonResponse
    {
        $q = StayProperty::query()->with(['translations', 'primaryArea'])->latest('id');
        if ($areaId = (int) $request->input('stay_area_id')) {
            $q->where('primary_stay_area_id', $areaId);
        }
        if ($search = trim((string) $request->input('search', ''))) {
            $q->where(function ($sq) use ($search): void {
                $sq->where('source_hotel_key', 'like', "%{$search}%")
                    ->orWhere('canonical_url', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($tq) => $tq->where('title', 'like', "%{$search}%"));
            });
        }
        $paginator = $q->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (StayProperty $p) => $this->serializeProperty($p)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function propertyImpact(int $id): JsonResponse
    {
        $property = StayProperty::query()->findOrFail($id);
        $projections = Service::withoutGlobalScopes()
            ->where('stay_property_id', $property->id)
            ->get(['id', 'project_id', 'code', 'status']);

        return ApiResponse::success([
            'property_id' => $property->id,
            'source_hotel_key' => $property->source_hotel_key,
            'projections' => $projections,
            'warning' => 'Xóa catalog sẽ gỡ mọi projection trên các dự án.',
        ]);
    }

    public function destroyProperty(int $id): JsonResponse
    {
        $property = StayProperty::query()->findOrFail($id);
        Service::withoutGlobalScopes()->where('stay_property_id', $property->id)->update([
            'stay_property_id' => null,
        ]);
        StayCrawlItem::withoutGlobalScopes()->where('stay_property_id', $property->id)->update([
            'stay_property_id' => null,
        ]);
        $property->delete();

        return ApiResponse::success(null, 'Đã xóa property catalog (projection giữ nguyên hàng service).');
    }

    public function bindings(Request $request): JsonResponse
    {
        $q = StayCategoryBinding::query()
            ->with(['category:id,name,slug,project_id,cluster', 'area.translations', 'taxon.translations', 'extraAreas'])
            ->latest('id');
        if ($catId = (int) $request->input('service_category_id')) {
            $q->where('service_category_id', $catId);
        }
        $items = $q->limit(200)->get()->map(fn (StayCategoryBinding $b) => $this->serializeBinding($b));

        return ApiResponse::success(['items' => $items]);
    }

    public function bindCategory(int $id, Request $request): JsonResponse
    {
        $category = ServiceCategory::query()->findOrFail($id);
        try {
            $binding = $this->bindings->bind($category, $request->all(), $request->boolean('sync', true));
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        return ApiResponse::success(['binding' => $this->serializeBinding($binding)], 'Đã bind danh mục vào catalog.');
    }

    public function syncBinding(int $id): JsonResponse
    {
        $binding = StayCategoryBinding::query()->findOrFail($id);
        $result = $this->projections->syncBinding($binding);

        return ApiResponse::success($result, 'Đã sync projection.');
    }

    public function unbind(int $id): JsonResponse
    {
        $binding = StayCategoryBinding::query()->findOrFail($id);
        $this->bindings->unbind($binding);

        return ApiResponse::success(null, 'Đã gỡ bind.');
    }

    public function categoryBindings(int $id): JsonResponse
    {
        $items = StayCategoryBinding::query()
            ->where('service_category_id', $id)
            ->with(['area.translations', 'taxon.translations', 'extraAreas'])
            ->get()
            ->map(fn (StayCategoryBinding $b) => $this->serializeBinding($b));

        return ApiResponse::success(['items' => $items]);
    }

    private function discoverJob(int $id): StayCrawlJob
    {
        $job = StayCrawlJob::withoutGlobalScopes()->findOrFail($id);
        if ($job->job_type !== StayCrawlJob::TYPE_AREA_DISCOVER) {
            abort(404);
        }

        return $job;
    }

    /** @return array<string, mixed> */
    private function serializeDiscover(StayCrawlJob $job): array
    {
        $meta = is_array($job->meta) ? $job->meta : [];
        $discover = is_array($meta['discover'] ?? null) ? $meta['discover'] : [];
        $childIds = is_array($discover['child_job_ids'] ?? null) ? $discover['child_job_ids'] : [];
        $children = [];
        $childProgress = ['total' => 0, 'done' => 0, 'crawling' => 0, 'pending' => 0, 'failed' => 0];
        if ($childIds !== []) {
            $rows = StayCrawlJob::withoutGlobalScopes()
                ->whereIn('id', $childIds)
                ->get(['id', 'status', 'items_found', 'list_url', 'job_type']);
            $children = $rows->all();
            $childProgress['total'] = $rows->count();
            foreach ($rows as $child) {
                match ($child->status) {
                    StayCrawlJob::STATUS_DONE => $childProgress['done']++,
                    StayCrawlJob::STATUS_CRAWLING => $childProgress['crawling']++,
                    StayCrawlJob::STATUS_FAILED => $childProgress['failed']++,
                    default => $childProgress['pending']++,
                };
            }
        }

        return [
            'id' => $job->id,
            'job_type' => $job->job_type,
            'status' => $job->status,
            'list_url' => $job->list_url,
            'stay_area_id' => $job->stay_area_id,
            'items_found' => $job->items_found,
            'error' => $job->error,
            'discover' => $discover,
            'child_jobs' => $children,
            'child_progress' => $childProgress,
            'created_at' => optional($job->created_at)->toIso8601String(),
            'updated_at' => optional($job->updated_at)->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeArea(StayArea $area): array
    {
        return [
            'id' => $area->id,
            'parent_id' => $area->parent_id,
            'slug' => $area->slug,
            'level' => $area->level,
            'name' => $area->displayName(),
            'country_code' => $area->country_code,
            'booking_dest_id' => $area->booking_dest_id,
            'booking_dest_type' => $area->booking_dest_type,
            'lat' => $area->lat,
            'lng' => $area->lng,
            'aliases' => $area->aliases,
            'list_url' => $area->list_url,
            'related' => $area->relatedAreas->map(fn (StayArea $r) => [
                'id' => $r->id,
                'slug' => $r->slug,
                'name' => $r->displayName(),
            ])->values(),
            'is_active' => $area->is_active,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeTaxon(StayTaxon $taxon): array
    {
        return [
            'id' => $taxon->id,
            'group' => $taxon->group,
            'code' => $taxon->code,
            'name' => $taxon->displayName(),
            'stay_area_id' => $taxon->stay_area_id,
            'area' => $taxon->area?->slug,
            'service_category_id' => $taxon->service_category_id,
            'filter_url' => $taxon->filter_url,
            'create_page' => $taxon->create_page,
            'is_active' => $taxon->is_active,
            'meta' => $taxon->meta,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeProperty(StayProperty $p): array
    {
        return [
            'id' => $p->id,
            'source_hotel_key' => $p->source_hotel_key,
            'title' => $p->translation()?->title,
            'canonical_url' => $p->canonical_url,
            'property_type' => $p->property_type,
            'area' => $p->primaryArea?->displayName(),
            'lat' => $p->lat,
            'lng' => $p->lng,
            'completeness' => $p->completeness,
            'last_crawled_at' => optional($p->last_crawled_at)->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeBinding(StayCategoryBinding $b): array
    {
        return [
            'id' => $b->id,
            'service_category_id' => $b->service_category_id,
            'category' => $b->category ? [
                'id' => $b->category->id,
                'name' => $b->category->name,
                'slug' => $b->category->slug,
                'project_id' => $b->category->project_id,
            ] : null,
            'stay_area_id' => $b->stay_area_id,
            'area' => $b->area ? ['id' => $b->area->id, 'slug' => $b->area->slug, 'name' => $b->area->displayName()] : null,
            'stay_taxon_id' => $b->stay_taxon_id,
            'taxon' => $b->taxon ? ['id' => $b->taxon->id, 'code' => $b->taxon->code, 'name' => $b->taxon->displayName()] : null,
            'include_child_areas' => $b->include_child_areas,
            'include_related_areas' => $b->include_related_areas,
            'sync_mode' => $b->sync_mode,
            'last_synced_at' => optional($b->last_synced_at)->toIso8601String(),
            'extra_areas' => $b->extraAreas->map(fn (StayArea $a) => [
                'id' => $a->id,
                'slug' => $a->slug,
                'name' => $a->displayName(),
            ])->values(),
        ];
    }

    /** @return array<string, mixed> */
    private function areaRules(?int $id = null): array
    {
        return [
            'parent_id' => ['nullable', 'integer'],
            'slug' => ['required', 'string', 'max:120'],
            'level' => ['required', 'in:country,region,destination,zone'],
            'name' => ['required', 'string', 'max:191'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'booking_dest_id' => ['nullable', 'string', 'max:32'],
            'booking_dest_type' => ['nullable', 'string', 'max:32'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'aliases' => ['nullable', 'array'],
            'list_url' => ['nullable', 'string', 'max:500'],
            'related_area_ids' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @param  array<string, mixed>  $validated */
    private function areaFill(array $validated): array
    {
        return [
            'parent_id' => $validated['parent_id'] ?? null,
            'slug' => $validated['slug'],
            'level' => $validated['level'],
            'country_code' => $validated['country_code'] ?? 'VN',
            'booking_dest_id' => $validated['booking_dest_id'] ?? null,
            'booking_dest_type' => $validated['booking_dest_type'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'aliases' => $validated['aliases'] ?? [],
            'list_url' => $validated['list_url'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];
    }

    /** @param  array<string, mixed>  $validated */
    private function saveAreaTranslation(StayArea $area, array $validated): void
    {
        $langId = Language::idByCode('vi');
        if (! $langId || empty($validated['name'])) {
            return;
        }
        StayAreaTranslation::query()->updateOrCreate(
            ['stay_area_id' => $area->id, 'language_id' => $langId],
            ['name' => $validated['name'], 'slug' => $validated['slug'] ?? $area->slug],
        );
    }
}
