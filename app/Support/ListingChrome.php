<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Chrome chuẩn cho mọi trang listing (hub / country / chủ đề tour / cruise type / service category).
 * Dùng chung public Blade + chuẩn bị payload AI.
 *
 * @phpstan-type ListingChrome array{
 *   kind: string,
 *   title: string,
 *   subtitle: string,
 *   seoBody: string,
 *   seoTitle: string,
 *   seoDescription: string,
 *   banner: ?string,
 *   bannerSrcset: ?string,
 *   breadcrumbs: list<array{label: string, url?: string|null}>,
 *   unitLabel: string,
 *   endpoint: string,
 *   endpointParams: array<string, mixed>,
 *   filterDefaults: array<string, mixed>,
 *   showCountryFilter: bool,
 *   showTypeFilter: bool,
 *   showCategoryFilter: bool,
 *   showDurationFilter: bool,
 *   showStyleFilter: bool,
 *   showPropertyTypeFilter: bool,
 *   showPriceRangeFilter: bool,
 *   showAmenityFilter: bool,
 *   showStarFilter: bool,
 *   countries: list<array<string, mixed>>,
 *   types: list<array<string, mixed>>,
 *   categories: list<array<string, mixed>>,
 *   categoryLegend: string,
 *   propertyTypes: list<array<string, mixed>>,
 *   priceRanges: array<string, mixed>,
 *   amenities: array<string, mixed>,
 *   stars: array<string, mixed>,
 *   durations: array<string, mixed>,
 *   styles: array<string, mixed>,
 *   faqs: list<array{q: string, a: string}>,
 *   faqTitle: string,
 *   schemaItems: list<array{name: string, url: string}>,
 *   schemaName: string,
 *   ratingMeta: ?string,
 *   skeletonCount: int,
 *   perPage: int,
 *   syncUrl: bool
 * }
 */
final class ListingChrome
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return ListingChrome
     */
    public static function make(array $overrides = []): array
    {
        $base = [
            'kind' => 'listing',
            'title' => '',
            'subtitle' => '',
            'seoBody' => '',
            'seoTitle' => '',
            'seoDescription' => '',
            'banner' => null,
            'bannerSrcset' => null,
            'breadcrumbs' => [],
            'unitLabel' => 'tour',
            'endpoint' => '',
            'endpointParams' => [],
            'filterDefaults' => [],
            'showCountryFilter' => false,
            'showTypeFilter' => false,
            'showCategoryFilter' => false,
            'showDurationFilter' => true,
            'showStyleFilter' => true,
            'showPropertyTypeFilter' => false,
            'showPriceRangeFilter' => false,
            'showAmenityFilter' => false,
            'showStarFilter' => false,
            'countries' => [],
            'types' => [],
            'categories' => [],
            'categoryLegend' => 'Danh mục',
            'propertyTypes' => [],
            'priceRanges' => [],
            'amenities' => [],
            'stars' => [],
            'durations' => [],
            'styles' => [],
            'faqs' => [],
            'faqTitle' => 'Câu hỏi thường gặp',
            'schemaItems' => [],
            'schemaName' => '',
            'ratingMeta' => null,
            'skeletonCount' => 5,
            'perPage' => 5,
            'syncUrl' => true,
        ];

        return array_merge($base, $overrides);
    }
}
