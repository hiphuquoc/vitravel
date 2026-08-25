<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Language;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\ServiceOptionTranslation;
use App\Models\ServiceTranslation;
use App\Services\HtmlCacheService;
use App\Services\MediaService;
use App\Services\PriceTableService;
use App\Services\ServicePurgeService;
use App\Services\ViewDataService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);
        $viewData = app(ViewDataService::class);
        $catalog = config('services_catalog.clusters', []);
        $requested = $request->string('cluster')->toString();
        $cluster = $requested !== '' ? $viewData->resolveAdminServiceCluster($requested) : '';

        if ($requested !== '' && ! isset($catalog[$cluster]) && ! in_array($cluster, $viewData->serviceClusterCodes(), true)) {
            return ApiResponse::error('Cluster không hợp lệ', 'INVALID_CLUSTER', 404);
        }

        $query = Service::query()
            ->with([
                'category',
                'country.translations',
                'translations',
                'seoEntry.translations',
                'mediaAttachments.media',
            ])
            ->orderBy('cluster')
            ->orderBy('sort')
            ->orderByDesc('id');

        if ($cluster !== '') {
            $query->forCluster($cluster);
        }

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->integer('service_category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('title', 'like', "%{$search}%"));
            });
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $items = collect($paginator->items())->map(fn (Service $s) => $this->serialize($s, $locale));

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        $viewData = app(ViewDataService::class);
        $cluster = $viewData->resolveAdminServiceCluster($request->string('cluster')->toString());
        $clusters = config('services_catalog.clusters', []);

        $hubKey = $clusters[$cluster]['hub_key'] ?? null;
        $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;
        $parentTypes = array_values(array_filter(['service_category', $hubKey]));
        $parents = $this->seoService()->parentOptions($parentTypes, null, $cluster);

        $categories = ServiceCategory::query()
            ->forCluster($cluster)
            ->orderBy('sort')
            ->get()
            ->map(fn (ServiceCategory $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'cluster' => $c->cluster,
            ]);

        $countries = Country::query()->with('translations')->orderBy('sort')->get()
            ->map(fn (Country $c) => [
                'id' => $c->id,
                'name' => $c->translation($locale)?->name,
                'code' => $c->code,
            ]);

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'cluster' => $cluster,
            'clusters' => $viewData->adminServiceClusterOptions(),
            'categories' => $categories,
            'countries' => $countries,
            'property_types' => $cluster === Service::CLUSTER_STAY
                ? collect(config('stay.property_types', []))->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all()
                : [],
            'deal_labels' => $cluster === Service::CLUSTER_STAY
                ? collect(\App\Support\StayRateCopy::dealLabels())->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])->values()->all()
                : [],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Nháp'],
                ['value' => 'published', 'label' => 'Xuất bản'],
                ['value' => 'archived', 'label' => 'Lưu trữ'],
            ],
            'hub_seo_id' => $hubSeo?->id,
            'seo_parents' => $this->mapSeoParents($parents, $locale),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);

        $service = Service::query()
            ->with([
                'categories',
                'category.seoEntry.translations',
                'country.translations',
                'translations',
                'seoEntry.translations',
                'seoEntry.parent',
                'options.translations',
                'mediaAttachments.media',
            ])
            ->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($service, $locale));
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->merge(['id' => $id]);

        return $this->save($request);
    }

    public function destroy(int $id): JsonResponse
    {
        $service = Service::query()->findOrFail($id);

        // Chỗ nghỉ: xóa cứng + GCS/media/relations (cùng pipeline crawler rerun=replace).
        if ($service->cluster === Service::CLUSTER_STAY) {
            app(ServicePurgeService::class)->purge($service);

            return ApiResponse::success(null, 'Đã xóa chỗ nghỉ (kèm media & quan hệ)');
        }

        $service->delete();

        return ApiResponse::success(null, 'Đã xóa sản phẩm dịch vụ');
    }

    public function storeOption(Request $request, int $id): JsonResponse
    {
        $service = Service::query()->findOrFail($id);
        $this->assertStayService($service);
        try {
            $validated = $request->validate($this->stayOptionRules(true));
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }
        $locale = (string) ($validated['locale'] ?? $request->string('locale', 'vi')->toString() ?: 'vi');
        $option = $this->upsertStayOption($service, $validated, $locale, null);

        return ApiResponse::success([
            'option' => $this->serializeStayOption($option->fresh(['translations']) ?? $option, $locale),
        ], 'Đã tạo hạng phòng');
    }

    public function updateOption(Request $request, int $id, int $optionId): JsonResponse
    {
        $service = Service::query()->findOrFail($id);
        $this->assertStayService($service);
        try {
            $validated = $request->validate($this->stayOptionRules(true));
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }
        $locale = (string) ($validated['locale'] ?? $request->string('locale', 'vi')->toString() ?: 'vi');
        $option = $this->upsertStayOption($service, $validated, $locale, $optionId);

        return ApiResponse::success([
            'option' => $this->serializeStayOption($option->fresh(['translations']) ?? $option, $locale),
        ], 'Đã cập nhật hạng phòng');
    }

    public function duplicateOption(Request $request, int $id, int $optionId): JsonResponse
    {
        $service = Service::query()->findOrFail($id);
        $this->assertStayService($service);
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        $source = ServiceOption::query()
            ->where('service_id', $service->id)
            ->with('translations')
            ->findOrFail($optionId);

        $row = $this->serializeStayOption($source, $locale);
        unset($row['id']);
        $row['name'] = trim((string) ($row['name'] ?? '')).' (bản sao)';
        $code = trim((string) ($row['code'] ?? ''));
        if ($code !== '') {
            $row['code'] = Str::limit($code.'-copy', 64, '');
        }

        $option = $this->upsertStayOption($service, $row, $locale, null);

        return ApiResponse::success([
            'option' => $this->serializeStayOption($option->fresh(['translations']) ?? $option, $locale),
        ], 'Đã sao chép hạng phòng');
    }

    public function destroyOption(int $id, int $optionId): JsonResponse
    {
        $service = Service::query()->findOrFail($id);
        $this->assertStayService($service);
        ServiceOption::query()
            ->where('service_id', $service->id)
            ->whereKey($optionId)
            ->delete();

        return ApiResponse::success(null, 'Đã xóa hạng phòng');
    }

    private function assertStayService(Service $service): void
    {
        if ($service->cluster !== Service::CLUSTER_STAY) {
            throw ValidationException::withMessages([
                'cluster' => ['Chỉ áp dụng cho dịch vụ lưu trú.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function stayOptionRules(bool $requireName): array
    {
        return [
            'locale' => 'nullable|string|max:12',
            'code' => 'nullable|string|max:64',
            'name' => ($requireName ? 'required' : 'nullable').'|string|max:255',
            'description' => 'nullable|string|max:8000',
            'price_from' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:1|max:40',
            'bed_label' => 'nullable|string|max:240',
            'size_sqm' => 'nullable|integer|min:1|max:9999',
            'view' => 'nullable|string|max:240',
            'amenities' => 'nullable',
            'unit_type' => 'nullable|string|max:64',
            'bathroom_count' => 'nullable|integer|min:0|max:20',
            'bedroom_count' => 'nullable|integer|min:0|max:20',
            'smoking' => 'nullable|string',
            'highlights' => 'nullable',
            'beds' => 'nullable|array',
            'amenity_groups' => 'nullable|array',
            'photos' => 'nullable|array',
            'beds_json' => 'nullable|string',
            'amenity_groups_json' => 'nullable|string',
            'photos_json' => 'nullable|string',
            'rate_options' => 'nullable|array',
            'rate_options_json' => 'nullable|string',
            'comfort_score' => 'nullable|numeric|min:0|max:10',
            'comfort_reviews' => 'nullable|integer|min:0',
            'scarcity' => 'nullable|string|max:240',
            'scarcity_active' => 'nullable|boolean',
            'deal_key' => 'nullable|string|max:64',
            'room_id' => 'nullable|string|max:64',
            'hash' => 'nullable|string|max:64',
            'crawl_dates' => 'nullable|array',
            'crawl_dates_json' => 'nullable|string',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertStayOption(Service $service, array $row, string $locale, ?int $optionId): ServiceOption
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages(['name' => ['Tên hạng phòng bắt buộc.']]);
        }

        $option = $optionId
            ? ServiceOption::query()->where('service_id', $service->id)->findOrFail($optionId)
            : new ServiceOption(['service_id' => $service->id]);

        $sort = $option->exists
            ? (int) $option->sort
            : (int) ($service->options()->max('sort') ?? -1) + 1;

        $option->fill([
            'code' => $row['code'] ?? $option->code,
            'price_from' => $row['price_from'] ?? null,
            'capacity' => $row['capacity'] ?? null,
            'sort' => $sort,
            'attrs' => $this->mergeStayOptionAttrs(is_array($option->attrs) ? $option->attrs : [], $row),
        ]);
        $option->save();

        $langId = Language::idByCode($locale);
        if ($langId) {
            ServiceOptionTranslation::query()->updateOrCreate(
                ['service_option_id' => $option->id, 'language_id' => $langId],
                [
                    'name' => $name,
                    'description' => $row['description'] ?? null,
                    'amenities' => is_array($row['amenities'] ?? null)
                        ? array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $row['amenities'])))
                        : $this->linesToArray(is_string($row['amenities'] ?? null) ? $row['amenities'] : null),
                ],
            );
        }

        return $option;
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);
        $clusters = array_keys(config('services_catalog.clusters', []));

        $request->merge([
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('title', ''))),
        ]);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:services,id',
                'cluster' => ['required', 'string', Rule::in($clusters)],
                'service_category_id' => 'nullable|integer|exists:service_categories,id',
                'service_category_ids' => 'nullable|array',
                'service_category_ids.*' => 'integer|exists:service_categories,id',
                'country_id' => 'nullable|integer|exists:countries,id',
                'code' => 'nullable|string|max:64',
                'title' => 'required|string|max:255',
                'location_label' => 'nullable|string|max:255',
                'summary' => 'nullable|string|max:5000',
                'content' => 'nullable|string',
                'featured_quote_text' => 'nullable|string|max:255',
                'featured_quote_author' => 'nullable|string|max:255',
                'highlights' => 'nullable|string',
                'inclusions' => 'nullable|string',
                'exclusions' => 'nullable|string',
                'notes' => 'nullable|string',
                'price_from' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|size:3',
                'rating' => 'nullable|numeric|min:0|max:5',
                'review_count' => 'nullable|integer|min:0',
                'star_rating' => 'nullable|integer|min:1|max:5',
                'discount_badge' => 'nullable|string|max:100',
                'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
                'sort' => 'nullable|integer|min:0',
                'is_featured' => 'nullable|boolean',
                'is_hot_deal' => 'nullable|boolean',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:320',
                'seo_keywords' => 'nullable|string|max:500',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
                'gallery_media_ids' => 'nullable|array|max:120',
                'gallery_media_ids.*' => 'integer|exists:media,id',
                'attrs' => 'nullable|array',
                'options' => 'nullable|array|max:24',
                'options.*.id' => 'nullable|integer',
                'options.*.code' => 'nullable|string|max:64',
                'options.*.name' => 'required_with:options|string|max:255',
                'options.*.description' => 'nullable|string|max:8000',
                'options.*.price_from' => 'nullable|numeric|min:0',
                'options.*.capacity' => 'nullable|integer|min:1|max:40',
                'options.*.bed_label' => 'nullable|string|max:240',
                'options.*.size_sqm' => 'nullable|integer|min:1|max:9999',
                'options.*.view' => 'nullable|string|max:240',
                'options.*.amenities' => 'nullable',
                'options.*.unit_type' => 'nullable|string|max:64',
                'options.*.bathroom_count' => 'nullable|integer|min:0|max:20',
                'options.*.bedroom_count' => 'nullable|integer|min:0|max:20',
                'options.*.smoking' => 'nullable|string',
                'options.*.highlights' => 'nullable',
                'options.*.highlights.*' => 'nullable|string|max:240',
                'options.*.beds' => 'nullable|array',
                'options.*.amenity_groups' => 'nullable|array',
                'options.*.photos' => 'nullable|array',
                'options.*.beds_json' => 'nullable|string',
                'options.*.amenity_groups_json' => 'nullable|string',
                'options.*.photos_json' => 'nullable|string',
                'options.*.rate_options' => 'nullable|array',
                'options.*.rate_options_json' => 'nullable|string',
                'options.*.comfort_score' => 'nullable|numeric|min:0|max:10',
                'options.*.comfort_reviews' => 'nullable|integer|min:0',
            'options.*.scarcity' => 'nullable|string|max:240',
            'options.*.scarcity_active' => 'nullable|boolean',
            'options.*.deal_key' => 'nullable|string|max:64',
            'options.*.room_id' => 'nullable|string|max:64',
                'options.*.hash' => 'nullable|string|max:64',
                'options.*.crawl_dates' => 'nullable|array',
                'options.*.crawl_dates_json' => 'nullable|string',
            ] + PriceTableService::validationRules());
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $service = DB::transaction(function () use ($request, $validated, $locale) {
            $cluster = $validated['cluster'];
            $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
            $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;

            $categoryIds = array_values(array_unique(array_filter(
                array_map('intval', (array) ($request->input('service_category_ids', [])))
            )));

            if ($categoryIds === [] && ! empty($validated['service_category_id'])) {
                $categoryIds = [(int) $validated['service_category_id']];
            }

            $category = ! empty($categoryIds[0])
                ? ServiceCategory::query()->find($categoryIds[0])
                : null;

            if ($category && $category->cluster !== $cluster) {
                $category = null;
            }

            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: null;
            if (! $parentId && $category) {
                $catSeo = $this->seoService()->ensureSeoFor($category, 'service_category', $locale, [
                    'slug' => $category->slug,
                    'title' => $category->name,
                    'seo_title' => $category->name,
                    'status' => $category->is_active ? 'published' : 'draft',
                    'parent_id' => $hubSeo?->id,
                ]);
                $parentId = $catSeo->id;
            }
            $parentId = $parentId ?: $hubSeo?->id;

            $seoSlug = $validated['seo_slug'] ?: Str::slug($validated['title']);

            $service = isset($validated['id'])
                ? Service::query()->findOrFail($validated['id'])
                : new Service;

            $status = $validated['status'];
            $fill = [
                'cluster' => $cluster,
                'service_category_id' => $category?->id,
                'country_id' => $validated['country_id'] ?? null,
                'code' => $validated['code'] ?? null,
                'price_from' => $validated['price_from'] ?? null,
                'currency' => strtoupper($validated['currency'] ?? 'VND'),
                'rating' => $validated['rating'] ?? $validated['rating_aggregate_star'] ?? 0,
                'review_count' => $validated['review_count'] ?? $validated['rating_aggregate_count'] ?? 0,
                'star_rating' => $validated['star_rating'] ?? null,
                'discount_badge' => $validated['discount_badge'] ?? null,
                'status' => $status,
                'is_featured' => $request->boolean('is_featured'),
                'is_hot_deal' => $request->boolean('is_hot_deal'),
                'sort' => $validated['sort'] ?? 0,
                'published_at' => $status === 'published'
                    ? ($service->published_at ?? now())
                    : null,
            ];
            if (array_key_exists('attrs', $validated)) {
                $incoming = $this->normalizeAttrs($validated['attrs'], $cluster);
                $existing = is_array($service->attrs) ? $service->attrs : [];
                $merged = $incoming === null ? $existing : array_merge($existing, $incoming);
                $fill['attrs'] = $cluster === Service::CLUSTER_STAY
                    ? \App\Support\StayFacilities::normalizeStayAttrs($merged)
                    : $merged;
            }
            $service->fill($fill);
            $service->save();

            // Đồng bộ quan hệ nhiều-nhiều categories
            if ($categoryIds !== []) {
                $service->categories()->sync($categoryIds);
            } else {
                $service->categories()->detach();
            }

            $this->saveModelTranslation(
                $service,
                ServiceTranslation::class,
                'service_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'location_label' => $validated['location_label'] ?? null,
                    'summary' => $validated['summary'] ?? null,
                    'content' => $validated['content'] ?? null,
                    'featured_quote_text' => $validated['featured_quote_text'] ?? null,
                    'featured_quote_author' => $validated['featured_quote_author'] ?? null,
                    'highlights' => $this->linesToArray($validated['highlights'] ?? null),
                    'inclusions' => $this->linesToArray($validated['inclusions'] ?? null),
                    'exclusions' => $this->linesToArray($validated['exclusions'] ?? null),
                    'notes' => $this->linesToArray($validated['notes'] ?? null),
                ],
                [
                    'title', 'location_label', 'summary', 'content',
                    'featured_quote_text', 'featured_quote_author',
                    'highlights', 'inclusions', 'exclusions', 'notes',
                ],
            );

            $this->saveSeoTranslations(
                $service,
                [
                    $locale => [
                        'slug' => $seoSlug,
                        'title' => $validated['title'],
                        'seo_title' => $validated['seo_title'] ?? $validated['title'],
                        'seo_description' => $validated['seo_description'] ?? ($validated['summary'] ?? null),
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $status === 'published' ? 'published' : 'draft',
                        'parent_id' => $parentId,
                    ],
                ],
                'service',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? $service->review_count,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? $service->rating,
                ],
            );

            app(MediaService::class)->syncCoverMediaId(
                $service,
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            if (array_key_exists('gallery_media_ids', $validated)) {
                $galleryIds = is_array($validated['gallery_media_ids']) ? $validated['gallery_media_ids'] : [];
                if ($cluster !== Service::CLUSTER_STAY || $galleryIds !== []) {
                    app(MediaService::class)->syncGalleryMediaIds($service, $galleryIds);
                }
            }

            if (isset($validated['price_table']) && is_array($validated['price_table'])) {
                app(PriceTableService::class)->sync($service, $validated['price_table'], $locale);
            }

            if (array_key_exists('options', $validated) && $cluster === Service::CLUSTER_STAY) {
                $this->syncStayOptions($service, is_array($validated['options']) ? $validated['options'] : [], $locale);
            }

            return $service->fresh([
                'categories',
                'category',
                'country.translations',
                'translations',
                'seoEntry.translations',
                'options.translations',
                'mediaAttachments.media',
            ]);
        });
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_PRICE_PERIOD', 422);
        }

        app(HtmlCacheService::class)->clearAll();

        return ApiResponse::success(
            $this->serializeDetail($service, $locale),
            isset($validated['id']) ? 'Đã cập nhật sản phẩm dịch vụ' : 'Đã tạo sản phẩm dịch vụ',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(Service $service, string $locale): array
    {
        $t = $service->translation($locale);
        $seo = $service->seoEntry?->translation($locale);
        $clusters = config('services_catalog.clusters', []);

        return [
            'id' => $service->id,
            'cluster' => $service->cluster,
            'cluster_label' => $clusters[$service->cluster]['label'] ?? $service->cluster,
            'code' => $service->code,
            'title' => $t?->title,
            'status' => $service->status,
            'price_from' => $service->price_from,
            'currency' => $service->currency,
            'sort' => $service->sort,
            'is_featured' => $service->is_featured,
            'is_hot_deal' => $service->is_hot_deal,
            'category' => $service->category ? [
                'id' => $service->category->id,
                'name' => $service->category->name,
            ] : null,
            'country' => $service->country ? [
                'id' => $service->country->id,
                'name' => $service->country->translation($locale)?->name,
            ] : null,
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
            ],
            'cover' => app(MediaService::class)->adminMediaPayload($service->coverMedia(), 'thumb'),
            'updated_at' => $service->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(Service $service, string $locale): array
    {
        $t = $service->translation($locale);
        $seo = $service->seoEntry?->translation($locale);
        $stayAttrs = is_array($service->attrs) ? $service->attrs : [];
        $stayPhotos = is_array($stayAttrs['photos'] ?? null) ? $stayAttrs['photos'] : [];

        $catIds = $service->relationLoaded('categories') && $service->categories->isNotEmpty()
            ? $service->categories->pluck('id')->all()
            : ($service->categories()->pluck('service_categories.id')->all() ?: ($service->service_category_id ? [$service->service_category_id] : []));

        return array_merge($this->serialize($service, $locale), [
            'service_category_id' => $service->service_category_id,
            'service_category_ids' => $catIds,
            'categories' => $service->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])->values()->all(),
            'country_id' => $service->country_id,
            'location_label' => $t?->location_label,
            'summary' => $t?->summary,
            'content' => $t?->content,
            'featured_quote_text' => $t?->featured_quote_text,
            'featured_quote_author' => $t?->featured_quote_author,
            'highlights' => $this->arrayToLines($t?->highlights),
            'inclusions' => $this->arrayToLines($t?->inclusions),
            'exclusions' => $this->arrayToLines($t?->exclusions),
            'notes' => $this->arrayToLines($t?->notes),
            'rating' => $service->rating !== null ? (float) $service->rating : null,
            'review_count' => $service->review_count,
            'star_rating' => $service->star_rating,
            'discount_badge' => $service->discount_badge,
            'attrs' => $stayAttrs,
            'options' => $service->cluster === Service::CLUSTER_STAY
                ? $this->serializeStayOptions($service, $locale)
                : [],
            'translated_locales' => $this->translatedLocaleCodes($service, 'title'),
            'cover' => app(MediaService::class)->adminMediaPayload($service->coverMedia(), 'card'),
            'gallery' => $service->cluster === Service::CLUSTER_STAY
                ? app(MediaService::class)->adminStayGalleryPayload($service, 'card')
                : app(MediaService::class)->adminGalleryPayload($service, 'card'),
            'gallery_count' => $service->cluster === Service::CLUSTER_STAY ? count($stayPhotos) : null,
            'price_table' => app(PriceTableService::class)->adminPayload($service, $locale),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
                'keywords' => $seo?->keywords,
                'parent_id' => $service->seoEntry?->parent_id,
                'rating_aggregate_star' => $service->seoEntry?->rating_aggregate_star !== null
                    ? (float) $service->seoEntry->rating_aggregate_star
                    : null,
                'rating_aggregate_count' => $service->seoEntry?->rating_aggregate_count !== null
                    ? (int) $service->seoEntry->rating_aggregate_count
                    : null,
            ],
        ]);
    }

    private function arrayToLines(mixed $value): string
    {
        if (! is_array($value) || $value === []) {
            return '';
        }

        return implode("\n", array_map(static fn ($v) => (string) $v, $value));
    }

    /** @param  array<string, mixed>|null  $attrs */
    private function normalizeAttrs(?array $attrs, string $cluster): ?array
    {
        if ($attrs === null) {
            return null;
        }

        if ($cluster !== Service::CLUSTER_STAY) {
            return $attrs === [] ? null : $attrs;
        }

        $out = \App\Support\StayFacilities::normalizeStayAttrs($attrs);
        if (isset($out['amenities']) && is_string($out['amenities'])) {
            $out['amenities'] = $this->linesToArray($out['amenities']);
        }
        if (isset($out['languages']) && is_string($out['languages'])) {
            $out['languages'] = $this->linesToArray($out['languages']);
        }
        if (isset($out['highlight_badges']) && is_string($out['highlight_badges'])) {
            $out['highlight_badges'] = $this->linesToArray($out['highlight_badges']);
        }
        if (isset($out['payment_cards']) && is_string($out['payment_cards'])) {
            $out['payment_cards'] = $this->linesToArray($out['payment_cards']);
        }

        return $out === [] ? null : $out;
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mergeStayOptionAttrs(array $existing, array $row): array
    {
        $patch = [];
        if (array_key_exists('bed_label', $row)) {
            $patch['bed'] = $row['bed_label'] ?: null;
            $patch['bed_label'] = $row['bed_label'] ?: null;
        }
        if (array_key_exists('size_sqm', $row)) {
            $patch['size_sqm'] = $row['size_sqm'] !== null && $row['size_sqm'] !== '' ? (int) $row['size_sqm'] : null;
        }
        if (array_key_exists('view', $row)) {
            $patch['view'] = $row['view'] ?: null;
        }
        foreach (['unit_type', 'smoking'] as $key) {
            if (array_key_exists($key, $row)) {
                $patch[$key] = $row[$key] !== '' ? $row[$key] : null;
            }
        }
        foreach (['bathroom_count', 'bedroom_count'] as $key) {
            if (array_key_exists($key, $row)) {
                $patch[$key] = $row[$key] !== null && $row[$key] !== '' ? (int) $row[$key] : null;
            }
        }
        if (array_key_exists('highlights', $row)) {
            $patch['highlights'] = is_array($row['highlights'])
                ? $row['highlights']
                : $this->linesToArray(is_string($row['highlights']) ? $row['highlights'] : null);
        }
        $beds = $row['beds'] ?? null;
        if (! is_array($beds) && ! empty($row['beds_json'])) {
            $decoded = json_decode((string) $row['beds_json'], true);
            $beds = is_array($decoded) ? $decoded : null;
        }
        if (is_array($beds)) {
            $patch['beds'] = $beds;
        }
        $groups = $row['amenity_groups'] ?? null;
        if (! is_array($groups) && ! empty($row['amenity_groups_json'])) {
            $decoded = json_decode((string) $row['amenity_groups_json'], true);
            $groups = is_array($decoded) ? $decoded : null;
        }
        if (is_array($groups)) {
            $patch['amenity_groups'] = $groups;
        }
        $photos = $row['photos'] ?? null;
        if (! is_array($photos) && ! empty($row['photos_json'])) {
            $decoded = json_decode((string) $row['photos_json'], true);
            $photos = is_array($decoded) ? $decoded : null;
        }
        if (is_array($photos)) {
            $patch['photos'] = $photos;
        }

        $rates = $row['rate_options'] ?? null;
        if (! is_array($rates) && array_key_exists('rate_options_json', $row)) {
            $raw = trim((string) $row['rate_options_json']);
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                $rates = is_array($decoded) ? $decoded : null;
            }
        }
        if (is_array($rates)) {
            $patch['rate_options'] = $rates;
        }

        if (array_key_exists('comfort_score', $row)) {
            $patch['comfort_score'] = $row['comfort_score'] !== null && $row['comfort_score'] !== ''
                ? (float) $row['comfort_score']
                : null;
        }
        if (array_key_exists('comfort_reviews', $row)) {
            $patch['comfort_reviews'] = $row['comfort_reviews'] !== null && $row['comfort_reviews'] !== ''
                ? (int) $row['comfort_reviews']
                : null;
        }
        if (array_key_exists('scarcity', $row)) {
            $patch['scarcity'] = $row['scarcity'] !== '' ? $row['scarcity'] : null;
        }
        if (array_key_exists('scarcity_active', $row)) {
            $patch['scarcity_active'] = filter_var($row['scarcity_active'], FILTER_VALIDATE_BOOLEAN);
            if ($patch['scarcity_active']) {
                unset($patch['scarcity']);
            }
        }
        if (array_key_exists('deal_key', $row)) {
            $dealKey = trim((string) $row['deal_key']);
            $patch['deal_key'] = $dealKey !== '' ? $dealKey : null;
            // Đồng bộ nhãn ưu đãi xuống từng rate nếu admin đổi select phòng.
            $existingRates = is_array($rates) ? $rates : (is_array($existing['rate_options'] ?? null) ? $existing['rate_options'] : []);
            if ($existingRates !== [] && $patch['deal_key']) {
                foreach ($existingRates as &$er) {
                    if (is_array($er)) {
                        $er['deal_key'] = $patch['deal_key'];
                    }
                }
                unset($er);
                $rates = $existingRates;
            }
        }
        foreach (['room_id', 'hash'] as $key) {
            if (array_key_exists($key, $row)) {
                $patch[$key] = $row[$key] !== '' ? $row[$key] : null;
            }
        }
        $crawlDates = $row['crawl_dates'] ?? null;
        if (! is_array($crawlDates) && array_key_exists('crawl_dates_json', $row)) {
            $raw = trim((string) $row['crawl_dates_json']);
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                $crawlDates = is_array($decoded) ? $decoded : null;
            }
        }
        if (is_array($crawlDates)) {
            $patch['crawl_dates'] = $crawlDates;
        }
        if (is_array($rates)) {
            $dates = is_array($patch['crawl_dates'] ?? null)
                ? $patch['crawl_dates']
                : (is_array($existing['crawl_dates'] ?? null) ? $existing['crawl_dates'] : null);
            $patch['rate_options'] = \App\Support\StayRateCopy::enrichRateOptions(
                $rates,
                $dates,
                (string) ($patch['deal_key'] ?? $existing['deal_key'] ?? \App\Support\StayRateCopy::DEFAULT_DEAL_KEY),
            );
        }

        return array_filter(
            array_merge($existing, $patch),
            fn ($v) => $v !== null && $v !== '',
        );
    }

    /** @return list<string> */
    private function linesToArray(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: [])));
    }

    /** @param  list<array<string, mixed>>  $options */
    private function syncStayOptions(Service $service, array $options, string $locale): void
    {
        $langId = Language::idByCode($locale);
        $keptIds = [];

        foreach ($options as $sort => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $option = ! empty($row['id'])
                ? ServiceOption::query()->where('service_id', $service->id)->find($row['id'])
                : null;
            if (! $option) {
                $option = new ServiceOption(['service_id' => $service->id]);
            }

            $option->fill([
                'code' => $row['code'] ?? $option->code,
                'price_from' => $row['price_from'] ?? null,
                'capacity' => $row['capacity'] ?? null,
                'sort' => $sort,
                'attrs' => $this->mergeStayOptionAttrs(is_array($option->attrs) ? $option->attrs : [], $row),
            ]);
            $option->save();
            $keptIds[] = $option->id;

            if ($langId) {
                ServiceOptionTranslation::query()->updateOrCreate(
                    ['service_option_id' => $option->id, 'language_id' => $langId],
                    [
                        'name' => $name,
                        'description' => $row['description'] ?? null,
                        'amenities' => is_array($row['amenities'] ?? null)
                            ? array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $row['amenities'])))
                            : $this->linesToArray(is_string($row['amenities'] ?? null) ? $row['amenities'] : null),
                    ],
                );
            }
        }

        $service->options()->whereNotIn('id', $keptIds)->delete();
    }

    /** @return list<array<string, mixed>> */
    private function serializeStayOptions(Service $service, string $locale): array
    {
        return $service->options->map(
            fn (ServiceOption $opt) => $this->serializeStayOption($opt, $locale),
        )->values()->all();
    }

    /** @return array<string, mixed> */
    private function serializeStayOption(ServiceOption $opt, string $locale): array
    {
        $t = $opt->translation($locale);
        $attrs = is_array($opt->attrs) ? $opt->attrs : [];

        return [
            'id' => $opt->id,
            'code' => $opt->code,
            'name' => $t?->name ?? '',
            'description' => $t?->description ?? '',
            'price_from' => $opt->price_from,
            'capacity' => $opt->capacity,
            'bed_label' => $attrs['bed'] ?? $attrs['bed_label'] ?? '',
            'size_sqm' => $attrs['size_sqm'] ?? null,
            'view' => $attrs['view'] ?? '',
            'unit_type' => $attrs['unit_type'] ?? '',
            'bathroom_count' => $attrs['bathroom_count'] ?? null,
            'bedroom_count' => $attrs['bedroom_count'] ?? null,
            'smoking' => $attrs['smoking'] ?? '',
            'highlights' => $this->arrayToLines($attrs['highlights'] ?? null),
            'beds_json' => ! empty($attrs['beds']) ? json_encode($attrs['beds'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '',
            'amenity_groups_json' => ! empty($attrs['amenity_groups'])
                ? json_encode($attrs['amenity_groups'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : '',
            'photos_json' => ! empty($attrs['photos'])
                ? json_encode($attrs['photos'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : '',
            'rate_options_json' => ! empty($attrs['rate_options'])
                ? json_encode($attrs['rate_options'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : '',
            'comfort_score' => $attrs['comfort_score'] ?? null,
            'comfort_reviews' => $attrs['comfort_reviews'] ?? null,
            'scarcity' => $attrs['scarcity'] ?? '',
            'scarcity_active' => \App\Support\StayRateCopy::scarcityActive($attrs),
            'deal_key' => $attrs['deal_key'] ?? \App\Support\StayRateCopy::DEFAULT_DEAL_KEY,
            'room_id' => isset($attrs['room_id']) ? (string) $attrs['room_id'] : '',
            'hash' => isset($attrs['hash']) ? (string) $attrs['hash'] : '',
            'crawl_dates_json' => ! empty($attrs['crawl_dates'])
                ? json_encode($attrs['crawl_dates'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : '',
            'amenities' => $this->arrayToLines($t?->amenities),
        ];
    }
}
