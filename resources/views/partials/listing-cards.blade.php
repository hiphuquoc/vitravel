@php
    /** @var array<int, array<string, mixed>> $items */
    $variant = ($variant ?? 'wide') === 'compact' ? 'compact' : 'wide';
    $kind = match ($kind ?? 'tour') {
        'cruise' => 'cruise',
        'service' => 'service',
        default => 'tour',
    };
    $isAppend = (bool) ($isAppend ?? false);
    /** Animate chỉ khi append / client replace — seed SSR giữ tĩnh để tránh CLS. */
    $animate = (bool) ($animate ?? $isAppend);
    $itemClass = $animate ? 'listing-card-animate' : 'listing-card-static';
    /** Số card trong 1 hàng desktop — vượt ngưỡng thì dùng snap-carousel dùng chung. */
    $gridCap = (int) ($gridCap ?? 3);
    $useCarousel = $variant === 'compact' && count($items) > $gridCap && ! $isAppend;
@endphp

@if ($isAppend)
    @foreach ($items as $item)
        @php
            $href = !empty($item['href'])
                ? $item['href']
                : (!empty($item['slugFull'])
                    ? url('/' . ltrim($item['slugFull'], '/'))
                    : match ($kind) {
                        'cruise' => locale_route('cruises.show', ['type' => $item['typeSlug'] ?? '', 'slug' => $item['slug'] ?? '']),
                        'service' => locale_route('services.show', [
                            'cluster' => $item['cluster'] ?? 'stay',
                            'category' => $item['categorySlug'] ?? '',
                            'slug' => $item['slug'] ?? '',
                        ]),
                        default => locale_route('tours.show', ['country' => $item['countrySlug'] ?? '', 'slug' => $item['slug'] ?? '']),
                    });
        @endphp
        <div class="{{ $itemClass }}" data-listing-item>
            @if ($kind === 'service')
                @if ($variant === 'compact')
                    <x-service.card-compact :item="$item" :href="$href" />
                @else
                    <x-service.card :item="$item" :href="$href" />
                @endif
            @else
                @if ($variant === 'compact')
                    <x-tour.card-compact :item="$item" :href="$href" />
                @else
                    <x-tour.card :item="$item" :href="$href" />
                @endif
            @endif
        </div>
    @endforeach
@elseif (count($items) === 0)
    <div class="card listing-empty">
        <x-icon :name="match ($kind) { 'cruise' => 'cruise', 'service' => 'briefcase', default => 'compass' }" class="size-10 text-muted" />
        <p class="font-semibold">Không tìm thấy kết quả khớp bộ lọc.</p>
        <p class="body-text text-muted">Thử chọn thêm tiêu chí hoặc xoá bớt điều kiện lọc.</p>
        <a href="{{ route('customize') }}" class="btn-primary site-mt">
            <x-icon name="sparkles" class="size-4" /> Thiết kế hành trình riêng
        </a>
    </div>
@elseif ($variant === 'compact' && $useCarousel)
    <div x-data="carousel" class="relative listing-snap">
        <div x-ref="track" class="snap-carousel" role="list">
            @foreach ($items as $item)
                @php
                    $href = !empty($item['slugFull'])
                        ? url('/' . ltrim($item['slugFull'], '/'))
                        : match ($kind) {
                            'cruise' => locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']]),
                            'service' => locale_route('services.show', [
                                'cluster' => $item['cluster'],
                                'category' => $item['categorySlug'],
                                'slug' => $item['slug'],
                            ]),
                            default => locale_route('tours.show', ['country' => $item['countrySlug'], 'slug' => $item['slug']]),
                        };
                @endphp
                <div class="snap-carousel__item {{ $itemClass }}" role="listitem" data-listing-item>
                    @if ($kind === 'service')
                        <x-service.card-compact :item="$item" :href="$href" />
                    @else
                        <x-tour.card-compact :item="$item" :href="$href" />
                    @endif
                </div>
            @endforeach
        </div>

        <button type="button" @click="go(-1)" x-show="canPrev" x-cloak
            class="absolute top-1/2 -left-3 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600"
            aria-label="Xem mục trước">
            <x-icon name="chevron-left" class="size-5" />
        </button>
        <button type="button" @click="go(1)" x-show="canNext" x-cloak
            class="absolute top-1/2 -right-3 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600"
            aria-label="Xem mục tiếp theo">
            <x-icon name="chevron-right" class="size-5" />
        </button>
    </div>
@elseif ($variant === 'compact')
    <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3" data-listing-container>
        @foreach ($items as $item)
            @php
                $href = !empty($item['slugFull'])
                    ? url('/' . ltrim($item['slugFull'], '/'))
                    : match ($kind) {
                        'cruise' => locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']]),
                        'service' => locale_route('services.show', [
                            'cluster' => $item['cluster'],
                            'category' => $item['categorySlug'],
                            'slug' => $item['slug'],
                        ]),
                        default => locale_route('tours.show', ['country' => $item['countrySlug'], 'slug' => $item['slug']]),
                    };
            @endphp
            <div class="{{ $itemClass }}" data-listing-item>
                @if ($kind === 'service')
                    <x-service.card-compact :item="$item" :href="$href" />
                @else
                    <x-tour.card-compact :item="$item" :href="$href" />
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="site-stack" data-listing-container>
        @foreach ($items as $item)
            @php
                $href = !empty($item['slugFull'])
                    ? url('/' . ltrim($item['slugFull'], '/'))
                    : match ($kind) {
                        'cruise' => locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']]),
                        'service' => locale_route('services.show', [
                            'cluster' => $item['cluster'],
                            'category' => $item['categorySlug'],
                            'slug' => $item['slug'],
                        ]),
                        default => locale_route('tours.show', ['country' => $item['countrySlug'], 'slug' => $item['slug']]),
                    };
            @endphp
            <div class="{{ $itemClass }}" data-listing-item>
                @if ($kind === 'service')
                    <x-service.card :item="$item" :href="$href" />
                @else
                    <x-tour.card :item="$item" :href="$href" />
                @endif
            </div>
        @endforeach
    </div>
@endif
