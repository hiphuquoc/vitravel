@props([
    'homeOnly' => true,
    'limit' => 12,
    'section' => null,
    'showCta' => true,
])

@php
    $videos = view_data()->videos($homeOnly, $limit);
    $data = $section ?? view_data()->homeSection('videos');
    $count = count($videos);
    $useCarousel = $count > 4;
    $eyebrow = $data['eyebrow'] ?? 'Trải nghiệm thật';
    $title = $data['title'] ?? 'Video trải nghiệm chân thật';
    $subtitle = $data['subtitle'] ?? $data['body'] ?? null;
    $ctaLabel = $data['ctaLabel'] ?? null;
    $ctaUrl = $data['ctaUrl'] ?? null;
@endphp

@if ($count > 0)
<section
    {{ $attributes->merge(['class' => 'vt-videos cv-auto']) }}
    aria-labelledby="vt-videos-title"
    x-data="mediaLightbox(@js($videos))"
>
    <div class="vt-videos__scene" aria-hidden="true">
        <span class="vt-videos__scene-mark">▶</span>
        <span class="vt-videos__scene-leak vt-videos__scene-leak--gold"></span>
        <span class="vt-videos__scene-leak vt-videos__scene-leak--leaf"></span>
        <span class="vt-videos__scene-frame"></span>
    </div>

    <div class="vt-videos__intro">
        @if ($eyebrow)
            <div class="vt-videos__eyebrow-wrap">
                <span class="vt-videos__eyebrow-dot" aria-hidden="true"></span>
                <span class="vt-videos__eyebrow">{{ $eyebrow }}</span>
            </div>
        @endif
        <h2 class="vt-videos__title" id="vt-videos-title">{{ $title }}</h2>
        @if ($subtitle)
            <p class="vt-videos__desc">{{ $subtitle }}</p>
        @endif
        <div class="vt-videos__meta">
            <span class="vt-videos__count">
                <strong>{{ str_pad((string) $count, 2, '0', STR_PAD_LEFT) }}</strong> video
            </span>
            <span class="vt-videos__meta-divider" aria-hidden="true"></span>
            @if ($showCta && $ctaLabel && $ctaUrl)
                <a href="{{ $ctaUrl }}" class="vt-videos__meta-link">{{ $ctaLabel }}</a>
            @else
                <span class="vt-videos__meta-caption">Thư viện video ViTravel</span>
            @endif
        </div>
    </div>

    <div class="vt-videos__band">
        @if ($useCarousel)
            <div x-data="carousel" class="relative vt-videos__carousel-wrap">
                <div x-ref="track" class="snap-carousel vt-videos__strip-carousel" role="list" aria-label="{{ $title }}">
                    @foreach ($videos as $i => $v)
                        @php $index = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); @endphp
                        <article class="vt-videos__tile snap-carousel__item" role="listitem">
                            <button
                                type="button"
                                class="vt-videos__tile-btn"
                                @click="open({{ $i }})"
                                aria-label="Xem video: {{ $v['title'] }}"
                            >
                                <figure class="vt-videos__frame">
                                    @if (! empty($v['image']))
                                        <x-img
                                            :src="$v['image']"
                                            :srcset="$v['imageSrcset'] ?? null"
                                            preset="card"
                                            :alt="$v['title']"
                                            class="vt-videos__img"
                                        />
                                    @else
                                        <div class="vt-videos__ph" aria-hidden="true"></div>
                                    @endif
                                    <span class="vt-videos__play" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M8 5v14l11-7z"/></svg>
                                    </span>
                                    @if (! empty($v['duration']))
                                        <span class="vt-videos__duration">{{ $v['duration'] }}</span>
                                    @endif
                                    <figcaption class="vt-videos__cap">
                                        <span class="vt-videos__cap-index" aria-hidden="true">{{ $index }}</span>
                                        <span class="vt-videos__cap-rule" aria-hidden="true"></span>
                                        <span class="vt-videos__cap-title">{{ $v['tag'] ?: $v['title'] }}</span>
                                    </figcaption>
                                </figure>
                            </button>
                        </article>
                    @endforeach
                </div>
                <button type="button" @click="go(-1)" x-show="canPrev" x-cloak
                    class="absolute top-1/2 left-2 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white/95 text-ink shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600 lg:left-4"
                    aria-label="Video trước">
                    <x-icon name="chevron-left" class="size-5" />
                </button>
                <button type="button" @click="go(1)" x-show="canNext" x-cloak
                    class="absolute top-1/2 right-2 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white/95 text-ink shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600 lg:right-4"
                    aria-label="Video tiếp">
                    <x-icon name="chevron-right" class="size-5" />
                </button>
            </div>
        @else
            <div class="vt-videos__grid" role="list" aria-label="{{ $title }}">
                @foreach ($videos as $i => $v)
                    @php $index = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); @endphp
                    <article class="vt-videos__tile" role="listitem">
                        <button
                            type="button"
                            class="vt-videos__tile-btn"
                            @click="open({{ $i }})"
                            aria-label="Xem video: {{ $v['title'] }}"
                        >
                            <figure class="vt-videos__frame">
                                @if (! empty($v['image']))
                                    <x-img
                                        :src="$v['image']"
                                        :srcset="$v['imageSrcset'] ?? null"
                                        preset="card"
                                        :alt="$v['title']"
                                        class="vt-videos__img"
                                    />
                                @else
                                    <div class="vt-videos__ph" aria-hidden="true"></div>
                                @endif
                                <span class="vt-videos__play" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                                @if (! empty($v['duration']))
                                    <span class="vt-videos__duration">{{ $v['duration'] }}</span>
                                @endif
                                <figcaption class="vt-videos__cap">
                                    <span class="vt-videos__cap-index" aria-hidden="true">{{ $index }}</span>
                                    <span class="vt-videos__cap-rule" aria-hidden="true"></span>
                                    <span class="vt-videos__cap-title">{{ $v['tag'] ?: $v['title'] }}</span>
                                </figcaption>
                            </figure>
                        </button>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <x-shared.media-lightbox />
</section>
@endif
