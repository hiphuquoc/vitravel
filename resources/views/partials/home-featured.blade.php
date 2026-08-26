{{--
  Khối featured trang chủ: skeleton compact (carousel) + listingGrid lazy IO.
  Không chặn SSR chrome; fetch khi gần viewport.
--}}
@props([
    'endpoint',
    'params' => [],
    'kind' => 'tour', // tour | cruise | service
    'limit' => 12,
    'skeletonCount' => 4,
])

@php
    $kind = match ((string) $kind) {
        'cruise', 'service' => (string) $kind,
        default => 'tour',
    };
    $limit = max(1, min(12, (int) $limit));
    $skeletonCount = max(3, min(6, (int) $skeletonCount));
    $params = is_array($params) ? $params : [];
    $params['limit'] = $params['limit'] ?? $limit;
@endphp

<div
    class="home-featured"
    x-data="listingGrid(@js([
        'endpoint' => $endpoint,
        'params' => $params,
        'syncUrl' => false,
        'initialLimit' => $limit,
        'eagerLimit' => 0,
        'scrollLimit' => 0,
        'maxScrollAutoLoads' => 0,
        'skeletonCount' => $skeletonCount,
        'cardKind' => $kind,
        'deferUntilVisible' => true,
        'deferRootMargin' => '280px',
    ]))"
>
    <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>
    <div
        class="listing-results home-featured__results"
        x-ref="results"
        :class="loading && 'listing-results--busy'"
        :aria-busy="loading ? 'true' : 'false'"
    >
        <x-tour.listing-skeleton
            :count="$skeletonCount"
            variant="compact"
            :kind="$kind"
            layout="carousel"
        />
    </div>
    <div class="hidden" x-ref="skeletonTpl" aria-hidden="true">
        <x-tour.listing-skeleton
            :count="$skeletonCount"
            variant="compact"
            :kind="$kind"
            layout="carousel"
        />
    </div>
</div>
