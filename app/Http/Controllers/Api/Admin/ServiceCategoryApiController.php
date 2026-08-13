<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\ServiceCategory;
use App\Services\MediaService;
use App\Services\ViewDataService;
use App\Support\ApiResponse;
use App\Support\ListingFields;
use App\Support\ProjectUnique;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceCategoryApiController extends Controller
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

        $query = ServiceCategory::query()
            ->with(['banner', 'cover', 'seoEntry.translations'])
            ->orderBy('cluster')
            ->orderBy('sort')
            ->orderBy('id');

        if ($cluster !== '') {
            $query->forCluster($cluster);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $items = collect($paginator->items())->map(fn (ServiceCategory $c) => $this->serialize($c, $locale));

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
        $clusters = config('services_catalog.clusters', []);
        $requested = $request->string('cluster')->toString();
        $cluster = $requested !== '' ? $viewData->resolveAdminServiceCluster($requested) : '';

        $hubKey = ($cluster !== '' && isset($clusters[$cluster]))
            ? ($clusters[$cluster]['hub_key'] ?? null)
            : null;
        $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;
        $parents = $hubKey
            ? $this->seoService()->parentOptions($hubKey)
            : $this->seoService()->parentOptions([
                'trains_hub', 'ferries_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub',
            ]);

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'cluster' => $cluster !== '' ? $cluster : null,
            'clusters' => $viewData->adminServiceClusterOptions(),
            'hub_seo_id' => $hubSeo?->id,
            'seo_parents' => $this->mapSeoParents($parents, $locale),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);

        $category = ServiceCategory::query()
            ->with(['banner', 'cover', 'seoEntry.translations', 'seoEntry.parent.translations'])
            ->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($category, $locale));
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
        ServiceCategory::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa danh mục dịch vụ');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);
        $clusters = array_keys(config('services_catalog.clusters', []));

        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', '')),
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('slug', ''))),
        ]);

        ListingFields::mergeAliases($request, [
            'intro' => 'subtitle',
        ]);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:service_categories,id',
                'cluster' => ['required', 'string', Rule::in($clusters)],
                'name' => 'required|string|max:255',
                'intro' => 'nullable|string|max:2000',
                'subtitle' => 'nullable|string|max:2000',
                'seo_body' => 'nullable|string',
                'slug' => [
                    'required',
                    'string',
                    'max:64',
                    ProjectUnique::softDeleting('service_categories', 'slug')
                        ->ignore($request->integer('id') ?: null)
                        ->where('cluster', $request->input('cluster')),
                ],
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:320',
                'seo_keywords' => 'nullable|string|max:500',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'banner_media_id' => 'nullable|integer|exists:media,id',
                'remove_banner' => 'nullable|boolean',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $category = DB::transaction(function () use ($request, $validated, $locale) {
            $cluster = $validated['cluster'];
            $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
            $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;
            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: ($hubSeo?->id ?? null);
            $seoSlug = $validated['seo_slug'] ?? $validated['slug'];

            $category = isset($validated['id'])
                ? ServiceCategory::query()->findOrFail($validated['id'])
                : new ServiceCategory;

            $category->fill([
                'cluster' => $cluster,
                'name' => $validated['name'],
                'intro' => $validated['intro'] ?? null,
                'seo_body' => $validated['seo_body'] ?? null,
                'slug' => $seoSlug,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $category->save();

            $this->saveSeoTranslations(
                $category,
                [
                    $locale => [
                        'slug' => $seoSlug,
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? ($validated['intro'] ?? null),
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $category->is_active ? 'published' : 'draft',
                        'parent_id' => $parentId,
                    ],
                ],
                'service_category',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                ],
            );

            $media = app(MediaService::class);
            $media->syncDirectMediaId(
                $category,
                'banner_media_id',
                isset($validated['banner_media_id']) ? (int) $validated['banner_media_id'] : null,
                $request->boolean('remove_banner'),
            );
            $media->syncDirectMediaId(
                $category,
                'cover_media_id',
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            return $category->fresh(['banner', 'cover', 'seoEntry.translations', 'seoEntry.parent.translations']);
        });

        return ApiResponse::success(
            $this->serializeDetail($category, $locale),
            isset($validated['id']) ? 'Đã cập nhật danh mục dịch vụ' : 'Đã tạo danh mục dịch vụ',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(ServiceCategory $category, string $locale): array
    {
        $seo = $category->seoEntry?->translation($locale);
        $clusters = config('services_catalog.clusters', []);

        return [
            'id' => $category->id,
            'cluster' => $category->cluster,
            'cluster_label' => $clusters[$category->cluster]['label'] ?? $category->cluster,
            'name' => $category->name,
            'slug' => $category->slug,
            'intro' => $category->intro,
            'subtitle' => $category->intro,
            'seo_body' => $category->seo_body ?: $category->intro,
            'sort' => $category->sort,
            'is_active' => $category->is_active,
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
            ],
            'banner' => app(MediaService::class)->adminMediaPayload($category->banner, 'thumb'),
            'cover' => app(MediaService::class)->adminMediaPayload($category->cover, 'thumb'),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(ServiceCategory $category, string $locale): array
    {
        $seo = $category->seoEntry?->translation($locale);

        return array_merge($this->serialize($category, $locale), [
            'translated_locales' => $this->translatedLocaleCodesFromSeo($category),
            'banner' => app(MediaService::class)->adminMediaPayload($category->banner, 'lg'),
            'cover' => app(MediaService::class)->adminMediaPayload($category->cover, 'card'),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
                'keywords' => $seo?->keywords,
                'parent_id' => $category->seoEntry?->parent_id,
                'rating_aggregate_star' => $category->seoEntry?->rating_aggregate_star !== null
                    ? (float) $category->seoEntry->rating_aggregate_star
                    : null,
                'rating_aggregate_count' => $category->seoEntry?->rating_aggregate_count !== null
                    ? (int) $category->seoEntry->rating_aggregate_count
                    : null,
            ],
        ]);
    }

    /** @return list<string> */
    private function translatedLocaleCodesFromSeo(ServiceCategory $category): array
    {
        $category->loadMissing(['seoEntry.translations.language']);
        $codes = [];
        foreach ($category->seoEntry?->translations ?? [] as $row) {
            $code = $row->language?->code;
            $title = $row->seo_title ?: $row->title;
            if ($code && is_string($title) && trim($title) !== '') {
                $codes[] = (string) $code;
            }
        }

        return array_values(array_unique($codes));
    }
}
