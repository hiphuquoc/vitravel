<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;
use App\Models\Country;
use App\Models\Language;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlogCategoryApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = BlogCategory::query()->with([
            'translations',
            'seoEntry.translations',
            'country.translations',
        ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $paginator = $query->orderBy('sort')->orderBy('id')->paginate(
            min(max($request->integer('per_page', 20), 1), 100)
        );

        $items = collect($paginator->items())->map(fn (BlogCategory $c) => $this->serialize($c, $locale));

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
        $locale = $request->string('locale', 'vi')->toString();
        $hubSeo = $this->seoService()->ensureGuideHub($locale);
        $parents = $this->seoService()->parentOptions(['guide_hub', 'blog_category']);

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'hub_seo_id' => $hubSeo->id,
            'seo_parents' => $this->mapSeoParents($parents, $locale),
            'countries' => Country::query()->with('translations')->orderBy('sort')->get()->map(
                fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->translation($locale)?->name ?? $c->code,
                    'code' => $c->code,
                ]
            )->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);
        $category = BlogCategory::query()->with([
            'translations',
            'country.translations',
            'seoEntry.translations',
            'seoEntry.parent',
        ])->findOrFail($id);

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
        BlogCategory::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa chuyên mục');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', '')),
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('slug', ''))),
        ]);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:blog_categories,id',
                'country_id' => 'nullable|integer|exists:countries,id',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:191',
                'seo_intro' => 'nullable|string',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:320',
                'seo_keywords' => 'nullable|string|max:500',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $category = DB::transaction(function () use ($request, $validated, $locale) {
            $hubSeo = $this->seoService()->ensureGuideHub($locale);
            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: $hubSeo->id;
            $seoSlug = (string) ($validated['seo_slug'] ?: $validated['slug']);

            $category = isset($validated['id'])
                ? BlogCategory::query()->findOrFail($validated['id'])
                : new BlogCategory;

            $category->fill([
                'country_id' => $validated['country_id'] ?? null,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $category->save();

            $this->saveModelTranslation(
                $category,
                BlogCategoryTranslation::class,
                'blog_category_id',
                $locale,
                [
                    'name' => $validated['name'],
                    'slug' => $seoSlug,
                    'seo_intro' => $validated['seo_intro'] ?? null,
                ],
                ['name', 'slug', 'seo_intro'],
            );

            $this->saveSeoTranslations(
                $category,
                [
                    $locale => [
                        'slug' => $seoSlug,
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $category->is_active ? 'published' : 'draft',
                        'parent_id' => $parentId,
                    ],
                ],
                'blog_category',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                ],
            );

            return $category->fresh([
                'translations',
                'country.translations',
                'seoEntry.translations',
                'seoEntry.parent',
            ]);
        });

        return ApiResponse::success(
            $this->serializeDetail($category, $locale),
            isset($validated['id']) ? 'Đã cập nhật chuyên mục' : 'Đã tạo chuyên mục',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(BlogCategory $category, string $locale): array
    {
        $t = $category->translation($locale);
        $seo = $category->seoEntry?->translation($locale);

        return [
            'id' => $category->id,
            'name' => $t?->name,
            'slug' => $t?->slug,
            'sort' => $category->sort,
            'is_active' => $category->is_active,
            'country' => $category->country ? [
                'id' => $category->country->id,
                'name' => $category->country->translation($locale)?->name,
            ] : null,
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
            ],
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(BlogCategory $category, string $locale): array
    {
        $t = $category->translation($locale);
        $seo = $category->seoEntry?->translation($locale);

        return array_merge($this->serialize($category, $locale), [
            'country_id' => $category->country_id,
            'seo_intro' => $t?->seo_intro,
            'translated_locales' => $this->translatedLocaleCodes($category, 'name'),
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
}
