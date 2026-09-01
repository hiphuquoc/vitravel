{{-- Hàng checkbox trong danh sách điểm đến / danh mục --}}
@props([
    'group',
    'value',
    'label',
    'count' => null,
])

@php
    $valueStr = (string) $value;
@endphp

<label
    class="filter-row vt-check"
    :class="{
        'filter-row--on': isChecked(@js($group), @js($valueStr)),
        'filter-row--locked': isLockedFilter(@js($group), @js($valueStr)),
    }"
>
    <input
        type="checkbox"
        class="vt-check__input"
        value="{{ $valueStr }}"
        :checked="isChecked(@js($group), @js($valueStr))"
        :disabled="isLockedFilter(@js($group), @js($valueStr))"
        @click.prevent="toggleFilter(@js($group), @js($valueStr))"
    >
    <span class="vt-check__box" aria-hidden="true">
        <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
    <span class="vt-check__text filter-row__body">
        <span class="filter-row__label">{{ $label }}</span>
        @if ($count !== null && (int) $count > 0)
            <span class="filter-row__count">{{ (int) $count }}</span>
        @endif
    </span>
</label>
