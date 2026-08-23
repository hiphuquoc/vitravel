<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoints for tour/cruise grids (filter + skeleton fetch).
 * Listing pages still SSR ItemList schema separately.
 */
class ListingController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function tours(Request $request): JsonResponse
    {
        $tours = $this->filterTours($this->data->tours(), $request);

        return $this->cardsResponse($tours, 'tour', $request->input('variant', 'wide'));
    }

    public function cruises(Request $request): JsonResponse
    {
        $cruises = $this->filterCruises($this->data->cruises(), $request);

        return $this->cardsResponse($cruises, 'cruise', $request->input('variant', 'wide'));
    }

    public function featuredTours(Request $request): JsonResponse
    {
        $limit = max(1, min(12, (int) $request->input('limit', 3)));

        return $this->cardsResponse(
            $this->data->featuredTours($limit),
            'tour',
            'compact',
        );
    }

    public function featuredCruises(Request $request): JsonResponse
    {
        $limit = max(1, min(12, (int) $request->input('limit', 3)));

        return $this->cardsResponse(
            $this->data->featuredCruises($limit),
            'cruise',
            'compact',
        );
    }

    public function featuredServices(Request $request): JsonResponse
    {
        $cluster = (string) $request->input('cluster', 'train');
        if ($cluster === '' || ! config("services_catalog.clusters.{$cluster}")) {
            return response()->json(['count' => 0, 'html' => ''], 404);
        }

        $limit = max(1, min(12, (int) $request->input('limit', 3)));

        return $this->cardsResponse(
            $this->data->featuredServices($cluster, $limit),
            'service',
            'compact',
        );
    }

    public function services(Request $request): JsonResponse
    {
        $cluster = (string) $request->input('cluster', '');
        if ($cluster === '' || ! config("services_catalog.clusters.{$cluster}")) {
            return response()->json(['count' => 0, 'html' => ''], 404);
        }

        $services = $this->filterServices($this->data->servicesForHub($cluster), $request);

        return $this->cardsResponse($services, 'service', $request->input('variant', 'wide'));
    }

    public function related(Request $request): JsonResponse
    {
        $kind = (string) $request->input('kind', 'tour');
        $exclude = (string) $request->input('exclude', '');
        $limit = max(1, min(6, (int) $request->input('limit', 3)));

        if ($kind === 'service' || $kind === 'stay') {
            $cluster = (string) $request->input('cluster', 'stay');
            $category = (string) $request->input('category', '');
            $categoryId = $request->filled('category_id') ? (int) $request->input('category_id') : null;
            $serviceId = $request->filled('service_id') ? (int) $request->input('service_id') : null;

            if ($categoryId) {
                $items = $this->data->relatedServicesForCategory($cluster, $categoryId, $serviceId, $limit);
            } else {
                $cat = $category !== '' ? $this->data->serviceCategory($cluster, $category) : null;
                $catId = $cat['id'] ?? null;
                $items = $catId ? $this->data->relatedServicesForCategory($cluster, $catId, $serviceId, $limit) : [];
            }
            return $this->cardsResponse($items, 'service', 'compact');
        }

        if ($kind === 'cruise') {
            $type = (string) $request->input('type', '');
            $items = array_values(array_filter(
                $this->data->cruises(),
                fn (array $c) => ($c['slug'] ?? '') !== $exclude
                    && ($type === '' || ($c['typeSlug'] ?? '') === $type)
            ));
        } else {
            $country = (string) $request->input('country', '');
            $pool = $country !== ''
                ? $this->data->toursByCountry($country)
                : $this->data->tours();
            $items = array_values(array_filter(
                $pool,
                fn (array $t) => ($t['slug'] ?? '') !== $exclude
            ));
        }

        $items = array_slice($items, 0, $limit);

        return $this->cardsResponse($items, $kind, 'compact');
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function cardsResponse(array $items, string $kind, string $variant): JsonResponse
    {
        $variant = $variant === 'compact' ? 'compact' : 'wide';
        $layout = $variant === 'compact' ? 'grid' : 'stack';

        $html = view('partials.listing-cards', [
            'items' => $items,
            'kind' => $kind,
            'variant' => $variant,
            'layout' => $layout,
        ])->render();

        return response()->json([
            'count' => count($items),
            'html' => $html,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $tours
     * @return array<int, array<string, mixed>>
     */
    protected function filterTours(array $tours, Request $request): array
    {
        $tours = $this->filterByDurationAndStyle($tours, $request);

        if ($request->exists('country')) {
            $countries = array_values(array_filter((array) $request->input('country', [])));
            if ($countries === []) {
                return [];
            }
            $tours = array_values(array_filter(
                $tours,
                function (array $tour) use ($countries) {
                    $slugs = $tour['countrySlugs'] ?? [];
                    if ($slugs === []) {
                        $slugs = array_filter([(string) ($tour['countrySlug'] ?? '')]);
                    }

                    return count(array_intersect($slugs, $countries)) > 0;
                }
            ));
        }

        if ($request->exists('category')) {
            $raw = $request->input('category');
            $categories = is_array($raw)
                ? array_values(array_filter(array_map('strval', $raw)))
                : array_values(array_filter([(string) $raw]));
            if ($categories === []) {
                return [];
            }
            $tours = array_values(array_filter(
                $tours,
                function (array $tour) use ($categories) {
                    $slugs = $tour['categorySlugs'] ?? [];

                    return count(array_intersect($slugs, $categories)) > 0;
                }
            ));
        }

        $q = mb_strtolower(trim((string) $request->input('q', '')));
        if ($q !== '') {
            $tours = array_values(array_filter($tours, function (array $tour) use ($q) {
                $haystack = mb_strtolower(implode(' ', [
                    $tour['title'] ?? '',
                    $tour['country'] ?? '',
                    $tour['start'] ?? '',
                    $tour['end'] ?? '',
                    implode(' ', $tour['places'] ?? []),
                ]));

                return str_contains($haystack, $q);
            }));
        }

        return $tours;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cruises
     * @return array<int, array<string, mixed>>
     */
    protected function filterCruises(array $cruises, Request $request): array
    {
        $cruises = $this->filterByDurationAndStyle($cruises, $request);

        // type[] (filter sidebar) — ưu tiên; fallback type= string (related / legacy)
        if ($request->exists('type')) {
            $raw = $request->input('type');
            $types = is_array($raw)
                ? array_values(array_filter(array_map('strval', $raw)))
                : array_values(array_filter([(string) $raw]));

            if ($types === []) {
                return [];
            }

            $cruises = array_values(array_filter(
                $cruises,
                fn (array $c) => in_array((string) ($c['typeSlug'] ?? ''), $types, true)
            ));
        }

        return $cruises;
    }

    /**
     * @param  array<int, array<string, mixed>>  $services
     * @return array<int, array<string, mixed>>
     */
    protected function filterServices(array $services, Request $request): array
    {
        if ($request->exists('category')) {
            $raw = $request->input('category');
            $categories = is_array($raw)
                ? array_values(array_filter(array_map('strval', $raw)))
                : array_values(array_filter([(string) $raw]));

            if ($categories === []) {
                return [];
            }

            $services = array_values(array_filter(
                $services,
                function (array $s) use ($categories) {
                    $slug = (string) ($s['categorySlug'] ?? '');
                    if ($slug !== '' && in_array($slug, $categories, true)) {
                        return true;
                    }

                    // Hub "Tất cả dịch vụ": nhóm theo cluster tàu / máy bay
                    $cluster = (string) ($s['cluster'] ?? '');
                    if ($cluster !== '' && in_array('_cluster_'.$cluster, $categories, true)) {
                        return true;
                    }

                    return false;
                }
            ));
        }

        return $services;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function filterByDurationAndStyle(array $items, Request $request): array
    {
        if ($request->exists('duration')) {
            $durations = array_values(array_filter((array) $request->input('duration', [])));
            if ($durations === []) {
                return [];
            }
            $items = array_values(array_filter($items, function (array $item) use ($durations) {
                $days = (int) ($item['days'] ?? 0);

                return collect($durations)->contains(fn (string $bucket) => match ($bucket) {
                    'lt7' => $days < 7,
                    '7-10' => $days >= 7 && $days <= 10,
                    '11-15' => $days >= 11 && $days <= 15,
                    'gt16' => $days > 15,
                    default => true,
                });
            }));
        }

        if ($request->exists('style')) {
            $styles = array_values(array_filter((array) $request->input('style', [])));
            if ($styles === []) {
                return [];
            }
            $items = array_values(array_filter(
                $items,
                fn (array $item) => array_intersect($styles, $item['styles'] ?? []) !== []
            ));
        }

        return $items;
    }
}
