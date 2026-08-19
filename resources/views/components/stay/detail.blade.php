@props([
    'service',
    'hub',
    'related' => [],
    'breadcrumbs' => [],
])

@php
    $contact = view_data()->companyContact();
    $waPhone = preg_replace('/\D/', '', $contact['whatsapp'] ?? '');
    $coverSrc = $service['imageDetail'] ?? $service['image'] ?? null;
    $coverSrcset = $service['imageDetailSrcset'] ?? $service['imageSrcset'] ?? null;

    $bodyHtml = trim((string) ($service['content'] ?? ''));
    $bodyHtml = $bodyHtml !== '' ? rich_body_html($bodyHtml) : '';

    $highlights = array_values(array_filter($service['highlights'] ?? [], fn ($h) => filled($h)));
    $rooms = $service['rooms'] ?? [];
    $amenityGroups = $service['amenityGroups'] ?? [];
    $nearby = $service['nearby'] ?? [];
    $nearbyGroups = $service['nearbyGroups'] ?? [];
    $policies = $service['policies'] ?? [];
    $policyLabels = config('stay.policy_labels', []);
    $highlightBadges = array_values(array_filter($service['highlightBadges'] ?? [], fn ($h) => filled($h)));
    $reviewScores = $service['reviewScores'] ?? [];
    $quote = $service['featuredQuote'] ?? ($service['quote'] ?? ['text' => '', 'author' => '']);
    $address = $service['address'] ?: ($service['location'] ?? null);

    $overviewFacts = array_values(array_filter([
        ['icon' => 'building', 'label' => 'Loại hình', 'value' => $service['propertyTypeLabel'] ?? null],
        ['icon' => 'star', 'label' => 'Hạng sao', 'value' => $service['starRating'] ?? null, 'isStar' => true],
        ['icon' => 'map-pin', 'label' => 'Địa chỉ', 'value' => $address, 'wide' => true],
        ['icon' => 'clock', 'label' => 'Nhận phòng', 'value' => $service['checkIn'] ?? null],
        ['icon' => 'clock', 'label' => 'Trả phòng', 'value' => $service['checkOut'] ?? null],
    ], fn ($row) => filled($row['value'] ?? null)));

    $policyRows = array_values(array_filter([
        ['key' => 'check_in', 'icon' => 'clock', 'label' => $policyLabels['check_in'] ?? 'Nhận phòng', 'value' => $policies['check_in'] ?? null],
        ['key' => 'check_out', 'icon' => 'clock', 'label' => $policyLabels['check_out'] ?? 'Trả phòng', 'value' => $policies['check_out'] ?? null],
        ['key' => 'cancellation', 'icon' => 'calendar', 'label' => $policyLabels['cancellation'] ?? 'Huỷ / đổi ngày', 'value' => $policies['cancellation'] ?? null, 'wide' => true],
        ['key' => 'child', 'icon' => 'users', 'label' => $policyLabels['child'] ?? 'Trẻ em', 'value' => $policies['child'] ?? null, 'wide' => true],
        ['key' => 'extra_bed', 'icon' => 'users', 'label' => $policyLabels['extra_bed'] ?? 'Giường phụ / cũi', 'value' => $policies['extra_bed'] ?? null, 'wide' => true],
        ['key' => 'age_restriction', 'icon' => 'shield', 'label' => $policyLabels['age_restriction'] ?? 'Độ tuổi tối thiểu', 'value' => $policies['age_restriction'] ?? null],
        ['key' => 'pet', 'icon' => 'sparkles', 'label' => $policyLabels['pet'] ?? 'Thú cưng', 'value' => $policies['pet'] ?? null, 'wide' => true],
        ['key' => 'smoking', 'icon' => 'flag', 'label' => $policyLabels['smoking'] ?? 'Hút thuốc', 'value' => $policies['smoking'] ?? null],
        ['key' => 'payment', 'icon' => 'tag', 'label' => $policyLabels['payment'] ?? 'Thanh toán', 'value' => $policies['payment'] ?? null, 'wide' => true],
        ['key' => 'payment_cards', 'icon' => 'tag', 'label' => $policyLabels['payment_cards'] ?? 'Thẻ được nhận', 'value' => $policies['payment_cards'] ?? null],
        ['key' => 'id_required', 'icon' => 'shield', 'label' => $policyLabels['id_required'] ?? 'Giấy tờ', 'value' => $policies['id_required'] ?? null, 'wide' => true],
    ], fn ($r) => filled($r['value'] ?? null)));

    $hasOverview = filled($service['summary'] ?? null)
        || $highlights !== []
        || $highlightBadges !== []
        || $reviewScores !== []
        || $overviewFacts !== []
        || filled($quote['text'] ?? null);

    $tabs = [];
    $sectionIds = [];
    if ($hasOverview) {
        $tabs['tong-quan'] = 'Tổng quan';
        $sectionIds[] = 'tong-quan';
    }
    if ($amenityGroups !== [] || ! empty($service['amenities'])) {
        $tabs['tien-ich'] = 'Tiện ích';
        $sectionIds[] = 'tien-ich';
    }
    if ($rooms !== []) {
        $tabs['phong'] = 'Hạng phòng';
        $sectionIds[] = 'phong';
    }
    if ($bodyHtml !== '') {
        $tabs['gioi-thieu'] = 'Giới thiệu';
        $sectionIds[] = 'gioi-thieu';
    }
    if ($nearbyGroups !== [] || $nearby !== []) {
        $tabs['vi-tri'] = 'Vị trí';
        $sectionIds[] = 'vi-tri';
    }
    if ($policyRows !== []) {
        $tabs['chinh-sach'] = 'Chính sách';
        $sectionIds[] = 'chinh-sach';
    }
    if (! empty($service['faqs'])) {
        $tabs['faq'] = 'FAQ';
        $sectionIds[] = 'faq';
    }

    $sidebarPrice = $service['priceFormatted'] ?? null;
    if ($sidebarPrice === 'Liên hệ') {
        $sidebarPrice = null;
    }
    $priceUnit = $service['priceUnitLabel'] ?? '/ đêm';

    $sidebarMeta = array_values(array_filter([
        ['icon' => 'building', 'label' => 'Loại hình', 'value' => $service['propertyTypeLabel'] ?? null],
        ['icon' => 'clock', 'label' => 'Nhận phòng', 'value' => $service['checkIn'] ?? null],
        ['icon' => 'clock', 'label' => 'Trả phòng', 'value' => $service['checkOut'] ?? null],
        ['icon' => 'map-pin', 'label' => 'Vị trí', 'value' => $service['location'] ?? null],
    ], fn ($row) => filled($row['value'] ?? null)));
