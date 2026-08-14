@props(['item', 'href'])

{{-- Card dọc gọn cho Trang chủ / khối "tour tương tự" --}}
<article {{ $attributes->merge(['class' => 'card card--stand group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-(--shadow-card-hover)']) }}>
    <a href="{{ $href }}" class="relative block" aria-hidden="true" tabindex="-1">
        <div class="relative aspect-[3/2] overflow-hidden">
            @if (!empty($item['image']))
                <x-img
                    :src="$item['image']"
                    :srcset="$item['imageSrcset'] ?? null"
                    preset="card"
                    :alt="$item['title']"
                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
            @else
                <x-ph class="absolute inset-0" :label="'Ảnh: ' . $item['title']" />
            @endif
            @if (!empty($item['badge']))
                <span class="tour-card-badge">
                    {{ $item['badge'] }}
                </span>
            @endif
            @if (!empty($item['duration']))
                <span class="tour-card-duration">
                    <x-icon name="calendar" class="tour-card-duration__icon" />
                    {{ $item['duration'] }}
                </span>
            @endif
        </div>
    </a>
    <div class="card-body flex flex-1 flex-col">
        <div class="card-inner flex-1">
            <h3 class="tour-card-title">
                <a href="{{ $href }}" class="transition group-hover:text-primary-600">{{ $item['title'] }}</a>
            </h3>

            <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount']" />

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

            @if (!empty($item['quote']['text']))
                <blockquote class="tour-card-quote tour-card-quote--clamp">
                    <x-icon name="quote" class="size-4 shrink-0 text-primary-300" />
                    <span>{{ $item['quote']['text'] }}
                        @if (!empty($item['quote']['author']))
                            <span class="font-medium not-italic text-ink">— {{ $item['quote']['author'] }}</span>
                        @endif
                    </span>
                </blockquote>
            @endif
        </div>

        <div class="card-footer card-footer-row card-footer-row--price">
            @if (!empty($item['priceFormatted']))
                <div class="tour-card-price">
                    <span class="tour-card-price__label">Giá từ</span>
                    <span class="tour-card-price__value">{{ $item['priceFormatted'] }}</span>
                </div>
            @endif
            <a href="{{ $href }}" class="btn-primary-sm ml-auto">
                Xem chi tiết <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </div>
</article>
