<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Support\ListingChrome;
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
            $services = $this->data->servicesForHub($cluster);
            $filterCategories = array_values(array_filter(
                $categories,
                fn ($cat) => ((int) ($cat['count'] ?? 0)) > 0 && ! empty($cat['slug'])
            ));
            $categorySlugs = array_values(array_map(fn ($cat) => (string) $cat['slug'], $filterCategories));
            $unitLabel = $hub['unitLabel'] ?? 'dịch vụ';

            $listing = ListingChrome::make([
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
                'endpointParams' => ['cluster' => $cluster, 'variant' => 'wide'],
                'filterDefaults' => ['category' => $categorySlugs],
                'showCategoryFilter' => true,
                'showDurationFilter' => false,
                'showStyleFilter' => false,
                'categories' => $filterCategories,
                'categoryLegend' => $this->categoryLegend($cluster),
                'faqs' => $this->data->serviceListingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về '.strtolower((string) ($hub['title'] ?? 'dịch vụ')),
                'schemaItems' => collect($services)->map(fn ($s) => [
                    'name' => $s['title'],
                    'url' => locale_route('services.show', [
                        'cluster' => $s['cluster'] ?? $cluster,
                        'category' => $s['categorySlug'],
                        'slug' => $s['slug'],
                    ]),
                ])->all(),
                'schemaName' => seo_page_title($hub['title'] ?? 'Dịch vụ'),
            ]);

            return view('pages.services.hub', compact('listing', 'cluster'))->render();
        });
    }

    public function index(string $cluster, string $category)
    {
        return $this->cachedHtmlResponse(function () use ($cluster, $category) {
            $this->assertCluster($cluster);
            $cat = $this->data->serviceCategory($cluster, $category) ?? abort(404);
            $categories = $this->data->serviceCategories($cluster);
            $services = array_values(array_filter(
                $this->data->services($cluster),
                fn ($s) => ($s['categorySlug'] ?? '') === $category
            ));
            $hub = $this->data->serviceHub($cluster);
            $filterCategories = array_values(array_filter(
                $categories,
                fn ($c) => ((int) ($c['count'] ?? 0)) > 0 && ! empty($c['slug'])
            ));
            if (! empty($cat['slug']) && ! collect($filterCategories)->contains(fn ($c) => ($c['slug'] ?? '') === $cat['slug'])) {
                array_unshift($filterCategories, $cat);
            }
            $name = (string) ($cat['title'] ?? $cat['name'] ?? '');

            $listing = ListingChrome::make([
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
                'endpointParams' => ['cluster' => $cluster, 'variant' => 'wide'],
                'filterDefaults' => ['category' => [$cat['slug'] ?? $category]],
                'showCategoryFilter' => true,
                'showDurationFilter' => false,
                'showStyleFilter' => false,
                'categories' => $filterCategories,
                'categoryLegend' => $this->categoryLegend($cluster),
                'faqs' => $this->data->serviceListingFaqs(),
                'faqTitle' => 'Câu hỏi thường gặp về '.strtolower($name),
                'schemaItems' => collect($services)->map(fn ($s) => [
                    'name' => $s['title'],
                    'url' => locale_route('services.show', [
                        'cluster' => $cluster,
                        'category' => $s['categorySlug'],
                        'slug' => $s['slug'],
                    ]),
                ])->all(),
                'schemaName' => seo_page_title($name),
                'ratingMeta' => 'Đánh giá từ khách hàng đã chọn '.$name,
            ]);

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

            // Tối ưu hóa truy vấn: Chỉ lấy đúng 3 dịch vụ liên quan cùng danh mục thay vì map toàn bộ hàng trăm dịch vụ
            $catId = $service['categoryId'] ?? null;
            $serviceId = $service['id'] ?? null;
            $related = $this->data->relatedServicesForCategory($cluster, $catId, $serviceId, 3);

            return view('pages.services.show', [
                'cluster' => $cluster,
                'service' => $service,
                'related' => $related,
                'hub' => $this->data->serviceHub($cluster),
            ])->render();
        });
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
