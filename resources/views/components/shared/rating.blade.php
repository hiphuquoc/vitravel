@props([
    'rating' => 0,
    'count' => null,
])

@php
    $rating = (float) $rating;
    $label = match (true) {
        $rating >= 4.5 => 'Xuất sắc',
        $rating >= 4.0 => 'Rất tốt',
        $rating >= 3.5 => 'Tốt',
        $rating >= 3.0 => 'Khá',
        $rating > 0 => 'Trung bình',
        default => null,
    };
@endphp

{{-- Điểm số + sao tip bo tròn nhẹ + nhãn + lượt đánh giá (một style dùng chung mọi nơi) --}}
<span {{ $attributes->merge(['class' => 'rating']) }}>
    <span class="rating-badge">
        {{ $rating > 0 ? number_format($rating, 1) : '—' }}
    </span>

    @if ($rating > 0)
        <x-shared.stars :rating="$rating" />
    @endif

    @if ($label)
        <span class="text-sm font-semibold text-ink">{{ $label }}</span>
    @endif

    @if ($count !== null && (int) $count > 0)
        <span class="text-sm text-muted">— {{ number_format((int) $count) }} đánh giá</span>
    @endif
</span>
