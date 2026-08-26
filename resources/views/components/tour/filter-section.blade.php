{{-- Nhóm lọc luôn mở, chảy theo trang — không accordion, không thanh cuộn lồng. --}}
@props([
    'title',
    'group' => null,
])

<section class="filter-group">
    <header class="filter-group__head">
        <h3 class="filter-group__title">{{ $title }}</h3>
        @if ($group)
            <button
                type="button"
                class="filter-group__clear"
                x-show="activeFilterCount(@js($group)) > 0"
                x-cloak
                @click="clearGroup(@js($group))"
                aria-label="Bỏ chọn nhóm {{ $title }}"
            >
                <span x-text="activeFilterCount(@js($group))"></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        @endif
    </header>
    <div class="filter-group__body">
        {{ $slot }}
    </div>
</section>
