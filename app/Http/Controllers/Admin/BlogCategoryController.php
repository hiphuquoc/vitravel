<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $hubSeo = $this->seoService()->ensureGuideHub($locale);
        $hubSeo->load(['translations', 'reference']);

        $query = BlogCategory::query()->with([
            'translations',
            'seoEntry.translations',
            'seoEntry.parent.translations',
            'country.translations',
        ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $categories = $query->orderBy('sort')->orderBy('id')->get();

        return view('admin.blog-category.list', [
            'categories' => $categories,
            'hubSeo' => $hubSeo,
            'locale' => $locale,
            'title' => 'Chuyên mục Blog',
        ]);
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $hubSeo = $this->seoService()->ensureGuideHub($locale);

        $category = $request->filled('id')
            ? BlogCategory::query()->with([
                'translations',
                'country.translations',
                'seoEntry.translations',
                'seoEntry.parent',
            ])->findOrFail($request->integer('id'))
            : null;

        $languages = $this->activeLanguages();
        $translation = $category?->translation($locale);
        $seoTranslation = $category?->seoEntry?->translation($locale);
        $countries = Country::query()->with('translations')->orderBy('sort')->get();
        $parents = $this->seoService()->parentOptionsForType(
            'blog_category',
            $category?->seoEntry?->id,
        );
        $defaultParentId = $category ? $category->seoEntry?->parent_id : $hubSeo->id;
        $title = $category ? 'Chỉnh sửa chuyên mục' : 'Thêm chuyên mục mới';

        return view('admin.blog-category.view', compact(
            'category', 'locale', 'languages', 'translation', 'seoTranslation',
            'countries', 'parents', 'defaultParentId', 'hubSeo', 'title',
        ));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

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

        $category = DB::transaction(function () use ($request, $validated, $locale) {
            $hubSeo = $this->seoService()->ensureGuideHub($locale);
            $parentId = $request->has('seo_parent_id') ? ((int) $request->input('seo_parent_id') ?: null) : $hubSeo->id;
            $seoSlug = Str::slug((string) ($validated['seo_slug'] ?? $validated['slug']));

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

            return $category;
        });

        return redirect()
            ->route('admin.blogCategories.view', ['id' => $category->id, 'language' => $locale])
            ->with('success', 'Đã lưu chuyên mục thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        BlogCategory::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.blogCategories.list')->with('success', 'Đã xóa chuyên mục thành công.');
    }
}
