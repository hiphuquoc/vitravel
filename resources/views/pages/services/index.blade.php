@extends('layouts.app')

@section('title', seo_page_title(($category['name'] ?? '') . ' — ' . ($hub['title'] ?? 'Dịch vụ')))
@section('meta_description', apply_site_brand($category['intro'] ?? ('Tuyển chọn ' . strtolower($category['name'] ?? 'dịch vụ') . ' tốt nhất — đặt qua chuyên gia bản địa :brand.')))

@section('content')
    @php
        $unitLabel = $hub['unitLabel'] ?? 'dịch vụ';
        $filterCategories = array_values(array_filter(
            $categories,
            fn ($cat) => ((int) ($cat['count'] ?? 0)) > 0 && ! empty($cat['slug'])
        ));
        // Giữ danh mục hiện tại trong filter kể cả khi count = 0 (tránh sidebar trống)
        if (! empty($category['slug']) && ! collect($filterCategories)->contains(fn ($c) => ($c['slug'] ?? '') === $category['slug'])) {
            array_unshift($filterCategories, $category);
        }
        $activeSlug = (string) ($category['slug'] ?? '');
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
                'cluster' => $cluster,
                'category' => $s['categorySlug'],
                'slug' => $s['slug'],
            ]),
        ])->all();
    @endphp

    <x-layout.page-header
        :title="$category['name']"
        :subtitle="$category['intro'] ?? null"
        :banner-src="$category['imageHero'] ?? null"
        :banner-srcset="$category['imageSrcset'] ?? null"
        :banner-alt="'Banner ' . $category['name']"
        :breadcrumbs="[
            ['label' => $hub['navLabel'] ?? $hub['title'], 'url' => locale_route('services.hub', ['cluster' => $cluster])],
            ['label' => $category['name']],
        ]" />

    <div class="container-site listing-layout section-band--sm"
        x-data="listingGrid(@js([
            'endpoint' => route('api.listings.services'),
            'params' => ['cluster' => $cluster, 'variant' => 'wide'],
            'syncUrl' => true,
            'filters' => [
                'category' => $activeSlug !== '' ? [$activeSlug] : [],
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

            @if (! empty($category['intro']))
                <div class="prose-travel listing-seo">
                    <p>{{ $category['intro'] }}</p>
                </div>
            @endif

            <x-shared.faq :faqs="$faqs" class="listing-faq" :title="'Câu hỏi thường gặp về ' . strtolower($category['name'])" />
        </div>
    </div>

    {!! schema_ld(schema()->itemList($schemaItems, seo_page_title($category['name'] ?? 'Dịch vụ'))) !!}
@endsection
