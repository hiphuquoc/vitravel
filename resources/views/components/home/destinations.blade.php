@props([
    'countries' => [],
    'section' => null,
])

@php
    $data = $section ?? view_data()->homeSection('destinations');
    $isEn = app()->getLocale() === 'en';

    $eyebrow = $data['eyebrow'] ?? ($isEn ? 'Southeast Asia' : 'Đông Nam Á');
    $title = $data['title'] ?? ($isEn ? 'Our most loved destinations' : 'Những điểm đến được yêu thích nhất');
    $subtitle = $data['subtitle'] ?? ($isEn
        ? 'Each place has its own colour — choose where you want to hear local stories.'
        : 'Mỗi điểm đến một sắc màu — chọn nơi bạn muốn lắng nghe câu chuyện bản địa.');

    $list = collect($countries)->values();
    $hero = $list->first(fn (array $c) => ($c['size'] ?? '') === 'large') ?? $list->first();
    $heroSlug = $hero['slug'] ?? null;
    $strip = $list->filter(fn (array $c) => ($c['slug'] ?? null) !== $heroSlug)->values();
    $exploreLabel = $isEn ? 'Explore tours' : 'Khám phá tour';
@endphp

@if ($hero)
<section
    {{ $attributes->merge(['class' => 'vt-dest cv-auto']) }}
    aria-label="{{ $title }}"
    x-data="destShowcase"
    :class="{ 'is-inview': inview }"
>
    <div class="container-site">
        <x-shared.section-heading
            :eyebrow="$eyebrow"
            :title="$title"
            :subtitle="$subtitle"
        />

        {{-- Hero Việt Nam — trong container, nổi bật --}}
        <a
            href="{{ locale_route('tours.index', $hero['slug']) }}"
            class="vt-dest__hero group"
            aria-label="{{ $exploreLabel }}: {{ $hero['name'] }}"
        >
            <div class="vt-dest__hero-media" aria-hidden="true">
                @if (! empty($hero['imageHero'] ?? $hero['image'] ?? null))
                    <x-img
                        :src="$hero['imageHero'] ?? $hero['image']"
                        :srcset="$hero['imageSrcset'] ?? null"
                        preset="hero"
                        :alt="$hero['name']"
                        class="vt-dest__hero-img"
                    />
                @else
                    <x-ph class="vt-dest__hero-img" :label="'Ảnh điểm đến: '.$hero['name']" icon="photo" icon-class="size-14" />
                @endif
            </div>
            <span class="vt-dest__hero-scrim" aria-hidden="true"></span>
            <span class="vt-dest__hero-glow" aria-hidden="true"></span>

            @if (! empty($hero['tourCount']))
                <span class="vt-dest__hero-badge">
                    <strong>{{ $hero['tourCount'] }}</strong>
                    <span>tour</span>
                </span>
            @endif

            <div class="vt-dest__hero-body">
                <div class="vt-dest__hero-copy">
                    <h3 class="vt-dest__hero-name">{{ $hero['name'] }}</h3>
                    @if (! empty($hero['tagline']))
                        <p class="vt-dest__hero-tagline">{{ $hero['tagline'] }}</p>
                    @endif
                    <span class="btn-primary vt-dest__hero-cta">
                        {{ $exploreLabel }}
                        <x-icon name="arrow-right" class="size-4" />
                    </span>
                </div>
            </div>
        </a>

        @if ($strip->isNotEmpty())
            <div class="vt-dest__board">
                <div class="vt-dest__grid" role="list">
                    @foreach ($strip as $i => $c)
                        @php $index = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); @endphp
                        <a
                            href="{{ locale_route('tours.index', $c['slug']) }}"
                            class="vt-dest__portrait group"
                            role="listitem"
                            style="--vt-dest-i: {{ $i }}"
                        >
                            <span class="vt-dest__portrait-media" aria-hidden="true">
                                @if (! empty($c['image']))
                                    <x-img
                                        :src="$c['image']"
                                        :srcset="$c['imageSrcset'] ?? null"
                                        preset="country"
                                        :alt="$c['name']"
                                        class="vt-dest__portrait-img"
                                    />
                                @else
                                    <x-ph class="vt-dest__portrait-img" :label="'Ảnh: '.$c['name']" icon-class="size-8" />
                                @endif
                            </span>
                            <span class="vt-dest__portrait-scrim" aria-hidden="true"></span>
                            <span class="vt-dest__portrait-index" aria-hidden="true">{{ $index }}</span>
                            <span class="vt-dest__portrait-copy">
                                <span class="vt-dest__portrait-name">{{ $c['name'] }}</span>
                                @if (! empty($c['tagline']))
                                    <span class="vt-dest__portrait-tagline">{{ $c['tagline'] }}</span>
                                @endif
                                @if (! empty($c['tourCount']))
                                    <span class="vt-dest__portrait-meta">{{ $c['tourCount'] }} tour</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endif
