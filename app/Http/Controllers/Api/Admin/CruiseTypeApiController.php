<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\CruiseType;
use App\Models\Language;
use App\Services\MediaService;
use App\Support\ApiResponse;
use App\Support\ListingFields;
use App\Support\ProjectUnique;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CruiseTypeApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = CruiseType::query()->with(['seoEntry.translations', 'banner', 'cover']);

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

        $paginator = $query->orderBy('sort')->orderBy('id')->paginate(
            min(max($request->integer('per_page', 50), 1), 100)
        );

        $items = collect($paginator->items())->map(fn (CruiseType $t) => $this->serialize($t, $locale));

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $type = CruiseType::query()
            ->with(['seoEntry.translations', 'seoEntry.parent.translations', 'banner', 'cover'])
            ->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($type, $locale));
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
        CruiseType::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa loại du thuyền');
    }

    public function meta(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $hubSeo = $this->seoService()->ensureCruisesHub($locale);

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'hub_seo_id' => $hubSeo->id,
            'seo_parents' => $this->mapSeoParents(
                $this->seoService()->parentOptions('cruises_hub'),
                $locale,
            ),
        ]);
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', '')),
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('slug', ''))),
        ]);

        ListingFields::mergeAliases($request, [
            'intro' => 'subtitle',
        ]);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:cruise_types,id',
                'name' => 'required|string|max:255',
                'intro' => 'nullable|string|max:2000',
                'subtitle' => 'nullable|string|max:2000',
                'seo_body' => 'nullable|string',
                'slug' => [
                    'required',
                    'string',
                    'max:64',
                    ProjectUnique::softDeleting('cruise_types', 'slug')
                        ->ignore($request->integer('id') ?: null),
                ],
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:320',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'banner_media_id' => 'nullable|integer|exists:media,id',
                'remove_banner' => 'nullable|boolean',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $type = DB::transaction(function () use ($request, $validated, $locale) {
            $type = isset($validated['id'])
                ? CruiseType::query()->findOrFail($validated['id'])
                : new CruiseType;

            $type->fill([
                'name' => $validated['name'],
                'intro' => $validated['intro'] ?? null,
                'seo_body' => $validated['seo_body'] ?? null,
                'slug' => $validated['slug'],
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $type->save();

            $hubSeo = $this->seoService()->ensureCruisesHub($locale);
            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: $hubSeo->id;
            $ratingStar = $validated['rating_aggregate_star'] ?? null;
            $ratingCount = $validated['rating_aggregate_count'] ?? null;

            $this->saveSeoTranslations(
                $type,
                [
                    $locale => [
                        'slug' => $validated['seo_slug'] ?? $validated['slug'],
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'status' => $type->is_active ? 'published' : 'draft',
                        'parent_id' => $parentId,
                        'rating_aggregate_star' => $ratingStar,
                        'rating_aggregate_count' => $ratingCount,
                    ],
                ],
                'cruise_type',
                [
                    'rating_aggregate_star' => $ratingStar,
                    'rating_aggregate_count' => $ratingCount,
                ],
            );

            $media = app(MediaService::class);
            $media->syncDirectMediaId(
                $type,
                'banner_media_id',
                isset($validated['banner_media_id']) ? (int) $validated['banner_media_id'] : null,
                $request->boolean('remove_banner'),
            );
            $media->syncDirectMediaId(
                $type,
                'cover_media_id',
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            return $type->fresh(['seoEntry.translations', 'seoEntry.parent.translations', 'banner', 'cover']);
        });

        return ApiResponse::success(
            $this->serializeDetail($type, $locale),
            isset($validated['id']) ? 'Đã cập nhật loại du thuyền' : 'Đã tạo loại du thuyền',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(CruiseType $type, string $locale): array
    {
        $seo = $type->seoEntry?->translationExact($locale);
        $slugFull = $type->seoEntry
            ? $this->seoService()->resolveEntrySlugFull($type->seoEntry, $locale)
            : null;

        return [
            'id' => $type->id,
            'name' => $type->name,
            'slug' => $type->slug,
            'intro' => $type->intro,
            'subtitle' => $type->intro,
            'seo_body' => $type->seo_body,
            'sort' => $type->sort,
            'is_active' => $type->is_active,
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $slugFull,
            ],
            'banner' => app(MediaService::class)->adminMediaPayload($type->banner, 'thumb'),
            'cover' => app(MediaService::class)->adminMediaPayload($type->cover, 'thumb'),
            'updated_at' => $type->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(CruiseType $type, string $locale): array
    {
        $seo = $type->seoEntry?->translationExact($locale);
        $slugFull = $type->seoEntry
            ? $this->seoService()->resolveEntrySlugFull($type->seoEntry, $locale)
            : null;

        return array_merge($this->serialize($type, $locale), [
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $slugFull,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
                'parent_id' => $type->seoEntry?->parent_id,
                'rating_aggregate_star' => $type->seoEntry?->rating_aggregate_star !== null
                    ? (float) $type->seoEntry->rating_aggregate_star
                    : null,
                'rating_aggregate_count' => $type->seoEntry?->rating_aggregate_count !== null
                    ? (int) $type->seoEntry->rating_aggregate_count
                    : null,
            ],
            'translated_locales' => $this->translatedLocaleCodesFromSeo($type),
            'banner' => app(MediaService::class)->adminMediaPayload($type->banner, 'lg'),
            'cover' => app(MediaService::class)->adminMediaPayload($type->cover, 'card'),
        ]);
    }

    /**
     * CruiseType không có translations bảng riêng — theo dõi qua SEO translations.
     *
     * @return list<string>
     */
    private function translatedLocaleCodesFromSeo(CruiseType $type): array
    {
        $type->loadMissing(['seoEntry.translations.language']);
        $codes = [];
        foreach ($type->seoEntry?->translations ?? [] as $row) {
            $code = $row->language?->code;
            $title = $row->seo_title ?: $row->title;
            if ($code && is_string($title) && trim($title) !== '') {
                $codes[] = (string) $code;
            }
        }

        return array_values(array_unique($codes));
    }
}
