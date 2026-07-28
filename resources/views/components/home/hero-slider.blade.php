@props(['slides' => [], 'pills' => [], 'countries' => []])

<section class="relative" aria-label="Khám phá tour" aria-roledescription="carousel"
    x-data="{
        active: 0,
        total: {{ count($slides) ?: 1 }},
        timer: null,
        startAutoplay() {
            if (this.total <= 1) return;
            this.timer = setInterval(() => { this.goNext(); }, 6000);
        },
        stopAutoplay() { clearInterval(this.timer); },
        goTo(i) { this.active = i; this.stopAutoplay(); this.startAutoplay(); },
        goPrev() { this.active = (this.active - 1 + this.total) % this.total; this.stopAutoplay(); this.startAutoplay(); },
        goNext() { this.active = (this.active + 1) % this.total; this.stopAutoplay(); this.startAutoplay(); },
    }"
    x-init="startAutoplay()"
    @mouseenter="stopAutoplay()"
    @mouseleave="startAutoplay()"
    tabindex="0"
    @keydown.arrow-left.prevent="if (total > 1) goPrev()"
    @keydown.arrow-right.prevent="if (total > 1) goNext()">

    <div class="relative h-[420px] w-full overflow-hidden sm:h-[480px] lg:h-[540px]">
        @forelse ($slides as $index => $slide)
            <div
                class="absolute inset-0 transition-opacity duration-700 ease-in-out"
                :class="active === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'"
                role="group"
                aria-roledescription="slide"
                :aria-hidden="active !== {{ $index }}"
            >
                @if ($slide['image'])
                    <picture>
                        @if ($slide['imageMobile'])
                            <source
                                media="(max-width: 768px)"
                                srcset="{{ $slide['imageMobileSrcset'] ?? $slide['imageMobile'] }}"
                                sizes="100vw"
                            >
                        @endif
                        <x-img
                            :src="$slide['image']"
                            :srcset="$slide['imageSrcset'] ?? null"
                            preset="hero"
                            :alt="$slide['imageAlt'] ?? ''"
                            :loading="$index === 0 ? 'eager' : 'lazy'"
                            :fetchpriority="$index === 0 ? 'high' : null"
                            class="h-full w-full object-cover"
                        />
                    </picture>
                @else
                    <x-ph class="h-full w-full" :label="$slide['imageAlt'] ?? 'Ảnh hero'" icon-class="size-16" />
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-ink/60 via-ink/15 to-ink/30"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_70%_55%_at_50%_42%,rgb(39_43_35/0.28),transparent_68%)]"></div>

                @if ($slide['title'] || $slide['titleAccent'] || $slide['description'])
                    <div @class([
                        'absolute inset-0 flex flex-col justify-center px-4 sm:px-6',
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
                                    @if ($slide['title'])<span class="hero-slide-title">{{ $slide['title'] }}</span>@endif
                                    @if ($slide['titleAccent'])
                                        <span class="hero-slide-accent">{{ $slide['titleAccent'] }}</span>
                                    @endif
                                </h1>
                            @endif
                            @if ($slide['description'])
                                <p class="hero-slide-desc">{{ $slide['description'] }}</p>
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
            <x-ph class="h-full w-full" label="Ảnh hero: vịnh Hạ Long lúc hoàng hôn" icon-class="size-16" />
            <div class="absolute inset-0 bg-gradient-to-t from-ink/60 via-ink/15 to-ink/30"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_70%_55%_at_50%_42%,rgb(39_43_35/0.28),transparent_68%)]"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center px-4 sm:px-6">
                <div class="hero-slide-caption text-center">
                    <h1>
                        <span class="hero-slide-title">Miền Bắc Việt Nam</span>
                        <span class="hero-slide-accent">theo cách của bạn</span>
                    </h1>
                    <p class="hero-slide-desc">
                        Tour trọn gói & hành trình riêng qua Hạ Long, Sa Pa, Ninh Bình, Hà Giang — thiết kế bởi chuyên gia bản địa.
                    </p>
                </div>
            </div>
        @endforelse

        {{-- Pills chọn nhanh --}}
        @if ($pills)
            <div class="absolute inset-x-0 top-5 z-20">
                <div class="container-site flex flex-wrap justify-center gap-2">
                    @foreach ($pills as $pill)
                        <a href="{{ $pill['url'] }}" class="btn-chip border-0 bg-white/90 text-sm shadow backdrop-blur hover:bg-primary-500 hover:text-white">
                            {{ $pill['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Prev / Next — hai bên slider --}}
        @if (count($slides) > 1)
            <button type="button" @click="goPrev()" class="hero-slider-nav hero-slider-nav--prev" aria-label="Slide trước">
                <x-icon name="chevron-left" class="size-5" />
            </button>
            <button type="button" @click="goNext()" class="hero-slider-nav hero-slider-nav--next" aria-label="Slide tiếp theo">
                <x-icon name="chevron-right" class="size-5" />
            </button>

            {{-- Dots — góc phải dưới, clean, active dạng dài --}}
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

    {{-- Search box --}}
    @if ($countries)
        <div class="container-site">
            <form action="{{ route('tours.index', 'viet-nam') }}" method="get"
                class="card relative -mt-12 z-30 grid gap-3 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end sm:gap-4 sm:p-5 lg:mx-auto lg:max-w-3xl">
                <div>
                    <label for="hero-dest" class="field-label">Điểm đến</label>
                    <select id="hero-dest" name="destination" class="field-input appearance-none">
                        @foreach ($countries as $c)
                            <option value="{{ $c['slug'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="hero-duration" class="field-label">Thời lượng</label>
                    <select id="hero-duration" name="duration" class="field-input appearance-none">
                        <option value="">Bao lâu cũng được</option>
                        <option value="lt7">Dưới 7 ngày</option>
                        <option value="7-10">7 – 10 ngày</option>
                        <option value="11-15">11 – 15 ngày</option>
                        <option value="gt16">Trên 16 ngày</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary h-11 min-w-[9rem] gap-2.5 px-8">
                    <x-icon name="search" class="size-5 shrink-0" /> Tìm tour
                </button>
            </form>
        </div>
    @endif
</section>
