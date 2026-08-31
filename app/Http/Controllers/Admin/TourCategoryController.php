<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\TourCategory;
use App\Models\TourCategoryTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TourCategoryController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $query = TourCategory::query()
            ->with(['country.translations', 'translations', 'seoEntry.translations']);

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

        $categories = $query->orderBy('sort')->orderByDesc('id')->paginate(20)->withQueryString();
        $countries = Country::query()->with('translations')->orderBy('sort')->get();
        $typeOptions = TourCategory::typeOptions();
        $title = 'Danh mục Tour';

        return view('admin.tour-category.list', compact('categories', 'countries', 'typeOptions', 'title'));
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $language = $locale;

        $category = $request->filled('id')
            ? TourCategory::query()
                ->with(['translations', 'country.seoEntry.translations', 'seoEntry.translations', 'mediaAttachments.media'])
                ->findOrFail($request->integer('id'))
            : null;

        $countries = Country::query()->with('translations')->orderBy('sort')->get();
        $languages = $this->activeLanguages();
        $translation = $category?->translation($locale);
        $seoTranslation = $category?->seoEntry?->translation($locale);
        $typeOptions = TourCategory::typeOptions();
        $parents = $this->seoService()->parentOptionsForType('tour_category');

        if ($parents->isEmpty()) {
            $this->seoService()->ensureToursHub($locale);
            $parents = $this->seoService()->parentOptionsForType('tour_category');
        }

        $title = $category ? 'Chỉnh sửa danh mục tour' : 'Thêm danh mục tour';

        return view('admin.tour-category.view', compact(
            'category', 'locale', 'language', 'countries', 'languages',
            'translation', 'seoTranslation', 'typeOptions', 'parents', 'title',
        ));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

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
            'seo_slug' => 'nullable|string|max:191',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:350',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
            'rating_aggregate_count' => 'nullable|integer|min:0',
            'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
            ...$this->coverImageRules(),
        ]);

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

            $category->load(['country.seoEntry.translations']);

            $country = $category->country;
            $countryCode = $country?->code ?? 'vn';
            $countrySlug = $country?->translation($locale)?->slug
                ?? $country?->translation()?->slug
                ?? $countryCode;

            $parentId = $validated['seo_parent_id']
                ?? $this->seoService()->ensureToursHub($locale)->id;

            $this->saveSeoTranslations(
                $category,
                [
                    $locale => [
                        'slug' => $validated['seo_slug'] ?? $validated['slug'],
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $category->is_active ? 'published' : 'draft',
                        'parent_id' => $parentId,
                        'country_slug' => $countrySlug,
                        'country_code' => $countryCode,
                    ],
                ],
                'tour_category',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                ],
            );

            $this->syncCoverAttachment($category, $request, config('media.tour_categories'));

            return $category;
        });

        return redirect()
            ->route('admin.tourCategories.view', ['id' => $category->id, 'language' => $locale])
            ->with('success', 'Đã lưu danh mục tour thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $row = TourCategory::query()->findOrFail($request->integer('id'));
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return redirect()->route('admin.tourCategories.list')->with('success', 'Đã xóa danh mục tour thành công.');
    }
}
