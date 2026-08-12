@extends('layouts.app')

@section('title', $hub['seoTitle'] ?: seo_page_title('Tour trọn gói Đông Nam Á'))
@section('meta_description', $hub['seoDescription'] ?: 'Tất cả tour trọn gói Việt Nam, Campuchia, Lào, Thái Lan và Bali — thiết kế bởi chuyên gia bản địa. Lọc theo quốc gia, thời lượng và phong cách.')

@section('content')
    @php
        $brand = site_brand();
        $durationKeys = array_map('strval', array_keys($durations));
        $styleKeys = array_map('strval', array_keys($styles));
        $countrySlugs = array_values(array_filter(array_map(fn ($c) => $c['slug'] ?? null, $countries)));
        $filterDefaults = [
            'country' => $countrySlugs,
            'duration' => $durationKeys,
            'style' => $styleKeys,
        ];
        $schemaItems = collect($tours)->map(fn ($t) => [
            'name' => $t['title'],
            'url' => locale_route('tours.show', ['country' => $t['countrySlug'], 'slug' => $t['slug']]),
        ])->all();
    @endphp

    <x-layout.page-header
        :title="$hub['title']"
        :subtitle="$hub['subtitle']"
        :banner-src="$hub['listingBanner'] ?? null"
        :banner-srcset="$hub['listingBannerSrcset'] ?? null"
        :banner-alt="'Banner ' . $hub['title']"
        :breadcrumbs="[
            ['label' => 'Tour'],
        ]" />

    <div class="container-site listing-layout section-band--sm"
        x-data="listingGrid(@js([
            'endpoint' => route('api.listings.tours'),
            'syncUrl' => true,
            'filters' => $filterDefaults,
        ]))">
        <x-tour.filter-sidebar
            :durations="$durations"
            :styles="$styles"
            :countries="$countries"
            :show-country-filter="true" />

        <div class="min-w-0">
            <div class="listing-toolbar">
                <p class="listing-toolbar__count" x-show="count !== null" x-cloak>
                    <span class="listing-toolbar__count-num" x-text="count"></span>
                    <span class="listing-toolbar__count-label">tour</span>
                </p>
                <x-shared.sort-dropdown />
            </div>

            <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>

            <div class="listing-results" x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
                <x-tour.listing-skeleton :count="5" variant="wide" />
            </div>

            <div class="listing-rating-summary">
                <p class="listing-rating-summary__score">5.0</p>
                <x-shared.stars :rating="5" aria-label="5 trên 5 sao" />
                <p class="listing-rating-summary__meta">Đánh giá từ khách hàng đã đi tour với {{ $brand }}</p>
            </div>

            @if (! empty($hub['seoBody']))
                <div class="prose-travel listing-seo">
                    {!! nl2br(e($hub['seoBody'])) !!}
                </div>
            @endif

            <x-shared.faq :faqs="$faqs" class="listing-faq" title="Câu hỏi thường gặp về tour trọn gói" />
        </div>
    </div>

    {!! schema_ld(schema()->itemList($schemaItems, seo_page_title('Tour trọn gói'))) !!}
@endsection
