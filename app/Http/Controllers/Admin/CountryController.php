<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CountryController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $query = Country::query()->with('translations');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $countries = $query->orderBy('sort')->paginate(20)->withQueryString();

        return view('admin.country.list', compact('countries'));
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $country = $request->filled('id')
            ? Country::query()->with(['translations', 'banner', 'seoEntry.translations'])->findOrFail($request->integer('id'))
            : null;

        $languages = $this->activeLanguages();
        $translation = $country?->translation($locale);
        $seoTranslation = $country?->seoEntry?->translation($locale);
        $language = $locale;
        $title = $country ? 'Chỉnh sửa quốc gia' : 'Thêm quốc gia mới';

        return view('admin.country.view', compact('country', 'locale', 'language', 'languages', 'translation', 'seoTranslation', 'title'));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:countries,id',
            'code' => 'required|string|max:10',
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
            'rating_aggregate_count' => 'nullable|integer|min:0',
            'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
            ...$this->coverImageRules(),
        ]);

        $country = DB::transaction(function () use ($request, $validated, $locale) {
            $country = isset($validated['id'])
                ? Country::query()->findOrFail($validated['id'])
                : new Country;

            $country->fill([
                'code' => strtolower($validated['code']),
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
                    ],
                ],
                'country',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                ],
            );

            $this->syncDirectCover($country, 'banner_media_id', $request, config('media.countries'));

            return $country;
        });

        return redirect()
            ->route('admin.countries.view', ['id' => $country->id, 'language' => $locale])
            ->with('success', 'Đã lưu quốc gia thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        Country::query()->findOrFail($request->integer('id'))->delete();

        return redirect()->route('admin.countries.list')->with('success', 'Đã xóa quốc gia thành công.');
    }
}
