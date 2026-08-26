{{-- Nút xem thêm / thu gọn cho danh sách dài — thay cho thanh cuộn lồng --}}
@props([
    'total',
    'limit',
    'searchModel' => null,
])

@php
    $hidden = max(0, (int) $total - (int) $limit);
@endphp

@if ($hidden > 0)
    <button
        type="button"
        class="filter-more"
        @click="expanded = !expanded"
        :aria-expanded="expanded"
        @if ($searchModel)
            x-show="!{{ $searchModel }}"
        @endif
    >
        <span x-text="expanded ? 'Thu gọn' : 'Xem thêm {{ $hidden }}'"></span>
        <svg class="filter-more__icon" :class="expanded && 'filter-more__icon--open'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 9l6 6 6-6"/>
        </svg>
    </button>
@endif
