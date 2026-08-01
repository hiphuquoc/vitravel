@extends('layouts.app')

@section('title', $hub['seoTitle'] ?? ($hub['title'] . ' — ViTravel'))
@section('meta_description', $hub['seoDescription'] ?? $hub['subtitle'])

@section('content')
    @php
        $unitLabel = $hub['unitLabel'] ?? 'dịch vụ';
        $filterCategories = array_values(array_filter(
            $categories,
            fn ($cat) => ((int) ($cat['count'] ?? 0)) > 0 && ! empty($cat['slug'])
        ));
        $categorySlugs = array_values(array_map(fn ($cat) => (string) $cat['slug'], $filterCategories));
        $categoryLegend = match ($cluster) {
            'train' => 'Tuyến tàu',
            'flight' => 'Tuyến bay',
            'stay' => 'Khu vực lưu trú',
            'experience' => 'Loại trải nghiệm',
            default => 'Danh mục',
        };
        $schemaItems = collect($services)->map(fn ($s) => [
            'name' => $s['title'],
            'url' => locale_route('services.show', [
                'cluster' => $s['cluster'] ?? $cluster,
                'category' => $s['categorySlug'],
                'slug' => $s['slug'],
            ]),
        ])->all();
    @endphp

    <x-layout.page-header
        :title="$hub['title']"
        :subtitle="$hub['subtitle']"
        :banner-src="$hub['listingBanner'] ?? null"
        :banner-srcset="$hub['listingBannerSrcset'] ?? null"
        :banner-alt="'Banner ' . $hub['title']"
        :breadcrumbs="[
            ['label' => $hub['navLabel'] ?? $hub['title']],
        ]" />

    <div class="container-site listing-layout section-band--sm"
        x-data="listingGrid(@js([
            'endpoint' => route('api.listings.services'),
            'params' => ['cluster' => $cluster, 'variant' => 'wide'],
            'syncUrl' => true,
            'filters' => [
                'category' => $categorySlugs,
            ],
        ]))">
        <x-tour.filter-sidebar
            :categories="$filterCategories"
            :show-category-filter="true"
            :show-duration-filter="false"
            :show-style-filter="false"
            :category-legend="$categoryLegend" />

        <div class="min-w-0">
            <div class="listing-toolbar">
                <p class="listing-toolbar__count" x-show="count !== null" x-cloak>
                    <span class="listing-toolbar__count-num" x-text="count"></span>
                    <span class="listing-toolbar__count-label">{{ $unitLabel }}</span>
                </p>
            </div>

            <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>

            <div class="listing-results" x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
                <x-tour.listing-skeleton :count="4" variant="wide" />
            </div>

            <div class="prose-travel listing-seo">
                <p>
                    Trang <strong>{{ strtolower($hub['title']) }}</strong> của ViTravel tập hợp các dịch vụ được chọn lọc —
                    đặt qua <strong>chuyên gia bản địa</strong>, giá minh bạch và hỗ trợ 24/7 suốt hành trình.
                </p>
            </div>

            <x-shared.faq :faqs="$faqs" class="listing-faq" :title="'Câu hỏi thường gặp về ' . strtolower($hub['title'])" />
        </div>
    </div>

    {!! schema_ld(schema()->itemList($schemaItems, $hub['title'] . ' — ViTravel')) !!}
@endsection
