<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\ListingHubController as BladeListingHubController;
use App\Models\Language;
use App\Models\StaticPage;
use App\Services\MediaService;
use App\Support\ApiResponse;
use App\Support\ListingFields;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;

class ListingHubApiController extends BladeListingHubController
{
    public function show(Request $request, string $hubKey): JsonResponse
    {
        $cfg = $this->callHubConfig($hubKey);
        $locale = $request->string('locale', 'vi')->toString();
        $hubSeo = $this->seoService()->ensureHub($hubKey, $locale);

        $page = StaticPage::query()
            ->with(['translations', 'banner', 'cover', 'seoEntry.translations'])
            ->where('template', $cfg['template'])
            ->first();

        if (! $page) {
            $page = StaticPage::query()->create([
                'template' => $cfg['template'],
                'status' => 'published',
                'published_at' => now(),
            ]);
            $this->seoService()->ensureHub($hubKey, $locale);
            $page->load(['translations', 'banner', 'cover', 'seoEntry.translations']);
        }

        $t = $page->translationExact($locale) ?? $page->translation($locale);
        $seo = $page->seoEntry?->translationExact($locale)
            ?? $hubSeo->translationExact($locale);
        $media = app(MediaService::class);

        return ApiResponse::success([
            'hub_key' => $hubKey,
            'label' => $cfg['label'] ?? $cfg['default_title'] ?? $hubKey,
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'title' => $t?->title,
            'body' => $t?->body,
            'subtitle' => $t?->body,
            'seo_body' => $t?->seo_body,
            'seo_slug' => $seo?->slug,
            'seo_title' => $seo?->seo_title,
            'seo_description' => $seo?->seo_description,
            'seo_keywords' => $seo?->keywords,
            'slug_full' => $seo && $page->seoEntry
                ? $this->seoService()->resolveEntrySlugFull($page->seoEntry, $locale)
                : ($seo?->slug_full),
            'rating_aggregate_star' => $page->seoEntry?->rating_aggregate_star ?? $hubSeo->rating_aggregate_star,
            'rating_aggregate_count' => $page->seoEntry?->rating_aggregate_count ?? $hubSeo->rating_aggregate_count,
            'banner' => $media->adminMediaPayload($page->banner, 'lg'),
            'cover' => $media->adminMediaPayload($page->cover, 'card'),
        ]);
    }

    public function update(Request $request, string $hubKey): JsonResponse
    {
        $cfg = $this->callHubConfig($hubKey);
        $locale = $request->string('locale', 'vi')->toString();

        try {
            ListingFields::mergeAliases($request, [
                'body' => 'subtitle',
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'nullable|string',
                'subtitle' => 'nullable|string',
                'seo_body' => 'nullable|string',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:350',
                'seo_keywords' => 'nullable|string|max:500',
                'rating_aggregate_count' => 'nullable|integer|min:0',
                'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
                'banner_media_id' => 'nullable|integer|exists:media,id',
                'remove_banner' => 'nullable|boolean',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        DB::transaction(function () use ($request, $validated, $locale, $cfg, $hubKey) {
            $page = StaticPage::query()->firstOrCreate(
                ['template' => $cfg['template']],
                ['status' => 'published', 'published_at' => now()],
            );
            $page->fill([
                'status' => 'published',
                'published_at' => $page->published_at ?? now(),
            ])->save();

            $this->saveModelTranslation(
                $page,
                \App\Models\StaticPageTranslation::class,
                'static_page_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'body' => $validated['body'] ?? null,
                    'seo_body' => $validated['seo_body'] ?? null,
                ],
                ['title', 'body', 'seo_body'],
            );

            $slug = $validated['seo_slug'] ?? $cfg['default_slug'];
            $this->saveSeoTranslations(
                $page,
                [
                    $locale => [
                        'slug' => $slug,
                        'title' => $validated['title'],
                        'seo_title' => $validated['seo_title'] ?? $validated['title'],
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => 'published',
                        'parent_id' => null,
                    ],
                ],
                $cfg['seo_type'],
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? null,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? null,
                ],
            );

            app(MediaService::class)->syncDirectMediaId(
                $page,
                'banner_media_id',
                isset($validated['banner_media_id']) ? (int) $validated['banner_media_id'] : null,
                $request->boolean('remove_banner'),
            );
            app(MediaService::class)->syncDirectMediaId(
                $page,
                'cover_media_id',
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            match ($hubKey) {
                'cruises_hub' => $this->seoService()->attachCruiseTypesToCruisesHub($locale),
                'tours_hub' => $this->seoService()->attachCountriesToToursHub($locale),
                'guide_hub' => $this->seoService()->attachBlogCategoriesToGuideHub($locale),
                'trains_hub', 'ferries_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub'
                    => $this->seoService()->rebuildServicesSeoTree($locale),
                default => null,
            };
        });

        return $this->show($request->merge(['locale' => $locale]), $hubKey);
    }

    /** @return array<string, mixed> */
    private function callHubConfig(string $hubKey): array
    {
        $method = new ReflectionMethod(BladeListingHubController::class, 'hubConfig');
        $method->setAccessible(true);

        return $method->invoke($this, $hubKey);
    }
}
