<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\HeroPill;
use App\Models\HeroPillTranslation;
use App\Models\HomeFeaturedCountry;
use App\Models\HomeFeaturedCruise;
use App\Models\HomeFeaturedReview;
use App\Models\HomeFeaturedReviewPlatform;
use App\Models\HomeFeaturedService;
use App\Models\HomeFeaturedTeamMember;
use App\Models\HomeFeaturedTour;
use App\Models\HomeFeaturedVideo;
use App\Models\HomeSection;
use App\Models\HomeSectionTranslation;
use App\Models\Language;
use App\Models\Service;
use App\Models\Usp;
use App\Models\UspTranslation;
use App\Services\MediaService;
use App\Support\HomePageDefaults;
use Database\Seeders\HomeSectionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    use ManagesTranslations;

    public function __construct(protected MediaService $mediaService) {}

    public function edit(Request $request): View
    {
        $this->ensureDefaults();

        $locale = $request->string('language', 'vi')->toString();
        $language = $locale;

        $sections = HomeSection::query()
            ->with(['translations', 'image'])
            ->orderBy('sort')
            ->get()
            ->keyBy('key');

        $usps = Usp::query()
            ->orderBy('sort')
            ->with('translations')
            ->get();

        $heroPills = HeroPill::query()
            ->orderBy('sort')
            ->with(['translations', 'tourCategory.country.translations', 'country.translations'])
            ->get();

        $heroPillLinkOptions = HeroPill::linkTargetOptions($locale);

        $featuredTours = HomeFeaturedTour::query()
            ->orderBy('sort')
            ->with(['package.translations', 'package.country.translations'])
            ->get();

        $featuredTourOptions = HomeFeaturedTour::tourOptions($locale);

        $featuredCruises = HomeFeaturedCruise::query()
            ->orderBy('sort')
            ->with(['package.translations', 'package.country.translations'])
            ->get();

        $featuredCruiseOptions = HomeFeaturedCruise::cruiseOptions($locale);

        $featuredCountries = HomeFeaturedCountry::query()
            ->orderBy('sort')
            ->with(['country.translations'])
            ->get();

        $featuredCountryOptions = HomeFeaturedCountry::countryOptions($locale);

        $featuredPlatforms = HomeFeaturedReviewPlatform::query()
            ->orderBy('sort')
            ->with('platform')
            ->get();

        $featuredPlatformOptions = HomeFeaturedReviewPlatform::platformOptions();

        $languages = $this->activeLanguages();
        $title = 'Nội dung trang chủ';
        $mediaDisk = $this->mediaService->defaultDisk();
        $uspIconOptions = Usp::iconOptions();

        return view('admin.home-section.edit', compact(
            'sections', 'usps', 'heroPills', 'heroPillLinkOptions',
            'featuredTours', 'featuredTourOptions',
            'featuredCruises', 'featuredCruiseOptions',
            'featuredCountries', 'featuredCountryOptions',
            'featuredPlatforms', 'featuredPlatformOptions',
            'locale', 'language', 'languages',
            'title', 'mediaDisk', 'uspIconOptions',
        ));
    }

    public function save(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();
        $maxKb = (int) config('media.max_upload_kb', 5120);
        $iconKeys = implode(',', array_keys(Usp::iconOptions()));

        // jquery.repeater names checkboxes as field[] → PHP gets ["1"]; flatten before validate.
        $this->normalizeRepeaterBooleans($request, 'pills', ['is_active']);

        $validated = $request->validate([
            'language' => 'required|string',
            'sections' => 'required|array',
            'sections.*.id' => 'required|integer|exists:home_sections,id',
            'sections.*.is_active' => 'nullable|boolean',
            'sections.*.eyebrow' => 'nullable|string|max:255',
            'sections.*.title' => 'nullable|string|max:255',
            'sections.*.subtitle' => 'nullable|string',
            'sections.*.body' => 'nullable|string',
            'sections.*.meta_line' => 'nullable|string|max:255',
            'sections.*.cta_label' => 'nullable|string|max:100',
            'sections.*.cta_url' => 'nullable|string|max:500',
            'sections.*.image_alt' => 'nullable|string|max:255',
            'sections.*.image' => 'nullable|image|max:'.$maxKb,
            'sections.*.remove_image' => 'nullable|boolean',
            'usps' => 'required|array|min:1|max:4',
            'usps.*.id' => 'nullable|integer|exists:usps,id',
            'usps.*.icon' => 'required|string|in:'.$iconKeys,
            'usps.*.title' => 'nullable|string|max:255',
            'usps.*.description' => 'nullable|string|max:500',
            'pills' => 'nullable|array|max:12',
            'pills.*.id' => 'nullable|integer|exists:hero_pills,id',
            'pills.*.target' => 'nullable|string|max:100',
            'pills.*.label' => 'nullable|string|max:100',
            'pills.*.is_active' => 'nullable|boolean',
            'featured_tours' => 'nullable|array|max:12',
            'featured_tours.*.id' => 'nullable|integer|exists:home_featured_tours,id',
            'featured_tours.*.package_id' => 'nullable|integer|exists:packages,id',
            'featured_cruises' => 'nullable|array|max:12',
            'featured_cruises.*.id' => 'nullable|integer|exists:home_featured_cruises,id',
            'featured_cruises.*.package_id' => 'nullable|integer|exists:packages,id',
            'featured_countries' => 'nullable|array|max:12',
            'featured_countries.*.id' => 'nullable|integer|exists:home_featured_countries,id',
            'featured_countries.*.country_id' => 'nullable|integer|exists:countries,id',
            'featured_platforms' => 'nullable|array|max:8',
            'featured_platforms.*.id' => 'nullable|integer|exists:home_featured_review_platforms,id',
            'featured_platforms.*.review_platform_id' => 'nullable|integer|exists:review_platforms,id',
        ]);

        DB::transaction(function () use ($request, $validated, $locale) {
            foreach ($validated['sections'] as $key => $sectionData) {
                $section = HomeSection::query()->with('image')->findOrFail($sectionData['id']);
                $section->is_active = $request->boolean("sections.{$key}.is_active");
                $section->save();

                if (in_array('image', HomeSection::fieldsForKey($section->key), true)) {
                    $folder = config('media.home_sections', 'vitravel/home-sections');

                    if ($request->boolean("sections.{$key}.remove_image") && $section->image) {
                        $this->mediaService->deleteMedia($section->image);
                        $section->image_media_id = null;
                        $section->save();
                    }

                    if ($request->hasFile("sections.{$key}.image")) {
                        if ($section->image) {
                            $this->mediaService->deleteMedia($section->image);
                        }
                        $media = $this->mediaService->storeUploadedFile(
                            $request->file("sections.{$key}.image"),
                            $folder,
                        );
                        $section->image_media_id = $media->id;
                        $section->save();
                    }
                }

                $this->saveModelTranslation(
                    $section,
                    HomeSectionTranslation::class,
                    'home_section_id',
                    $locale,
                    [
                        'eyebrow' => $sectionData['eyebrow'] ?? null,
                        'title' => $sectionData['title'] ?? null,
                        'subtitle' => $sectionData['subtitle'] ?? null,
                        'body' => $sectionData['body'] ?? null,
                        'meta_line' => $sectionData['meta_line'] ?? null,
                        'cta_label' => $sectionData['cta_label'] ?? null,
                        'cta_url' => $sectionData['cta_url'] ?? null,
                        'image_alt' => $sectionData['image_alt'] ?? null,
                    ],
                    ['eyebrow', 'title', 'subtitle', 'body', 'meta_line', 'cta_label', 'cta_url', 'image_alt'],
                );

                if ($section->image && filled($sectionData['image_alt'] ?? null)) {
                    $section->image->update(['alt' => $sectionData['image_alt']]);
                }
            }

            foreach ($validated['usps'] as $sort => $uspData) {
                $langId = Language::idByCode($locale);
                if (! $langId) {
                    continue;
                }

                $usp = isset($uspData['id'])
                    ? Usp::query()->findOrFail($uspData['id'])
                    : Usp::query()->create(['icon' => $uspData['icon'], 'sort' => $sort, 'is_active' => true]);

                $usp->update([
                    'icon' => $uspData['icon'],
                    'sort' => $sort,
                    'is_active' => true,
                ]);

                UspTranslation::query()->updateOrCreate(
                    ['usp_id' => $usp->id, 'language_id' => $langId],
                    [
                        'title' => $uspData['title'] ?? null,
                        'description' => $uspData['description'] ?? null,
                    ],
                );
            }

            $this->saveHeroPills($request, $validated['pills'] ?? [], $locale);
            $this->saveFeaturedTours($request, $validated['featured_tours'] ?? []);
            $this->saveFeaturedCruises($request, $validated['featured_cruises'] ?? []);
            $this->saveFeaturedCountries($request, $validated['featured_countries'] ?? []);
            $this->saveFeaturedPlatforms($request, $validated['featured_platforms'] ?? []);
        });

        return redirect()
            ->route('admin.homeSections.edit', ['language' => $locale])
            ->with('success', 'Đã lưu nội dung trang chủ thành công.');
    }

    /** @param  array<int|string, array<string, mixed>>  $pillsData */
    protected function saveHeroPills(Request $request, array $pillsData, string $locale): void
    {
        $hasAnyData = collect($pillsData)->contains(
            fn (array $row) => ! empty($row['target']) || ! empty($row['id']),
        );

        if (! $hasAnyData) {
            return;
        }

        $langId = Language::idByCode($locale);
        $sort = 0;
        $keptIds = [];

        foreach ($pillsData as $pillData) {
            if (empty($pillData['target'])) {
                continue;
            }

            $links = HeroPill::parseLinkTarget($pillData['target']);

            if (! $links['tour_category_id'] && ! $links['country_id']) {
                continue;
            }

            $pill = ! empty($pillData['id'])
                ? HeroPill::query()->findOrFail($pillData['id'])
                : new HeroPill;

            $pill->fill([
                'tour_category_id' => $links['tour_category_id'],
                'country_id' => $links['country_id'],
                'target_url' => null,
                'sort' => $sort,
                'is_active' => ! empty($pillData['is_active']),
            ]);
            $pill->save();
            $keptIds[] = $pill->id;

            if ($langId) {
                HeroPillTranslation::query()->updateOrCreate(
                    ['hero_pill_id' => $pill->id, 'language_id' => $langId],
                    ['label' => $pillData['label'] ?? ''],
                );
            }

            $sort++;
        }

        HeroPill::query()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    /** @param  array<int|string, array<string, mixed>>  $rows */
    protected function saveFeaturedTours(Request $request, array $rows): void
    {
        if (! $request->exists('featured_tours')) {
            return;
        }

        $sort = 0;
        $usedPackageIds = [];
        $keptIds = [];

        foreach ($rows as $row) {
            if (empty($row['package_id'])) {
                continue;
            }

            $packageId = (int) $row['package_id'];

            if (in_array($packageId, $usedPackageIds, true)) {
                continue;
            }

            $usedPackageIds[] = $packageId;

            $item = ! empty($row['id'])
                ? HomeFeaturedTour::query()->findOrFail($row['id'])
                : new HomeFeaturedTour;

            $item->fill([
                'package_id' => $packageId,
                'sort' => $sort,
            ]);
            $item->save();
            $keptIds[] = $item->id;

            $sort++;
        }

        HomeFeaturedTour::query()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    /** @param  array<int|string, array<string, mixed>>  $rows */
    protected function saveFeaturedCruises(Request $request, array $rows): void
    {
        if (! $request->exists('featured_cruises')) {
            return;
        }

        $sort = 0;
        $usedPackageIds = [];
        $keptIds = [];

        foreach ($rows as $row) {
            if (empty($row['package_id'])) {
                continue;
            }

            $packageId = (int) $row['package_id'];

            if (in_array($packageId, $usedPackageIds, true)) {
                continue;
            }

            $usedPackageIds[] = $packageId;

            $item = ! empty($row['id'])
                ? HomeFeaturedCruise::query()->findOrFail($row['id'])
                : new HomeFeaturedCruise;

            $item->fill([
                'package_id' => $packageId,
                'sort' => $sort,
            ]);
            $item->save();
            $keptIds[] = $item->id;

            $sort++;
        }

        HomeFeaturedCruise::query()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    /** @param  array<int|string, array<string, mixed>>  $rows */
    protected function saveFeaturedCountries(Request $request, array $rows): void
    {
        if (! $request->exists('featured_countries')) {
            return;
        }

        $sort = 0;
        $used = [];
        $keptIds = [];

        foreach ($rows as $row) {
            if (empty($row['country_id'])) {
                continue;
            }

            $countryId = (int) $row['country_id'];
            if (in_array($countryId, $used, true)) {
                continue;
            }
            $used[] = $countryId;

            $item = ! empty($row['id'])
                ? HomeFeaturedCountry::query()->findOrFail($row['id'])
                : new HomeFeaturedCountry;

            $item->fill(['country_id' => $countryId, 'sort' => $sort]);
            $item->save();
            $keptIds[] = $item->id;
            $sort++;
        }

        HomeFeaturedCountry::query()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    /** @param  array<int|string, array<string, mixed>>  $rows */
    protected function saveFeaturedPlatforms(Request $request, array $rows): void
    {
        if (! $request->exists('featured_platforms')) {
            return;
        }

        $sort = 0;
        $used = [];
        $keptIds = [];

        foreach ($rows as $row) {
            if (empty($row['review_platform_id'])) {
                continue;
            }

            $platformId = (int) $row['review_platform_id'];
            if (in_array($platformId, $used, true)) {
                continue;
            }
            $used[] = $platformId;

            $item = ! empty($row['id'])
                ? HomeFeaturedReviewPlatform::query()->findOrFail($row['id'])
                : new HomeFeaturedReviewPlatform;

            $item->fill(['review_platform_id' => $platformId, 'sort' => $sort]);
            $item->save();
            $keptIds[] = $item->id;
            $sort++;
        }

        HomeFeaturedReviewPlatform::query()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    /**
     * Lưu danh sách dịch vụ nổi bật theo nhóm cluster (transport / support).
     * Chỉ đụng các dòng thuộc $clusters — không xoá nhóm kia.
     *
     * @param  array<int|string, array<string, mixed>>  $rows
     * @param  list<string>  $clusters
     */
    protected function saveFeaturedServicesByClusters(Request $request, string $requestKey, array $rows, array $clusters): void
    {
        if (! $request->exists($requestKey) || $clusters === []) {
            return;
        }

        $allowedIds = Service::query()
            ->whereIn('cluster', $clusters)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $sort = 0;
        $used = [];
        $keptIds = [];

        foreach ($rows as $row) {
            if (empty($row['service_id'])) {
                continue;
            }

            $serviceId = (int) $row['service_id'];
            if (! in_array($serviceId, $allowedIds, true) || in_array($serviceId, $used, true)) {
                continue;
            }
            $used[] = $serviceId;

            $item = ! empty($row['id'])
                ? HomeFeaturedService::query()->findOrFail($row['id'])
                : new HomeFeaturedService;

            $item->fill(['service_id' => $serviceId, 'sort' => $sort]);
            $item->save();
            $keptIds[] = $item->id;
            $sort++;
        }

        HomeFeaturedService::query()
            ->whereHas('service', fn ($q) => $q->whereIn('cluster', $clusters))
            ->whereNotIn('id', $keptIds ?: [0])
            ->delete();
    }

    /**
     * Lưu danh sách curated generic (team / review / video …).
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  array<int|string, array<string, mixed>>  $rows
     */
    protected function saveFeaturedRows(
        Request $request,
        string $requestKey,
        array $rows,
        string $modelClass,
        string $foreignKey,
    ): void {
        if (! $request->exists($requestKey)) {
            return;
        }

        $sort = 0;
        $used = [];
        $keptIds = [];

        foreach ($rows as $row) {
            if (empty($row[$foreignKey])) {
                continue;
            }

            $foreignId = (int) $row[$foreignKey];
            if (in_array($foreignId, $used, true)) {
                continue;
            }
            $used[] = $foreignId;

            /** @var \Illuminate\Database\Eloquent\Model $item */
            $item = ! empty($row['id'])
                ? $modelClass::query()->findOrFail($row['id'])
                : new $modelClass;

            $item->fill([$foreignKey => $foreignId, 'sort' => $sort]);
            $item->save();
            $keptIds[] = $item->id;
            $sort++;
        }

        $modelClass::query()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    protected function ensureDefaults(): void
    {
        $viId = Language::idByCode('vi');
        $enId = Language::idByCode('en');

        foreach (HomePageDefaults::sections() as $row) {
            $section = HomeSection::query()->updateOrCreate(
                ['key' => $row['key']],
                ['sort' => $row['sort'], 'is_active' => true],
            );

            foreach (['vi' => $viId, 'en' => $enId] as $code => $langId) {
                if (! $langId || empty($row[$code])) {
                    continue;
                }

                HomeSectionTranslation::query()->firstOrCreate(
                    ['home_section_id' => $section->id, 'language_id' => $langId],
                    $row[$code],
                );
            }
        }

        foreach (HomePageDefaults::usps() as $row) {
            $usp = Usp::query()->updateOrCreate(
                ['sort' => $row['sort']],
                ['icon' => $row['icon'], 'is_active' => true],
            );

            foreach (['vi' => $viId, 'en' => $enId] as $code => $langId) {
                if (! $langId) {
                    continue;
                }

                UspTranslation::query()->firstOrCreate(
                    ['usp_id' => $usp->id, 'language_id' => $langId],
                    [
                        'title' => $row[$code]['title'],
                        'description' => $row[$code]['description'],
                    ],
                );
            }
        }

        HomeSectionSeeder::seedHeroPillsIfEmpty();
        HomeSectionSeeder::seedFeaturedToursIfEmpty();
        HomeSectionSeeder::seedFeaturedCruisesIfEmpty();
    }

    /**
     * jquery.repeater appends [] to checkbox names, so PHP receives ["1"] instead of "1".
     *
     * @param  list<string>  $fields
     */
    protected function normalizeRepeaterBooleans(Request $request, string $listKey, array $fields): void
    {
        $rows = $request->input($listKey);
        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($fields as $field) {
                if (! array_key_exists($field, $row) || ! is_array($row[$field])) {
                    continue;
                }

                $rows[$i][$field] = ! empty(array_filter($row[$field], fn ($v) => $v !== null && $v !== '' && $v !== false));
            }
        }

        $request->merge([$listKey => $rows]);
    }
}
