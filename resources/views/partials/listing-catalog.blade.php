@php
    /** @var array<string, mixed> $listing */
    $listing = \App\Support\ListingChrome::make(is_array($listing ?? null) ? $listing : []);
    $title = (string) ($listing['title'] ?? '');

    $labelMap = [];
    foreach ($listing['categories'] ?? [] as $c) {
        if (!empty($c['slug'])) {
            $labelMap['category:' . $c['slug']] = $c['name'] ?? $c['title'] ?? $c['slug'];
            $labelMap[$c['slug']] = $c['name'] ?? $c['title'] ?? $c['slug'];
        }
    }
    foreach ($listing['propertyTypes'] ?? [] as $pt) {
        if (!empty($pt['slug'])) {
            $labelMap['property_type:' . $pt['slug']] = $pt['name'] ?? $pt['slug'];
            $labelMap[$pt['slug']] = $pt['name'] ?? $pt['slug'];
        }
    }
    foreach ($listing['priceRanges'] ?? [] as $pk => $pv) {
        $labelMap['price_range:' . $pk] = is_array($pv) ? ($pv['label'] ?? $pk) : (string)$pv;
        $labelMap[$pk] = is_array($pv) ? ($pv['label'] ?? $pk) : (string)$pv;
    }
    foreach ($listing['amenities'] ?? [] as $ak => $av) {
        $labelMap['amenity:' . $ak] = is_array($av) ? ($av['label'] ?? $ak) : (string)$av;
        $labelMap[$ak] = is_array($av) ? ($av['label'] ?? $ak) : (string)$av;
    }
    foreach ($listing['stars'] ?? [] as $sk => $sv) {
        $labelMap['star:' . $sk] = is_array($sv) ? ($sv['label'] ?? $sk) : (string)$sv;
        $labelMap[$sk] = is_array($sv) ? ($sv['label'] ?? $sk) : (string)$sv;
    }
    foreach ($listing['countries'] ?? [] as $co) {
        if (!empty($co['slug'])) {
            $labelMap['country:' . $co['slug']] = $co['name'] ?? $co['slug'];
            $labelMap[$co['slug']] = $co['name'] ?? $co['slug'];
        }
    }
    foreach ($listing['types'] ?? [] as $t) {
        if (!empty($t['slug'])) {
            $labelMap['type:' . $t['slug']] = $t['name'] ?? $t['slug'];
            $labelMap[$t['slug']] = $t['name'] ?? $t['slug'];
        }
    }
    foreach ($listing['durations'] ?? [] as $dk => $dv) {
        $labelMap['duration:' . $dk] = is_array($dv) ? ($dv['label'] ?? $dk) : (string)$dv;
        $labelMap[$dk] = is_array($dv) ? ($dv['label'] ?? $dk) : (string)$dv;
    }
    foreach ($listing['styles'] ?? [] as $sk => $sv) {
        $labelMap['style:' . $sk] = is_array($sv) ? ($sv['label'] ?? $sk) : (string)$sv;
        $labelMap[$sk] = is_array($sv) ? ($sv['label'] ?? $sk) : (string)$sv;
    }
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
        'labelMap' => $labelMap,
        'initialLimit' => 5,
        'eagerLimit' => 10,
        'scrollLimit' => 20,
    ]))">
    <x-tour.filter-sidebar
        :durations="$listing['durations']"
        :styles="$listing['styles']"
        :countries="$listing['countries']"
        :types="$listing['types']"
        :categories="$listing['categories']"
        :property-types="$listing['propertyTypes']"
        :price-ranges="$listing['priceRanges']"
        :amenities="$listing['amenities']"
        :stars="$listing['stars']"
        :show-country-filter="$listing['showCountryFilter']"
        :show-type-filter="$listing['showTypeFilter']"
        :show-category-filter="$listing['showCategoryFilter']"
        :show-duration-filter="$listing['showDurationFilter']"
        :show-style-filter="$listing['showStyleFilter']"
        :show-property-type-filter="$listing['showPropertyTypeFilter']"
        :show-price-range-filter="$listing['showPriceRangeFilter']"
        :show-amenity-filter="$listing['showAmenityFilter']"
        :show-star-filter="$listing['showStarFilter']"
        :category-legend="$listing['categoryLegend']"
        :type-legend="$listing['typeLegend'] ?? 'Loại du thuyền'"
        :unit-label="$listing['unitLabel']"
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

        {{-- Dải thẻ lọc đang chọn (Active Filters Bar — Chuẩn Design System) --}}
        <div x-show="hasActiveFilters" x-cloak class="listing-active-filters flex flex-wrap items-center gap-2 mb-4 p-3 rounded-xl bg-page-soft border border-line">
            <span class="text-xs font-bold text-muted flex items-center gap-1.5 pl-1">
                <x-icon name="filter" class="size-3.5 text-primary-600" />
                <span>Đang lọc:</span>
            </span>
            
            <template x-for="(vals, grp) in filters" :key="grp">
                <template x-for="val in vals" :key="grp + '-' + val">
                    <span class="vt-chip inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white border border-line text-xs font-semibold text-ink shadow-2xs">
                        <span x-text="getFilterLabel(grp, val)"></span>
                        <button
                            type="button"
                            @click="clearFilter(grp, val)"
                            class="text-muted hover:text-accent-600 cursor-pointer transition-colors"
                            aria-label="Bỏ tiêu chí lọc"
                        >
                            <x-icon name="close" class="size-3" />
                        </button>
                    </span>
                </template>
            </template>

            <button
                type="button"
                @click="clearAllFilters()"
                class="text-xs font-bold text-accent-600 hover:text-accent-700 hover:underline ml-auto pr-1 cursor-pointer"
            >
                Xóa tất cả
            </button>
        </div>

        <div class="listing-results" x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
            <x-tour.listing-skeleton :count="5" variant="wide" />
        </div>

        {{-- Sentinel quan sát cuộn đón đầu (Chỉ chạy trong 2 đợt cuộn đầu tiên của Giai đoạn 3) --}}
        <div x-ref="sentinel" x-show="!requireManualClick && hasMore" class="listing-sentinel h-10 w-full pointer-events-none" aria-hidden="true"></div>

        {{-- Loading indicator nhẹ nhàng khi đang tự động cuộn tải --}}
        <div x-show="loadingMore && !requireManualClick" x-cloak class="listing-loading-more site-mt">
            <div class="flex flex-col items-center justify-center gap-2.5 py-6 text-center">
                <div class="flex items-center gap-2">
                    <span class="inline-block size-2 rounded-full bg-primary-600 animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="inline-block size-2 rounded-full bg-primary-600 animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="inline-block size-2 rounded-full bg-primary-600 animate-bounce" style="animation-delay: 300ms"></span>
                </div>
                <p class="text-xs font-medium text-muted">Đang tải thêm {{ $listing['unitLabel'] }}...</p>
            </div>
        </div>

        {{-- KHU VỰC NÚT TẢI THÊM CAO CẤP (Hiển thị từ lần cuộn thứ 3 trở đi để tránh tải vô hạn) --}}
        <div x-show="!loading && hasMore && requireManualClick" x-cloak class="listing-load-more-section site-mt">
            <div class="flex flex-col items-center justify-center gap-4 py-8 text-center">
                {{-- Thanh đếm tiến trình xem danh mục trực quan --}}
                <div class="flex flex-col items-center gap-2" x-show="count > 0">
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-muted">
                        <span>Đang hiển thị <strong class="text-ink font-bold" x-text="loadedCount"></strong> / <strong class="text-ink font-bold" x-text="count"></strong> {{ $listing['unitLabel'] }}</span>
                    </div>
                    <div class="h-1.5 w-48 sm:w-64 rounded-full bg-page-soft overflow-hidden border border-line">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-500"
                            :style="'width: ' + progressPercent + '%'"
                        ></div>
                    </div>
                </div>

                {{-- Nút bấm tải thêm chuẩn Luxury UI --}}
                <button
                    type="button"
                    @click="loadMore()"
                    :disabled="loadingMore"
                    class="group relative inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl font-bold text-sm text-primary-700 bg-white hover:bg-primary-50/80 border-2 border-primary-600/30 hover:border-primary-600 shadow-sm hover:shadow-md hover:shadow-primary-600/10 active:scale-[0.98] transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed cursor-pointer"
                >
                    <template x-if="loadingMore">
                        <span class="flex items-center gap-2.5">
                            <svg class="animate-spin size-4 text-primary-600" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Đang tải thêm...</span>
                        </span>
                    </template>
                    <template x-if="!loadingMore">
                        <span class="flex items-center gap-2.5">
                            <span>Tải thêm {{ $listing['unitLabel'] }}</span>
                            <span class="inline-flex items-center justify-center text-xs font-semibold px-2 py-0.5 rounded-full bg-primary-100/80 text-primary-700" x-show="remainingCount > 0" x-text="'+' + Math.min(20, remainingCount)"></span>
                            <x-icon name="arrow-down" class="size-4 text-primary-600 group-hover:translate-y-0.5 transition-transform duration-200" />
                        </span>
                    </template>
                </button>
            </div>
        </div>

        {{-- Thông báo khi đã hiển thị hết danh sách --}}
        <div x-show="!loading && !loadingMore && !hasMore && count > 5" x-cloak class="listing-end-notice site-mt">
            <div class="flex items-center justify-center gap-3 py-8 text-center text-xs text-muted">
                <span class="h-px w-12 bg-line sm:w-24"></span>
                <div class="flex items-center gap-2">
                    <x-icon name="check" class="size-4 text-primary-600" />
                    <span>Đã hiển thị tất cả <strong class="text-ink font-semibold" x-text="count"></strong> {{ $listing['unitLabel'] }}</span>
                </div>
                <span class="h-px w-12 bg-line sm:w-24"></span>
            </div>
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
