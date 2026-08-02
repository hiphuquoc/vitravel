<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\CruiseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CruiseTypeController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $hubSeo = $this->seoService()->ensureCruisesHub($locale);
        $hubSeo->load(['translations', 'reference']);

        $types = CruiseType::query()
            ->with(['banner', 'cover', 'seoEntry.translations', 'seoEntry.parent.translations'])
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return view('admin.cruise-type.list', [
            'types' => $types,
            'hubSeo' => $hubSeo,
            'locale' => $locale,
            'title' => 'Loại du thuyền',
        ]);
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $hubSeo = $this->seoService()->ensureCruisesHub($locale);

        $id = $request->integer('id');
        $type = $id > 0
            ? CruiseType::query()->with(['banner', 'cover', 'seoEntry.translations', 'seoEntry.parent'])->findOrFail($id)
            : null;

        $seoTranslation = $type?->seoEntry?->translation($locale);
        $parents = $this->seoService()->parentOptions('cruises_hub');
        $defaultParentId = $type?->seoEntry?->parent_id ?: $hubSeo->id;

        return view('admin.cruise-type.view', [
            'type' => $type,
            'locale' => $locale,
            'language' => $locale,
            'seoTranslation' => $seoTranslation,
            'parents' => $parents,
            'defaultParentId' => $defaultParentId,
            'hubSeo' => $hubSeo,
            'title' => $type ? 'Chỉnh sửa loại du thuyền' : 'Thêm loại du thuyền',
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $this->assertUploadedFileOk($request);
        $this->assertUploadedFileOk($request, 'cover');

        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', '')),
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('slug', ''))),
        ]);

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:cruise_types,id',
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:64',
                Rule::unique('cruise_types', 'slug')
                    ->ignore($request->integer('id') ?: null)
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
            ...$this->coverImageRules('cover', 'remove_cover'),
        ]);

        $type = DB::transaction(function () use ($request, $validated) {
            $locale = $request->string('language', 'vi')->toString() ?: 'vi';
            $hubSeo = $this->seoService()->ensureCruisesHub($locale);
            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: $hubSeo->id;
            $seoSlug = $validated['seo_slug'] ?? $validated['slug'];

            $type = isset($validated['id'])
                ? CruiseType::query()->findOrFail($validated['id'])
                : new CruiseType;

            $type->fill([
                'name' => $validated['name'],
                'slug' => $seoSlug,
                'sort' => $validated['sort'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);
            $type->save();

            $this->saveSeoTranslations(
                $type,
                [
                    $locale => [
                        'slug' => $seoSlug,
                        'title' => $validated['name'],
                        'seo_title' => $validated['seo_title'] ?? $validated['name'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $type->is_active ? 'published' : 'draft',
                        'parent_id' => $parentId,
                    ],
                ],
                'cruise_type',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                ],
            );

            $this->syncDirectCover($type, 'banner_media_id', $request, config('media.cruise_types'));
            $this->syncDirectCover(
                $type,
                'cover_media_id',
                $request,
                config('media.cruise_types'),
                'cover',
                'remove_cover',
            );

            return $type;
        });

        return redirect()
            ->route('admin.cruiseTypes.view', ['id' => $type->id])
            ->with('success', 'Đã lưu loại du thuyền thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        CruiseType::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.cruiseTypes.list')->with('success', 'Đã xóa loại du thuyền thành công.');
    }
}
