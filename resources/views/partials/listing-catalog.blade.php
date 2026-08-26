@php
    /** @var array<string, mixed> $listing */
    $listing = \App\Support\ListingChrome::make(is_array($listing ?? null) ? $listing : []);
    $title = (string) ($listing['title'] ?? '');
    $skeletonCount = max(1, min(12, (int) ($listing['skeletonCount'] ?? 5)));
    $cardKind = match ((string) ($listing['cardKind'] ?? 'tour')) {
        'cruise', 'service' => (string) $listing['cardKind'],
        default => 'tour',
    };
    $seedHtml = trim((string) ($listing['seedHtml'] ?? ''));
    $hasSeed = $seedHtml !== '';
    $seedCount = $listing['seedCount'];
    $seedCursor = $listing['seedCursor'] ?? null;
    $seedHasMore = (bool) ($listing['seedHasMore'] ?? false);
    $selectedSort = (string) request('sort', 'popular');
    if (! in_array($selectedSort, ['popular', 'newest', 'price_asc', 'price_desc', 'rating_desc', 'duration_asc'], true)) {
        $selectedSort = 'popular';
    }

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
        'lockedFilters' => $listing['filterDefaults'],
        'filters' => [],
        'labelMap' => $labelMap,
        'priceMin' => (int) ($listing['priceMin'] ?? 0),
        'priceMax' => (int) ($listing['priceMax'] ?? 10000000),
        'priceStep' => (int) ($listing['priceStep'] ?? 100000),
        'sort' => $selectedSort,
        'initialLimit' => $skeletonCount,
        'eagerLimit' => 10,
        'scrollLimit' => 20,
        'seeded' => $hasSeed,
        'seedCount' => $hasSeed ? $seedCount : null,
        'seedCursor' => $hasSeed ? $seedCursor : null,
        'seedHasMore' => $hasSeed ? $seedHasMore : false,
        'skeletonCount' => $skeletonCount,
        'cardKind' => $cardKind,
    ]))"
    @vt-select-change="if ($event.detail?.name === 'sort') setSort($event.detail.value)"
