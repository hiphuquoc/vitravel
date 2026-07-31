<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\StaticPageTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Hub cấp 1 (tours/cruises/guide + 5 cụm dịch vụ) — parent null, level 1.
 */
class ListingHubController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function edit(Request $request, string $hubKey): View
    {
        $cfg = $this->hubConfig($hubKey);
        $locale = $request->string('language', 'vi')->toString();
        $hubSeo = $this->seoService()->ensureHub($hubKey, $locale);

        $page = StaticPage::query()
            ->with(['translations', 'banner', 'seoEntry.translations'])
            ->where('template', $cfg['template'])
            ->first();

        if (! $page) {
            $page = StaticPage::query()->create([
                'template' => $cfg['template'],
                'status' => 'published',
                'published_at' => now(),
            ]);
            $this->seoService()->ensureHub($hubKey, $locale);
            $page->load(['translations', 'banner', 'seoEntry.translations']);
        }

        $seoTranslation = $page->seoEntry?->translation($locale) ?? $hubSeo->translation($locale);
        $viewUrl = $seoTranslation?->slug_full
            ? seo_url((string) $seoTranslation->slug_full, $locale)
            : url('/'.ltrim((string) ($cfg['default_slug'] ?? ''), '/'));

        return view('admin.listing-hub.edit', [
            'hubKey' => $hubKey,
            'cfg' => $cfg,
            'page' => $page,
            'locale' => $locale,
            'language' => $locale,
            'languages' => $this->activeLanguages(),
            'translation' => $page->translation($locale),
            'seoTranslation' => $seoTranslation,
            'seoEntry' => $page->seoEntry ?? $hubSeo,
            'title' => $cfg['label'] ?? ($cfg['default_title'] ?? 'Hub'),
            'backRoute' => $cfg['back_route'] ?? 'admin.dashboard',
            'viewUrl' => $viewUrl,
        ]);
    }

    public function save(Request $request, string $hubKey): RedirectResponse
    {
        $cfg = $this->hubConfig($hubKey);
        $locale = $request->string('language', 'vi')->toString();
        $this->assertUploadedFileOk($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'seo_slug' => 'nullable|string|max:191',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:320',
            'seo_keywords' => 'nullable|string|max:500',
            'rating_aggregate_count' => 'nullable|integer|min:0',
            'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
            ...$this->coverImageRules(),
        ]);

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
                StaticPageTranslation::class,
                'static_page_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'body' => $validated['body'] ?? null,
                ],
                ['title', 'body'],
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

            $this->syncDirectCover($page, 'banner_media_id', $request, config('media.countries'));

            // Safety net: orphans attach to hub after hub slug/parent sync
            match ($hubKey) {
                'cruises_hub' => $this->seoService()->attachCruiseTypesToCruisesHub($locale),
                'tours_hub' => $this->seoService()->attachCountriesToToursHub($locale),
                'guide_hub' => $this->seoService()->attachBlogCategoriesToGuideHub($locale),
                'trains_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub'
                    => $this->seoService()->rebuildServicesSeoTree($locale),
                default => null,
            };
        });

        return redirect()
            ->route('admin.listingHub.edit', ['hubKey' => $hubKey, 'language' => $locale])
            ->with('success', 'Đã lưu hub thành công.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function hubConfig(string $hubKey): array
    {
        $cfg = config("seo.hubs.{$hubKey}");
        abort_unless(is_array($cfg), 404);

        $back = match ($hubKey) {
            'tours_hub' => 'admin.countries.list',
            'cruises_hub' => 'admin.cruiseTypes.list',
            'guide_hub' => 'admin.blogCategories.list',
            default => 'admin.dashboard',
        };

        return array_merge($cfg, [
            'label' => config("seo.types.{$cfg['seo_type']}.label", $cfg['default_title']),
            'back_route' => $back,
        ]);
    }
}
