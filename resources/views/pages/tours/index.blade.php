@extends('layouts.app')

@section('title', seo_page_title('Tour ' . $country['name'] . ' trọn gói'))
@section('meta_description', 'Danh sách tour ' . $country['name'] . ' trọn gói được thiết kế bởi chuyên gia bản địa: ' . $country['tagline'] . '. Nhận báo giá miễn phí trong 24 giờ.')

@section('content')
    @php
        $brand = site_brand();
        $durationKeys = array_map('strval', array_keys($durations));
        $styleKeys = array_map('strval', array_keys($styles));
        $filterDefaults = [
            // Chỉ quốc gia trang hiện tại được check; user có thể thêm quốc gia khác → fetch thêm
            'country' => [$country['slug']],
            'duration' => $durationKeys,
            'style' => $styleKeys,
        ];
        $schemaItems = collect($tours)->map(fn ($t) => [
            'name' => $t['title'],
            'url' => locale_route('tours.show', ['country' => $t['countrySlug'], 'slug' => $t['slug']]),
        ])->all();
    @endphp

    <x-layout.page-header
        :title="'Tour ' . $country['name']"
        :subtitle="$country['tagline']"
        :banner-src="$country['listingBanner'] ?? null"
        :banner-srcset="$country['listingBannerSrcset'] ?? null"
        :banner-alt="'Banner Tour ' . $country['name']"
        :breadcrumbs="[
            ['label' => 'Tour', 'url' => locale_route('tours.hub')],
            ['label' => 'Tour ' . $country['name']],
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
                <x-tour.listing-skeleton :count="4" variant="wide" />
            </div>

            <div class="listing-rating-summary">
                <p class="listing-rating-summary__score">5.0</p>
                <x-shared.stars :rating="5" aria-label="5 trên 5 sao" />
                <p class="listing-rating-summary__meta">Đánh giá từ khách hàng đã đi tour {{ $country['name'] }}</p>
            </div>

            @if (! empty($country['longForm'] ?? null))
                <div class="prose-travel listing-seo">
                    {!! nl2br(e($country['longForm'])) !!}
                </div>
            @endif

            <x-shared.faq :faqs="$faqs" class="listing-faq" title="Câu hỏi thường gặp về tour {{ $country['name'] }}" />
        </div>
    </div>

    {!! schema_ld(schema()->itemList($schemaItems, seo_page_title('Tour ' . $country['name']))) !!}
@endsection
