@props([
    'rating' => 5,
    'size' => 'md', // sm, md, lg
    'showLabel' => true,
])

@php
    $stars = (int) round(max(1, min(5, (float) $rating)));
    $sizeClasses = match($size) {
        'sm' => 'stay-star-rating--sm',
        'lg' => 'stay-star-rating--lg',
        default => 'stay-star-rating--md',
    };
@endphp

<span {{ $attributes->merge(['class' => 'stay-star-rating ' . $sizeClasses, 'aria-label' => 'Khách sạn ' . $stars . ' sao']) }}>
    <span class="stay-star-rating__stars" aria-hidden="true">
        @for ($i = 1; $i <= $stars; $i++)
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="stay-star-rating__icon">
                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
            </svg>
        @endfor
    </span>
    @if ($showLabel)
        <span class="stay-star-rating__badge">{{ $stars }} sao</span>
    @endif
</span>
