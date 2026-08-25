<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Support\ListingChrome;
use App\Services\ViewDataService;

class CruiseController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    public function hub()
    {
        return $this->cachedHtmlResponse(function () {
            $hub = $this->data->cruisesHub();
            $cruises = $this->data->cruises();
            $types = $this->data->cruiseTypes();
            $styles = $this->data->travelStyles();
            $durations = $this->data->durationBuckets();

            $listing = ListingChrome::make([
                'kind' => 'cruises_hub',
                'title' => $hub['title'] ?? 'Du thuyền',
                'subtitle' => $hub['subtitle'] ?? '',
                'seoBody' => $hub['seoBody'] ?? '',
                'seoTitle' => $hub['seoTitle'] ?? '',
                'seoDescription' => $hub['seoDescription'] ?? '',
                'banner' => $hub['listingBanner'] ?? null,
                'bannerSrcset' => $hub['listingBannerSrcset'] ?? null,
                'breadcrumbs' => [['label' => $hub['title'] ?? 'Du thuyền']],
                'unitLabel' => 'du thuyền',
                'endpoint' => route('api.listings.cruises'),
                'endpointParams' => ['variant' => 'wide'],
                'filterDefaults' => [],
                'showTypeFilter' => true,
                'types' => $types,
                'durations' => $durations,
                'styles' => $styles,
                'faqs' => $this->data->listingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về du thuyền',
                'schemaItems' => collect($cruises)->map(fn ($c) => [
                    'name' => $c['title'],
                    'url' => locale_route('cruises.show', ['type' => $c['typeSlug'], 'slug' => $c['slug']]),
                ])->all(),
                'schemaName' => seo_page_title('Du thuyền'),
            ]);

            return view('pages.cruises.hub', compact('listing'))->render();
        });
    }

    public function index(string $type)
    {
        return $this->cachedHtmlResponse(function () use ($type) {
            $types = collect($this->data->cruiseTypes());
            $typeData = $types->firstWhere('slug', $type) ?? abort(404);
            $styles = $this->data->travelStyles();
            $durations = $this->data->durationBuckets();
            $cruises = array_values(array_filter(
                $this->data->cruises(),
                fn ($c) => $c['typeSlug'] === $type
            ));
            $name = (string) ($typeData['title'] ?? $typeData['name'] ?? '');

            $listing = ListingChrome::make([
                'kind' => 'cruise_type',
                'title' => $name,
                'subtitle' => $typeData['subtitle'] ?? '',
                'seoBody' => $typeData['seoBody'] ?? ($typeData['intro'] ?? ''),
                'seoTitle' => $typeData['seoTitle'] ?: seo_page_title($name.' — Danh sách du thuyền'),
                'seoDescription' => $typeData['seoDescription'] ?: apply_site_brand('Tuyển chọn '.strtolower($name).' tốt nhất. Đặt cabin qua chuyên gia bản địa :brand.'),
                'banner' => $typeData['banner'] ?? ($typeData['imageHero'] ?? null),
                'bannerSrcset' => $typeData['bannerSrcset'] ?? ($typeData['imageSrcset'] ?? null),
                'breadcrumbs' => [
                    ['label' => 'Du thuyền', 'url' => locale_route('cruises.hub')],
                    ['label' => $name],
                ],
                'unitLabel' => 'du thuyền',
                'endpoint' => route('api.listings.cruises'),
                'endpointParams' => ['variant' => 'wide'],
                'filterDefaults' => [
                    'type' => [$typeData['slug']],
                ],
                'showTypeFilter' => true,
                'types' => $types->all(),
                'durations' => $durations,
                'styles' => $styles,
                'faqs' => $this->data->listingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về '.strtolower($name),
                'schemaItems' => collect($cruises)->map(fn ($c) => [
                    'name' => $c['title'],
                    'url' => locale_route('cruises.show', ['type' => $c['typeSlug'], 'slug' => $c['slug']]),
                ])->all(),
                'schemaName' => seo_page_title($name),
                'ratingMeta' => 'Đánh giá từ khách hàng đã chọn '.$name,
            ]);

            return view('pages.cruises.index', compact('listing'))->render();
        });
    }

    public function show(string $type, string $slug)
    {
        return $this->cachedHtmlResponse(function () use ($type, $slug) {
            $cruise = $this->data->cruise($slug);
            if (! $cruise || $cruise['typeSlug'] !== $type) {
                abort(404);
            }

            return view('pages.cruises.show', [
                'cruise' => $cruise,
            ])->render();
        });
    }
}