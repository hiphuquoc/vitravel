@props([
    // items: [['label' => ..., 'url' => ...], ...] — phần tử cuối là trang hiện tại (không cần url)
    'items' => [],
])

<nav {{ $attributes->merge(['class' => 'breadcrumb']) }} aria-label="Breadcrumb">
    <ol class="breadcrumb__list">
        <li>
            <a href="{{ route('home') }}" class="breadcrumb__link">Trang chủ</a>
        </li>
        @foreach ($items as $item)
            @if (empty($item['label']))
                @continue
            @endif
            <li class="breadcrumb__sep" aria-hidden="true">
                <x-icon name="chevron-right" class="size-3.5" />
            </li>
            <li>
                @if (! $loop->last && ! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="breadcrumb__link">{{ $item['label'] }}</a>
                @else
                    <span class="breadcrumb__current" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

{{-- JSON-LD BreadcrumbList --}}
@php
    $breadcrumbJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
            ...collect($items)->values()->map(fn ($item, $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 2,
                'name' => $item['label'],
                'item' => $item['url'] ?? null,
            ]))->all(),
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
