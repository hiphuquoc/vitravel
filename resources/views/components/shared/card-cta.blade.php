@props([
    'href',
    'label' => 'Xem chi tiết',
])

{{-- CTA chân card lưới — gọn, nowrap, dùng chung tour/cruise/service --}}
<a
    href="{{ $href }}"
    {{ $attributes->class(['card-cta']) }}
    aria-label="{{ $label }}"
>
    <span class="card-cta__label">{{ $label }}</span>
    <x-icon name="arrow-right" class="card-cta__icon" />
</a>
