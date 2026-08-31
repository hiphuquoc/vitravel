<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Language;
use App\Models\TourCategory;
use App\Models\TourCategoryTranslation;
use App\Services\MediaService;
use App\Support\ApiResponse;
use App\Support\ListingFields;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TourCategoryApiController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = TourCategory::query()
            ->with([
                'country.translations',
                'translations',
                'seoEntry.translations',
                'mediaAttachments.media',
            ]);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $paginator = $query->orderBy('sort')->orderByDesc('id')->paginate(
            min(max($request->integer('per_page', 20), 1), 100)
        );

        $items = collect($paginator->items())->map(fn (TourCategory $c) => $this->serialize($c, $locale));

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'type_options' => collect(TourCategory::typeOptions())
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $category = TourCategory::query()
            ->with(['translations', 'country.translations', 'seoEntry.translations', 'mediaAttachments.media'])
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
        $row = TourCategory::query()->findOrFail($id);
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return ApiResponse::success(null, 'Đã xóa danh mục tour (kèm media & quan hệ)');
    }

    public function meta(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $countries = Country::query()
            ->with('translations')
            ->orderBy('sort')
            ->get()
            ->map(fn (Country $c) => [
                'id' => $c->id,
                'name' => $c->translation($locale)?->name ?? $c->code,
            ]);

        return ApiResponse::success([
            'countries' => $countries,
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'type_options' => collect(TourCategory::typeOptions())
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
            'seo_parents' => $this->mapSeoParents(
                $this->seoService()->parentOptionsForType('tour_category'),
                $locale,
            ),
        ]);
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        // Canonical AI/listing aliases → cột DB
        ListingFields::mergeAliases($request, [
            'description' => 'subtitle',
            'seo_intro' => 'seo_body',
        ]);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:tour_categories,id',
                'country_id' => 'nullable|integer|exists:countries,id',
                'type' => 'required|string|in:'.implode(',', array_keys(TourCategory::typeOptions())),
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:191',
                'description' => 'nullable|string',
                'seo_intro' => 'nullable|string',
                'subtitle' => 'nullable|string',
                'seo_body' => 'nullable|string',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:350',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $category = DB::transaction(function () use ($request, $validated, $locale) {
            $category = isset($validated['id'])
                ? TourCategory::query()->findOrFail($validated['id'])
                : new TourCategory;

            $category->fill([
                'country_id' => $validated['country_id'] ?? null,
                'type' => $validated['type'],
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $category->save();

            $this->saveModelTranslation(
                $category,
                TourCategoryTranslation::class,
                'tour_category_id',
                $locale,
                [
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? null,
                    'seo_intro' => $validated['seo_intro'] ?? null,
                ],
                ['name', 'slug', 'description', 'seo_intro'],
            );

            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: null;
            if (! $parentId) {
                $parentId = $this->seoService()->ensureToursHub($locale)->id;
            }

            $ratingStar = $validated['rating_aggregate_star'] ?? null;
            $ratingCount = $validated['rating_aggregate_count'] ?? null;

            $this->saveSeoTranslations(
                $category,
                [
                    $locale => [
                        'slug' => $validated['seo_slug'] ?? $validated['slug'],
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'status' => $category->is_active ? 'published' : 'draft',
                        'parent_id' => $parentId,
                        'rating_aggregate_star' => $ratingStar,
                        'rating_aggregate_count' => $ratingCount,
                    ],
                ],
                'tour_category',
                [
                    'rating_aggregate_star' => $ratingStar,
                    'rating_aggregate_count' => $ratingCount,
                ],
            );

            app(MediaService::class)->syncCoverMediaId(
                $category,
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            return $category->fresh([
                'translations',
                'country.translations',
                'seoEntry.translations',
                'mediaAttachments.media',
            ]);
        });

        return ApiResponse::success(
            $this->serializeDetail($category, $locale),
            isset($validated['id']) ? 'Đã cập nhật danh mục' : 'Đã tạo danh mục',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(TourCategory $category, string $locale): array
    {
        $t = $category->translation($locale);
        $seo = $category->seoEntry?->translation($locale);
        $types = TourCategory::typeOptions();

        return [
            'id' => $category->id,
            'type' => $category->type,
            'type_label' => $types[$category->type] ?? $category->type,
            'name' => $t?->name,
            'slug' => $t?->slug,
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
            ],
            'sort' => $category->sort,
            'is_active' => $category->is_active,
            'country' => $category->country ? [
                'id' => $category->country->id,
                'name' => $category->country->translation($locale)?->name,
            ] : null,
            'cover' => app(MediaService::class)->adminMediaPayload($category->coverMedia(), 'thumb'),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(TourCategory $category, string $locale): array
    {
        $t = $category->translation($locale);
        $seo = $category->seoEntry?->translation($locale);

        return array_merge($this->serialize($category, $locale), [
            'country_id' => $category->country_id,
            'description' => $t?->description,
            'seo_intro' => $t?->seo_intro,
            // Canonical listing / AI aliases (map → description / seo_intro)
            'subtitle' => $t?->description,
            'seo_body' => $t?->seo_intro,
            'translated_locales' => $this->translatedLocaleCodes($category, 'name'),
            'cover' => app(MediaService::class)->adminMediaPayload($category->coverMedia(), 'card'),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
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
}
