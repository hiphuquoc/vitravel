@props([
    'count' => 5,
    'variant' => 'wide', // wide | compact
    'kind' => 'tour', // tour | cruise | service
    'layout' => 'grid', // grid | carousel (compact — khớp listing-cards snap)
])

@php
    $count = max(1, min(12, (int) $count));
    $variant = $variant === 'compact' ? 'compact' : 'wide';
    $kind = match ($kind) {
        'cruise', 'service' => $kind,
        default => 'tour',
    };
    $isStay = $kind === 'service';
    $layout = ($variant === 'compact' && $layout === 'carousel') ? 'carousel' : 'grid';
    $showRoute = $kind === 'tour' || $kind === 'cruise';
@endphp

@if ($variant === 'compact')
    @if ($layout === 'carousel')
        <div class="relative listing-snap" aria-hidden="true" data-listing-skeleton="compact-carousel" data-listing-kind="{{ $kind }}">
            <div class="snap-carousel">
                @for ($i = 0; $i < $count; $i++)
                    <div class="snap-carousel__item">
                        @include('components.tour.partials.listing-skeleton-compact-card', [
                            'kind' => $kind,
                            'showRoute' => $showRoute,
                        ])
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true" data-listing-skeleton="compact" data-listing-kind="{{ $kind }}">
            @for ($i = 0; $i < $count; $i++)
                @include('components.tour.partials.listing-skeleton-compact-card', [
                    'kind' => $kind,
                    'showRoute' => $showRoute,
                ])
            @endfor
        </div>
    @endif
@else
    <div class="site-stack" aria-hidden="true" data-listing-skeleton="wide" data-listing-kind="{{ $kind }}">
        @for ($i = 0; $i < $count; $i++)
            <div class="card overflow-hidden listing-skeleton-card listing-skeleton-card--wide{{ $isStay ? ' listing-skeleton-card--stay' : '' }}">
                <div class="grid sm:grid-cols-[40%_1fr]">
                    <div class="relative overflow-hidden listing-skeleton__media-wrap">
                        <div class="listing-skeleton__media listing-skeleton__media--wide listing-skeleton__shimmer"></div>
                        <span class="listing-skeleton__badge listing-skeleton__shimmer"></span>
                        <span class="listing-skeleton__duration listing-skeleton__shimmer"></span>
                    </div>
                    <div class="card-body flex flex-col">
                        <div class="card-inner flex-1">
                            <div class="listing-skeleton__line listing-skeleton__line--title listing-skeleton__shimmer"></div>
                            <div class="listing-skeleton__rating">
                                <div class="listing-skeleton__line listing-skeleton__line--stars listing-skeleton__shimmer"></div>
                                <div class="listing-skeleton__line listing-skeleton__line--meta listing-skeleton__shimmer"></div>
                            </div>
                            <div class="listing-skeleton__line listing-skeleton__line--places listing-skeleton__shimmer"></div>
                            @if ($showRoute)
                                <div class="listing-skeleton__route">
                                    <div class="listing-skeleton__line listing-skeleton__line--route-start listing-skeleton__shimmer"></div>
                                    <span class="listing-skeleton__route-line" aria-hidden="true"></span>
                                    <div class="listing-skeleton__line listing-skeleton__line--route-end listing-skeleton__shimmer"></div>
                                </div>
                            @endif
                            <div class="listing-skeleton__line listing-skeleton__line--quote listing-skeleton__shimmer"></div>
                        </div>
                        <div class="card-footer">
                            <div class="card-footer-row card-footer-row--price">
                                <div class="listing-skeleton__price">
                                    <div class="listing-skeleton__line listing-skeleton__line--price-label listing-skeleton__shimmer"></div>
                                    <div class="listing-skeleton__line listing-skeleton__line--price listing-skeleton__shimmer"></div>
                                </div>
                                <div class="listing-skeleton__line listing-skeleton__line--btn listing-skeleton__shimmer ml-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
@endif
