@extends('layouts.app')

@section('title', $type['name'] . ' — Danh sách du thuyền | ViTravel')
@section('meta_description', 'Tuyển chọn ' . strtolower($type['name']) . ' tốt nhất với đánh giá thật từ khách hàng. Đặt cabin qua chuyên gia bản địa, nhận báo giá trong 24 giờ.')

@section('content')
    @php
        $durationKeys = array_map('strval', array_keys($durations));
        $styleKeys = array_map('strval', array_keys($styles));
        $filterDefaults = [
            // Chỉ loại trang hiện tại được check; user có thể thêm loại khác → fetch thêm
            'type' => [$type['slug']],
            'duration' => $durationKeys,
            'style' => $styleKeys,
        ];
        $schemaItems = collect($cruises)->map(fn ($c) => [
            'name' => $c['title'],
            'url' => locale_route('cruises.show', ['type' => $c['typeSlug'], 'slug' => $c['slug']]),
        ])->all();
    @endphp

    <x-layout.page-header
        :title="$type['name']"
        subtitle="Tuyển chọn những du thuyền đáng trải nghiệm nhất, được kiểm chứng bởi chính khách hàng của chúng tôi"
        :banner-src="$type['imageHero'] ?? null"
        :banner-srcset="$type['imageSrcset'] ?? null"
        :banner-alt="'Banner ' . $type['name']"
        :breadcrumbs="[
            ['label' => 'Du thuyền', 'url' => locale_route('cruises.hub')],
            ['label' => $type['name']],
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

            <div class="prose-travel listing-seo">
                <p>
                    Ngủ đêm trên <strong>{{ strtolower($type['name']) }}</strong> là trải nghiệm không thể thay thế.
                    ViTravel làm việc trực tiếp với từng nhà thuyền — không qua trung gian.
                </p>
            </div>

            <x-shared.faq :faqs="$faqs" class="listing-faq" title="Câu hỏi thường gặp về {{ strtolower($type['name']) }}" />
        </div>
    </div>

    {!! schema_ld(schema()->itemList($schemaItems, $type['name'] . ' — ViTravel')) !!}
@endsection
