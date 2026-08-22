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
    'kicker' => null,
    'starRating' => null,
    'location' => null,
])

@php
    $normUrl = static function (?string $url): string {
        if (! $url) {
            return '';
        }
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return preg_replace('/(thumb|card|sm|md|lg|xl)(\.[a-z0-9]+)$/i', '$2', $path) ?: $path;
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

    if (! filled($coverSrc) && $uniqueThumbs !== []) {
        $first = array_shift($uniqueThumbs);
        $coverSrc = $first['full'] ?? $first['src'] ?? null;
        $coverSrcset = $first['fullSrcset'] ?? ($first['srcset'] ?? null);
    }

    $thumbSlots = max(1, (int) $thumbSlots);
    $thumbs = array_slice($uniqueThumbs, 0, $thumbSlots);
    $thumbCount = count($thumbs);
    $coverOnly = $thumbCount === 0;
    $shown = $thumbCount + (filled($coverSrc) ? 1 : 0);
    $catalogCount = max((int) $galleryCount, count($uniqueThumbs) + (filled($coverSrc) ? 1 : 0));
    $moreCount = max(0, $catalogCount - $shown);

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
        'detail-gallery__grid--coverOnly' => $coverOnly,
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
                    referrerpolicy="no-referrer"
                />
                <span class="detail-gallery__zoom" aria-hidden="true">
                    <x-icon name="search" class="size-5" />
                </span>
                @if ($catalogCount > 1)
                    <span class="detail-gallery__count" aria-hidden="true">{{ $catalogCount }} ảnh</span>
                @endif
            </button>
        @else
            <div class="detail-gallery__cover-btn detail-gallery__cover-btn--static">
                <x-ph class="detail-gallery__cover" :label="'Ảnh chính: ' . $title" icon-class="size-14" />
            </div>
        @endif

        @if ($thumbCount > 0)
            <div class="detail-gallery__thumbs" role="list" data-count="{{ $thumbCount }}">
                @foreach ($thumbs as $i => $thumb)
                    @php
                        $lbIndex = $thumbBaseIndex + $i;
                        $isLast = $i === $thumbCount - 1;
                        $showMore = $isLast && $moreCount > 0;
                    @endphp
                    <div class="detail-gallery__thumb" role="listitem">
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
                                referrerpolicy="no-referrer"
                            />
                            @if ($showMore)
                                <span class="detail-gallery__more" aria-hidden="true">
                                    +{{ $moreCount }}
                                </span>
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if ($showTitleCard)
        <div class="card detail-title-card">
            @if (count($breadcrumbs) > 0)
                <x-layout.breadcrumb :items="$breadcrumbs" class="breadcrumb--page mb-2.5" />
            @endif

            <div class="detail-title-card__main">
                <div class="detail-title-card__header-group">
                    {{-- Hàng 1: Badge Phân loại + Hạng sao --}}
                    @if (filled($kicker) || filled($starRating))
                        <div class="detail-title-card__tags">
                            @if (filled($kicker))
                                <span class="stay-property-type-pill">{{ $kicker }}</span>
                            @endif
                            @if (filled($starRating))
                                <x-stay.star-rating :rating="$starRating" size="sm" />
                            @endif
                        </div>
                    @endif

                    {{-- Hàng 2: Tiêu đề H1 sạch sẽ, vừa vặn, chuẩn font dự án --}}
                    <h1 class="detail-title-card__h1">{{ $title }}</h1>
                </div>

                {{-- Hàng 3: Rating + Vị trí tự nhiên, liền mạch --}}
                @if (filled($location) || $rating !== null)
                    <div class="detail-title-card__meta">
                        @if ($rating !== null)
                            <div class="detail-title-card__rating-wrap">
                                <x-shared.rating :rating="$rating" :count="$reviewCount ?? 0" class="detail-title-card__rating" />
                            </div>
                        @endif

                        @if (filled($location))
                            <div class="detail-title-card__location">
                                <span class="detail-title-card__pin-icon">
                                    <x-icon name="map-pin" class="size-4" />
                                </span>
                                <span class="detail-title-card__address">{{ $location }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    <x-shared.media-lightbox />
</section>
