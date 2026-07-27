@props([
    'durations' => [], // ['key' => 'label']
    'styles' => [],    // ['key' => 'label']
])

{{-- Bộ lọc trang danh mục Tour/Du thuyền — desktop cột trái, mobile là drawer --}}
<div x-data="{ drawer: false }">
    {{-- Nút mở filter trên mobile --}}
    <button type="button" @click="drawer = true"
        class="btn-outline mb-4 w-full lg:hidden">
        <x-icon name="filter" class="size-4" /> Bộ lọc
    </button>

    {{-- Overlay mobile --}}
    <div x-cloak x-show="drawer" class="fixed inset-0 z-50 bg-ink/40 lg:hidden" @click="drawer = false"></div>

    <form method="get"
        x-cloak
        :class="drawer ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 w-[300px] overflow-y-auto bg-white p-6 shadow-2xl transition-transform duration-200 lg:static lg:z-auto lg:block lg:w-auto lg:translate-x-0 lg:rounded-2xl lg:shadow-(--shadow-card)"
        aria-label="Bộ lọc tour">

        <div class="mb-5 flex items-center justify-between">
            <h2 class="item-title flex items-center gap-2 text-lg">
                <x-icon name="filter" class="size-5 text-primary-600" /> Lọc theo
            </h2>
            <button type="button" @click="drawer = false" class="flex size-8 cursor-pointer items-center justify-center rounded-full hover:bg-page lg:hidden" aria-label="Đóng bộ lọc">
                <x-icon name="close" class="size-4.5" />
            </button>
        </div>

        <fieldset class="border-t border-line pt-4">
            <legend class="filter-legend">Thời lượng</legend>
            <div class="space-y-3">
                @foreach ($durations as $key => $label)
                    <label class="filter-option">
                        <input type="checkbox" name="duration[]" value="{{ $key }}"
                            class="size-[1.125rem] rounded border-line text-primary-500 focus:ring-primary-400">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="mt-6 border-t border-line pt-4">
            <legend class="filter-legend">Phong cách du lịch</legend>
            <div class="space-y-3">
                @foreach ($styles as $key => $label)
                    <label class="filter-option">
                        <input type="checkbox" name="style[]" value="{{ $key }}"
                            class="size-[1.125rem] rounded border-line text-primary-500 focus:ring-primary-400">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="mt-6 flex items-center gap-3 border-t border-line pt-4">
            <button type="reset" class="cursor-pointer text-sm font-medium text-muted underline underline-offset-2 transition hover:text-ink">Xoá lọc</button>
            <button type="submit" class="btn-primary-sm ml-auto">
                <x-icon name="check" class="size-4" /> Áp dụng
            </button>
        </div>
    </form>
</div>
