@props([
    'rating' => 5,
    'size' => 'md', // sm | md | lg
    'eco' => false,
])

@php
    $stars = (int) round(max(0, min(5, (float) $rating)));
    $isEco = (bool) $eco || $stars <= 2;
    $sizeClass = match ($size) {
        'sm' => 'stay-star-rating--sm',
        'lg' => 'stay-star-rating--lg',
        default => 'stay-star-rating--md',
    };
    $aria = $isEco ? 'Homestay / Eco' : ('Khách sạn ' . $stars . ' sao');
@endphp

{{-- Badge hạng sao đồng bộ filter (5★ / Eco) — ngôi sao vàng SVG bo góc --}}
<span
    {{ $attributes->merge([
        'class' => 'stay-star-rating ' . $sizeClass . ($isEco ? ' stay-star-rating--eco' : ''),
        'aria-label' => $aria,
    ]) }}
>
    @if ($isEco)
        <span class="stay-star-rating__mark" aria-hidden="true">Eco</span>
    @else
        <span class="stay-star-rating__num" aria-hidden="true">{{ $stars }}</span>
        <x-stay.star-glyph class="stay-star-rating__glyph" />
    @endif
</span>
