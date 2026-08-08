<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\Country;
use App\Models\CruiseType;
use App\Models\Package;
use App\Models\SeoEntry;
use App\Models\StaticPage;
use App\Models\TeamMember;
use App\Services\SeoService;
use App\Support\UrlPath;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoutingController extends Controller
{
    public function routing(Request $request): Response
    {
        [$localeFromUrl, $segments] = UrlPath::cleanRequestPathWithLocale(rawurldecode($request->path()));
        $locale = $localeFromUrl ?: app()->getLocale();

        if ($segments === []) {
            abort(404);
        }

        $path = implode('/', $segments);
        $entry = app(SeoService::class)->findBySlugFull($path, $locale);

        if ($entry) {
            return $this->dispatch($entry, $locale);
        }

        // Cẩm nang: /{hub}/{country|category} và /{hub}/{…}/{article-slug} khi chưa có / lệch slug_full
        $fallback = $this->dispatchGuidePathFallback($segments, $locale);
        if ($fallback !== null) {
            return $fallback;
        }

        // Tour / cruise listing khi locale chưa có SEO — resolve theo slug + nội dung EN/VI
        $tourFallback = $this->dispatchTourPathFallback($segments, $locale);
        if ($tourFallback !== null) {
            return $tourFallback;
        }

        $cruiseFallback = $this->dispatchCruisePathFallback($segments, $locale);
        if ($cruiseFallback !== null) {
            return $cruiseFallback;
        }

        abort(404);
    }

    protected function dispatch(SeoEntry $entry, string $locale): Response
    {
        app()->setLocale($locale);

        $ref = $entry->reference;
        $seoTrans = $entry->relationLoaded('translation')
            ? $entry->getRelation('translation')
            : $entry->translation($locale);

        // Một số bản ghi seed cũ để trống `type` — fallback reference_type (morph map)
        $type = filled($entry->type) ? (string) $entry->type : (string) ($entry->reference_type ?? '');

        return match ($type) {
            'tours_hub' => app(TourController::class)->hub(),
            'country' => app(TourController::class)->index(
                $this->entitySlug($ref, $locale, $seoTrans?->slug)
            ),
            'package_tour' => $this->dispatchPackageTour($entry, $ref, $locale, $seoTrans?->slug),
            'tour_category' => abort(404),
            'cruises_hub' => app(CruiseController::class)->hub(),
            'cruise_type' => app(CruiseController::class)->index(
                $this->cruiseTypeSlug($ref, $seoTrans?->slug)
            ),
            'package_cruise' => $this->dispatchPackageCruise($entry, $ref, $locale, $seoTrans?->slug),
            'guide_hub' => app(GuideController::class)->index(),
            'blog_category' => app(GuideController::class)->country(
                $this->entitySlug($ref, $locale, $seoTrans?->slug)
            ),
            'article' => $this->dispatchArticle($entry, $ref, $locale, $seoTrans?->slug),
            'static_page' => $this->dispatchStaticPage($ref),
            'team_hub' => app(PageController::class)->team(),
            'team_member' => $this->dispatchTeamMember($ref),
            'trains_hub', 'ferries_hub', 'flights_hub', 'stays_hub', 'experiences_hub', 'extras_hub'
                => $this->dispatchServiceHub($type),
            'service_category' => $this->dispatchServiceCategory($entry, $ref, $locale, $seoTrans?->slug),
            'service' => $this->dispatchService($entry, $ref, $locale, $seoTrans?->slug),
            default => abort(404),
        };
    }

    protected function dispatchServiceHub(string $seoType): Response
    {
        $cluster = config("services_catalog.hub_to_cluster.{$seoType}");
        if (! $cluster) {
            abort(404);
        }

        return app(ServiceController::class)->hub($cluster);
    }

    protected function dispatchServiceCategory(SeoEntry $entry, mixed $ref, string $locale, ?string $fallbackSlug): Response
    {
        $cat = $ref instanceof \App\Models\ServiceCategory ? $ref : null;
        $slug = $cat?->slug ?: ($fallbackSlug ?: abort(404));
        $cluster = $cat?->cluster
            ?? config('services_catalog.hub_to_cluster.'.$entry->parent?->type)
            ?? abort(404);

        return app(ServiceController::class)->index($cluster, $slug);
    }

    protected function dispatchService(SeoEntry $entry, mixed $ref, string $locale, ?string $serviceSlug): Response
    {
        $service = $ref instanceof \App\Models\Service ? $ref : null;
        $slug = $serviceSlug
            ?: $service?->seoEntry?->translation($locale)?->slug
            ?: abort(404);

        $cluster = $service?->cluster;
        $categorySlug = $service?->category?->slug
            ?? $entry->parent?->translation($locale)?->slug
            ?? null;

        if (! $cluster) {
            $parentType = $entry->parent?->type;
            $cluster = config("services_catalog.hub_to_cluster.{$parentType}")
                ?? ($entry->parent?->reference instanceof \App\Models\ServiceCategory
                    ? $entry->parent->reference->cluster
                    : null);
        }

        if (! $cluster || ! $categorySlug) {
            abort(404);
        }

        return app(ServiceController::class)->show($cluster, $categorySlug, $slug);
    }

    /**
     * Soft-route listing tour theo country khi locale chưa có SEO row.
     *
     * @param  list<string>  $segments
     */
    protected function dispatchTourPathFallback(array $segments, string $locale): ?Response
    {
        if ($segments === []) {
            return null;
        }

        $seo = app(SeoService::class);
        $hubFull = '';
        foreach (\App\Models\Language::contentLocaleChain($locale) as $code) {
            $hubFull = trim($seo->hubSlugFullPath('tours_hub', $code), '/');
            if ($hubFull !== '') {
                break;
            }
        }

        if ($hubFull === '') {
            $hubFull = 'tours';
        }

        $hubSegments = explode('/', $hubFull);
        $hubCount = count($hubSegments);
        if ($hubCount === 0 || array_slice($segments, 0, $hubCount) !== $hubSegments) {
            return null;
        }

        $rest = array_slice($segments, $hubCount);
        if ($rest === []) {
            return app(TourController::class)->hub();
        }

        if (count($rest) === 1) {
            return app(TourController::class)->index($rest[0]);
        }

        return app(TourController::class)->show($rest[0], $rest[1]);
    }

    /**
     * Soft-route listing cruise khi locale chưa có SEO row.
     *
     * @param  list<string>  $segments
     */
    protected function dispatchCruisePathFallback(array $segments, string $locale): ?Response
    {
        if ($segments === []) {
            return null;
        }

        $seo = app(SeoService::class);
        $hubFull = '';
        foreach (\App\Models\Language::contentLocaleChain($locale) as $code) {
            $hubFull = trim($seo->hubSlugFullPath('cruises_hub', $code), '/');
            if ($hubFull !== '') {
                break;
            }
        }

        if ($hubFull === '') {
            $hubFull = 'cruises';
        }

        $hubSegments = explode('/', $hubFull);
        $hubCount = count($hubSegments);
        if ($hubCount === 0 || array_slice($segments, 0, $hubCount) !== $hubSegments) {
            return null;
        }

        $rest = array_slice($segments, $hubCount);
        if ($rest === []) {
            return app(CruiseController::class)->hub();
        }

        if (count($rest) === 1) {
            return app(CruiseController::class)->index($rest[0]);
        }

        return app(CruiseController::class)->show($rest[0], $rest[1]);
    }

    /**
     * Soft-route cẩm nang khi chưa có seo_entry (vd /cam-nang-du-lich/viet-nam)
     * hoặc URL lệch segment giữa (category vs country) nhưng đúng slug bài.
     *
     * @param  list<string>  $segments
     */
    protected function dispatchGuidePathFallback(array $segments, string $locale): ?Response
    {
        $seo = app(SeoService::class);
        $hubFull = '';
        foreach (\App\Models\Language::contentLocaleChain($locale) as $code) {
            $hubFull = trim($seo->hubSlugFullPath('guide_hub', $code), '/');
            if ($hubFull !== '') {
                break;
            }
        }
        if ($hubFull === '') {
            return null;
        }

        $hubSegments = explode('/', $hubFull);
        $hubCount = count($hubSegments);
        if ($hubCount === 0 || array_slice($segments, 0, $hubCount) !== $hubSegments) {
            return null;
        }

        $rest = array_slice($segments, $hubCount);
        if ($rest === []) {
            return null;
        }

        if (count($rest) === 1) {
            // /hub/{country|blog_category-slug} — GuideController tự resolve
            return app(GuideController::class)->country($rest[0]);
        }

        if (count($rest) >= 2) {
            $articleSlug = (string) end($rest);
            $middle = (string) $rest[count($rest) - 2];

            // Bài viết: tìm theo leaf slug (bỏ qua lệch country vs category ở giữa)
            $ids = \App\Models\Language::contentLanguageIdChain($locale);
            $article = $ids === [] ? null : Article::query()
                ->whereHas('seoEntry.translations', function ($q) use ($articleSlug, $ids) {
                    $q->where('slug', $articleSlug)
                        ->where('status', 'published')
                        ->whereIn('language_id', $ids);
                })
                ->first();

            if ($article) {
                return app(GuideController::class)->show($middle, $articleSlug);
            }
        }

        return null;
    }

    protected function dispatchPackageTour(SeoEntry $entry, mixed $ref, string $locale, ?string $packageSlug): Response
    {
        $package = $ref instanceof Package ? $ref : null;
        $packageSlug = $packageSlug
            ?: $package?->translation($locale)?->slug
            ?: abort(404);

        $country = $package?->country;
        $countrySlug = $country?->seoEntry?->translation($locale)?->slug
            ?? $country?->translation($locale)?->slug
            ?? $entry->parent?->translation($locale)?->slug
            ?? abort(404);

        return app(TourController::class)->show($countrySlug, $packageSlug);
    }

    protected function dispatchPackageCruise(SeoEntry $entry, mixed $ref, string $locale, ?string $packageSlug): Response
    {
        $package = $ref instanceof Package ? $ref : null;
        $packageSlug = $packageSlug
            ?: $package?->translation($locale)?->slug
            ?: abort(404);

        $type = $package?->cruiseType;
        $typeSlug = $type?->slug
            ?? $entry->parent?->translation($locale)?->slug
            ?? abort(404);

        return app(CruiseController::class)->show($typeSlug, $packageSlug);
    }

    protected function dispatchArticle(SeoEntry $entry, mixed $ref, string $locale, ?string $articleSlug): Response
    {
        $article = $ref instanceof Article ? $ref : null;
        $articleSlug = $articleSlug
            ?: $article?->translation($locale)?->slug
            ?: abort(404);

        $parentCatSlug = $article?->blogCategory?->translation($locale)?->slug
            ?? $entry->parent?->translation($locale)?->slug
            ?? abort(404);

        return app(GuideController::class)->show($parentCatSlug, $articleSlug);
    }

    protected function dispatchTeamMember(mixed $ref): Response
    {
        if (! $ref instanceof TeamMember) {
            abort(404);
        }

        return app(PageController::class)->teamShow($ref);
    }

    protected function dispatchStaticPage(mixed $ref): Response
    {
        if (! $ref instanceof StaticPage) {
            abort(404);
        }

        $method = match ($ref->template) {
            'about', 've-chung-toi' => 'about',
            'contact', 'lien-he' => 'contact',
            'customize', 'thiet-ke-tour-rieng' => 'customize',
            'team', 'doi-ngu' => 'team',
            'reviews', 'cam-nhan-khach-hang' => 'reviews',
            'gallery', 'thu-vien-khoanh-khac' => 'gallery',
            'videos', 'video-trai-nghiem' => 'videos',
            default => null,
        };

        if ($method === null || ! method_exists(PageController::class, $method)) {
            abort(404);
        }

        return app(PageController::class)->{$method}();
    }

    protected function entitySlug(mixed $ref, string $locale, ?string $fallback): string
    {
        if ($ref instanceof Country || $ref instanceof BlogCategory) {
            return $ref->translation($locale)?->slug
                ?? $fallback
                ?? abort(404);
        }

        return $fallback ?: abort(404);
    }

    protected function cruiseTypeSlug(mixed $ref, ?string $fallback): string
    {
        if ($ref instanceof CruiseType) {
            return $ref->slug ?: ($fallback ?: abort(404));
        }

        return $fallback ?: abort(404);
    }
}
