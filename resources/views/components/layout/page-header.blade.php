@props([
    'title',
    'breadcrumbs' => [],   // truyền tiếp cho x-layout.breadcrumb
    'subtitle' => null,
    'bannerLabel' => null, // nhãn ảnh banner placeholder
    'bannerSrc' => null,   // URL variant lg/full — first-view / LCP
    'bannerSrcset' => null,
    'bannerAlt' => null,
])

{{-- Pattern chuẩn toàn site: banner ảnh + card trắng chứa H1/breadcrumb đè lên --}}
@php
    $bannerAltText = $bannerAlt ?? ('Ảnh banner: '.$title);
@endphp

@if (filled($bannerSrc))
    @push('head')
        <link rel="preload" as="image" href="{{ $bannerSrc }}"
            @if (filled($bannerSrcset)) imagesrcset="{{ $bannerSrcset }}" imagesizes="100vw" @endif
            fetchpriority="high">
    @endpush
@endif

<section class="relative page-header">
    <div
        @class([
            'page-header__banner',
            'page-header__banner--has-image' => filled($bannerSrc),
        ])
    >
        @if (filled($bannerSrc))
            {{-- <img> thật (không CSS background) để LCP discoverable + fetchpriority --}}
            <img
                class="page-header__banner-img"
                src="{{ $bannerSrc }}"
                @if (filled($bannerSrcset)) srcset="{{ $bannerSrcset }}" @endif
                sizes="100vw"
                alt="{{ $bannerAltText }}"
                width="1920"
                height="640"
                loading="eager"
                fetchpriority="high"
                decoding="async"
            >
        @else
            <x-ph class="h-full w-full" :label="$bannerLabel ?? 'Ảnh banner: ' . $title" icon="photo" icon-class="size-12" />
        @endif
    </div>
    <div class="container-site">
        <div class="page-header__card-wrap">
            <div class="card page-header__card">
                <x-layout.breadcrumb :items="$breadcrumbs" class="breadcrumb--page" />
                <h1 class="page-header__title">{{ $title }}</h1>
                @php
                    $subtitleText = filled($subtitle)
                        ? trim(html_entity_decode(strip_tags((string) $subtitle), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
                        : '';
                @endphp
                @if ($subtitleText !== '')
                    <p class="body-text mt-2">{{ $subtitleText }}</p>
                @endif
            </div>
        </div>
    </div>
</section>
