<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\HomeSectionController as BladeHomeSectionController;
use App\Models\HeroPill;
use App\Models\HomeFeaturedCountry;
use App\Models\HomeFeaturedCruise;
use App\Models\HomeFeaturedReview;
use App\Models\HomeFeaturedReviewPlatform;
use App\Models\HomeFeaturedService;
use App\Models\HomeFeaturedTeamMember;
use App\Models\HomeFeaturedTour;
use App\Models\HomeFeaturedVideo;
use App\Models\HomeSection;
use App\Models\Language;
use App\Models\Usp;
use App\Services\MediaService;
use App\Support\ApiResponse;
use App\Support\HomeFeaturedSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * API wrapper around Blade HomeSectionController save logic (JSON + media ids).
 */
class HomeSectionApiController extends BladeHomeSectionController
{
    public function show(Request $request): JsonResponse
    {
        $this->ensureDefaults();
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);
        $media = app(MediaService::class);

        $sections = HomeSection::query()
            ->with(['translations', 'image'])
            ->orderBy('sort')
            ->get()
            ->map(function (HomeSection $section) use ($locale, $media) {
                $t = $section->translation($locale);

                return [
                    'id' => $section->id,
                    'key' => $section->key,
                    'label' => $section->label(),
                    'is_active' => $section->is_active,
                    'fields' => HomeSection::fieldsForKey($section->key),
                    'eyebrow' => $t?->eyebrow,
                    'title' => $t?->title,
                    'subtitle' => $t?->subtitle,
                    'body' => $t?->body,
                    'meta_line' => $t?->meta_line,
                    'cta_label' => $t?->cta_label,
                    'cta_url' => $t?->cta_url,
                    'image_alt' => $t?->image_alt,
                    'image' => $media->adminMediaPayload($section->image, 'card'),
                ];
            })
            ->values();

        $usps = Usp::query()->orderBy('sort')->with('translations')->get()->map(function (Usp $usp) use ($locale) {
            $t = $usp->translation($locale);

            return [
                'id' => $usp->id,
                'icon' => $usp->icon,
                'title' => $t?->title,
                'description' => $t?->description,
            ];
        })->values();

        $pills = HeroPill::query()
            ->orderBy('sort')
            ->with(['translations', 'tourCategory.country.translations', 'country.translations'])
            ->get()
            ->map(function (HeroPill $pill) use ($locale) {
                $t = $pill->translation($locale);

                return [
                    'id' => $pill->id,
                    'target' => $pill->linkTargetValue(),
                    'label' => $t?->label,
                    'is_active' => $pill->is_active,
                ];
            })
            ->values();

