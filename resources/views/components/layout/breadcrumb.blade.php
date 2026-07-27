@props([
    // items: [['label' => ..., 'url' => ...], ...] — phần tử cuối là trang hiện tại (không cần url)
    'items' => [],
])

<nav {{ $attributes->merge(['class' => 'text-[13px]']) }} aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
        <li class="flex items-center gap-1.5">
            <a href="{{ route('home') }}" class="font-medium text-muted transition hover:text-primary-600">Trang chủ</a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <x-icon name="chevron-right" class="size-3 text-muted/70" />
                @if (!$loop->last && !empty($item['url']))
                    <a href="{{ $item['url'] }}" class="font-medium text-muted transition hover:text-primary-600">{{ $item['label'] }}</a>
                @else
                    <span class="font-semibold text-ink" aria-current="page">{{ $item['label'] }}</span>
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
