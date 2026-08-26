{{--
  Overlay góc trái trên ảnh card lưu trú.
  Gộp loại hình + hạng sao vào 1 pill. Eco không hiển thị trên grid.
--}}
@props([
    'propertyTypeLabel' => null,
    'starRating' => null,
])

@php
    $stars = filled($starRating) ? (int) round(max(0, min(5, (float) $starRating))) : 0;
    $showStar = $stars >= 3; // Eco / ≤2 sao: không ghi gì trên grid
    $showType = filled($propertyTypeLabel);
@endphp

@if ($showType || $showStar)
    <div {{ $attributes->merge(['class' => 'stay-card-media-tags']) }}>
        <span
            class="stay-media-pill"
            @if ($showStar)
                aria-label="{{ ($showType ? $propertyTypeLabel . ', ' : '') . $stars . ' sao' }}"
            @endif
        >
            @if ($showType)
                <span class="stay-media-pill__type">{{ $propertyTypeLabel }}</span>
            @endif
            @if ($showStar)
                @if ($showType)
                    <span class="stay-media-pill__sep" aria-hidden="true"></span>
                @endif
                <span class="stay-media-pill__star" aria-hidden="true">
                    <span class="stay-media-pill__num">{{ $stars }}</span>
                    <x-stay.star-glyph class="stay-media-pill__glyph" />
                </span>
            @endif
        </span>
    </div>
@endif
