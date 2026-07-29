@props([
    'count' => 6,
    'variant' => 'wide', // wide | compact
])

@if ($variant === 'compact')
    <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
        @for ($i = 0; $i < $count; $i++)
            <div class="card overflow-hidden listing-skeleton-card listing-skeleton-card--compact">
                <div class="listing-skeleton__media listing-skeleton__shimmer"></div>
                <div class="card-body space-y-3 p-4">
                    <div class="listing-skeleton__line listing-skeleton__line--lg listing-skeleton__shimmer"></div>
                    <div class="listing-skeleton__line listing-skeleton__line--sm listing-skeleton__shimmer"></div>
                    <div class="listing-skeleton__line listing-skeleton__line--md listing-skeleton__shimmer"></div>
                </div>
            </div>
        @endfor
    </div>
@else
    <div class="site-stack" aria-hidden="true">
        @for ($i = 0; $i < $count; $i++)
            <div class="card overflow-hidden listing-skeleton-card">
                <div class="grid sm:grid-cols-[40%_1fr]">
                    <div class="listing-skeleton__media listing-skeleton__media--wide listing-skeleton__shimmer"></div>
                    <div class="card-body flex flex-col gap-3 p-5 sm:p-6">
                        <div class="listing-skeleton__line listing-skeleton__line--lg listing-skeleton__shimmer"></div>
                        <div class="listing-skeleton__line listing-skeleton__line--sm listing-skeleton__shimmer"></div>
                        <div class="listing-skeleton__line listing-skeleton__line--md listing-skeleton__shimmer"></div>
                        <div class="listing-skeleton__line listing-skeleton__line--full listing-skeleton__shimmer"></div>
                        <div class="mt-auto flex gap-3 pt-2">
                            <div class="listing-skeleton__line listing-skeleton__line--btn listing-skeleton__shimmer"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
@endif
