@php
    /** @var array<string, mixed> $listing */
    $listing = \App\Support\ListingChrome::make(is_array($listing ?? null) ? $listing : []);
    $title = (string) ($listing['title'] ?? '');
@endphp

<x-layout.page-header
    :title="$title"
    :subtitle="$listing['subtitle'] ?: null"
    :banner-src="$listing['banner']"
    :banner-srcset="$listing['bannerSrcset']"
    :banner-alt="'Banner ' . $title"
    :breadcrumbs="$listing['breadcrumbs']"
/>

<div class="container-site listing-layout section-band--sm"
    x-data="listingGrid(@js([
        'endpoint' => $listing['endpoint'],
        'params' => $listing['endpointParams'],
        'syncUrl' => (bool) $listing['syncUrl'],
        'filters' => $listing['filterDefaults'],
    ]))">
    <x-tour.filter-sidebar
        :durations="$listing['durations']"
        :styles="$listing['styles']"
        :countries="$listing['countries']"
        :types="$listing['types']"
        :categories="$listing['categories']"
        :show-country-filter="$listing['showCountryFilter']"
        :show-type-filter="$listing['showTypeFilter']"
        :show-category-filter="$listing['showCategoryFilter']"
        :show-duration-filter="$listing['showDurationFilter']"
        :show-style-filter="$listing['showStyleFilter']"
        :category-legend="$listing['categoryLegend']"
    />

    <div class="min-w-0">
        <div class="listing-toolbar">
            <p class="listing-toolbar__count" x-show="count !== null" x-cloak>
                <span class="listing-toolbar__count-num" x-text="count"></span>
                <span class="listing-toolbar__count-label">{{ $listing['unitLabel'] }}</span>
            </p>
            @if ($listing['showDurationFilter'] || $listing['showStyleFilter'] || $listing['showCountryFilter'] || $listing['showTypeFilter'])
                <x-shared.sort-dropdown />
            @endif
        </div>

        <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>

        <div class="listing-results" x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
            <x-tour.listing-skeleton :count="(int) $listing['skeletonCount']" variant="wide" />
        </div>

        @if (! empty($listing['ratingMeta']))
            <div class="listing-rating-summary">
                <p class="listing-rating-summary__score">5.0</p>
                <x-shared.stars :rating="5" aria-label="5 trên 5 sao" />
                <p class="listing-rating-summary__meta">{{ $listing['ratingMeta'] }}</p>
            </div>
        @endif

        @if (! empty($listing['seoBody']))
            <div class="prose-travel listing-seo">
                {!! rich_body_html($listing['seoBody']) !!}
            </div>
        @endif

        @if (! empty($listing['faqs']))
            <x-shared.faq
                :faqs="$listing['faqs']"
                class="listing-faq"
                :title="$listing['faqTitle']"
            />
        @endif
    </div>
</div>

@if (! empty($listing['schemaItems']))
    {!! schema_ld(schema()->itemList(
        $listing['schemaItems'],
        $listing['schemaName'] ?: seo_page_title($title)
    )) !!}
@endif
