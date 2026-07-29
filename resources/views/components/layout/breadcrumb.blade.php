@props([
    // items: [['label' => ..., 'url' => ...], ...] — phần tử cuối là trang hiện tại (không cần url)
    'items' => [],
])

<nav {{ $attributes->merge(['class' => 'breadcrumb']) }} aria-label="Breadcrumb">
    <ol class="breadcrumb__list">
        <li>
            <a href="{{ locale_route('home') }}" class="breadcrumb__link">Trang chủ</a>
        </li>
        @foreach ($items as $item)
            @if (empty($item['label']))
                @continue
            @endif
            <li class="breadcrumb__sep" aria-hidden="true"></li>
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

{!! schema_ld(schema()->breadcrumbList($items)) !!}
