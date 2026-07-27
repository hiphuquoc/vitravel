@props([
    'item',  // tour hoặc cruise (cùng cấu trúc field)
    'href',
])

{{-- Card ngang cho trang danh mục: ảnh trái ~40%, nội dung phải --}}
<article {{ $attributes->merge(['class' => 'card group overflow-hidden transition hover:shadow-(--shadow-card-hover)']) }}>
    <div class="grid sm:grid-cols-[40%_1fr]">
        <a href="{{ $href }}" class="relative block overflow-hidden" aria-hidden="true" tabindex="-1">
            @if (!empty($item['image']))
                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="h-52 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-full sm:min-h-64" loading="lazy">
            @else
                <x-ph class="h-52 w-full sm:h-full sm:min-h-64" :label="'Ảnh: ' . $item['title']" />
            @endif
            @if (!empty($item['badge']))
                <span class="absolute top-3 left-3 z-10 rounded-full bg-accent-500 px-3 py-1 text-xs font-bold text-white shadow">
                    {{ $item['badge'] }}
                </span>
            @endif
        </a>

        <div class="flex flex-col p-5 sm:p-6">
            <h3 class="tour-card-title">
                <a href="{{ $href }}" class="transition group-hover:text-primary-600">{{ $item['title'] }}</a>
            </h3>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount']" />
                <span class="inline-flex items-center gap-1 rounded-full bg-leaf-100 px-2.5 py-1 text-xs font-semibold text-leaf-700">
                    <x-icon name="calendar" class="size-3.5" /> {{ $item['duration'] }}
                </span>
            </div>

            <div class="mt-3 space-y-2.5">
                @if (!empty($item['places']))
                    <p class="tour-card-places">
                        {{ implode(' – ', $item['places']) }}
                    </p>
                @endif

                @if (!empty($item['start']) || !empty($item['end']))
                    <div class="tour-card-route">
                        @if (!empty($item['start']))
                            <span class="tour-card-route__start">
                                <span class="tour-card-route__label">Khởi hành:</span>
                                {{ $item['start'] }}
                            </span>
                        @endif
                        @if (!empty($item['start']) && !empty($item['end']))
                            <span class="tour-card-route__line" aria-hidden="true"></span>
                        @endif
                        @if (!empty($item['end']))
                            <span class="tour-card-route__end">
                                <span class="tour-card-route__label">Kết thúc:</span>
                                {{ $item['end'] }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            @if (!empty($item['quote']['text']))
                <blockquote class="tour-card-quote">
                    <x-icon name="quote" class="size-4 shrink-0 text-primary-300" />
                    <span>{{ $item['quote']['text'] }}
                        @if (!empty($item['quote']['author']))
                            <span class="font-medium not-italic text-ink">— {{ $item['quote']['author'] }}</span>
                        @endif
                    </span>
                </blockquote>
            @endif

            <div class="mt-auto flex flex-wrap items-center gap-x-5 gap-y-3 pt-4" x-data="{ open: false }">
                @if (!empty($item['highlights']))
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink transition hover:text-primary-600"
                        :aria-expanded="open">
                        Điểm nhấn chính
                        <x-icon name="chevron-down" class="size-4 transition" ::class="open && 'rotate-180'" />
                    </button>
                @endif
            <a href="{{ $href }}" class="btn-primary-sm ml-auto">
                    Xem chi tiết <x-icon name="arrow-right" class="size-4" />
                </a>

                @if (!empty($item['highlights']))
                    <div x-show="open" x-collapse x-cloak class="w-full">
                        <ul class="mt-1 grid gap-2.5 rounded-xl bg-page-soft p-3.5 text-sm leading-6 text-ink-soft sm:grid-cols-2 sm:gap-x-5 sm:gap-y-2.5 sm:p-4">
                            @foreach ($item['highlights'] as $h)
                                <li class="flex gap-2.5">
                                    <x-icon name="check" class="mt-1 size-4 shrink-0 text-leaf-600" />
                                    <span>{{ $h }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</article>
