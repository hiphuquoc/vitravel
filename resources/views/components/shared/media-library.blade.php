@props([
    'kind' => 'video', // video | photo
    'items' => [],
    'eyebrow' => null,
    'title' => '',
    'subtitle' => null,
])

@php
    $count = count($items);
    $isVideo = $kind === 'video';
    $unit = $isVideo
        ? (app()->getLocale() === 'vi' ? 'video' : 'videos')
        : (app()->getLocale() === 'vi' ? 'album' : 'albums');
    $aria = $title ?: ($isVideo ? 'Thư viện video' : 'Thư viện ảnh');
@endphp

@if ($count > 0)
<section
    {{ $attributes->merge(['class' => 'media-library']) }}
    aria-labelledby="media-library-title"
    x-data="mediaLightbox(@js($items))"
>
    <header class="media-library__intro">
        <div class="media-library__intro-main">
            @if ($eyebrow)
                <p class="section-eyebrow">{{ $eyebrow }}</p>
            @endif
            <h2 class="section-title" id="media-library-title">{{ $title }}</h2>
            @if ($subtitle)
                <p class="body-text media-library__lead">{{ $subtitle }}</p>
            @endif
        </div>
        <p class="media-library__count" aria-label="{{ $count }} {{ $unit }}">
            <span class="media-library__count-num">{{ str_pad((string) $count, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="media-library__count-label">{{ $unit }}</span>
        </p>
    </header>

    <div class="media-library__wall" role="list" aria-label="{{ $aria }}">
        @foreach ($items as $i => $item)
            @php
                $index = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
                $label = (string) ($item['title'] ?? '');
                $tag = (string) ($item['tag'] ?? '');
                $meta = $isVideo
                    ? ($item['duration'] ?? null)
                    : (($item['photos'] ?? null)
                        ? (($item['photos']).' '.(app()->getLocale() === 'vi' ? 'ảnh' : 'photos'))
                        : ($item['date'] ?? null));
                $image = $item['image'] ?? null;
            @endphp
            <article class="media-library__cell" role="listitem">
                <button
                    type="button"
                    class="media-library__hit"
                    @click="open({{ $i }})"
                    aria-label="{{ $isVideo ? 'Xem video' : 'Xem album' }}: {{ $label }}"
                >
                    @if ($image)
                        <x-img
                            :src="$image"
                            :srcset="$item['imageSrcset'] ?? null"
                            preset="card"
                            :alt="$label"
                            class="media-library__img"
                        />
                    @else
                        <x-ph class="media-library__ph" :label="$label" icon-class="size-10" />
                    @endif

                    <span class="media-library__veil" aria-hidden="true"></span>

                    <span class="media-library__top" aria-hidden="true">
                        <span class="media-library__index">{{ $index }}</span>
                        @if ($meta)
                            <span class="media-library__badge">{{ $meta }}</span>
                        @endif
                    </span>

                    <span class="media-library__play" aria-hidden="true">
                        @if ($isVideo)
                            <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M8 5v14l11-7z"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        @endif
                    </span>

                    <span class="media-library__foot">
                        @if ($tag !== '' && $tag !== $label)
                            <span class="media-library__kicker">{{ $tag }}</span>
                        @endif
                        <span class="media-library__caption">{{ $label }}</span>
                    </span>
                </button>
            </article>
        @endforeach
    </div>

    <x-shared.media-lightbox />
</section>
@endif
