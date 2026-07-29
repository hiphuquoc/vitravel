@props([
    'title',
    'breadcrumbs' => [],   // truyền tiếp cho x-layout.breadcrumb
    'subtitle' => null,
    'bannerLabel' => null, // nhãn ảnh banner placeholder
    'bannerSrc' => null,   // URL variant lg/full — first-view
    'bannerSrcset' => null,
    'bannerAlt' => null,
])

{{-- Pattern chuẩn toàn site: banner ảnh + card trắng chứa H1/breadcrumb đè lên --}}
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
        @if (filled($bannerSrc))
            style="--page-header-banner-image: url(&quot;{{ $bannerSrc }}&quot;)"
            role="img"
            aria-label="{{ $bannerAlt ?? ('Ảnh banner: '.$title) }}"
        @endif
    >
        @unless (filled($bannerSrc))
            <x-ph class="h-full w-full" :label="$bannerLabel ?? 'Ảnh banner: ' . $title" icon="photo" icon-class="size-12" />
        @endunless
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
