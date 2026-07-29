@props([
    'label' => null,
])

{{-- Checkbox thương hiệu dùng chung: bộ lọc listing, form, sidebar --}}
<label class="vt-check">
    <input type="checkbox" {{ $attributes->class(['vt-check__input']) }}>
    <span class="vt-check__box" aria-hidden="true">
        <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
    @if ($slot->isNotEmpty())
        <span class="vt-check__text">{{ $slot }}</span>
    @elseif (filled($label))
        <span class="vt-check__text">{{ $label }}</span>
    @endif
</label>