        return ApiResponse::success([
            'locale' => $locale,
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'usp_icon_options' => collect(Usp::iconOptions())->map(
                fn ($label, $value) => ['value' => $value, 'label' => $label]
            )->values(),
            'pill_link_options' => HeroPill::linkTargetOptions($locale),
            'tour_options' => $this->selectOptions(HomeFeaturedTour::tourOptions($locale)),
            'cruise_options' => $this->selectOptions(HomeFeaturedCruise::cruiseOptions($locale)),
            'country_options' => $this->selectOptions(HomeFeaturedCountry::countryOptions($locale)),
            'platform_options' => $this->selectOptions(HomeFeaturedReviewPlatform::platformOptions()),
            'transport_service_options' => $this->selectOptions(
                HomeFeaturedSchema::hasServices()
                    ? HomeFeaturedService::serviceOptions(HomeFeaturedService::TRANSPORT_CLUSTERS, $locale)
                    : []
            ),
            'support_service_options' => $this->selectOptions(
                HomeFeaturedSchema::hasServices()
                    ? HomeFeaturedService::serviceOptions(HomeFeaturedService::SUPPORT_CLUSTERS, $locale)
                    : []
            ),
            'team_member_options' => $this->selectOptions(
                HomeFeaturedSchema::hasTeamMembers()
                    ? HomeFeaturedTeamMember::memberOptions($locale)
                    : []
            ),
            'review_options' => $this->selectOptions(
                HomeFeaturedSchema::hasReviews()
                    ? HomeFeaturedReview::reviewOptions($locale)
                    : []
            ),
            'video_options' => $this->selectOptions(
                HomeFeaturedSchema::hasVideos()
                    ? HomeFeaturedVideo::videoOptions($locale)
                    : []
            ),
            'sections' => $sections,
            'usps' => $usps,
            'pills' => $pills,
            'featured_tours' => HomeFeaturedTour::query()->orderBy('sort')->get()->map(
                fn (HomeFeaturedTour $r) => ['id' => $r->id, 'package_id' => $r->package_id]
            )->values(),
            'featured_cruises' => HomeFeaturedCruise::query()->orderBy('sort')->get()->map(
                fn (HomeFeaturedCruise $r) => ['id' => $r->id, 'package_id' => $r->package_id]
            )->values(),
            'featured_countries' => HomeFeaturedCountry::query()->orderBy('sort')->get()->map(
                fn (HomeFeaturedCountry $r) => ['id' => $r->id, 'country_id' => $r->country_id]
            )->values(),
            'featured_platforms' => HomeFeaturedReviewPlatform::query()->orderBy('sort')->get()->map(
                fn (HomeFeaturedReviewPlatform $r) => ['id' => $r->id, 'review_platform_id' => $r->review_platform_id]
            )->values(),
            'featured_transport' => HomeFeaturedSchema::hasServices()
                ? $this->featuredServicesPayload(HomeFeaturedService::TRANSPORT_CLUSTERS)
                : [],
            'featured_support' => HomeFeaturedSchema::hasServices()
                ? $this->featuredServicesPayload(HomeFeaturedService::SUPPORT_CLUSTERS)
                : [],
            'featured_team_members' => HomeFeaturedSchema::hasTeamMembers()
                ? HomeFeaturedTeamMember::query()->orderBy('sort')->get()->map(
                    fn (HomeFeaturedTeamMember $r) => ['id' => $r->id, 'team_member_id' => $r->team_member_id]
                )->values()
                : [],
            'featured_reviews' => HomeFeaturedSchema::hasReviews()
                ? HomeFeaturedReview::query()->orderBy('sort')->get()->map(
                    fn (HomeFeaturedReview $r) => ['id' => $r->id, 'review_id' => $r->review_id]
                )->values()
                : [],
            'featured_videos' => HomeFeaturedSchema::hasVideos()
                ? HomeFeaturedVideo::query()->orderBy('sort')->get()->map(
                    fn (HomeFeaturedVideo $r) => ['id' => $r->id, 'experience_video_id' => $r->experience_video_id]
                )->values()
                : [],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $this->ensureDefaults();
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);
        $iconKeys = implode(',', array_keys(Usp::iconOptions()));

        try {
            $validated = $request->validate([
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
                'sections.*.image_media_id' => 'nullable|integer|exists:media,id',
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
                'featured_transport' => 'nullable|array|max:12',
                'featured_transport.*.id' => 'nullable|integer|exists:home_featured_services,id',
                'featured_transport.*.service_id' => 'nullable|integer|exists:services,id',
                'featured_support' => 'nullable|array|max:12',
                'featured_support.*.id' => 'nullable|integer|exists:home_featured_services,id',
                'featured_support.*.service_id' => 'nullable|integer|exists:services,id',
                'featured_team_members' => 'nullable|array|max:12',
                'featured_team_members.*.id' => 'nullable|integer|exists:home_featured_team_members,id',
                'featured_team_members.*.team_member_id' => 'nullable|integer|exists:team_members,id',
                'featured_reviews' => 'nullable|array|max:12',
                'featured_reviews.*.id' => 'nullable|integer|exists:home_featured_reviews,id',
                'featured_reviews.*.review_id' => 'nullable|integer|exists:reviews,id',
                'featured_videos' => 'nullable|array|max:12',
                'featured_videos.*.id' => 'nullable|integer|exists:home_featured_videos,id',
                'featured_videos.*.experience_video_id' => 'nullable|integer|exists:experience_videos,id',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        DB::transaction(function () use ($request, $validated, $locale) {
            foreach ($validated['sections'] as $sectionData) {
                $section = HomeSection::query()->with('image')->findOrFail($sectionData['id']);
                $section->is_active = (bool) ($sectionData['is_active'] ?? $section->is_active);
                $section->save();

                if (in_array('image', HomeSection::fieldsForKey($section->key), true)) {
                    app(MediaService::class)->syncDirectMediaId(
                        $section,
                        'image_media_id',
                        isset($sectionData['image_media_id']) ? (int) $sectionData['image_media_id'] : null,
                        (bool) ($sectionData['remove_image'] ?? false),
                    );
                }

                $this->saveModelTranslation(
                    $section,
                    \App\Models\HomeSectionTranslation::class,
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
            }

            foreach ($validated['usps'] as $sort => $uspData) {
                $langId = Language::idByCode($locale);
                if (! $langId) {
                    continue;
                }
                $usp = isset($uspData['id'])
                    ? Usp::query()->findOrFail($uspData['id'])
                    : Usp::query()->create(['icon' => $uspData['icon'], 'sort' => $sort, 'is_active' => true]);
                $usp->update(['icon' => $uspData['icon'], 'sort' => $sort, 'is_active' => true]);
                \App\Models\UspTranslation::query()->updateOrCreate(
                    ['usp_id' => $usp->id, 'language_id' => $langId],
                    [
                        'title' => $uspData['title'] ?? null,
                        'description' => $uspData['description'] ?? null,
                    ],
                );
            }

            $this->saveHeroPills($request, $validated['pills'] ?? [], $locale);

            $request->merge([
                'featured_tours' => $validated['featured_tours'] ?? [],
                'featured_cruises' => $validated['featured_cruises'] ?? [],
                'featured_countries' => $validated['featured_countries'] ?? [],
                'featured_platforms' => $validated['featured_platforms'] ?? [],
                'featured_transport' => $validated['featured_transport'] ?? [],
                'featured_support' => $validated['featured_support'] ?? [],
                'featured_team_members' => $validated['featured_team_members'] ?? [],
                'featured_reviews' => $validated['featured_reviews'] ?? [],
                'featured_videos' => $validated['featured_videos'] ?? [],
            ]);

            $this->saveFeaturedTours($request, $validated['featured_tours'] ?? []);
            $this->saveFeaturedCruises($request, $validated['featured_cruises'] ?? []);
            $this->saveFeaturedCountries($request, $validated['featured_countries'] ?? []);
            $this->saveFeaturedPlatforms($request, $validated['featured_platforms'] ?? []);
            if (HomeFeaturedSchema::hasServices()) {
                $this->saveFeaturedServicesByClusters(
                    $request,
                    'featured_transport',
                    $validated['featured_transport'] ?? [],
                    HomeFeaturedService::TRANSPORT_CLUSTERS,
                );
                $this->saveFeaturedServicesByClusters(
                    $request,
                    'featured_support',
                    $validated['featured_support'] ?? [],
                    HomeFeaturedService::SUPPORT_CLUSTERS,
                );
            }
            if (HomeFeaturedSchema::hasTeamMembers()) {
                $this->saveFeaturedRows(
                    $request,
                    'featured_team_members',
                    $validated['featured_team_members'] ?? [],
                    HomeFeaturedTeamMember::class,
                    'team_member_id',
                );
            }
            if (HomeFeaturedSchema::hasReviews()) {
                $this->saveFeaturedRows(
                    $request,
                    'featured_reviews',
                    $validated['featured_reviews'] ?? [],
                    HomeFeaturedReview::class,
                    'review_id',
                );
            }
            if (HomeFeaturedSchema::hasVideos()) {
                $this->saveFeaturedRows(
                    $request,
                    'featured_videos',
                    $validated['featured_videos'] ?? [],
                    HomeFeaturedVideo::class,
                    'experience_video_id',
                );
            }
        });

        return $this->show($request->merge(['locale' => $locale]));
    }

    /**
     * @param  array<int|string, string>  $map
     * @return list<array{value: int|string, label: string}>
     */
    protected function selectOptions(array $map): array
    {
        return collect($map)
            ->map(fn (string $label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $clusters
     * @return list<array{id: int, service_id: int}>
     */
    protected function featuredServicesPayload(array $clusters): array
    {
        return HomeFeaturedService::query()
            ->orderBy('sort')
            ->whereHas('service', fn ($q) => $q->whereIn('cluster', $clusters))
            ->get()
            ->map(fn (HomeFeaturedService $r) => ['id' => $r->id, 'service_id' => $r->service_id])
            ->values()
            ->all();
    }
}
