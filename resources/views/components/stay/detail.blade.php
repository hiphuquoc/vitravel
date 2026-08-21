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

    $stayAttrs = \App\Support\StayFacilities::normalizeStayAttrs(
        is_array($service['attrs'] ?? null) ? $service['attrs'] : [],
    );
    $staySections = \App\Support\StayFacilities::resolvePublicSections($stayAttrs);
    $payloadAmenity = is_array($service['amenityGroups'] ?? null) ? $service['amenityGroups'] : [];
    $payloadNearbyGroups = is_array($service['nearbyGroups'] ?? null) ? $service['nearbyGroups'] : [];
    $amenityGroups = count($payloadAmenity) > count($staySections['amenityGroups'])
        ? $payloadAmenity
        : $staySections['amenityGroups'];
    $nearbyGroups = count($payloadNearbyGroups) > count($staySections['nearbyGroups'])
        ? $payloadNearbyGroups
        : $staySections['nearbyGroups'];
    $reviewScores = $staySections['reviewScores'] !== []
        ? $staySections['reviewScores']
        : ($service['reviewScores'] ?? []);

    $rooms = $service['rooms'] ?? [];
    $policies = $service['policies'] ?? [];
    $policyLabels = config('stay.policy_labels', []);
    $address = $service['address'] ?: ($service['location'] ?? null);
    $languages = is_array($service['languages'] ?? null) ? $service['languages'] : [];
    $totalRooms = $service['totalRooms'] ?? null;
    $galleryCatalogCount = max((int) ($service['galleryCount'] ?? 0), count($service['gallery'] ?? []));
    $policyRows = \App\Support\StayFacilities::policyRows($policies, is_array($service['attrs'] ?? null) ? $service['attrs'] : []);
    $paymentCards = array_values(array_filter($service['paymentCards'] ?? [], fn ($c) => filled($c)));

    $overviewFacts = array_values(array_filter([
        ['icon' => 'building', 'label' => 'Loại hình', 'value' => $service['propertyTypeLabel'] ?? null],
        ['icon' => 'star', 'label' => 'Hạng sao', 'value' => $service['starRating'] ?? null, 'isStar' => true],
        ['icon' => 'map-pin', 'label' => 'Địa chỉ', 'value' => $address, 'wide' => true],
        ['icon' => 'clock', 'label' => 'Nhận phòng', 'value' => $service['checkIn'] ?? null],
        ['icon' => 'clock', 'label' => 'Trả phòng', 'value' => $service['checkOut'] ?? null],
        ['icon' => 'building', 'label' => 'Số phòng', 'value' => $totalRooms ? (string) $totalRooms : null],
        ['icon' => 'flag', 'label' => 'Ngôn ngữ', 'value' => $languages !== [] ? implode(', ', $languages) : null, 'wide' => true],
    ], fn ($row) => filled($row['value'] ?? null)));

    $hasOverview = $reviewScores !== []
        || $overviewFacts !== [];

    $tabs = [];
    $sectionIds = [];
    if ($hasOverview) {
        $tabs['tong-quan'] = 'Tổng quan';
        $sectionIds[] = 'tong-quan';
    }
    if ($amenityGroups !== []) {
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
    if ($nearbyGroups !== []) {
        $tabs['vi-tri'] = 'Vị trí';
        $sectionIds[] = 'vi-tri';
    }
    if ($policyRows !== []) {
        $tabs['chinh-sach'] = 'Quy tắc chung';
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
@endphp

<x-shared.detail-gallery
    :title="$service['title']"
    :cover-src="$coverSrc"
    :cover-srcset="$coverSrcset"
    :gallery="$service['gallery'] ?? []"
    :gallery-count="$galleryCatalogCount"
    :breadcrumbs="$breadcrumbs"
    :rating="$service['rating'] ?? null"
    :review-count="$service['reviewCount'] ?? 0"
    :kicker="$service['propertyTypeLabel'] ?? null"
    :star-rating="$service['starRating'] ?? null"
    :location="$address"
    :thumb-slots="4"
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
            @if ($hasOverview || $bodyHtml !== '')
                <section id="tong-quan" class="detail-section" aria-label="Tổng quan">
                    <h2 class="detail-section__title">Tổng quan</h2>
                    <div class="stay-overview-card">
                        @if ($overviewFacts !== [])
                            <dl class="stay-facts-grid">
                                @foreach ($overviewFacts as $fact)
                                    <div @class(['stay-facts-grid__item', 'stay-facts-grid__item--wide' => ! empty($fact['wide'])])>
                                        <dt class="stay-facts-grid__label">
                                            <x-icon :name="$fact['icon']" class="size-4 text-primary-600 shrink-0" />
                                            <span>{{ $fact['label'] }}</span>
                                        </dt>
                                        <dd class="stay-facts-grid__value">
                                            @if (! empty($fact['isStar']))
                                                <x-stay.star-rating :rating="$fact['value']" size="md" />
                                            @else
                                                {{ $fact['value'] }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif

                        @if ($reviewScores !== [])
                            <div class="stay-scores-section" aria-label="Điểm theo hạng mục">
                                <div class="stay-section-subhead">
                                    <div class="stay-facts-grid__label">
                                        <x-icon name="star" class="size-4 text-primary-600 shrink-0" />
                                        <span>Điểm theo hạng mục</span>
                                    </div>
                                </div>
                                <div class="stay-score-grid">
                                    @foreach ($reviewScores as $row)
                                        <div class="stay-score-card">
                                            <div class="stay-score-card__header">
                                                <span class="stay-score-card__label">
                                                    <x-icon :name="view_data()->stayScoreIcon($row['tag'] ?? $row['key'] ?? $row['label'])" class="size-4 text-primary-600 shrink-0" />
                                                    <span>{{ $row['label'] }}</span>
                                                </span>
                                                <strong class="stay-score-card__badge">{{ number_format((float) $row['score'], 1) }}</strong>
                                            </div>
                                            <div class="stay-score-card__track" aria-hidden="true">
                                                <div class="stay-score-card__bar" style="width: {{ min(100, (float) $row['score'] * 10) }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($bodyHtml !== '')
                            <div id="gioi-thieu" class="stay-about-section" x-data="{ expanded: false }">
                                <div class="stay-section-subhead">
                                    <div class="stay-facts-grid__label">
                                        <x-icon name="info" class="size-4 text-primary-600 shrink-0" />
                                        <span>Về chỗ nghỉ</span>
                                    </div>
                                </div>
                                <div class="stay-about-content" :class="{ 'is-expanded': expanded }">
                                    <div class="prose-travel prose-travel--itinerary">
                                        {!! $bodyHtml !!}
                                    </div>
                                    <div class="stay-about-fade" x-show="!expanded" aria-hidden="true"></div>
                                </div>
                                <div class="stay-about-actions">
                                    <button type="button" class="stay-about-toggle-btn" @click="expanded = !expanded" :aria-expanded="expanded.toString()">
                                        <span x-text="expanded ? 'Thu gọn' : 'Xem thêm về chỗ nghỉ'"></span>
                                        <x-icon name="chevron-down" class="size-4 transition-transform duration-200" ::class="expanded ? 'rotate-180' : ''" />
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            @if ($amenityGroups !== [])
                <section id="tien-ich" class="detail-section" aria-label="Tiện ích">
                    <h2 class="detail-section__title">Tiện ích</h2>
                    <div class="stay-feature-flow stay-feature-flow--amenities">
                        @foreach ($amenityGroups as $group)
                            <article @class(['stay-feature-block', 'stay-feature-block--popular' => ($group['key'] ?? '') === 'popular'])>
                                <h3 class="stay-feature-block__title">{{ $group['label'] }}</h3>
                                <ul class="stay-amenity-grid">
                                    @foreach ($group['items'] as $item)
                                        <li>
                                            <x-icon :name="view_data()->stayAmenityIcon($item)" class="size-4" />
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <x-stay.rooms :rooms="$rooms" />


            @if ($nearbyGroups !== [])
                <section id="vi-tri" class="detail-section" aria-label="Vị trí">
                    <h2 class="detail-section__title">Vị trí &amp; lân cận</h2>
                    <div class="stay-feature-flow stay-feature-flow--nearby">
                        @foreach ($nearbyGroups as $group)
                            <article class="stay-feature-block stay-feature-block--nearby">
                                <h3 class="stay-feature-block__title">{{ $group['label'] }}</h3>
                                <ul class="stay-nearby">
                                    @foreach ($group['items'] as $place)
                                        <li>
                                            <x-icon :name="$place['icon'] ?? 'map-pin'" class="size-4" />
                                            <div class="stay-nearby__copy">
                                                <strong>{{ $place['name'] ?? '' }}</strong>
                                                @if (! empty($place['distance']))
                                                    <span class="stay-nearby__distance">{{ $place['distance'] }}</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($policyRows !== [])
                <section id="chinh-sach" class="detail-section" aria-label="Quy tắc chung">
                    <h2 class="detail-section__title">Quy tắc chung</h2>
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
                    @if ($paymentCards !== [])
                        <div class="stay-payment-cards">
                            <h3 class="stay-panel__title">Thẻ thanh toán</h3>
                            <ul class="vt-chip-list stay-chip-list">
                                @foreach ($paymentCards as $card)
                                    <li class="vt-chip vt-chip--soft">{{ $card }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif

            @if (! empty($service['faqs']))
                <div id="faq" class="detail-section detail-section--faq">
                    <x-shared.faq :faqs="$service['faqs']" title="Câu hỏi thường gặp" />
                </div>
            @endif
        </div>

        <x-stay.booking-sidebar
            :price="$sidebarPrice"
            :price-unit="$priceUnit"
            :badge="$service['badge'] ?? null"
            :check-in="$service['checkIn'] ?? null"
            :check-out="$service['checkOut'] ?? null"
            :has-rooms="$rooms !== []"
            :whatsapp="$waPhone !== '' ? $waPhone : null"
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
