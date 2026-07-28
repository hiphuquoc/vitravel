@props(['options' => ['Phổ biến nhất', 'Mới nhất', 'Thời lượng ngắn → dài', 'Đánh giá cao nhất']])

<div x-data="{ open: false, selected: @js($options[0]) }" class="relative" @click.outside="open = false">
    <button type="button" @click="open = !open"
        class="sort-dropdown__trigger"
        :aria-expanded="open">
        <span class="text-muted">Sắp xếp:</span> <span class="font-semibold" x-text="selected"></span>
        <x-icon name="chevron-down" class="size-3.5 transition" ::class="open && 'rotate-180'" />
    </button>
    <ul x-cloak x-show="open" x-transition.opacity.duration.100ms
        class="sort-dropdown__menu" role="listbox">
        @foreach ($options as $opt)
            <li>
                <button type="button" @click="selected = @js($opt); open = false"
                    class="sort-dropdown__option"
                    :class="selected === @js($opt) && 'text-primary-600'" role="option"
                    :aria-selected="selected === @js($opt)">{{ $opt }}</button>
            </li>
        @endforeach
    </ul>
</div>
