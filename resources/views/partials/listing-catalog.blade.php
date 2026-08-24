@php
    /** @var array<string, mixed> $listing */
    $listing = \App\Support\ListingChrome::make(is_array($listing ?? null) ? $listing : []);
    $title = (string) ($listing['title'] ?? '');
    $perPage = (int) ($listing['perPage'] ?? 5);
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
        'perPage' => $perPage,
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
            <x-tour.listing-skeleton :count="(int) ($listing['skeletonCount'] ?? 5)" variant="wide" />
        </div>

        {{-- Sentinel quan sát cuộn vô tận (IntersectionObserver) --}}
        <div x-ref="sentinel" class="listing-sentinel h-4 w-full pointer-events-none" aria-hidden="true"></div>

        {{-- Loading indicator khi cuộn tải tiếp 5 khách sạn --}}
        <div x-show="loadingMore" x-cloak class="listing-loading-more site-mt">
            <div class="flex flex-col items-center justify-center gap-2.5 py-6 text-center">
                <div class="flex items-center gap-2">
                    <span class="inline-block size-2 rounded-full bg-primary-600 animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="inline-block size-2 rounded-full bg-primary-600 animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="inline-block size-2 rounded-full bg-primary-600 animate-bounce" style="animation-delay: 300ms"></span>
                </div>
                <p class="text-xs font-medium text-muted">Đang tải thêm {{ $listing['unitLabel'] }}...</p>
            </div>
        </div>

        {{-- Thông báo khi đã hiển thị hết danh sách --}}
        <div x-show="!loading && !loadingMore && !hasMore && count > 5" x-cloak class="listing-end-notice site-mt">
            <div class="flex items-center justify-center gap-3 py-6 text-center text-xs text-muted">
                <span class="h-px w-12 bg-line sm:w-20"></span>
                <span>Đã hiển thị tất cả <strong class="text-ink font-semibold" x-text="count"></strong> {{ $listing['unitLabel'] }}</span>
                <span class="h-px w-12 bg-line sm:w-20"></span>
            </div>
        </div>

        {{-- Nút bấm thủ công dự phòng khi cần --}}
        <div x-show="!loading && !loadingMore && hasMore" x-cloak class="listing-load-more-btn site-mt text-center">
            <button type="button" @click="loadMore()" class="btn-outline">
                <span>Xem thêm {{ $listing['unitLabel'] }}</span>
                <x-icon name="arrow-down" class="size-4" />
            </button>
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
