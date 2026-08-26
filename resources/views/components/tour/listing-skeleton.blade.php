@props([
    'count' => 5,
    'variant' => 'wide', // wide | compact
    'kind' => 'tour', // tour | cruise | service
])

@php
    $count = max(1, min(12, (int) $count));
    $variant = $variant === 'compact' ? 'compact' : 'wide';
    $kind = match ($kind) {
        'cruise', 'service' => $kind,
        default => 'tour',
    };
    $isStay = $kind === 'service';
@endphp

@if ($variant === 'compact')
    <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true" data-listing-skeleton="compact">
        @for ($i = 0; $i < $count; $i++)
            <div class="card card--stand overflow-hidden listing-skeleton-card listing-skeleton-card--compact">
                <div class="relative aspect-[3/2] overflow-hidden">
                    <div class="listing-skeleton__media listing-skeleton__media--compact listing-skeleton__shimmer"></div>
                    <span class="listing-skeleton__badge listing-skeleton__shimmer"></span>
                    <span class="listing-skeleton__duration listing-skeleton__shimmer"></span>
                </div>
                <div class="card-body flex flex-1 flex-col">
                    <div class="card-inner flex-1">
                        <div class="listing-skeleton__line listing-skeleton__line--title listing-skeleton__shimmer"></div>
                        <div class="listing-skeleton__rating">
                            <div class="listing-skeleton__line listing-skeleton__line--stars listing-skeleton__shimmer"></div>
                            <div class="listing-skeleton__line listing-skeleton__line--meta listing-skeleton__shimmer"></div>
                        </div>
                        <div class="listing-skeleton__line listing-skeleton__line--places listing-skeleton__shimmer"></div>
                        <div class="listing-skeleton__line listing-skeleton__line--route listing-skeleton__shimmer"></div>
                    </div>
                    <div class="card-footer card-footer-row card-footer-row--price">
                        <div class="listing-skeleton__price">
                            <div class="listing-skeleton__line listing-skeleton__line--price-label listing-skeleton__shimmer"></div>
                            <div class="listing-skeleton__line listing-skeleton__line--price listing-skeleton__shimmer"></div>
                        </div>
                        <div class="listing-skeleton__line listing-skeleton__line--btn listing-skeleton__shimmer ml-auto"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
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
                            <div class="listing-skeleton__route">
                                <div class="listing-skeleton__line listing-skeleton__line--route-start listing-skeleton__shimmer"></div>
                                <span class="listing-skeleton__route-line" aria-hidden="true"></span>
                                <div class="listing-skeleton__line listing-skeleton__line--route-end listing-skeleton__shimmer"></div>
                            </div>
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
