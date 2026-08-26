{{-- Chip chọn nhanh (thời lượng, phong cách, tiện ích, loại hình) --}}
@props([
    'group',
    'value',
    'label',
])

@php $valueStr = (string) $value; @endphp

<button
    type="button"
    class="filter-chip"
    :class="isChecked(@js($group), @js($valueStr)) && 'is-active'"
    :aria-pressed="isChecked(@js($group), @js($valueStr))"
    @click="toggleFilter(@js($group), @js($valueStr))"
>
    <svg class="filter-chip__mark" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span>{{ $label }}</span>
</button>
