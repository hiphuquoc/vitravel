@props(['options' => ['Phổ biến nhất', 'Mới nhất', 'Thời lượng ngắn → dài', 'Đánh giá cao nhất']])

<div x-data="{ open: false, selected: @js($options[0]) }" class="relative" @click.outside="open = false">
    <button type="button" @click="open = !open"
        class="flex items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2 text-base font-medium transition hover:border-primary-300"
        :aria-expanded="open">
        <span class="text-muted">Sắp xếp:</span> <span class="font-semibold" x-text="selected"></span>
        <x-icon name="chevron-down" class="size-3.5 transition" ::class="open && 'rotate-180'" />
    </button>
    <ul x-cloak x-show="open" x-transition.opacity.duration.100ms
        class="absolute right-0 z-20 mt-2 w-60 rounded-xl border border-line bg-white p-1.5 shadow-(--shadow-card-hover)" role="listbox">
        @foreach ($options as $opt)
            <li>
                <button type="button" @click="selected = @js($opt); open = false"
                    class="w-full cursor-pointer rounded-lg px-3 py-2 text-left text-base font-medium transition hover:bg-primary-50 hover:text-primary-600"
                    :class="selected === @js($opt) && 'text-primary-600'" role="option"
                    :aria-selected="selected === @js($opt)">{{ $opt }}</button>
            </li>
        @endforeach
    </ul>
</div>
