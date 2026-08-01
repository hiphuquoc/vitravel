<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
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

            return view('pages.services.hub', [
                'cluster' => $cluster,
                'hub' => $hub,
                'categories' => $categories,
                'services' => $services,
                'faqs' => $this->data->serviceListingFaqs(),
            ])->render();
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

            return view('pages.services.index', [
                'cluster' => $cluster,
                'hub' => $hub,
                'category' => $cat,
                'categories' => $categories,
                'services' => $services,
                'faqs' => $this->data->serviceListingFaqs(),
            ])->render();
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

            $related = array_values(array_filter(
                $this->data->services($cluster),
                fn ($s) => ($s['slug'] ?? '') !== $slug
                    && ($s['categorySlug'] ?? '') === $category
            ));
            $related = array_slice($related, 0, 3);

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
}