@endphp

<x-shared.detail-gallery
    :title="$service['title']"
    :cover-src="$coverSrc"
    :cover-srcset="$coverSrcset"
    :gallery="$service['gallery'] ?? []"
    :gallery-count="$service['galleryCount'] ?? 0"
    :breadcrumbs="$breadcrumbs"
    :rating="$service['rating'] ?? null"
    :review-count="$service['reviewCount'] ?? 0"
    :kicker="$service['propertyTypeLabel'] ?? null"
    :star-rating="$service['starRating'] ?? null"
    :location="$address"
    :thumb-slots="5"
/>

<div @class(['detail-body', 'detail-body--plain' => count($tabs) < 2]) @if (count($sectionIds) > 1) x-data="scrollSpy(@js($sectionIds))" @endif>
    @if (count($tabs) > 1)
        <nav class="detail-tabs" aria-label="Điều hướng trong trang">
            <div class="container-site detail-tabs__inner">
                @foreach ($tabs as $id => $label)
                    <a href="#{{ $id }}"
                        class="detail-tabs__link"
                        @if (count($sectionIds) > 1)
                            :class="active === '{{ $id }}' ? 'is-active' : ''"
                        @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </nav>
    @endif

    <div class="container-site detail-layout section-band--sm">
        <div class="min-w-0 detail-stack">
            @if ($hasOverview)
                <section id="tong-quan" class="detail-section" aria-label="Tổng quan">
                    <h2 class="detail-section__title">Tổng quan</h2>
                    @if (! empty($service['summary']))
                        <p class="detail-section__lead body-text prose-travel">{{ $service['summary'] }}</p>
                    @endif
                    @if ($highlightBadges !== [])
                        <div class="stay-highlight-panel">
                            <h3 class="stay-panel__title">Điểm nổi bật</h3>
                            <ul class="vt-chip-list stay-chip-list" aria-label="Tiện ích nổi bật">
                                @foreach ($highlightBadges as $badge)
                                    <li class="vt-chip vt-chip--soft">
                                        <x-icon :name="view_data()->stayAmenityIcon($badge)" class="size-3.5" />
                                        <span>{{ $badge }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($overviewFacts !== [] || $highlights !== [])
                        <dl class="detail-facts">
                            @foreach ($overviewFacts as $fact)
                                <div @class(['detail-facts__item', 'detail-facts__item--wide' => ! empty($fact['wide'])])>
                                    <dt>
                                        <x-icon :name="$fact['icon']" class="size-4" />
                                        {{ $fact['label'] }}
                                    </dt>
                                    <dd>
                                        @if (! empty($fact['isStar']))
                                            <span class="detail-facts__stars">
                                                <x-shared.stars :rating="$fact['value']" />
                                                <span>{{ $fact['value'] }} sao</span>
                                            </span>
                                        @else
                                            {{ $fact['value'] }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                            @if ($highlights !== [])
                                <div class="detail-facts__item detail-facts__item--wide detail-facts__item--highlights">
                                    <dt>
                                        <x-icon name="sparkles" class="size-4" />
                                        Điểm nhấn
                                    </dt>
                                    <dd>
                                        <ul class="detail-facts__checks">
                                            @foreach ($highlights as $h)
                                                <li>
                                                    <span class="detail-facts__check-mark" aria-hidden="true">
                                                        <x-icon name="check" class="size-3.5" />
                                                    </span>
                                                    <span>{{ $h }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                    @if ($reviewScores !== [])
                        <div class="stay-score-panel" aria-label="Điểm theo hạng mục">
                            <h3 class="stay-panel__title">Điểm theo hạng mục</h3>
                            <div class="stay-score-bars">
                                @foreach ($reviewScores as $row)
                                    <div class="stay-score-bars__row">
                                        <span>{{ $row['label'] }}</span>
                                        <div class="stay-score-bars__track" aria-hidden="true">
                                            <i style="width: {{ min(100, (float) $row['score'] * 10) }}%"></i>
                                        </div>
                                        <strong>{{ number_format((float) $row['score'], 1) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if (! empty($quote['text']))
                        <blockquote class="stay-quote">
                            <p>“{{ $quote['text'] }}”</p>
                            @if (! empty($quote['author']))
                                <footer>— {{ $quote['author'] }}</footer>
                            @endif
                        </blockquote>
                    @endif
                </section>
            @endif

            @if ($amenityGroups !== [] || ! empty($service['amenities']))
                <section id="tien-ich" class="detail-section" aria-label="Tiện ích">
                    <h2 class="detail-section__title">Tiện ích nổi bật</h2>
                    @if ($amenityGroups !== [])
                        <div class="stay-amenity-groups">
                            @foreach ($amenityGroups as $group)
                                <div class="stay-amenity-group">
                                    <h3 class="stay-amenity-group__title">{{ $group['label'] }}</h3>
                                    <ul class="stay-amenity-grid">
                                        @foreach ($group['items'] as $item)
                                            <li>
                                                <x-icon :name="view_data()->stayAmenityIcon($item)" class="size-4" />
                                                <span>{{ $item }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="stay-amenity-group">
                            <ul class="stay-amenity-grid">
                                @foreach ($service['amenities'] as $item)
                                    <li>
                                        <x-icon :name="view_data()->stayAmenityIcon($item)" class="size-4" />
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif

            @if ($rooms !== [])
                <section id="phong" class="detail-section" aria-label="Hạng phòng" x-data="stayRooms(@js($rooms))">
                    <h2 class="detail-section__title">Hạng phòng &amp; giá tham khảo</h2>
                    <p class="detail-section__lead body-text">Giá / đêm tham khảo — mở chi tiết phòng để xem tiện nghi, giường và ảnh. Báo giá chính xác theo ngày check-in qua {{ site_brand() }}.</p>
                    <div class="stay-rooms">
                        @foreach ($rooms as $i => $room)
                            @php
                                $roomTags = ! empty($room['highlights']) ? $room['highlights'] : ($room['amenities'] ?? []);
                            @endphp
                            <article
                                @class(['stay-room-card', 'stay-room-card--gallery' => ($room['photos'] ?? []) !== []])
                                @click="show({{ $i }})"
                                aria-label="Chi tiết hạng phòng {{ $room['name'] }}"
                            >
                                @if (($room['photos'] ?? []) !== [])
                                    @php
                                        $roomPhotos = array_values($room['photos']);
                                        $shownPhotos = array_slice($roomPhotos, 0, 4);
                                        $morePhotos = max(0, count($roomPhotos) - count($shownPhotos));
                                    @endphp
                                    <div class="stay-room-card__gallery stay-room-card__gallery--{{ count($shownPhotos) }}" @click.stop>
                                        @foreach ($shownPhotos as $pi => $photo)
                                            <button
                                                type="button"
                                                class="stay-room-card__shot"
                                                @click="show({{ $i }}, {{ $pi }})"
                                                aria-label="Xem ảnh {{ $pi + 1 }} — {{ $room['name'] }}"
                                            >
                                                <img
                                                    src="{{ $photo['url'] }}"
                                                    alt="{{ $photo['alt'] ?? $room['name'] }}"
                                                    loading="lazy"
                                                    referrerpolicy="no-referrer"
                                                />
                                                @if ($loop->last && $morePhotos > 0)
                                                    <span class="stay-room-card__more-count" aria-hidden="true">+{{ $morePhotos }}</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="stay-room-card__body">
                                    <h3 class="item-title stay-room-card__name">{{ $room['name'] }}</h3>
                                    @if (! empty($room['description']))
                                        <p class="stay-room-card__desc body-text max-line-2">{{ strip_tags((string) $room['description']) }}</p>
                                    @endif
                                    <dl class="stay-room-card__specs">
                                        @if (! empty($room['unitTypeLabel']))
                                            <div><dt>Loại</dt><dd>{{ $room['unitTypeLabel'] }}</dd></div>
                                        @endif
                                        @if (! empty($room['bedLabel']))
                                            <div><dt>Giường</dt><dd>{{ $room['bedLabel'] }}</dd></div>
                                        @endif
                                        @if (! empty($room['sizeSqm']))
                                            <div><dt>Diện tích</dt><dd>{{ $room['sizeSqm'] }} m²</dd></div>
                                        @endif
                                        @if (! empty($room['capacity']))
                                            <div><dt>Sức chứa</dt><dd>{{ $room['capacity'] }} khách</dd></div>
                                        @endif
                                        @if (! empty($room['bathroomCount']))
                                            <div><dt>Phòng tắm</dt><dd>{{ $room['bathroomCount'] }}</dd></div>
                                        @endif
                                        @if (! empty($room['view']))
                                            <div><dt>View</dt><dd>{{ $room['view'] }}</dd></div>
                                        @endif
                                    </dl>
                                    @if ($roomTags !== [])
                                        <ul class="vt-chip-list stay-room-card__tags">
                                            @foreach (array_slice($roomTags, 0, 6) as $tag)
                                                <li class="vt-chip vt-chip--soft">{{ is_string($tag) ? $tag : ($tag['label'] ?? '') }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="stay-room-card__price">
                                    @if (! empty($room['priceFormatted']))
                                        <span class="kicker stay-room-card__from">Giá từ</span>
                                        <span class="stay-room-card__amount">{{ $room['priceFormatted'] }}</span>
                                        <span class="stay-room-card__unit">/ đêm</span>
                                    @else
                                        <span class="stay-room-card__amount stay-room-card__amount--soft">Nhận báo giá</span>
                                    @endif
                                    <button type="button" class="btn-ghost stay-room-card__more" @click.stop="show({{ $i }})">
                                        Chi tiết phòng
                                        <x-icon name="arrow-right" class="size-4" />
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="stay-room-modal" x-cloak x-show="open" x-transition.opacity.duration.150ms @keydown.escape.window="close()">
                        <div class="stay-room-modal__backdrop" @click="close()"></div>
                        <div class="stay-room-modal__panel" role="dialog" aria-modal="true" :aria-label="room ? room.name : 'Chi tiết phòng'" @click.stop>
                            <button type="button" class="stay-room-modal__close" x-ref="roomClose" @click="close()" aria-label="Đóng">
                                <x-icon name="close" class="size-5" />
                            </button>
                            <template x-if="room">
                                <div class="stay-room-modal__grid">
                                    <div class="stay-room-modal__photos" x-show="room.photos && room.photos.length">
                                        <div class="stay-room-modal__hero">
                                            <img
                                                x-show="room.photos[photo]"
                                                :src="room.photos[photo] ? room.photos[photo].url : ''"
                                                :alt="(room.photos[photo] && room.photos[photo].alt) || room.name"
                                                referrerpolicy="no-referrer"
                                            />
                                        </div>
                                        <div class="stay-room-modal__thumbs" x-show="room.photos.length > 1">
                                            <template x-for="(p, pi) in room.photos" :key="pi">
                                                <button type="button" class="stay-room-modal__thumb" :class="pi === photo ? 'is-active' : ''" @click="photo = pi">
                                                    <img :src="p.url" :alt="p.alt || room.name" loading="lazy" referrerpolicy="no-referrer" />
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="stay-room-modal__body">
                                        <p class="kicker" x-show="room.unitTypeLabel" x-text="room.unitTypeLabel"></p>
                                        <h2 class="stay-room-modal__title" x-text="room.name"></h2>
                                        <ul class="vt-chip-list stay-chip-list">
                                            <template x-if="room.sizeSqm">
                                                <li class="vt-chip vt-chip--soft" x-text="room.sizeSqm + ' m²'"></li>
                                            </template>
                                            <template x-for="h in (room.highlights || []).slice(0, 8)" :key="h">
                                                <li class="vt-chip vt-chip--soft" x-text="h"></li>
                                            </template>
                                        </ul>
                                        <template x-if="room.beds && room.beds.length">
                                            <section class="stay-room-modal__block">
                                                <h3 class="stay-panel__title">Bố trí giường</h3>
                                                <template x-for="(br, bi) in room.beds" :key="bi">
                                                    <p class="stay-room-modal__bed">
                                                        <strong x-text="br.name"></strong>
                                                        <span x-text="(br.items || []).map(i => i.label).join(', ')"></span>
                                                    </p>
                                                </template>
                                            </section>
                                        </template>
                                        <p class="stay-room-modal__meta body-text" x-show="room.bathroomCount">
                                            Số phòng tắm: <strong x-text="room.bathroomCount"></strong>
                                        </p>
                                        <p class="stay-room-modal__comfort" x-show="room.comfortScore">
                                            Giường thoải mái, <span x-text="room.comfortScore"></span>
                                            <span x-show="room.comfortReviews" x-text="' – dựa trên ' + room.comfortReviews + ' đánh giá'"></span>
                                        </p>
                                        <p class="stay-room-modal__desc body-text" x-show="room.description" x-text="room.description"></p>
                                        <template x-for="g in (room.amenityGroups || [])" :key="g.key">
                                            <section class="stay-room-modal__block">
                                                <h3 class="stay-panel__title" x-text="g.label"></h3>
                                                <ul class="stay-amenity-grid">
                                                    <template x-for="item in g.items" :key="item">
                                                        <li>
                                                            <x-icon name="check" class="size-3.5" />
                                                            <span x-text="item"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </section>
                                        </template>
                                        <p class="stay-room-modal__smoke" x-show="room.smoking">
                                            Hút thuốc: <strong x-text="room.smoking"></strong>
                                        </p>
                                        <div class="stay-room-modal__cta" x-show="room.priceFormatted">
                                            <div class="stay-room-card__price stay-room-card__price--inline">
                                                <span class="kicker stay-room-card__from">Giá từ</span>
                                                <span class="stay-room-card__amount" x-text="room.priceFormatted"></span>
                                                <span class="stay-room-card__unit">/ đêm</span>
                                            </div>
                                            <a class="btn-primary" href="{{ locale_route('customize') }}">Nhận báo giá phòng này</a>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>
            @endif

            <x-shared.detail-content :html="$bodyHtml" title="Về chỗ nghỉ" id="gioi-thieu" />

            @if ($nearbyGroups !== [] || $nearby !== [])
                <section id="vi-tri" class="detail-section" aria-label="Vị trí">
                    <h2 class="detail-section__title">Vị trí &amp; lân cận</h2>
                    @if ($address)
                        <p class="detail-section__lead body-text">{{ $address }}</p>
                    @endif
                    @if ($nearbyGroups !== [])
                        <div class="stay-nearby-groups">
                            @foreach ($nearbyGroups as $group)
                                <div class="stay-amenity-group">
                                    <h3 class="stay-amenity-group__title">{{ $group['label'] }}</h3>
                                    <ul class="stay-nearby">
                                        @foreach ($group['items'] as $place)
                                            <li>
                                                <x-icon :name="$place['icon'] ?? 'map-pin'" class="size-4" />
                                                <div>
                                                    <strong>{{ $place['name'] ?? '' }}</strong>
                                                    @if (! empty($place['distance']))
                                                        <span>{{ $place['distance'] }}</span>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="stay-amenity-group">
                            <ul class="stay-nearby stay-nearby--flat">
                                @foreach ($nearby as $place)
                                    <li>
                                        <x-icon :name="$place['icon'] ?? 'map-pin'" class="size-4" />
                                        <div>
                                            <strong>{{ $place['name'] ?? '' }}</strong>
                                            @if (! empty($place['distance']))
                                                <span>{{ $place['distance'] }}</span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif

            @if ($policyRows !== [])
                <section id="chinh-sach" class="detail-section" aria-label="Chính sách">
                    <h2 class="detail-section__title">Chính sách chỗ nghỉ</h2>
                    <dl class="detail-facts">
                        @foreach ($policyRows as $row)
                            <div @class(['detail-facts__item', 'detail-facts__item--wide' => ! empty($row['wide'])])>
                                <dt>
                                    <x-icon :name="$row['icon']" class="size-4" />
                                    {{ $row['label'] }}
                                </dt>
                                <dd>{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            @if (! empty($service['faqs']))
                <div id="faq" class="detail-section detail-section--faq">
                    <x-shared.faq :faqs="$service['faqs']" title="Câu hỏi thường gặp" />
                </div>
            @endif
        </div>

        <x-shared.detail-booking-sidebar
            aria-label="Đặt phòng"
            :price="$sidebarPrice"
            price-label="Giá từ"
            :price-unit="$priceUnit"
            price-hint="Giá / đêm tham khảo — báo giá chính xác trong 24h"
            fallback-price="Nhận báo giá trong 24h"
            :meta-items="$sidebarMeta"
            :badge="$service['badge'] ?? null"
            :primary-href="locale_route('customize')"
            primary-label="Nhận báo giá phòng"
            primary-label-short="Báo giá"
            primary-icon="building"
            :secondary-href="locale_route('contact')"
            secondary-label="Tư vấn lưu trú"
            secondary-icon="mail"
            :whatsapp="$waPhone !== '' ? $waPhone : null"
            :usps="[
                ['icon' => 'expert', 'label' => 'Chọn phòng đúng nhu cầu & ngân sách'],
                ['icon' => 'value', 'label' => 'Giá minh bạch, không phí ẩn'],
                ['icon' => 'support', 'label' => 'Hỗ trợ trước & trong kỳ nghỉ'],
            ]"
        />
    </div>
</div>

@if (count($related) > 0)
    <section class="container-site section-band--sm" aria-label="Chỗ nghỉ liên quan">
        <x-shared.section-heading title="Chỗ nghỉ liên quan" />
        <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($related as $item)
                @php
                    $relatedHref = locale_route('services.show', [
                        'cluster' => $item['cluster'] ?? $service['cluster'],
                        'category' => $item['categorySlug'],
                        'slug' => $item['slug'],
                    ]);
                @endphp
                <x-service.card-compact :item="$item" :href="$relatedHref" />
            @endforeach
        </div>
    </section>
@endif
