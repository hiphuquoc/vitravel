{{--
  Loại hình + hạng sao trên card lưu trú.
  variant=overlay — góc ảnh (legacy)
  variant=inline — dưới tiêu đề, không box chồng ảnh (mặc định grid/danh mục)
--}}
@props([
    'propertyTypeLabel' => null,
    'starRating' => null,
    'variant' => 'inline',
])

@php
    $stars = filled($starRating) ? (int) round(max(0, min(5, (float) $starRating))) : 0;
    $showStar = $stars >= 3;
    $showType = filled($propertyTypeLabel);
    $isInline = ($variant ?? 'inline') === 'inline';
@endphp

@if ($showType || $showStar)
    @if ($isInline)
        <p {{ $attributes->merge(['class' => 'stay-card-meta']) }}>
            @if ($showType)
                <span class="stay-card-meta__type">{{ $propertyTypeLabel }}</span>
            @endif
            @if ($showStar)
                <span
                    class="stay-card-meta__star"
                    @if ($showType)
                        role="img"
                        aria-label="{{ $propertyTypeLabel }}, {{ $stars }} sao"
                    @else
                        role="img"
                        aria-label="{{ $stars }} sao"
                    @endif
                >
                    <span class="stay-card-meta__num">{{ $stars }}</span>
                    <x-stay.star-glyph class="stay-card-meta__glyph" aria-hidden="true" />
                </span>
            @endif
        </p>
    @else
        <div {{ $attributes->merge(['class' => 'stay-card-media-tags']) }}>
            <span
                class="stay-media-pill"
                @if ($showStar)
                    role="img"
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
@endif
