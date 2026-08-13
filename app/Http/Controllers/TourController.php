<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Support\ListingChrome;
use App\Services\ViewDataService;

class TourController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    /**
     * Hub tất cả tour: /tours
     */
    public function hub()
    {
        return $this->cachedHtmlResponse(function () {
            $hub = $this->data->toursHub();
            $tours = $this->data->tours();
            $countries = $this->data->countries();
            $styles = $this->data->travelStyles();
            $durations = $this->data->durationBuckets();
            $durationKeys = array_map('strval', array_keys($durations));
            $styleKeys = array_map('strval', array_keys($styles));
            $countrySlugs = array_values(array_filter(array_map(fn ($c) => $c['slug'] ?? null, $countries)));

            $listing = ListingChrome::make([
                'kind' => 'tours_hub',
                'title' => $hub['title'] ?? 'Tour',
                'subtitle' => $hub['subtitle'] ?? '',
                'seoBody' => $hub['seoBody'] ?? '',
                'seoTitle' => $hub['seoTitle'] ?? '',
                'seoDescription' => $hub['seoDescription'] ?? '',
                'banner' => $hub['listingBanner'] ?? null,
                'bannerSrcset' => $hub['listingBannerSrcset'] ?? null,
                'breadcrumbs' => [['label' => $hub['title'] ?? 'Tour']],
                'unitLabel' => 'tour',
                'endpoint' => route('api.listings.tours'),
                'filterDefaults' => [
                    'country' => $countrySlugs,
                    'duration' => $durationKeys,
                    'style' => $styleKeys,
                ],
                'showCountryFilter' => true,
                'countries' => $countries,
                'durations' => $durations,
                'styles' => $styles,
                'faqs' => $this->data->listingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về tour trọn gói',
                'schemaItems' => collect($tours)->map(fn ($t) => [
                    'name' => $t['title'],
                    'url' => locale_route('tours.show', ['country' => $t['countrySlug'], 'slug' => $t['slug']]),
                ])->all(),
                'schemaName' => seo_page_title('Tour trọn gói'),
                'ratingMeta' => 'Đánh giá từ khách hàng đã đi tour với '.site_brand(),
                'skeletonCount' => 5,
            ]);

            return view('pages.tours.hub', compact('listing'))->render();
        });
    }

    public function index(string $country)
    {
        return $this->cachedHtmlResponse(function () use ($country) {
            $countryData = $this->data->country($country) ?? abort(404);
            $tours = $this->data->toursByCountry($country);
            $countries = $this->data->countries();
            $styles = $this->data->travelStyles();
            $durations = $this->data->durationBuckets();
            $name = (string) ($countryData['name'] ?? '');

            $listing = ListingChrome::make([
                'kind' => 'country',
                'title' => 'Tour '.$name,
                'subtitle' => $countryData['subtitle'] ?? ($countryData['tagline'] ?? ''),
                'seoBody' => $countryData['seoBody'] ?? ($countryData['longForm'] ?? ''),
                'seoTitle' => seo_page_title('Tour '.$name.' trọn gói'),
                'seoDescription' => 'Danh sách tour '.$name.' trọn gói: '.($countryData['tagline'] ?? '').'. Nhận báo giá miễn phí trong 24 giờ.',
                'banner' => $countryData['listingBanner'] ?? null,
                'bannerSrcset' => $countryData['listingBannerSrcset'] ?? null,
                'breadcrumbs' => [
                    ['label' => 'Tour', 'url' => locale_route('tours.hub')],
                    ['label' => 'Tour '.$name],
                ],
                'unitLabel' => 'tour',
                'endpoint' => route('api.listings.tours'),
                'filterDefaults' => [
                    'country' => [$countryData['slug']],
                    'duration' => array_map('strval', array_keys($durations)),
                    'style' => array_map('strval', array_keys($styles)),
                ],
                'showCountryFilter' => true,
                'countries' => $countries,
                'durations' => $durations,
                'styles' => $styles,
                'faqs' => $this->data->listingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về tour '.$name,
                'schemaItems' => collect($tours)->map(fn ($t) => [
                    'name' => $t['title'],
                    'url' => locale_route('tours.show', ['country' => $t['countrySlug'], 'slug' => $t['slug']]),
                ])->all(),
                'schemaName' => seo_page_title('Tour '.$name),
                'ratingMeta' => 'Đánh giá từ khách hàng đã đi tour '.$name,
            ]);

            return view('pages.tours.index', compact('listing'))->render();
        });
    }

    /**
     * Chủ đề / danh mục tour: /tours/{country}/{category}
     */
    public function category(string $country, string $slug)
    {
        return $this->cachedHtmlResponse(function () use ($country, $slug) {
            $category = $this->data->tourCategory($country, $slug) ?? abort(404);
            $tours = $this->data->toursByCategory($country, $slug);
            $countries = $this->data->countries();
            $styles = $this->data->travelStyles();
            $durations = $this->data->durationBuckets();
            $title = (string) ($category['title'] ?? $category['name'] ?? '');

            $listing = ListingChrome::make([
                'kind' => 'tour_category',
                'title' => $title,
                'subtitle' => $category['subtitle'] ?? '',
                'seoBody' => $category['seoBody'] ?? '',
                'seoTitle' => $category['seoTitle'] ?: seo_page_title($title),
                'seoDescription' => $category['seoDescription'] ?? '',
                'banner' => $category['banner'] ?? null,
                'bannerSrcset' => $category['bannerSrcset'] ?? null,
                'breadcrumbs' => [
                    ['label' => 'Tour', 'url' => locale_route('tours.hub')],
                    [
                        'label' => 'Tour '.($category['countryName'] ?? ''),
                        'url' => locale_route('tours.index', ['country' => $category['countrySlug'] ?? $country]),
                    ],
                    ['label' => $title],
                ],
                'unitLabel' => 'tour',
                'endpoint' => route('api.listings.tours'),
                'filterDefaults' => [
                    'country' => [$category['countrySlug'] ?? $country],
                    'category' => [$category['slug'] ?? $slug],
                    'duration' => array_map('strval', array_keys($durations)),
                    'style' => array_map('strval', array_keys($styles)),
                ],
                'showCountryFilter' => true,
                'countries' => $countries,
                'durations' => $durations,
                'styles' => $styles,
                'faqs' => $category['faqs'] !== [] ? $category['faqs'] : $this->data->listingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về '.$title,
                'schemaItems' => collect($tours)->map(fn ($t) => [
                    'name' => $t['title'],
                    'url' => locale_route('tours.show', ['country' => $t['countrySlug'], 'slug' => $t['slug']]),
                ])->all(),
                'schemaName' => seo_page_title($title),
                'ratingMeta' => 'Đánh giá từ khách hàng đã chọn '.$title,
            ]);

            return view('pages.tours.category', compact('listing'))->render();
        });
    }

    public function show(string $country, string $slug)
    {
        return $this->cachedHtmlResponse(function () use ($country, $slug) {
            $tour = $this->data->tour($slug);
            if (! $tour || $tour['countrySlug'] !== $country) {
                abort(404);
            }

            return view('pages.tours.show', [
                'tour' => $tour,
            ])->render();
        });
    }
}
