@props(['slides' => [], 'pills' => [], 'countries' => []])

@php
    $slideCount = count($slides);
    $firstSlide = $slides[0] ?? null;
    $firstImage = $firstSlide['image'] ?? null;
    $firstImageMobile = $firstSlide['imageMobile'] ?? null;
    $firstSrcset = $firstSlide['imageSrcset'] ?? null;

    $durationOptions = [
        ['value' => '', 'label' => 'Bao lâu cũng được'],
        ['value' => 'lt7', 'label' => 'Dưới 7 ngày'],
        ['value' => '7-10', 'label' => '7 – 10 ngày'],
        ['value' => '11-15', 'label' => '11 – 15 ngày'],
        ['value' => 'gt16', 'label' => 'Trên 16 ngày'],
    ];
    $destinationOptions = collect($countries)
        ->map(fn ($c) => ['value' => $c['slug'], 'label' => $c['name']])
        ->values()
        ->all();
@endphp

@if (filled($firstImage))
    @push('head')
        <link rel="preload" as="image" href="{{ $firstImage }}"
            @if (filled($firstSrcset)) imagesrcset="{{ $firstSrcset }}" imagesizes="100vw" @endif
            fetchpriority="high">
        @if (filled($firstImageMobile))
            <link rel="preload" as="image" href="{{ $firstImageMobile }}" media="(max-width: 768px)" fetchpriority="high">
        @endif
    @endpush
@endif

<section
    class="relative hero-slider"
    aria-label="Khám phá tour"
    aria-roledescription="carousel"
    x-data="heroSlider({{ max($slideCount, 1) }})"
    @mouseenter="stopAutoplay()"
    @mouseleave="startAutoplay()"
    tabindex="0"
    @keydown.arrow-left.prevent="if (total > 1) goPrev()"
    @keydown.arrow-right.prevent="if (total > 1) goNext()"
