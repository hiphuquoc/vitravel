@php
    /** @var array<int, array<string, mixed>> $items */
    $variant = ($variant ?? 'wide') === 'compact' ? 'compact' : 'wide';
    $kind = match ($kind ?? 'tour') {
        'cruise' => 'cruise',
        'service' => 'service',
        default => 'tour',
    };
@endphp

@if (count($items) === 0)
    <div class="card listing-empty">
        <x-icon :name="match ($kind) { 'cruise' => 'cruise', 'service' => 'briefcase', default => 'compass' }" class="size-10 text-muted" />
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
                $href = match ($kind) {
                    'cruise' => locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']]),
                    'service' => locale_route('services.show', [
                        'cluster' => $item['cluster'],
                        'category' => $item['categorySlug'],
                        'slug' => $item['slug'],
                    ]),
                    default => locale_route('tours.show', ['country' => $item['countrySlug'], 'slug' => $item['slug']]),
                };
            @endphp
            @if ($kind === 'service')
                <x-service.card-compact :item="$item" :href="$href" />
            @else
                <x-tour.card-compact :item="$item" :href="$href" />
            @endif
        @endforeach
    </div>
@else
    <div class="site-stack">
        @foreach ($items as $item)
            @php
                $href = match ($kind) {
                    'cruise' => locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']]),
                    'service' => locale_route('services.show', [
                        'cluster' => $item['cluster'],
                        'category' => $item['categorySlug'],
                        'slug' => $item['slug'],
                    ]),
                    default => locale_route('tours.show', ['country' => $item['countrySlug'], 'slug' => $item['slug']]),
                };
            @endphp
            @if ($kind === 'service')
                <x-service.card :item="$item" :href="$href" />
            @else
                <x-tour.card :item="$item" :href="$href" />
            @endif
        @endforeach
    </div>
@endif
