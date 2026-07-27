@props(['item', 'href'])

{{-- Card dọc gọn cho Trang chủ / khối "tour tương tự" --}}
<article {{ $attributes->merge(['class' => 'card group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-(--shadow-card-hover)']) }}>
    <a href="{{ $href }}" class="relative block" aria-hidden="true" tabindex="-1">
        <div class="relative aspect-[3/2] overflow-hidden">
            @if (!empty($item['image']))
                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
            @else
                <x-ph class="absolute inset-0" :label="'Ảnh: ' . $item['title']" />
            @endif
            @if (!empty($item['badge']))
                <span class="absolute top-3 left-3 z-10 rounded-full bg-accent-500 px-3 py-1 text-xs font-bold text-white shadow">
                    {{ $item['badge'] }}
                </span>
            @endif
        </div>
    </a>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="tour-card-title">
            <a href="{{ $href }}" class="transition group-hover:text-primary-600">{{ $item['title'] }}</a>
        </h3>

        <div class="mt-3">
            <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount']" />
        </div>

        @if (!empty($item['places']))
            <p class="tour-card-places mt-3">
                {{ implode(' – ', $item['places']) }}
            </p>
        @endif

        @if (!empty($item['start']) || !empty($item['end']))
            <div class="tour-card-route mt-3">
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

        <div class="mt-auto flex items-center justify-between gap-3 pt-4">
            <span class="inline-flex items-center gap-1 rounded-full bg-leaf-100 px-2.5 py-1 text-xs font-semibold text-leaf-700">
                <x-icon name="calendar" class="size-3.5" /> {{ $item['duration'] }}
            </span>
            <a href="{{ $href }}" class="btn-primary-sm">
                Xem chi tiết <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </div>
</article>