>
    <div class="hero-slider-stage relative w-full overflow-hidden">
        @forelse ($slides as $index => $slide)
            @php
                $isFirst = $index === 0;
                $bgDesktop = $slide['image'] ?? null;
                $bgMobile = $slide['imageMobile'] ?? null;
                $firstBgStyle = null;
                if ($isFirst && ($bgDesktop || $bgMobile)) {
                    $parts = [];
                    if ($bgDesktop) {
                        $parts[] = '--hero-bg: url("'.$bgDesktop.'")';
                    }
                    if ($bgMobile) {
                        $parts[] = '--hero-bg-mobile: url("'.$bgMobile.'")';
                    }
                    $firstBgStyle = implode('; ', $parts);
                }
            @endphp
            <div
                class="hero-slide {{ $isFirst ? 'is-active' : '' }}"
                data-hero-slide="{{ $index }}"
                :class="{ 'is-active': active === {{ $index }} }"
                role="group"
                aria-roledescription="slide"
                :aria-hidden="active !== {{ $index }}"
            >
                @if ($bgDesktop || $bgMobile)
                    <div
                        @class([
                            'hero-slide__media',
                            'hero-slide__media--ready' => filled($firstBgStyle),
                            'hero-slide__media--has-mobile' => filled($bgMobile),
                        ])
                        @if (filled($firstBgStyle))
                            style="{{ $firstBgStyle }}"
                            data-bg-ready="1"
                        @endif
                        @if ($bgDesktop) data-bg="{{ $bgDesktop }}" @endif
                        @if ($bgMobile) data-bg-mobile="{{ $bgMobile }}" @endif
                        role="img"
                        aria-label="{{ $slide['imageAlt'] ?? '' }}"
                    ></div>
                @else
                    <x-ph class="h-full w-full" :label="$slide['imageAlt'] ?? 'Ảnh hero'" icon-class="size-16" />
                @endif

                <div class="hero-slide__shade hero-slide__shade--base" aria-hidden="true"></div>
                <div class="hero-slide__shade hero-slide__shade--radial" aria-hidden="true"></div>

                @if ($slide['title'] || $slide['titleAccent'] || $slide['description'])
                    <div @class([
                        'absolute inset-0 flex flex-col justify-center hero-slide-copy',
                        'items-start' => ($slide['textAlign'] ?? 'center') === 'left',
                        'items-end' => ($slide['textAlign'] ?? 'center') === 'right',
                        'items-center' => ($slide['textAlign'] ?? 'center') === 'center',
                    ])>
                        <div @class([
                            'hero-slide-caption',
                            'text-left' => ($slide['textAlign'] ?? 'center') === 'left',
                            'text-right' => ($slide['textAlign'] ?? 'center') === 'right',
                            'text-center' => ($slide['textAlign'] ?? 'center') === 'center',
                        ])>
                            @if ($slide['title'] || $slide['titleAccent'])
                                <h1>
                                    @if ($slide['title'])<span class="hero-slide-title max-line-1">{{ $slide['title'] }}</span>@endif
                                    @if ($slide['titleAccent'])
                                        <span class="hero-slide-accent max-line-1">{{ $slide['titleAccent'] }}</span>
                                    @endif
                                </h1>
                            @endif
                            @if ($slide['description'])
                                <p class="hero-slide-desc max-line-2">{{ $slide['description'] }}</p>
                            @endif
                            @if ($slide['buttonLabel'] && $slide['linkUrl'])
                                <a href="{{ $slide['linkUrl'] }}" class="btn-primary inline-flex">
                                    {{ $slide['buttonLabel'] }}
                                    <x-icon name="arrow-right" class="size-4" />
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="hero-slide is-active" data-hero-slide="0" :class="{ 'is-active': active === 0 }">
                <x-ph class="h-full w-full" label="Ảnh hero: vịnh Hạ Long lúc hoàng hôn" icon-class="size-16" />
                <div class="hero-slide__shade hero-slide__shade--base" aria-hidden="true"></div>
                <div class="hero-slide__shade hero-slide__shade--radial" aria-hidden="true"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center hero-slide-copy">
                    <div class="hero-slide-caption text-center">
                        <h1>
                            <span class="hero-slide-title max-line-1">Du lịch Việt Nam</span>
                            <span class="hero-slide-accent max-line-1">theo cách của bạn</span>
                        </h1>
                        <p class="hero-slide-desc max-line-2">
                            Tour trọn gói & hành trình riêng qua Hạ Long, Sa Pa, Ninh Bình, Hà Giang — thiết kế bởi chuyên gia bản địa.
                        </p>
                    </div>
                </div>
            </div>
        @endforelse

        @if ($pills)
            <div class="hero-slider-pills">
                <div class="container-site flex flex-wrap justify-center gap-2">
                    @foreach ($pills as $pill)
                        <a href="{{ $pill['url'] }}" class="btn-chip border-0 bg-white/90 text-sm shadow backdrop-blur hover:bg-primary-500 hover:text-white">
                            {{ $pill['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($slideCount > 1)
            <button type="button" @click="goPrev()" class="hero-slider-nav hero-slider-nav--prev" aria-label="Slide trước">
                <x-icon name="chevron-left" class="size-5" />
            </button>
            <button type="button" @click="goNext()" class="hero-slider-nav hero-slider-nav--next" aria-label="Slide tiếp theo">
                <x-icon name="chevron-right" class="size-5" />
            </button>

            <div class="absolute inset-x-0 bottom-5 z-20">
                <div class="container-site flex justify-end">
                    <div class="hero-slider-dots" role="tablist" aria-label="Chọn slide">
                        @foreach ($slides as $index => $slide)
                            <button type="button"
                                @click="goTo({{ $index }})"
                                class="hero-slider-dot"
                                :class="active === {{ $index }} ? 'hero-slider-dot--active' : 'hero-slider-dot--inactive'"
                                role="tab"
                                :aria-selected="active === {{ $index }} ? 'true' : 'false'"
                                aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($destinationOptions)
        <div class="container-site">
            <form
                action="{{ locale_route('tours.index', 'viet-nam') }}"
                method="get"
                class="hero-search card"
                aria-label="Tìm tour nhanh"
            >
                <div class="hero-search__grid">
                    <x-form.select
                        name="destination"
                        id="hero-dest"
                        label="Điểm đến"
                        icon="map-pin"
                        :options="$destinationOptions"
                        class="hero-search__field"
                    />
                    <x-form.select
                        name="duration"
                        id="hero-duration"
                        label="Thời lượng"
                        icon="clock"
                        :options="$durationOptions"
                        selected=""
                        :searchable="false"
                        class="hero-search__field"
                    />
                    <div class="hero-search__action">
                        <button type="submit" class="btn-primary hero-search__submit">
                            <x-icon name="search" class="size-5 shrink-0" />
                            <span>Tìm tour</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</section>
