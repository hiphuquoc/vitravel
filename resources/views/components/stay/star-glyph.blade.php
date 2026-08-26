{{-- Ngôi sao vàng tip bo tròn — dùng chung card + filter --}}
@props([
    'class' => 'stay-star-glyph',
])

<svg
    {{ $attributes->merge(['class' => $class]) }}
    viewBox="0 0 24 24"
    aria-hidden="true"
>
    <path
        fill="currentColor"
        stroke="currentColor"
        stroke-width="2"
        stroke-linejoin="round"
        stroke-linecap="round"
        d="M12 3.25l2.15 5.15 5.6.5-4.25 3.75 1.25 5.45L12 15.55 7.25 18.1l1.25-5.45-4.25-3.75 5.6-.5L12 3.25z"
    />
</svg>