>
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
            <div class="listing-toolbar__count-wrap">
                <p class="listing-toolbar__count" x-show="count !== null" x-cloak>
                    <span class="listing-toolbar__count-num" x-text="count"></span>
                    <span class="listing-toolbar__count-label">{{ $listing['unitLabel'] }}</span>
                </p>
                <p class="listing-toolbar__count listing-toolbar__count--skeleton" x-show="count === null && loading" aria-hidden="true">
                    <span class="listing-skeleton__line listing-skeleton__line--count listing-skeleton__shimmer"></span>
                    <span class="listing-toolbar__count-label text-transparent">{{ $listing['unitLabel'] }}</span>
                </p>
            </div>
            <div class="listing-toolbar__sort">
                <x-shared.sort-dropdown
                    :options="[
                        ['value' => 'popular', 'label' => 'Phổ biến nhất'],
                        ['value' => 'newest', 'label' => 'Mới nhất'],
                        ['value' => 'price_asc', 'label' => 'Giá thấp → cao'],
                        ['value' => 'price_desc', 'label' => 'Giá cao → thấp'],
                        ['value' => 'rating_desc', 'label' => 'Đánh giá cao nhất'],
                        ['value' => 'duration_asc', 'label' => 'Thời lượng ngắn → dài'],
                    ]"
                    :selected="$selectedSort"
                />
            </div>
        </div>

        <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>

        <div x-show="hasActiveFilters" x-cloak class="listing-active-filters">
            <span class="listing-active-filters__label">
                <x-icon name="filter" class="size-4 text-primary-600" />
                <span>Đang lọc</span>
            </span>

            <template x-if="isPriceFiltered">
                <span class="listing-active-filters__chip">
                    <span class="listing-active-filters__chip-kicker">Giá</span>
                    <span class="listing-active-filters__chip-value" x-text="priceRangeLabel"></span>
                    <button
                        type="button"
                        class="listing-active-filters__chip-remove"
                        @click="resetPriceRange()"
                        aria-label="Bỏ lọc khoảng giá"
                    >
                        <x-icon name="close" class="size-3.5" />
                    </button>
                </span>
            </template>

            <template x-for="(vals, grp) in filters" :key="grp">
                <template x-for="val in vals" :key="grp + '-' + val">
                    <span class="listing-active-filters__chip">
                        <span x-text="getFilterLabel(grp, val)"></span>
                        <button
                            type="button"
                            class="listing-active-filters__chip-remove"
                            @click="clearFilter(grp, val)"
                            aria-label="Bỏ tiêu chí lọc"
                        >
                            <x-icon name="close" class="size-3.5" />
                        </button>
                    </span>
                </template>
            </template>

            <button
                type="button"
                class="listing-active-filters__clear"
                @click="clearAllFilters()"
            >
                Xóa tất cả
            </button>
        </div>

        <div
            class="listing-results"
            x-ref="results"
            :class="loading && !seededBoot && 'listing-results--busy'"
            :aria-busy="loading ? 'true' : 'false'"
        >
            @if ($hasSeed)
                <div data-listing-mount="1">{!! $seedHtml !!}</div>
            @else
                <x-tour.listing-skeleton :count="$skeletonCount" variant="wide" :kind="$cardKind" />
            @endif
        </div>

        {{-- Markup skeleton tái dùng khi đổi bộ lọc --}}
        <div class="hidden" x-ref="skeletonTpl" aria-hidden="true">
            <x-tour.listing-skeleton :count="$skeletonCount" variant="wide" :kind="$cardKind" />
        </div>

        {{-- Sentinel quan sát cuộn đón đầu (Chỉ chạy trong 2 đợt cuộn đầu tiên của Giai đoạn 3) --}}
        <div x-ref="sentinel" x-show="!requireManualClick && hasMore" class="listing-sentinel h-10 w-full pointer-events-none" aria-hidden="true"></div>

        {{-- Loading indicator nhẹ nhàng khi đang tự động cuộn tải --}}
        <div x-show="loadingMore && !requireManualClick" x-cloak class="listing-loading-more site-mt">
            <div class="flex flex-col items-center justify-center gap-3 py-8 text-center">
                <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-white border border-line shadow-xs text-primary-700 text-sm font-medium">
                    <svg class="animate-spin size-4 text-primary-600" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Đang tải thêm {{ $listing['unitLabel'] }}...</span>
                </div>
            </div>
        </div>

        {{-- KHU VỰC NÚT TẢI THÊM CAO CẤP (Hiển thị từ lần cuộn thứ 3 trở đi để tránh tải vô hạn) --}}
        <div x-show="!loading && hasMore && requireManualClick" x-cloak class="listing-load-more-section site-mt">
            <div class="flex flex-col items-center justify-center gap-4 py-8 text-center">
                <div class="flex flex-col items-center gap-2" x-show="count > 0">
                    <div class="flex items-center gap-2 text-sm font-medium text-ink-soft">
                        <span>Đang hiển thị <strong class="text-ink font-bold" x-text="loadedCount"></strong> / <strong class="text-ink font-bold" x-text="count"></strong> {{ $listing['unitLabel'] }}</span>
                    </div>
                    <div class="h-2 w-52 sm:w-72 rounded-full bg-page-soft overflow-hidden border border-line p-0.5">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-500 shadow-xs"
                            :style="'width: ' + progressPercent + '%'"
                        ></div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="loadMore()"
                    :disabled="loadingMore"
                    class="group relative inline-flex items-center justify-center gap-2.5 px-8 py-3 rounded-xl font-bold text-sm text-primary-700 bg-white hover:bg-primary-50/80 border-2 border-primary-600/30 hover:border-primary-600 shadow-sm hover:shadow-md hover:shadow-primary-600/10 active:scale-[0.98] transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed cursor-pointer"
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
                            <span>Xem thêm {{ $listing['unitLabel'] }}</span>
                            <span class="inline-flex items-center justify-center text-xs font-bold px-2 py-0.5 rounded-full bg-primary-100 text-primary-800" x-show="remainingCount > 0" x-text="'+' + Math.min(20, remainingCount)"></span>
                            <x-icon name="arrow-down" class="size-4 text-primary-600 group-hover:translate-y-0.5 transition-transform duration-200" />
                        </span>
                    </template>
                </button>
            </div>
        </div>

        <div x-show="!loading && !loadingMore && !hasMore && count > 0" x-cloak class="listing-end-notice site-mt">
            <div class="relative flex items-center justify-center py-8">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-line/80"></div>
                </div>

                <div class="relative inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-page-soft border border-line shadow-2xs text-sm font-medium text-ink-soft select-none">
                    <span class="flex items-center justify-center size-5 rounded-full bg-primary-100 text-primary-700">
                        <svg class="size-3.5 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </span>
                    <span>Đã hiển thị toàn bộ <strong class="text-ink font-bold" x-text="count"></strong> {{ $listing['unitLabel'] }}</span>
                </div>
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
