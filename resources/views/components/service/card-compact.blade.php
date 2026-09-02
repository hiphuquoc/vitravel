@props([
    'item',
    'href',
])

@php
    $clusterIcon = $item['clusterIcon'] ?? 'briefcase';
    $isStay = ($item['cluster'] ?? '') === 'stay' || ! empty($item['isStay']);
    
    // Duration badge or room count for stays
    $mediaBadge = null;
    $mediaBadgeIcon = 'calendar';
    if ($isStay) {
        $roomsCount = $item['roomsCount'] ?? (is_array($item['rooms'] ?? null) ? count($item['rooms']) : 0);
        $totalRooms = $item['totalRooms'] ?? null;
        if ($roomsCount > 0) {
            $mediaBadge = $roomsCount . ' hạng phòng';
            $mediaBadgeIcon = 'bed';
        } elseif ($totalRooms > 0) {
            $mediaBadge = $totalRooms . ' phòng';
            $mediaBadgeIcon = 'building';
        }
    } elseif (! empty($item['duration'])) {
        $mediaBadge = $item['duration'];
        $mediaBadgeIcon = 'calendar';
    }

    $propertyTypeLabel = $item['propertyTypeLabel'] ?? null;
    $starRaw = isset($item['starRating']) ? (int) round((float) $item['starRating']) : 0;
    $showStayMediaTags = $isStay && (filled($propertyTypeLabel) || $starRaw >= 3);
@endphp

{{-- Card dọc gọn — lưới dịch vụ liên quan --}}
<article {{ $attributes->merge(['class' => 'card card--stand group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-(--shadow-card-hover)']) }}>
    <a href="{{ $href }}" class="relative block" aria-hidden="true" tabindex="-1">
        <div class="relative aspect-[3/2] overflow-hidden">
            @if (! empty($item['image']))
                <x-img
                    :src="$item['image']"
                    :srcset="$item['imageSrcset'] ?? null"
                    preset="card"
                    :alt="$item['title']"
                    loading="lazy"
                    decoding="async"
                    fetchpriority="low"
                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
            @else
                <x-ph
                    class="absolute inset-0"
                    :icon="$clusterIcon"
                    icon-class="size-8"
                    :label="'Ảnh: ' . $item['title']"
                />
            @endif
            @if (! empty($item['badge']))
                <span class="tour-card-badge">
                    {{ $item['badge'] }}
                </span>
            @endif
            @if ($showStayMediaTags)
                <x-stay.card-media-tags
                    variant="overlay"
                    :property-type-label="$propertyTypeLabel"
                    :star-rating="$item['starRating'] ?? null"
                />
            @endif
            @if ($mediaBadge !== null)
                <span class="tour-card-duration{{ $isStay ? ' tour-card-duration--plain' : '' }}">
                    <x-icon :name="$mediaBadgeIcon" class="tour-card-duration__icon" />
                    {{ $mediaBadge }}
                </span>
            @endif
        </div>
    </a>
    <div class="card-body flex flex-1 flex-col">
        <div class="card-inner flex-1">
            <h3 class="tour-card-title item-title">
                <a href="{{ $href }}" class="transition group-hover:text-primary-600">{{ $item['title'] }}</a>
            </h3>

            @if (! empty($item['rating']))
                <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount'] ?? 0" />
            @endif

            @if (! empty($item['location']))
                <p class="tour-card-places line-clamp-2">
                    {{ $item['location'] }}
                </p>
            @endif

            @if (! empty($item['quote']['text']))
                <blockquote class="tour-card-quote tour-card-quote--clamp">
                    <x-icon name="quote" class="size-4 shrink-0 text-primary-300" />
                    <span>{{ $item['quote']['text'] }}
                        @if (! empty($item['quote']['author']))
                            <span class="font-medium not-italic text-ink">— {{ $item['quote']['author'] }}</span>
                        @endif
                    </span>
                </blockquote>
            @endif
        </div>

        <div class="card-footer card-footer-row card-footer-row--price">
            @if (! empty($item['priceFormatted']))
                <div class="tour-card-price">
                    <span class="tour-card-price__label">Giá từ</span>
                    <span class="tour-card-price__value">
                        {{ $item['priceFormatted'] }}
                        @if (! empty($item['priceUnitLabel']))
                            <span class="text-xs font-normal text-muted">{{ $item['priceUnitLabel'] }}</span>
                        @endif
                    </span>
                </div>
            @else
                <div class="tour-card-price">
                    <span class="tour-card-price__label">Giá từ</span>
                    <span class="tour-card-price__value text-base">Liên hệ</span>
                </div>
            @endif
            <a href="{{ $href }}" class="btn-ghost ml-auto whitespace-nowrap text-sm">
                Chi tiết <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>
    </div>
</article>