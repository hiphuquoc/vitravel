@props([
    'homeOnly' => true,
    'limit' => 8,
    'section' => null,
    'showCta' => true,
])

@php
    $videos = view_data()->videos($homeOnly, $limit);
    $data = $section ?? view_data()->homeSection('videos');
    $count = count($videos);
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
    x-data="videoGallery(@js($videos))"
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
    </div>

    {{-- Fullscreen lightbox --}}
    <div
        class="vt-videos-lightbox"
        x-show="active !== null"
        x-cloak
        x-transition.opacity.duration.200ms
        @keydown.escape.window="close()"
        @keydown.arrow-left.window="if (active !== null) prev()"
        @keydown.arrow-right.window="if (active !== null) next()"
        role="dialog"
        aria-modal="true"
        :aria-label="activeItem?.title || 'Xem video'"
    >
        <div class="vt-videos-lightbox__backdrop" @click="close()"></div>
        <div class="vt-videos-lightbox__panel">
            <button type="button" class="vt-videos-lightbox__close" @click="close()" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <button type="button" class="vt-videos-lightbox__nav vt-videos-lightbox__nav--prev" @click="prev()" aria-label="Video trước" x-show="items.length > 1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button type="button" class="vt-videos-lightbox__nav vt-videos-lightbox__nav--next" @click="next()" aria-label="Video tiếp" x-show="items.length > 1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><polyline points="9 18 15 12 9 6"/></svg>
            </button>

            <div class="vt-videos-lightbox__stage">
                <template x-if="active !== null && activeItem?.embedUrl && activeItem?.provider !== 'file'">
                    <iframe
                        class="vt-videos-lightbox__frame"
                        :src="activeItem.embedUrl"
                        :title="activeItem.title"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </template>
                <template x-if="active !== null && activeItem?.provider === 'file' && activeItem?.embedUrl">
                    <video class="vt-videos-lightbox__frame" :src="activeItem.embedUrl" controls autoplay playsinline></video>
                </template>
            </div>

            <div class="vt-videos-lightbox__info" x-show="activeItem">
                <p class="vt-videos-lightbox__index" x-text="activeLabel"></p>
                <h3 class="vt-videos-lightbox__title" x-text="activeItem?.title"></h3>
                <p class="vt-videos-lightbox__desc" x-show="activeItem?.description" x-text="activeItem?.description"></p>
            </div>
        </div>
    </div>
</section>
@endif
