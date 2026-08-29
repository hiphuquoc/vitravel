<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CruiseType;
use App\Models\Faq;
use App\Models\Language;
use App\Models\Package;
use App\Models\PackageItineraryDay;
use App\Models\PackageTranslation;
use App\Models\TravelStyle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $type = $request->route('packageType') === 'cruise'
            ? Package::TYPE_CRUISE
            : Package::TYPE_TOUR;

        $query = Package::query()
            ->with(['country.translations', 'translations', 'seoEntry.translations'])
            ->where('type', $type);

        if ($request->filled('country_id')) {
            $countryId = $request->integer('country_id');
            $query->where(function ($q) use ($countryId) {
                $q->where('country_id', $countryId)
                    ->orWhereHas('countries', fn ($c) => $c->where('countries.id', $countryId));
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $packages = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $countries = Country::query()->with('translations')->orderBy('sort')->get();

        $title = $type === Package::TYPE_CRUISE ? 'Gói Cruise' : 'Gói Tour';

        return view('admin.package.list', compact('packages', 'countries', 'type', 'title'));
    }

    public function view(Request $request): View
    {
        $type = $request->route('packageType') === 'cruise'
            ? Package::TYPE_CRUISE
            : Package::TYPE_TOUR;

        if ($request->filled('type')) {
            $type = $request->string('type')->toString();
        }

        abort_unless(in_array($type, [Package::TYPE_TOUR, Package::TYPE_CRUISE], true), 404);

        $locale = $request->string('language', 'vi')->toString();
        $language = $locale;
        $package = null;

        if ($request->filled('id')) {
            $package = Package::query()
                ->with([
                    'translations',
                    'country.seoEntry.translations',
                    'cruiseType.seoEntry.translations',
                    'countries.translations',
                    'travelStyles.translations',
                    'itineraryDays.translations',
                    'faqs.translations',
                    'mediaAttachments.media',
                    'seoEntry.translations',
                ])
                ->findOrFail($request->integer('id'));
            $type = $package->type;
        }

        $countries = Country::query()->with('translations')->orderBy('sort')->get();
        $travelStyles = TravelStyle::query()->with('translations')->where('is_active', true)->orderBy('sort')->get();
        $cruiseTypes = CruiseType::query()->with('seoEntry.translations')->orderBy('sort')->get();
        $languages = $this->activeLanguages();
        $translation = $package?->translation($locale);
        $seoTranslation = $package?->seoEntry?->translation($locale);
        $seoType = $this->seoTypeForPackage($type);

        if ($type === Package::TYPE_CRUISE) {
            $hubSeo = $this->seoService()->ensureCruisesHub($locale);
            foreach ($cruiseTypes as $ct) {
                $this->seoService()->ensureSeoFor($ct, 'cruise_type', $locale, [
                    'slug' => $ct->slug,
                    'title' => $ct->name,
                    'seo_title' => $ct->name,
                    'status' => 'published',
                    'parent_id' => $hubSeo->id,
                ]);
            }
            $parents = $this->seoService()->parentOptionsForType('package_cruise');
            $defaultParentId = $package?->seoEntry?->parent_id
                ?? $package?->cruiseType?->seoEntry?->id
                ?? $hubSeo->id;
        } else {
            $parents = $this->seoService()->parentOptionsForType('package_tour');
            if ($parents->isEmpty()) {
                $this->seoService()->ensureToursHub($locale);
                $countriesWithSeo = Country::query()->with(['seoEntry.translations', 'translations'])->orderBy('sort')->get();
                foreach ($countriesWithSeo as $country) {
                    $this->seoService()->ensureSeoFor($country, 'country', $locale, [
                        'slug' => $country->translation($locale)?->slug ?? $country->code,
                        'title' => $country->translation($locale)?->name ?? $country->code,
                        'seo_title' => $country->translation($locale)?->name ?? $country->code,
                        'status' => 'published',
                        'country_slug' => $country->translation($locale)?->slug ?? $country->code,
                    ]);
                }
                $parents = $this->seoService()->parentOptionsForType('package_tour');
            }
            $defaultParentId = $package?->seoEntry?->parent_id
                ?? $package?->country?->seoEntry?->id;
        }

        $listRoute = $type === Package::TYPE_CRUISE ? 'admin.packages.cruises' : 'admin.packages.tours';
        $viewRoute = $type === Package::TYPE_CRUISE ? 'admin.packages.cruises.view' : 'admin.packages.tours.view';
        $saveRoute = $type === Package::TYPE_CRUISE ? 'admin.packages.cruises.save' : 'admin.packages.tours.save';
        $title = ($package ? 'Chỉnh sửa' : 'Thêm mới').' — '.($type === Package::TYPE_CRUISE ? 'Cruise' : 'Tour');

        return view('admin.package.view', compact(
            'package', 'type', 'locale', 'language', 'countries', 'travelStyles', 'cruiseTypes', 'languages',
            'translation', 'seoTranslation', 'title', 'parents', 'seoType', 'defaultParentId',
            'listRoute', 'viewRoute', 'saveRoute',
        ));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $type = $request->route('packageType') === 'cruise'
            ? Package::TYPE_CRUISE
            : $request->string('type', Package::TYPE_TOUR)->toString();
        abort_unless(in_array($type, [Package::TYPE_TOUR, Package::TYPE_CRUISE], true), 404);

        $locale = $request->string('language', 'vi')->toString();
        $seoType = $this->seoTypeForPackage($type);
        $viewRoute = $type === Package::TYPE_CRUISE ? 'admin.packages.cruises.view' : 'admin.packages.tours.view';

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:packages,id',
            'country_id' => 'required|integer|exists:countries,id',
            'country_ids' => 'required|array|min:1',
            'country_ids.*' => 'integer|exists:countries,id',
            'code' => 'nullable|string|max:64',
            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'nullable|integer|min:0',
            'price_from' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'status' => 'required|string|in:draft,published,archived',
            'discount_badge' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'sort' => 'nullable|integer|min:0',
            'cruise_type' => 'nullable|string|max:32',
            'departure_port' => 'nullable|string|max:255',
            'boat_class' => 'nullable|string|max:32',
            'nights_on_board' => 'nullable|integer|min:0',
            'title' => 'required|string|max:255',
            'start_location' => 'nullable|string|max:255',
            'end_location' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'highlights_intro' => 'nullable|string',
            'featured_quote_text' => 'nullable|string|max:255',
            'featured_quote_author' => 'nullable|string|max:255',
            'seo_slug' => 'nullable|string|max:191',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:320',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
            'travel_style_ids' => 'nullable|array',
            'travel_style_ids.*' => 'integer|exists:travel_styles,id',
            'itinerary' => 'nullable|array',
            'itinerary.*.id' => 'nullable|integer|exists:package_itinerary_days,id',
            'itinerary.*.day_number' => 'nullable|integer|min:1',
            'itinerary.*.title' => 'nullable|string|max:255',
            'itinerary.*.meals_included' => 'nullable|string|max:100',
            'itinerary.*.transport_icons' => 'nullable|string|max:255',
            'itinerary.*.overnight_at' => 'nullable|string|max:255',
            'itinerary.*.content' => 'nullable|string',
            'faqs' => 'nullable|array',
            'faqs.*.id' => 'nullable|integer|exists:faqs,id',
            'faqs.*.question' => 'nullable|string|max:500',
            'faqs.*.answer' => 'nullable|string',
            ...$this->coverImageRules(),
        ]);

        $countryIds = array_values(array_unique(array_map('intval', $validated['country_ids'])));
        if (! in_array((int) $validated['country_id'], $countryIds, true)) {
            $countryIds[] = (int) $validated['country_id'];
        }

        $package = DB::transaction(function () use ($request, $validated, $type, $locale, $seoType, $countryIds) {
            $package = isset($validated['id'])
                ? Package::query()->findOrFail($validated['id'])
                : new Package(['type' => $type]);

            $package->fill([
                'type' => $type,
                'country_id' => $validated['country_id'],
                'code' => $validated['code'] ?? null,
                'duration_days' => $validated['duration_days'],
                'duration_nights' => $validated['duration_nights'] ?? 0,
                'price_from' => $validated['price_from'] ?? null,
                'currency' => $validated['currency'] ?? 'VND',
                'status' => $validated['status'],
                'discount_badge' => $validated['discount_badge'] ?? null,
                'rating' => $validated['rating'] ?? $request->input('rating_aggregate_star') ?? 0,
                'review_count' => $validated['review_count'] ?? $request->input('rating_aggregate_count') ?? 0,
                'sort' => $validated['sort'] ?? 0,
                'cruise_type' => $validated['cruise_type'] ?? null,
                'departure_port' => $validated['departure_port'] ?? null,
                'boat_class' => $validated['boat_class'] ?? null,
                'nights_on_board' => $validated['nights_on_board'] ?? null,
                'published_at' => $validated['status'] === 'published' ? now() : null,
            ]);
            $package->save();

            $this->saveModelTranslation(
                $package,
                PackageTranslation::class,
                'package_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'start_location' => $validated['start_location'] ?? null,
                    'end_location' => $validated['end_location'] ?? null,
                    'summary' => $validated['summary'] ?? null,
                    'highlights_intro' => $validated['highlights_intro'] ?? null,
                    'featured_quote_text' => $validated['featured_quote_text'] ?? null,
                    'featured_quote_author' => $validated['featured_quote_author'] ?? null,
                    'places_to_visit' => $this->linesToArray($request->input('places_to_visit')),
                    'highlight_bullets' => $this->linesToArray($request->input('highlight_bullets')),
                    'inclusions' => $this->linesToArray($request->input('inclusions')),
                    'exclusions' => $this->linesToArray($request->input('exclusions')),
                    'notes' => $this->linesToArray($request->input('notes')),
                ],
                [
                    'title', 'start_location', 'end_location', 'summary', 'highlights_intro',
                    'featured_quote_text', 'featured_quote_author', 'places_to_visit',
                    'highlight_bullets', 'inclusions', 'exclusions', 'notes',
                ],
            );

            $package->load(['country.seoEntry.translations', 'cruiseType.seoEntry.translations']);

            $country = $package->country;
            $countryCode = $country?->code ?? 'vn';
            $countrySlug = $country?->translation($locale)?->slug
                ?? $country?->translation()?->slug
                ?? Str::slug((string) ($country?->translation($locale)?->name ?? $countryCode));

            $parentId = $validated['seo_parent_id'] ?? null;

            if ($type === Package::TYPE_CRUISE) {
                $cruiseType = $package->cruiseType;
                if ($cruiseType && ! $parentId) {
                    $hubSeo = $this->seoService()->ensureCruisesHub($locale);
                    $ctSeo = $this->seoService()->ensureSeoFor($cruiseType, 'cruise_type', $locale, [
                        'slug' => $cruiseType->slug,
                        'title' => $cruiseType->name,
                        'seo_title' => $cruiseType->name,
                        'status' => 'published',
                        'parent_id' => $hubSeo->id,
                    ]);
                    $parentId = $ctSeo->id;
                }
            } else {
                $countryParentId = null;
                if ($country) {
                    $countrySeo = $this->seoService()->ensureSeoFor($country, 'country', $locale, [
                        'slug' => $countrySlug,
                        'title' => $country->translation($locale)?->name ?? $countrySlug,
                        'seo_title' => $country->translation($locale)?->name ?? $countrySlug,
                        'status' => 'published',
                        'country_slug' => $countrySlug,
                    ]);
                    $countryParentId = $countrySeo->id;
                }
                $parentId = $parentId ?: $countryParentId;
            }

            $this->saveSeoTranslations(
                $package,
                [
                    $locale => [
                        'slug' => $validated['seo_slug'] ?? $validated['title'],
                        'title' => $validated['title'],
                        'seo_title' => $validated['seo_title'] ?? $validated['title'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $validated['status'],
                        'parent_id' => $parentId,
                        'country_slug' => $countrySlug,
                        'country_code' => $countryCode,
                        'cruise_type' => $package->cruise_type ?? null,
                        'rating_aggregate_count' => $request->input('rating_aggregate_count'),
                        'rating_aggregate_star' => $request->input('rating_aggregate_star'),
                    ],
                ],
                $seoType,
                [
                    'rating_aggregate_count' => $request->input('rating_aggregate_count'),
                    'rating_aggregate_star' => $request->input('rating_aggregate_star'),
                ],
            );

            $package->travelStyles()->sync($validated['travel_style_ids'] ?? []);

            $syncCountries = [];
            foreach ($countryIds as $sort => $cid) {
                $syncCountries[$cid] = ['sort' => $sort];
            }
            $package->countries()->sync($syncCountries);

            $this->syncItineraryDays($package, $request->input('itinerary', []), $locale);
            $this->syncFaqs($package, $request->input('faqs', []), $locale);
            $this->syncCoverAttachment($package, $request, config('media.packages'));

            return $package;
        });

        return redirect()
            ->route($viewRoute, ['id' => $package->id, 'language' => $locale])
            ->with('success', 'Đã lưu gói thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $package = Package::query()->findOrFail($request->integer('id'));
        $type = $package->type;
        $package->delete();

        $route = $type === Package::TYPE_CRUISE ? 'admin.packages.cruises' : 'admin.packages.tours';

        return redirect()->route($route)->with('success', 'Đã xóa gói thành công.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncItineraryDays(Package $package, array $rows, string $locale): void
    {
        $keepIds = [];

        foreach ($rows as $row) {
            if (empty($row['title'])) {
                continue;
            }

            $day = ! empty($row['id'])
                ? PackageItineraryDay::query()->find($row['id'])
                : new PackageItineraryDay(['package_id' => $package->id]);

            if (! $day) {
                continue;
            }

            $transport = array_values(array_filter(array_map(
                'trim',
                explode(',', (string) ($row['transport_icons'] ?? '')),
            )));

            $day->fill([
                'package_id' => $package->id,
                'day_number' => (int) ($row['day_number'] ?? 1),
                'meals_included' => $row['meals_included'] ?? null,
                'transport_icons' => $transport !== [] ? $transport : null,
                'distance_info' => $row['distance_info'] ?? null,
                'sort' => (int) ($row['sort'] ?? $row['day_number'] ?? 0),
            ]);
            $day->save();
            $keepIds[] = $day->id;

            $languageId = Language::idByCode($locale);
            if ($languageId) {
                $day->translations()->updateOrCreate(
                    ['language_id' => $languageId],
                    [
                        'title' => $row['title'],
                        'content' => $row['content'] ?? null,
                        'overnight_at' => $row['overnight_at'] ?? null,
                    ],
                );
            }
        }

        $package->itineraryDays()->whereNotIn('id', $keepIds)->delete();
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function syncFaqs(Package $package, array $rows, string $locale): void
    {
        $keepIds = [];
        $sort = 0;
        $langId = Language::idByCode($locale);

        foreach ($rows as $row) {
            if (empty($row['question'])) {
                continue;
            }

            $faq = ! empty($row['id'])
                ? Faq::query()->find($row['id'])
                : new Faq([
                    'faqable_type' => Package::class,
                    'faqable_id' => $package->id,
                ]);

            if (! $faq) {
                continue;
            }

            $faq->fill([
                'faqable_type' => Package::class,
                'faqable_id' => $package->id,
                'sort' => $sort,
                'is_active' => true,
            ]);
            $faq->save();
            $keepIds[] = $faq->id;

            if ($langId) {
                $faq->translations()->updateOrCreate(
                    ['language_id' => $langId],
                    [
                        'question' => $row['question'],
                        'answer' => $row['answer'] ?? null,
                    ],
                );
            }

            $sort++;
        }

        $package->faqs()->whereNotIn('id', $keepIds)->delete();
    }
}
