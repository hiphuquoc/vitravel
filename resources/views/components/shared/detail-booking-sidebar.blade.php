@props([
    'ariaLabel' => 'Đặt chỗ',
    'priceLabel' => 'Giá từ',
    'price' => null,
    'priceHint' => 'Giá tham khảo — nhận báo giá chính xác trong 24h',
    'fallbackPrice' => 'Nhận báo giá trong 24h',
    'metaItems' => [],
    'badge' => null,
    'primaryHref' => null,
    'primaryLabel' => 'Yêu cầu báo giá',
    'primaryLabelShort' => null,
    'primaryIcon' => 'sparkles',
    'secondaryHref' => null,
    'secondaryLabel' => null,
    'secondaryIcon' => 'mail',
    'whatsapp' => null,
    'usps' => [],
    'trust' => null,
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

    $barPrimaryLabel = filled($primaryLabelShort)
        ? trim((string) $primaryLabelShort)
        : (mb_strlen($primaryLabel) > 10 ? 'Báo giá' : $primaryLabel);
@endphp

<aside class="detail-sidebar" aria-label="{{ $ariaLabel }}">
    <div @class(['detail-book', 'detail-book--badged' => $badgeText !== ''])>
        @if ($badgeText !== '')
            <p class="detail-book__badge detail-book__badge--{{ $badgeTone }}">
                <x-icon name="sparkles" class="size-3.5" />
                <span>{{ $badgeText }}</span>
            </p>
        @endif

        <div class="detail-book__price-block">
            @if ($hasPrice)
                <p class="detail-book__from">{{ $priceLabel }}</p>
                <p class="detail-book__amount">{{ $price }}</p>
                @if ($priceHint)
                    <p class="detail-book__hint">{{ $priceHint }}</p>
                @endif
            @else
                <p class="detail-book__amount detail-book__amount--soft">{{ $fallbackPrice }}</p>
                @if ($priceHint)
                    <p class="detail-book__hint">{{ $priceHint }}</p>
                @endif
            @endif
        </div>

        @if (count($metaItems))
            <dl class="detail-book__meta">
                @foreach ($metaItems as $row)
                    @continue(empty($row['value']))
                    <div class="detail-book__meta-row">
                        <dt>
                            @if (! empty($row['icon']))
                                <x-icon :name="$row['icon']" class="size-3.5" />
                            @endif
                            {{ $row['label'] ?? '' }}
                        </dt>
                        <dd>{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        <div class="detail-book__actions">
            @if ($primaryHref)
                <a href="{{ $primaryHref }}" class="btn-primary w-full">
                    <x-icon :name="$primaryIcon" class="size-4" /> {{ $primaryLabel }}
                </a>
            @endif
            @if ($secondaryHref && $secondaryLabel)
                <a href="{{ $secondaryHref }}" class="btn-outline w-full">
                    <x-icon :name="$secondaryIcon" class="size-4" /> {{ $secondaryLabel }}
                </a>
            @endif
            @if ($whatsapp)
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
                    class="btn-whatsapp w-full">
                    <x-icon name="whatsapp" class="size-4.5" /> Chat WhatsApp
                </a>
            @endif
        </div>

        @if (count($usps))
            <ul class="detail-book__usp">
                @foreach ($usps as $usp)
                    <li>
                        <span class="detail-book__usp-icon" aria-hidden="true">
                            <x-icon :name="$usp['icon'] ?? 'check'" class="size-3.5" />
                        </span>
                        <span>{{ $usp['label'] ?? $usp }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($trust)
            <p class="detail-book__trust">
                <x-icon name="shield" class="size-4" /> {{ $trust }}
            </p>
        @endif
    </div>
</aside>

<nav class="detail-book-bar" aria-label="{{ $ariaLabel }}">
    <div class="detail-book-bar__lead">
        @if ($hasPrice)
            <p class="detail-book-bar__from">{{ $priceLabel }}</p>
            <p class="detail-book-bar__amount">{{ $price }}</p>
        @else
            <p class="detail-book-bar__amount detail-book-bar__amount--soft">{{ $fallbackPrice }}</p>
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
        @if ($secondaryHref && $secondaryLabel)
            <a href="{{ $secondaryHref }}"
                class="detail-book-bar__icon detail-book-bar__icon--ghost"
                aria-label="{{ $secondaryLabel }}">
                <x-icon :name="$secondaryIcon" class="size-4.5" />
            </a>
        @endif
        @if ($primaryHref)
            <a href="{{ $primaryHref }}" class="detail-book-bar__cta">
                <span class="detail-book-bar__cta-text">{{ $barPrimaryLabel }}</span>
            </a>
        @endif
    </div>
</nav>
