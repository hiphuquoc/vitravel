@props([
    'title' => 'Có thể bạn cũng thích',
    'ariaLabel' => 'Gợi ý liên quan',
    'params' => [],
    'count' => 3,
    'kind' => 'tour', // tour | cruise | service — skeleton compact
])

@php
    $count = max(1, min(6, (int) $count));
    $kind = match ((string) $kind) {
        'cruise', 'service' => (string) $kind,
        default => 'tour',
    };
    $params = is_array($params) ? $params : [];
    $params['limit'] = $params['limit'] ?? $count;
@endphp

{{-- Related: skeleton compact + listingGrid lazy (IntersectionObserver) — không chặn SSR chrome --}}
<section
    class="cv-auto container-site section-band--sm detail-related"
    aria-label="{{ $ariaLabel }}"
    x-data="listingGrid(@js([
        'endpoint' => route('api.listings.related'),
        'params' => $params,
        'syncUrl' => false,
        'initialLimit' => $count,
        'eagerLimit' => 0,
        'scrollLimit' => 0,
        'maxScrollAutoLoads' => 0,
        'skeletonCount' => $count,
        'cardKind' => $kind,
    ]))"
    x-show="loading || count === null || count > 0"
>
    <x-shared.section-heading :title="$title" />
    <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>
    <div
        class="listing-results detail-related__results"
        x-ref="results"
        :class="loading && 'listing-results--busy'"
        :aria-busy="loading ? 'true' : 'false'"
    >
        <x-tour.listing-skeleton :count="$count" variant="compact" :kind="$kind" />
    </div>
    <div class="hidden" x-ref="skeletonTpl" aria-hidden="true">
        <x-tour.listing-skeleton :count="$count" variant="compact" :kind="$kind" />
    </div>
</section>
