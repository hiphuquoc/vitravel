@props([
    'item',
    'href',
])

@php
    $clusterIcon = $item['clusterIcon'] ?? 'briefcase';
    $cardHighlights = ! empty($item['highlights']) ? array_slice($item['highlights'], 0, 3) : [];
@endphp

{{-- Card ngang dịch vụ: ảnh trái ~40%, nội dung phải --}}
<article {{ $attributes->merge(['class' => 'card group overflow-hidden transition hover:shadow-(--shadow-card-hover)']) }}>
    <div class="grid sm:grid-cols-[40%_1fr]">
        <a href="{{ $href }}" class="relative block overflow-hidden" aria-hidden="true" tabindex="-1">
            @if (! empty($item['image']))
                <x-img
                    :src="$item['image']"
                    :srcset="$item['imageSrcset'] ?? null"
                    preset="card-wide"
                    :alt="$item['title']"
                    class="tour-card-media tour-card-media--wide transition duration-500 group-hover:scale-105"
                />
            @else
                <x-ph
                    class="tour-card-media tour-card-media--wide"
                    :icon="$clusterIcon"
                    icon-class="size-10"
                    :label="'Ảnh: ' . $item['title']"
                />
            @endif
            @if (! empty($item['badge']))
                <span class="tour-card-badge">
                    {{ $item['badge'] }}
                </span>
            @endif
            @if (! empty($item['duration']))
                <span class="tour-card-duration">
                    <x-icon name="calendar" class="tour-card-duration__icon" />
                    {{ $item['duration'] }}
                </span>
            @endif
        </a>

        <div class="card-body flex flex-col">
            <div class="card-inner flex-1">
                <h3 class="tour-card-title item-title">
                    <a href="{{ $href }}" class="transition group-hover:text-primary-600">{{ $item['title'] }}</a>
                </h3>

                @if (! empty($item['starRating']))
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <x-shared.stars :rating="$item['starRating']" />
                        <span class="text-sm font-semibold text-ink">{{ $item['starRating'] }} sao</span>
                    </div>
                @else
                    <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount']" />
                @endif

                @if (! empty($item['places']))
                    <p class="tour-card-places">
                        <x-icon name="map-pin" class="mr-0.5 inline size-3.5 text-muted" />
                        {{ implode(' – ', $item['places']) }}
                    </p>
                @elseif (! empty($item['location']))
                    <p class="tour-card-places">
                        <x-icon name="map-pin" class="mr-0.5 inline size-3.5 text-muted" />
                        {{ $item['location'] }}
                    </p>
                @endif

                @if (! empty($item['start']) || ! empty($item['end']))
                    <div class="tour-card-route">
                        @if (! empty($item['start']))
                            <span class="tour-card-route__start">
                                <span class="tour-card-route__label">Đi:</span>
                                {{ $item['start'] }}
                            </span>
                        @endif
                        @if (! empty($item['start']) && ! empty($item['end']))
                            <span class="tour-card-route__line" aria-hidden="true"></span>
                        @endif
                        @if (! empty($item['end']))
                            <span class="tour-card-route__end">
                                <span class="tour-card-route__label">Đến:</span>
                                {{ $item['end'] }}
                            </span>
                        @endif
                    </div>
                @endif

                @if (! empty($item['quote']['text']))
                    <blockquote class="tour-card-quote">
                        <x-icon name="quote" class="size-4 shrink-0 text-primary-300" />
                        <span>{{ $item['quote']['text'] }}
                            @if (! empty($item['quote']['author']))
                                <span class="font-medium not-italic text-ink">— {{ $item['quote']['author'] }}</span>
                            @endif
                        </span>
                    </blockquote>
                @endif
            </div>

            <div class="card-footer" x-data="{ open: false }">
                <div class="card-footer-row card-footer-row--price">
                    @if (! empty($item['priceFormatted']))
                        <div class="tour-card-price">
                            <span class="tour-card-price__label">Từ</span>
                            <span class="tour-card-price__value">{{ $item['priceFormatted'] }}</span>
                        </div>
                    @endif

                    @if ($cardHighlights !== [])
                        <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink transition hover:text-primary-600"
                            :aria-expanded="open">
                            Điểm nhấn
                            <x-icon name="chevron-down" class="size-4 transition" ::class="open && 'rotate-180'" />
                        </button>
                    @endif

                    <a href="{{ $href }}" class="btn-ghost ml-auto whitespace-nowrap">
                        Xem chi tiết <x-icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                @if ($cardHighlights !== [])
                    <div x-show="open" x-collapse x-cloak class="w-full site-mt">
                        <ul class="tour-card-highlights">
                            @foreach ($cardHighlights as $h)
                                <li class="flex gap-2">
                                    <x-icon name="check" class="mt-0.5 size-4 shrink-0 text-leaf-600" />
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
