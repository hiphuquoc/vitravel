{{--
  Responsive image — lazy by default, srcset/sizes, decoding async.
  Usage:
    <x-img :src="$item['image']" :srcset="$item['imageSrcset'] ?? null" preset="card" :alt="$item['title']" class="…" />
    <x-img :src="$url" preset="hero" loading="eager" fetchpriority="high" class="…" />
--}}
@props([
    'src' => null,
    'srcset' => null,
    'sizes' => null,
    'preset' => null,
    'alt' => '',
    'loading' => 'lazy',
    'decoding' => 'async',
    'fetchpriority' => null,
    'width' => null,
    'height' => null,
])

@php
    if ($preset && ! $sizes) {
        $sizes = media_sizes((string) $preset);
    }
@endphp

@if (filled($src))
    <img
        src="{{ $src }}"
        @if (filled($srcset)) srcset="{{ $srcset }}" @endif
        @if (filled($sizes)) sizes="{{ $sizes }}" @endif
        alt="{{ $alt }}"
        loading="{{ $loading }}"
        decoding="{{ $decoding }}"
        @if (filled($fetchpriority)) fetchpriority="{{ $fetchpriority }}" @endif
        @if (filled($width)) width="{{ $width }}" @endif
        @if (filled($height)) height="{{ $height }}" @endif
        {{ $attributes }}
    >
@endif
