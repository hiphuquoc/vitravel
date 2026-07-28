@props([
    'durations' => [], // ['key' => 'label']
    'styles' => [],    // ['key' => 'label']
])

{{-- Bộ lọc trang danh mục Tour/Du thuyền — desktop cột trái, mobile là drawer --}}
<div x-data="{ drawer: false }">
    <button type="button" @click="drawer = true"
        class="btn-outline filter-sidebar__toggle">
        <x-icon name="filter" class="size-4" /> Bộ lọc
    </button>

    <div x-cloak x-show="drawer" class="filter-sidebar__overlay lg:hidden" @click="drawer = false"></div>

    <form method="get"
        x-cloak
        :class="drawer ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="filter-sidebar__panel"
        aria-label="Bộ lọc tour">

        <div class="filter-sidebar__head">
            <h2 class="filter-sidebar__title">
                <x-icon name="filter" class="size-5 text-primary-600" /> Lọc theo
            </h2>
            <button type="button" @click="drawer = false" class="filter-sidebar__close" aria-label="Đóng bộ lọc">
                <x-icon name="close" class="size-4.5" />
            </button>
        </div>

        <fieldset class="filter-sidebar__fieldset">
            <legend class="filter-legend">Thời lượng</legend>
            <div class="filter-sidebar__options">
                @foreach ($durations as $key => $label)
                    <label class="filter-option">
                        <input type="checkbox" name="duration[]" value="{{ $key }}"
                            class="size-[1.125rem] rounded border-line text-primary-500 focus:ring-primary-400">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="filter-sidebar__fieldset">
            <legend class="filter-legend">Phong cách du lịch</legend>
            <div class="filter-sidebar__options">
                @foreach ($styles as $key => $label)
                    <label class="filter-option">
                        <input type="checkbox" name="style[]" value="{{ $key }}"
                            class="size-[1.125rem] rounded border-line text-primary-500 focus:ring-primary-400">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="filter-sidebar__actions">
            <button type="reset" class="filter-sidebar__reset">Xoá lọc</button>
            <button type="submit" class="btn-primary-sm ml-auto">
                <x-icon name="check" class="size-4" /> Áp dụng
            </button>
        </div>
    </form>
</div>
