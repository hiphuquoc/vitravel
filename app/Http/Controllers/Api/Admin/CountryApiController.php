<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use App\Services\MediaService;
use App\Support\ApiResponse;
use App\Support\ProjectUnique;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CountryApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = Country::query()->with([
            'translations',
            'seoEntry.translations',
            'banner',
            'listingBanner',
        ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $paginator = $query->orderBy('sort')->orderBy('id')->paginate(
            min(max($request->integer('per_page', 20), 1), 100)
        );

        $items = collect($paginator->items())->map(fn (Country $c) => $this->serialize($c, $locale));

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
        app()->setLocale($locale);

        $hubSeo = $this->seoService()->ensureToursHub($locale);
        $parents = $this->seoService()->parentOptions('tours_hub');

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'hub_seo_id' => $hubSeo->id,
            'seo_parents' => $this->mapSeoParents($parents, $locale),
            'home_grid_sizes' => [
                ['value' => 'small', 'label' => 'Small'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'large', 'label' => 'Large'],
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $country = Country::query()
            ->with([
                'translations',
                'banner',
                'listingBanner',
                'seoEntry.translations',
                'seoEntry.parent.translations',
            ])
            ->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($country, $locale));
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
        Country::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa quốc gia');
    }

    public function setActive(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'is_active' => 'required|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $country = Country::query()->findOrFail($id);
        $country->is_active = (bool) $validated['is_active'];
        $country->save();

        return ApiResponse::success([
            'id' => $country->id,
            'is_active' => $country->is_active,
        ], $country->is_active ? 'Đã bật' : 'Đã tắt');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $request->merge([
            'code' => strtolower((string) $request->input('code', '')),
            'slug' => Str::slug((string) $request->input('slug', '')),
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('slug', ''))),
        ]);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:countries,id',
                'code' => [
                    'required',
                    'string',
                    'max:10',
                    ProjectUnique::softDeleting('countries', 'code')
                        ->ignore($request->integer('id') ?: null),
                ],
                'home_grid_size' => 'nullable|string|max:20',
                'sort' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'show_in_menu' => 'nullable|boolean',
                'show_in_customize_form' => 'nullable|boolean',
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:191',
                'tagline' => 'nullable|string|max:255',
                'intro_text' => 'nullable|string',
                'long_form_content' => 'nullable|string',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:320',
                'seo_keywords' => 'nullable|string|max:500',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'banner_media_id' => 'nullable|integer|exists:media,id',
                'remove_banner' => 'nullable|boolean',
                'listing_banner_media_id' => 'nullable|integer|exists:media,id',
                'remove_listing_banner' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $country = DB::transaction(function () use ($request, $validated, $locale) {
            $hubSeo = $this->seoService()->ensureToursHub($locale);
            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: $hubSeo->id;

            $country = isset($validated['id'])
                ? Country::query()->findOrFail($validated['id'])
                : new Country;

            $country->fill([
                'code' => $validated['code'],
                'home_grid_size' => $validated['home_grid_size'] ?? 'medium',
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
                'show_in_menu' => $request->boolean('show_in_menu', true),
                'show_in_customize_form' => $request->boolean('show_in_customize_form', true),
            ]);
            $country->save();

            $this->saveModelTranslation(
                $country,
                CountryTranslation::class,
                'country_id',
                $locale,
                [
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'tagline' => $validated['tagline'] ?? null,
                    'intro_text' => $validated['intro_text'] ?? null,
                    'long_form_content' => $validated['long_form_content'] ?? null,
                ],
                ['name', 'slug', 'tagline', 'intro_text', 'long_form_content'],
            );

            $ratingStar = $validated['rating_aggregate_star'] ?? null;
            $ratingCount = $validated['rating_aggregate_count'] ?? null;

            $this->saveSeoTranslations(
                $country,
                [
                    $locale => [
                        'slug' => $validated['seo_slug'] ?? $validated['slug'],
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $country->is_active ? 'published' : 'draft',
                        'country_code' => $country->code,
                        'parent_id' => $parentId,
                        'rating_aggregate_star' => $ratingStar,
                        'rating_aggregate_count' => $ratingCount,
                    ],
                ],
                'country',
                [
                    'rating_aggregate_count' => $ratingCount,
                    'rating_aggregate_star' => $ratingStar,
                ],
            );

            $media = app(MediaService::class);
            $media->syncDirectMediaId(
                $country,
                'banner_media_id',
                isset($validated['banner_media_id']) ? (int) $validated['banner_media_id'] : null,
                $request->boolean('remove_banner'),
            );
            $media->syncDirectMediaId(
                $country,
                'listing_banner_media_id',
                isset($validated['listing_banner_media_id']) ? (int) $validated['listing_banner_media_id'] : null,
                $request->boolean('remove_listing_banner'),
            );

            return $country->fresh([
                'translations',
                'banner',
                'listingBanner',
                'seoEntry.translations',
                'seoEntry.parent.translations',
            ]);
        });

        return ApiResponse::success(
            $this->serializeDetail($country, $locale),
            isset($validated['id']) ? 'Đã cập nhật quốc gia' : 'Đã tạo quốc gia',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(Country $country, string $locale): array
    {
        $t = $country->translation($locale);
        $seo = $country->seoEntry?->translation($locale);
        $media = app(MediaService::class);

        return [
            'id' => $country->id,
            'code' => $country->code,
            'name' => $t?->name,
            'slug' => $t?->slug,
            'sort' => $country->sort,
            'is_active' => $country->is_active,
            'show_in_menu' => $country->show_in_menu,
            'home_grid_size' => $country->home_grid_size,
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
            ],
            'banner' => $media->adminMediaPayload($country->banner, 'thumb'),
            'updated_at' => $country->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(Country $country, string $locale): array
    {
        $t = $country->translation($locale);
        $seo = $country->seoEntry?->translation($locale);
        $media = app(MediaService::class);

        return array_merge($this->serialize($country, $locale), [
            'show_in_customize_form' => $country->show_in_customize_form,
            'tagline' => $t?->tagline,
            'intro_text' => $t?->intro_text,
            'long_form_content' => $t?->long_form_content,
            'translated_locales' => $this->translatedLocaleCodes($country, 'name'),
            'banner' => $media->adminMediaPayload($country->banner, 'card'),
            'listing_banner' => $media->adminMediaPayload($country->listingBanner, 'lg'),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
                'keywords' => $seo?->keywords,
                'parent_id' => $country->seoEntry?->parent_id,
                'rating_aggregate_star' => $country->seoEntry?->rating_aggregate_star !== null
                    ? (float) $country->seoEntry->rating_aggregate_star
                    : null,
                'rating_aggregate_count' => $country->seoEntry?->rating_aggregate_count !== null
                    ? (int) $country->seoEntry->rating_aggregate_count
                    : null,
            ],
        ]);
    }
}
