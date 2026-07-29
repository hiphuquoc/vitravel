@php
    /** @var array<int, array<string, mixed>> $items */
    $variant = ($variant ?? 'wide') === 'compact' ? 'compact' : 'wide';
    $kind = ($kind ?? 'tour') === 'cruise' ? 'cruise' : 'tour';
@endphp

@if (count($items) === 0)
    <div class="card listing-empty">
        <x-icon :name="$kind === 'cruise' ? 'cruise' : 'compass'" class="size-10 text-muted" />
        <p class="font-semibold">Không tìm thấy kết quả khớp bộ lọc.</p>
        <p class="body-text text-muted">Thử chọn thêm tiêu chí hoặc xoá bớt điều kiện lọc.</p>
        <a href="{{ route('customize') }}" class="btn-primary site-mt">
            <x-icon name="sparkles" class="size-4" /> Thiết kế hành trình riêng
        </a>
    </div>
@elseif ($variant === 'compact')
    <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($items as $item)
            @php
                $href = $kind === 'cruise'
                    ? locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']])
                    : locale_route('tours.show', ['country' => $item['countrySlug'], 'slug' => $item['slug']]);
            @endphp
            <x-tour.card-compact :item="$item" :href="$href" />
        @endforeach
    </div>
@else
    <div class="site-stack">
        @foreach ($items as $item)
            @php
                $href = $kind === 'cruise'
                    ? locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']])
                    : locale_route('tours.show', ['country' => $item['countrySlug'], 'slug' => $item['slug']]);
            @endphp
            <x-tour.card :item="$item" :href="$href" />
        @endforeach
    </div>
@endif
