<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Support\ListingChrome;
use App\Support\ListingSeed;
use App\Services\ViewDataService;

class ServiceController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    public function hub(string $cluster)
    {
        return $this->cachedHtmlResponse(function () use ($cluster) {
            $this->assertCluster($cluster);
            $hub = $this->data->serviceHub($cluster);
            $categories = $this->data->serviceCategoriesForHub($cluster);
            $filterCategories = array_values(array_filter(
                $categories,
                fn ($cat) => ((int) ($cat['count'] ?? 0)) > 0 && ! empty($cat['slug'])
            ));
            $categorySlugs = array_values(array_map(fn ($cat) => (string) $cat['slug'], $filterCategories));
            $unitLabel = $hub['unitLabel'] ?? 'dịch vụ';
            $isStay = ($cluster === 'stay');
            $seedLimit = 5;
            $seedRes = $this->data->servicesForListing(
                cluster: $cluster,
                categories: [],
                limit: $seedLimit,
                variant: 'wide',
            );

            $listing = ListingChrome::make(array_merge([
                'kind' => 'service_hub',
                'title' => $hub['title'] ?? 'Dịch vụ',
                'subtitle' => $hub['subtitle'] ?? '',
                'seoBody' => $hub['seoBody'] ?? '',
                'seoTitle' => $hub['seoTitle'] ?: seo_page_title($hub['title'] ?? 'Dịch vụ'),
                'seoDescription' => $hub['seoDescription'] ?? ($hub['subtitle'] ?? ''),
                'banner' => $hub['listingBanner'] ?? null,
                'bannerSrcset' => $hub['listingBannerSrcset'] ?? null,
                'breadcrumbs' => [['label' => $hub['navLabel'] ?? $hub['title']]],
                'unitLabel' => $unitLabel,
                'endpoint' => route('api.listings.services'),
                'endpointParams' => ['cluster' => $cluster, 'variant' => 'wide', 'per_page' => $seedLimit],
                'perPage' => $seedLimit,
                'filterDefaults' => [],
                'showCategoryFilter' => true,
                'showDurationFilter' => false,
                'showStyleFilter' => false,
                'showPropertyTypeFilter' => $isStay,
                'showPriceRangeFilter' => $isStay,
                'showAmenityFilter' => $isStay,
                'showStarFilter' => $isStay,
                'categories' => $filterCategories,
                'categoryLegend' => $this->categoryLegend($cluster),
                'propertyTypes' => $isStay ? $this->stayPropertyTypes() : [],
                'priceRanges' => $isStay ? $this->stayPriceRanges() : [],
                'amenities' => $isStay ? $this->stayAmenities() : [],
                'stars' => $isStay ? $this->stayStars() : [],
                'faqs' => $this->data->serviceListingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về '.strtolower((string) ($hub['title'] ?? 'dịch vụ')),
                'schemaItems' => $this->data->serviceSchemaItems($cluster),
                'schemaName' => seo_page_title($hub['title'] ?? 'Dịch vụ'),
            ], ListingSeed::fromServiceListing($seedRes, $seedLimit)));

            return view('pages.services.hub', compact('listing', 'cluster'))->render();
        });
    }

    public function index(string $cluster, string $category)
    {
        return $this->cachedHtmlResponse(function () use ($cluster, $category) {
            $this->assertCluster($cluster);
            $cat = $this->data->serviceCategory($cluster, $category) ?? abort(404);
            $categories = $this->data->serviceCategories($cluster);
            $hub = $this->data->serviceHub($cluster);
            $filterCategories = array_values(array_filter(
                $categories,
                fn ($c) => ((int) ($c['count'] ?? 0)) > 0 && ! empty($c['slug'])
            ));
            if (! empty($cat['slug']) && ! collect($filterCategories)->contains(fn ($c) => ($c['slug'] ?? '') === $cat['slug'])) {
                array_unshift($filterCategories, $cat);
            }
            $name = (string) ($cat['title'] ?? $cat['name'] ?? '');
            $isStay = ($cluster === 'stay');
            $seedLimit = 5;
            $seedRes = $this->data->servicesForListing(
                cluster: $cluster,
                categories: [$cat['slug'] ?? $category],
                limit: $seedLimit,
                variant: 'wide',
            );

            $listing = ListingChrome::make(array_merge([
                'kind' => 'service_category',
                'title' => $name,
                'subtitle' => $cat['subtitle'] ?? ($cat['intro'] ?? ''),
                'seoBody' => $cat['seoBody'] ?? ($cat['intro'] ?? ''),
                'seoTitle' => $cat['seoTitle'] ?: seo_page_title($name.' — '.($hub['title'] ?? 'Dịch vụ')),
                'seoDescription' => $cat['seoDescription'] ?: apply_site_brand($cat['subtitle'] ?? ('Tuyển chọn '.strtolower($name).' — đặt qua chuyên gia bản địa :brand.')),
                'banner' => $cat['banner'] ?? ($cat['imageHero'] ?? null),
                'bannerSrcset' => $cat['bannerSrcset'] ?? ($cat['imageSrcset'] ?? null),
                'breadcrumbs' => [
                    [
                        'label' => $hub['navLabel'] ?? $hub['title'],
                        'url' => locale_route('services.hub', ['cluster' => $cluster]),
                    ],
                    ['label' => $name],
                ],
                'unitLabel' => $hub['unitLabel'] ?? 'dịch vụ',
                'endpoint' => route('api.listings.services'),
                'endpointParams' => ['cluster' => $cluster, 'variant' => 'wide', 'per_page' => $seedLimit],
                'perPage' => $seedLimit,
                'filterDefaults' => ['category' => [$cat['slug'] ?? $category]],
                'showCategoryFilter' => true,
                'showDurationFilter' => false,
                'showStyleFilter' => false,
                'showPropertyTypeFilter' => $isStay,
                'showPriceRangeFilter' => $isStay,
                'showAmenityFilter' => $isStay,
                'showStarFilter' => $isStay,
                'categories' => $filterCategories,
                'categoryLegend' => $this->categoryLegend($cluster),
                'propertyTypes' => $isStay ? $this->stayPropertyTypes() : [],
                'priceRanges' => $isStay ? $this->stayPriceRanges() : [],
                'amenities' => $isStay ? $this->stayAmenities() : [],
                'stars' => $isStay ? $this->stayStars() : [],
                'faqs' => $this->data->serviceListingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về '.strtolower($name),
                'schemaItems' => $this->data->serviceSchemaItems($cluster, $category),
                'schemaName' => seo_page_title($name),
                'ratingMeta' => 'Đánh giá từ khách hàng đã chọn '.$name,
            ], ListingSeed::fromServiceListing($seedRes, $seedLimit)));

            return view('pages.services.index', compact('listing', 'cluster'))->render();
        });
    }

    public function show(string $cluster, string $category, string $slug)
    {
        return $this->cachedHtmlResponse(function () use ($cluster, $category, $slug) {
            $this->assertCluster($cluster);
            $service = $this->data->service($slug, $cluster);
            if (! $service || ($service['categorySlug'] ?? '') !== $category) {
                abort(404);
            }

            // Related tải AJAX + skeleton (không chặn SSR / HTML cache)
            return view('pages.services.show', [
                'cluster' => $cluster,
                'service' => $service,
                'related' => [],
                'hub' => $this->data->serviceHub($cluster),
            ])->render();
        });
    }

    protected function stayPropertyTypes(): array
    {
        return [
            ['slug' => 'resort', 'name' => 'Resort & Nghỉ dưỡng'],
            ['slug' => 'hotel', 'name' => 'Khách sạn'],
            ['slug' => 'villa', 'name' => 'Biệt thự / Villa'],
            ['slug' => 'boutique', 'name' => 'Boutique Hotel'],
            ['slug' => 'homestay', 'name' => 'Homestay & Bungalow'],
            ['slug' => 'cabin', 'name' => 'Cabin & Nghỉ dưỡng'],
        ];
    }

    protected function stayPriceRanges(): array
    {
        return [
            'under_1m' => ['label' => 'Dưới 1.000.000 đ', 'sub' => 'Tiết kiệm / Homestay'],
            '1m_2m' => ['label' => '1.000.000 đ – 2.000.000 đ', 'sub' => 'Khách sạn 3–4 sao'],
            '2m_4m' => ['label' => '2.000.000 đ – 4.000.000 đ', 'sub' => 'Resort 4–5 sao'],
            'above_4m' => ['label' => 'Trên 4.000.000 đ', 'sub' => 'Luxury & Private Villa'],
        ];
    }

    protected function stayAmenities(): array
    {
        return [
            'pool' => ['label' => 'Hồ bơi / Bể bơi vô cực'],
            'beach' => ['label' => 'Bãi biển riêng / Sát biển'],
            'breakfast' => ['label' => 'Bao gồm bữa ăn / Bữa sáng'],
            'spa' => ['label' => 'Spa & Massage thư giãn'],
            'gym' => ['label' => 'Phòng Gym & Fitness'],
            'shuttle' => ['label' => 'Đưa đón bến tàu / Sân bay'],
        ];
    }

    protected function stayStars(): array
    {
        return [
            '5_star' => ['label' => '5 sao & Luxury Resort', 'badge' => '5★'],
            '4_star' => ['label' => '4 sao cao cấp', 'badge' => '4★'],
            '3_star' => ['label' => '3 sao tiêu chuẩn', 'badge' => '3★'],
            'homestay' => ['label' => 'Homestay & Bungalow', 'badge' => 'Eco'],
        ];
    }

    protected function assertCluster(string $cluster): void
    {
        if (! config("services_catalog.clusters.{$cluster}")) {
            abort(404);
        }
    }

    protected function categoryLegend(string $cluster): string
    {
        return match ($cluster) {
            'train', 'ferry' => 'Tuyến',
            'flight' => 'Tuyến bay',
            'stay' => 'Khu vực lưu trú',
            'experience' => 'Loại trải nghiệm',
            default => 'Danh mục',
        };
    }
}
