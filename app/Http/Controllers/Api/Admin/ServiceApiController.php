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
use App\Services\MediaService;
use App\Services\PriceTableService;
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
        $parents = $this->seoService()->parentOptions($parentTypes);

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
        Service::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa sản phẩm dịch vụ');
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
                'gallery_media_ids' => 'nullable|array|max:40',
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
                'options.*.smoking' => 'nullable|string|max:120',
                'options.*.highlights' => 'nullable',
                'options.*.highlights.*' => 'nullable|string|max:240',
                'options.*.beds' => 'nullable|array',
                'options.*.amenity_groups' => 'nullable|array',
                'options.*.photos' => 'nullable|array',
                'options.*.beds_json' => 'nullable|string',
                'options.*.amenity_groups_json' => 'nullable|string',
                'options.*.photos_json' => 'nullable|string',
            ] + PriceTableService::validationRules());
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $service = DB::transaction(function () use ($request, $validated, $locale) {
            $cluster = $validated['cluster'];
            $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
            $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;

            $category = ! empty($validated['service_category_id'])
                ? ServiceCategory::query()->find($validated['service_category_id'])
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
                $fill['attrs'] = $incoming === null ? $existing : array_merge($existing, $incoming);
            }
            $service->fill($fill);
            $service->save();

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
                app(MediaService::class)->syncGalleryMediaIds(
                    $service,
                    is_array($validated['gallery_media_ids']) ? $validated['gallery_media_ids'] : [],
                );
            }

            if (isset($validated['price_table']) && is_array($validated['price_table'])) {
                app(PriceTableService::class)->sync($service, $validated['price_table'], $locale);
            }

            if (array_key_exists('options', $validated) && $cluster === Service::CLUSTER_STAY) {
                $this->syncStayOptions($service, is_array($validated['options']) ? $validated['options'] : [], $locale);
            }

            return $service->fresh([
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

        return array_merge($this->serialize($service, $locale), [
            'service_category_id' => $service->service_category_id,
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
            'attrs' => is_array($service->attrs) ? $service->attrs : [],
            'options' => $service->cluster === Service::CLUSTER_STAY
                ? $this->serializeStayOptions($service, $locale)
                : [],
            'translated_locales' => $this->translatedLocaleCodes($service, 'title'),
            'cover' => app(MediaService::class)->adminMediaPayload($service->coverMedia(), 'card'),
            'gallery' => app(MediaService::class)->adminGalleryPayload($service, 'card'),
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

        $out = $attrs;
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
        foreach (['amenity_groups', 'nearby_groups', 'review_scores'] as $jsonKey) {
            if (! isset($out[$jsonKey]) || ! is_string($out[$jsonKey])) {
                continue;
            }
            $decoded = json_decode($out[$jsonKey], true);
            if (is_array($decoded)) {
                $out[$jsonKey] = $decoded;
            }
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
        return $service->options->map(function (ServiceOption $opt) use ($locale) {
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
                'amenities' => $this->arrayToLines($t?->amenities),
            ];
        })->values()->all();
    }
}
