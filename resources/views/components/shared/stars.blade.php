@props([
    'rating' => 5,
    'max' => 5,
])

@php
    $filled = (int) round(max(0, min((int) $max, (float) $rating)));
@endphp

{{-- Hàng sao tip bo tròn nhẹ — dùng chung rating / summary / platforms --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0', 'role' => 'img', 'aria-label' => number_format((float) $rating, 1) . ' trên ' . $max . ' sao']) }}>
    @for ($i = 1; $i <= $max; $i++)
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            class="size-[1.2rem] shrink-0 {{ $i <= $filled ? 'text-accent-500' : 'text-accent-200' }}"
            aria-hidden="true">
            <path fill="currentColor" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"
                d="M12 3.2l2.55 5.35 5.85.7-4.35 3.95 1.2 5.7L12 16.2l-5.25 2.7 1.2-5.7-4.35-3.95 5.85-.7L12 3.2z" />
        </svg>
    @endfor
</span>
