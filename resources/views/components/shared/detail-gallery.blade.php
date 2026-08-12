@props([
    'title',
    'coverSrc' => null,
    'coverSrcset' => null,
    'gallery' => [],
    'galleryCount' => 0,
    'breadcrumbs' => [],
    'rating' => null,
    'reviewCount' => null,
    'showTitleCard' => true,
    'thumbSlots' => 4,
])

@php
    $normUrl = static function (?string $url): string {
        if (! $url) {
            return '';
        }
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return preg_replace('/-(thumb|card|sm|md|lg|xl)(\.[a-z0-9]+)$/i', '$2', $path) ?: $path;
    };

    $coverNorm = $normUrl($coverSrc);
    $uniqueThumbs = collect($gallery)
        ->filter(function ($t) use ($normUrl, $coverNorm) {
            $src = $t['src'] ?? ($t['full'] ?? null);
            if (! filled($src)) {
                return false;
            }
            if ($coverNorm === '') {
                return true;
            }

            return $normUrl($src) !== $coverNorm;
        })
        ->values()
        ->all();

    $thumbSlots = max(1, (int) $thumbSlots);
    $thumbs = array_slice($uniqueThumbs, 0, $thumbSlots);
    while (count($thumbs) < $thumbSlots) {
        $thumbs[] = null;
    }

    $extraCount = max(
        (int) $galleryCount,
        count($uniqueThumbs),
    );
    $moreCount = max(0, $extraCount - $thumbSlots);

    $lightboxItems = [];
    if (filled($coverSrc)) {
        $lightboxItems[] = [
            'type' => 'image',
            'src' => $coverSrc,
            'srcset' => $coverSrcset,
            'title' => $title,
            'caption' => null,
        ];
    }
    foreach ($uniqueThumbs as $g) {
        $lightboxItems[] = [
            'type' => 'image',
            'src' => $g['full'] ?? $g['src'] ?? null,
            'srcset' => $g['fullSrcset'] ?? ($g['srcset'] ?? null),
            'title' => $g['title'] ?? $title,
            'caption' => $g['caption'] ?? null,
        ];
    }

    $coverIndex = filled($coverSrc) ? 0 : null;
    $thumbBaseIndex = filled($coverSrc) ? 1 : 0;
@endphp

<section
    class="container-site detail-gallery"
    aria-label="Thư viện ảnh"
    x-data="mediaLightbox(@js($lightboxItems))"
>
    <div @class([
        'detail-gallery__grid',
        'detail-gallery__grid--coverOnly' => collect($thumbs)->every(fn ($t) => empty($t['src'] ?? null)) && $lightboxItems === [],
    ])>
        @if ($coverSrc)
            <button
                type="button"
                class="detail-gallery__cover-btn"
                @click="open({{ (int) $coverIndex }})"
                aria-label="Xem ảnh lớn: {{ $title }}"
            >
                <x-img
                    :src="$coverSrc"
                    :srcset="$coverSrcset"
                    preset="detail"
                    :alt="$title"
                    loading="eager"
                    fetchpriority="high"
                    class="detail-gallery__cover"
                />
                <span class="detail-gallery__zoom" aria-hidden="true">
                    <x-icon name="search" class="size-5" />
                </span>
            </button>
        @else
            <x-ph class="detail-gallery__cover" :label="'Ảnh chính: ' . $title" icon-class="size-14" />
        @endif

        <div class="detail-gallery__thumbs" role="list">
            @foreach ($thumbs as $i => $thumb)
                @php
                    $hasImg = ! empty($thumb['src']);
                    $lbIndex = $thumbBaseIndex + $i;
                    $isLast = $i === $thumbSlots - 1;
                    $showMore = $isLast && $moreCount > 0 && $hasImg;
                @endphp
                <div class="detail-gallery__thumb" role="listitem">
                    @if ($hasImg)
                        <button
                            type="button"
                            class="detail-gallery__thumb-btn"
                            @click="open({{ $lbIndex }})"
                            aria-label="{{ $showMore ? ('Xem thêm '.$moreCount.' ảnh') : ('Xem ảnh '.($i + 2)) }}"
                        >
                            <x-img
                                :src="$thumb['src']"
                                :srcset="$thumb['srcset'] ?? null"
                                preset="gallery"
                                :alt="$title"
                                class="detail-gallery__thumb-img"
                            />
                            @if ($showMore)
                                <span class="detail-gallery__more" aria-hidden="true">
                                    +{{ $moreCount }}
                                </span>
                            @endif
                        </button>
                    @else
                        <x-ph class="detail-gallery__thumb-ph" icon-class="size-6" :label="null" />
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if ($showTitleCard)
        <div class="card detail-title-card">
            <div class="detail-title-card__row">
                <div class="detail-title-card__copy min-w-0">
                    @if (count($breadcrumbs) > 0)
                        <x-layout.breadcrumb :items="$breadcrumbs" class="breadcrumb--page" />
                    @endif
                    <h1 class="detail-title-card__h1">{{ $title }}</h1>
                </div>
                @if ($rating !== null)
                    <x-shared.rating :rating="$rating" :count="$reviewCount ?? 0" class="detail-title-card__rating" />
                @endif
            </div>
        </div>
    @endif

    <x-shared.media-lightbox />
</section>
