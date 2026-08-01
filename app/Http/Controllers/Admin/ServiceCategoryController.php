<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString() ?: 'vi';
        $cluster = $request->string('cluster')->toString();
        $clusters = config('services_catalog.clusters', []);

        if ($cluster !== '' && ! isset($clusters[$cluster])) {
            abort(404);
        }

        $query = ServiceCategory::query()
            ->with(['banner', 'seoEntry.translations', 'seoEntry.parent.translations'])
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

        $categories = $query->get();

        $hubSeo = null;
        $hubKey = null;
        if ($cluster !== '') {
            $hubKey = $clusters[$cluster]['hub_key'] ?? null;
            if ($hubKey) {
                $hubSeo = $this->seoService()->ensureHub($hubKey, $locale);
                $hubSeo->load(['translations', 'reference']);
            }
        }

        $title = $cluster !== ''
            ? ('Danh mục — '.($clusters[$cluster]['label'] ?? $cluster))
            : 'Danh mục dịch vụ';

        return view('admin.service-category.list', [
            'categories' => $categories,
            'clusters' => $clusters,
            'cluster' => $cluster,
            'hubSeo' => $hubSeo,
            'hubKey' => $hubKey,
            'locale' => $locale,
            'title' => $title,
        ]);
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString() ?: 'vi';
        $clusters = config('services_catalog.clusters', []);
        $cluster = $request->string('cluster')->toString();

        $id = $request->integer('id');
        $category = $id > 0
            ? ServiceCategory::query()->with(['banner', 'seoEntry.translations', 'seoEntry.parent'])->findOrFail($id)
            : null;

        if ($category) {
            $cluster = $category->cluster;
        }

        if ($cluster === '' || ! isset($clusters[$cluster])) {
            $cluster = array_key_first($clusters) ?: 'stay';
        }

        $hubKey = $clusters[$cluster]['hub_key'] ?? null;
        $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;

        $seoTranslation = $category?->seoEntry?->translation($locale);
        $parents = $hubKey
            ? $this->seoService()->parentOptions($hubKey)
            : $this->seoService()->parentOptions([
                'trains_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub',
            ]);
        $defaultParentId = $category?->seoEntry?->parent_id ?: $hubSeo?->id;

        $clusterOptions = collect($clusters)->mapWithKeys(
            fn (array $cfg, string $key) => [$key => $cfg['label'] ?? $key]
        )->all();

        return view('admin.service-category.view', [
            'category' => $category,
            'locale' => $locale,
            'language' => $locale,
            'seoTranslation' => $seoTranslation,
            'parents' => $parents,
            'defaultParentId' => $defaultParentId,
            'hubSeo' => $hubSeo,
            'hubKey' => $hubKey,
            'cluster' => $cluster,
            'clusterOptions' => $clusterOptions,
            'title' => $category ? 'Chỉnh sửa danh mục dịch vụ' : 'Thêm danh mục dịch vụ',
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $this->assertUploadedFileOk($request);

        $clusters = array_keys(config('services_catalog.clusters', []));

        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', '')),
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('slug', ''))),
        ]);

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:service_categories,id',
            'cluster' => ['required', 'string', Rule::in($clusters)],
            'name' => 'required|string|max:255',
            'intro' => 'nullable|string|max:2000',
            'slug' => [
                'required',
                'string',
                'max:64',
                Rule::unique('service_categories', 'slug')
                    ->ignore($request->integer('id') ?: null)
                    ->where('cluster', $request->input('cluster'))
                    ->whereNull('deleted_at'),
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
            ...$this->coverImageRules(),
        ]);

        $category = DB::transaction(function () use ($request, $validated) {
            $locale = $request->string('language', 'vi')->toString() ?: 'vi';
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

            $this->syncDirectCover($category, 'banner_media_id', $request, config('media.service_categories'));

            return $category;
        });

        return redirect()
            ->route('admin.serviceCategories.view', [
                'id' => $category->id,
                'cluster' => $category->cluster,
            ])
            ->with('success', 'Đã lưu danh mục dịch vụ thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $category = ServiceCategory::query()->findOrFail($request->integer('id'));
        $cluster = $category->cluster;
        $category->delete();

        return redirect()
            ->route('admin.serviceCategories.list', ['cluster' => $cluster])
            ->with('success', 'Đã xóa danh mục dịch vụ thành công.');
    }
}
