@extends('layouts.app')

@section('title', $hub['seoTitle'] ?: seo_page_title('Du thuyền Đông Nam Á'))
@section('meta_description', $hub['seoDescription'] ?: apply_site_brand('Tuyển chọn du thuyền Hạ Long, Lan Hạ, Mekong — đặt cabin qua chuyên gia bản địa :brand.'))

@section('content')
    @php
        $durationKeys = array_map('strval', array_keys($durations));
        $styleKeys = array_map('strval', array_keys($styles));
        $typeSlugs = array_values(array_filter(array_map(fn ($t) => $t['slug'] ?? null, $types)));
        $filterDefaults = [
            'type' => $typeSlugs,
            'duration' => $durationKeys,
            'style' => $styleKeys,
        ];
        $schemaItems = collect($cruises)->map(fn ($c) => [
            'name' => $c['title'],
            'url' => locale_route('cruises.show', ['type' => $c['typeSlug'], 'slug' => $c['slug']]),
        ])->all();
    @endphp

    <x-layout.page-header
        :title="$hub['title']"
        :subtitle="$hub['subtitle']"
        :banner-src="$hub['listingBanner'] ?? null"
        :banner-srcset="$hub['listingBannerSrcset'] ?? null"
        :banner-alt="'Banner ' . $hub['title']"
        :breadcrumbs="[
            ['label' => 'Du thuyền'],
        ]" />

    <div class="container-site listing-layout section-band--sm"
        x-data="listingGrid(@js([
            'endpoint' => route('api.listings.cruises'),
            'params' => ['variant' => 'wide'],
            'syncUrl' => true,
            'filters' => $filterDefaults,
        ]))">
        <x-tour.filter-sidebar
            :durations="$durations"
            :styles="$styles"
            :types="$types"
            :show-type-filter="true" />

        <div class="min-w-0">
            <div class="listing-toolbar">
                <p class="listing-toolbar__count" x-show="count !== null" x-cloak>
                    <span class="listing-toolbar__count-num" x-text="count"></span>
                    <span class="listing-toolbar__count-label">du thuyền</span>
                </p>
                <x-shared.sort-dropdown />
            </div>

            <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>

            <div class="listing-results" x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
                <x-tour.listing-skeleton :count="4" variant="wide" />
            </div>

            @if (! empty($hub['seoBody']))
                <div class="prose-travel listing-seo">
                    {!! nl2br(e($hub['seoBody'])) !!}
                </div>
            @endif

            <x-shared.faq :faqs="$faqs" class="listing-faq" title="Câu hỏi thường gặp về du thuyền" />
        </div>
    </div>

    {!! schema_ld(schema()->itemList($schemaItems, seo_page_title('Du thuyền'))) !!}
@endsection
