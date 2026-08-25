@props([
    'price' => null,
    'priceUnit' => '/ đêm',
    'badge' => null,
    'checkIn' => null,
    'checkOut' => null,
    'hasRooms' => false,
    'whatsapp' => null,
    'bookHref' => null,
])

@php
    $hasPrice = filled($price);
    $badgeText = is_string($badge) ? trim($badge) : '';
    $badgeTone = 'brand';
    if ($badgeText !== '') {
        $needle = mb_strtolower($badgeText);
        if (str_contains($needle, 'hot') || str_contains($needle, 'deal') || str_contains($needle, 'ưu đãi') || str_contains($needle, 'giảm')) {
            $badgeTone = 'deal';
        } elseif (str_contains($needle, 'mới') || str_contains($needle, 'new')) {
            $badgeTone = 'new';
        } elseif (str_contains($needle, 'bán chạy') || str_contains($needle, 'yêu thích') || str_contains($needle, 'best')) {
            $badgeTone = 'popular';
        }
    }

    $primaryHref = $bookHref ?: ($hasRooms ? '#phong' : locale_route('customize'));
    $facts = array_values(array_filter([
        filled($checkIn) ? ['label' => 'Nhận phòng', 'value' => $checkIn] : null,
        filled($checkOut) ? ['label' => 'Trả phòng', 'value' => $checkOut] : null,
    ]));
@endphp

<aside class="detail-sidebar stay-book-sidebar" aria-label="Đặt phòng">
    <div @class(['detail-book', 'stay-book', 'detail-book--badged' => $badgeText !== ''])>
        @if ($badgeText !== '')
            <p class="detail-book__badge detail-book__badge--{{ $badgeTone }}">
                <x-icon name="sparkles" class="size-3.5" />
                <span>{{ $badgeText }}</span>
            </p>
        @endif

        <div class="detail-book__price-block stay-book__price">
            @if ($hasPrice)
                <p class="detail-book__from">Giá từ</p>
                <p class="detail-book__amount">
                    {{ $price }}@if ($priceUnit)<span class="detail-book__unit">{{ $priceUnit }}</span>@endif
                </p>
                <p class="detail-book__hint">Giá cố định theo hạng phòng</p>
            @else
                <p class="detail-book__from">Đặt trực tuyến</p>
                <p class="detail-book__amount detail-book__amount--soft">Chọn hạng & đặt phòng</p>
                <p class="detail-book__hint">Giá hiển thị ngay trên từng hạng</p>
            @endif
        </div>

        @if ($facts !== [])
            <dl class="stay-book__facts">
                @foreach ($facts as $row)
                    <div class="stay-book__fact">
                        <dt>{{ $row['label'] }}</dt>
                        <dd>{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        <div class="detail-book__actions stay-book__actions">
            <a href="{{ $primaryHref }}" class="btn-primary w-full stay-book__cta" @if ($primaryHref === '#phong') @click.prevent="document.getElementById('phong')?.scrollIntoView({ behavior: 'smooth', block: 'start' })" @endif>
                <x-icon name="calendar" class="size-4" /> Đặt phòng
            </a>
            @if ($whatsapp)
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
                    class="stay-book__wa">
                    <x-icon name="whatsapp" class="size-4" /> Hỏi nhanh qua WhatsApp
                </a>
            @endif
        </div>

        <p class="detail-book__trust">
            <x-icon name="shield" class="size-4" />
            Đảm bảo chất lượng &amp; hỗ trợ tận tâm
        </p>
    </div>
</aside>

<nav class="detail-book-bar stay-book-bar" aria-label="Đặt phòng">
    <div class="detail-book-bar__lead">
        @if ($hasPrice)
            <p class="detail-book-bar__from">Giá từ</p>
            <p class="detail-book-bar__amount">
                {{ $price }}@if ($priceUnit)<span class="detail-book-bar__unit">{{ $priceUnit }}</span>@endif
            </p>
        @else
            <p class="detail-book-bar__amount detail-book-bar__amount--soft">Chọn hạng phòng</p>
        @endif
    </div>
    <div class="detail-book-bar__actions">
        @if ($whatsapp)
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
                class="detail-book-bar__icon"
                aria-label="Chat WhatsApp">
                <x-icon name="whatsapp" class="size-5" />
            </a>
        @endif
        <a href="{{ $primaryHref }}" class="detail-book-bar__cta" @if ($primaryHref === '#phong') @click.prevent="document.getElementById('phong')?.scrollIntoView({ behavior: 'smooth', block: 'start' })" @endif>
            <span class="detail-book-bar__cta-text">Đặt phòng</span>
        </a>
    </div>
</nav>
