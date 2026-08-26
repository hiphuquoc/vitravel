{{-- Card skeleton compact — dùng chung grid & carousel --}}
<div class="card card--stand overflow-hidden listing-skeleton-card listing-skeleton-card--compact" data-listing-kind="{{ $kind }}">
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
            @if (! empty($showRoute))
                <div class="listing-skeleton__route">
                    <div class="listing-skeleton__line listing-skeleton__line--route-start listing-skeleton__shimmer"></div>
                    <span class="listing-skeleton__route-line" aria-hidden="true"></span>
                    <div class="listing-skeleton__line listing-skeleton__line--route-end listing-skeleton__shimmer"></div>
                </div>
            @endif
            <div class="listing-skeleton__line listing-skeleton__line--quote listing-skeleton__shimmer"></div>
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
