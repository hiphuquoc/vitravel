@props([
    'tip' => '',
    'label' => 'Chi tiết',
])

@php
    $tip = trim((string) $tip);
@endphp

@if ($tip !== '')
    <button
        type="button"
        class="stay-hprt__tip"
        aria-label="{{ $label }}"
        data-tip="{{ $tip }}"
    >
        <x-icon name="info" class="size-3.5" />
    </button>
@endif
