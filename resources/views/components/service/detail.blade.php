@props([
    'service',
    'hub',
    'related' => [],
    'breadcrumbs' => [],
])

@php
    $clusterIcon = $service['clusterIcon'] ?? 'briefcase';
    $attrs = $service['attrs'] ?? [];
    $attrLabels = [
        'from' => 'Điểm đi',
        'to' => 'Điểm đến',
        'duration_hours' => 'Thời lượng',
        'flight_time' => 'Thời gian bay',
        'operator' => 'Nhà vận hành',
        'train_class' => 'Hạng ghế',
        'train_number' => 'Số hiệu',
        'airline' => 'Hãng bay',
        'aircraft' => 'Loại máy bay',
        'check_in' => 'Nhận phòng',
        'check_out' => 'Trả phòng',
        'room_type' => 'Loại phòng',
        'venue' => 'Địa điểm',
        'validity' => 'Hiệu lực vé',
    ];
    $displayAttrs = [];
    foreach ($attrLabels as $key => $label) {
        if (! empty($attrs[$key])) {
            $value = $attrs[$key];
            if ($key === 'duration_hours') {
                $value = ((int) $value).' giờ';
            }
            $displayAttrs[] = ['label' => $label, 'value' => $value, 'key' => $key];
        }
    }
    if (! empty($service['starRating'])) {
        $displayAttrs[] = ['label' => 'Hạng sao', 'value' => $service['starRating'], 'key' => 'star_rating', 'isStar' => true];
    }
    if (! empty($service['duration']) && ! collect($displayAttrs)->contains(fn ($a) => in_array($a['key'], ['duration_hours', 'flight_time'], true))) {
        array_unshift($displayAttrs, ['label' => 'Thời lượng', 'value' => $service['duration'], 'key' => 'duration']);
    }
    if (! empty($service['location'])) {
        $displayAttrs[] = ['label' => 'Vị trí', 'value' => $service['location'], 'key' => 'location', 'wide' => true];
    }

    $contact = view_data()->companyContact();
    $waPhone = preg_replace('/\D/', '', $contact['whatsapp'] ?? '');
    $coverSrc = $service['imageDetail'] ?? $service['image'] ?? null;
    $coverSrcset = $service['imageDetailSrcset'] ?? $service['imageSrcset'] ?? null;

    $serviceBodyRaw = trim((string) ($service['content'] ?? ''));
    $serviceBodyHtml = $serviceBodyRaw !== '' ? rich_body_html($serviceBodyRaw) : '';

    $tabs = [];
    $sectionIds = [];
    if (! empty($service['summary']) || $displayAttrs !== []) {
        $tabs['tong-quan'] = 'Tổng quan';
        $sectionIds[] = 'tong-quan';
    }
    if ($serviceBodyHtml !== '') {
        $tabs['noi-dung'] = 'Nội dung';
        $sectionIds[] = 'noi-dung';
    }
    if (! empty($service['highlights'])) {
        $tabs['diem-nhan'] = 'Điểm nhấn';
        $sectionIds[] = 'diem-nhan';
    }
    if (! empty($service['options'])) {
        $tabs['tuy-chon'] = 'Tuỳ chọn';
        $sectionIds[] = 'tuy-chon';
    }
    if (! empty($service['inclusions']) || ! empty($service['exclusions']) || ! empty($service['notes'])) {
        $tabs['bao-gom'] = 'Bao gồm';
        $sectionIds[] = 'bao-gom';
    }
    if (! empty($service['faqs'])) {
        $tabs['faq'] = 'FAQ';
        $sectionIds[] = 'faq';
    }

    $sidebarMeta = array_values(array_filter([
        ['icon' => 'calendar', 'label' => 'Thời lượng', 'value' => $service['duration'] ?? null],
        ['icon' => 'tag', 'label' => 'Mã', 'value' => $service['code'] ?? null],
        ['icon' => 'map-pin', 'label' => 'Vị trí', 'value' => $service['location'] ?? null],
    ], fn ($row) => filled($row['value'] ?? null)));

    $sidebarPrice = $service['priceFormatted'] ?? null;
    if ($sidebarPrice === 'Liên hệ') {
        $sidebarPrice = null;
    }
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
            @if (! empty($service['summary']) || $displayAttrs !== [])
                <section id="tong-quan" class="detail-section" aria-label="Tổng quan">
                    <h2 class="detail-section__title">Tổng quan</h2>
                    @if (! empty($service['summary']))
                        <p class="detail-section__lead body-text prose-travel">{{ $service['summary'] }}</p>
                    @endif
                    @if ($displayAttrs !== [])
                        <dl class="detail-facts">
                            @foreach ($displayAttrs as $attr)
                                <div @class([
                                    'detail-facts__item',
                                    'detail-facts__item--wide' => ! empty($attr['wide']) || in_array($attr['key'], ['operator', 'venue', 'location'], true),
                                ])>
                                    <dt>
                                        <x-icon :name="$attr['key'] === 'location' ? 'map-pin' : $clusterIcon" class="size-4" />
                                        {{ $attr['label'] }}
                                    </dt>
                                    <dd>
                                        @if (! empty($attr['isStar']))
                                            <span class="detail-facts__stars">
                                                <x-shared.stars :rating="$attr['value']" />
                                                <span>{{ $attr['value'] }} sao</span>
                                            </span>
                                        @else
                                            {{ $attr['value'] }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </section>
            @endif

            @if ($serviceBodyHtml !== '')
                <section id="noi-dung" class="detail-section" aria-label="Nội dung chi tiết">
                    <h2 class="detail-section__title">Nội dung chi tiết</h2>
                    <div class="detail-prose prose-travel prose-travel--itinerary">
                        {!! $serviceBodyHtml !!}
                    </div>
                </section>
            @endif

            @if (! empty($service['highlights']))
                <section id="diem-nhan" class="detail-section" aria-label="Điểm nhấn">
                    <h2 class="detail-section__title">Điểm nhấn</h2>
                    <ul class="detail-checklist">
                        @foreach ($service['highlights'] as $h)
                            <li>
                                <span class="detail-checklist__mark" aria-hidden="true">
                                    <x-icon name="check" class="size-3.5" />
                                </span>
                                <span>{{ $h }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (! empty($service['options']))
                <section id="tuy-chon" class="detail-section" aria-label="Tuỳ chọn">
                    <h2 class="detail-section__title">Tuỳ chọn &amp; hạng</h2>
                    <div class="detail-option-grid">
                        @foreach ($service['options'] as $option)
                            <article class="detail-option">
                                <div class="detail-option__body">
                                    <h3 class="detail-option__name">{{ $option['name'] }}</h3>
                                    @if (! empty($option['priceFormatted']))
                                        <p class="detail-option__price">
                                            <span class="detail-option__price-label">Từ</span>
                                            <span class="detail-option__price-value">{{ $option['priceFormatted'] }}</span>
                                        </p>
                                    @endif
                                    @if (! empty($option['description']))
                                        <p class="detail-option__desc">{{ $option['description'] }}</p>
                                    @endif
                                    @if (! empty($option['amenities']))
                                        <ul class="detail-option__amenities">
                                            @foreach ($option['amenities'] as $amenity)
                                                <li>
                                                    <x-icon name="check" class="size-3.5 shrink-0" />
                                                    <span>{{ $amenity }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <x-shared.detail-inclusions
                title="Bao gồm &amp; lưu ý"
                :inclusions="$service['inclusions'] ?? []"
                :exclusions="$service['exclusions'] ?? []"
                :notes="$service['notes'] ?? []"
            />

            @if (! empty($service['faqs']))
                <div id="faq" class="detail-section detail-section--faq">
                    <x-shared.faq :faqs="$service['faqs']" title="Câu hỏi thường gặp" />
                </div>
            @endif
        </div>

        <x-shared.detail-booking-sidebar
            aria-label="Đặt dịch vụ"
            :price="$sidebarPrice"
            price-label="Giá từ"
            price-hint="Giá tham khảo — báo giá chính xác trong 24h"
            fallback-price="Nhận báo giá trong 24h"
            :meta-items="$sidebarMeta"
            :badge="$service['badge'] ?? null"
            :primary-href="locale_route('customize')"
            primary-label="Thiết kế hành trình"
            primary-icon="route"
            :secondary-href="locale_route('contact')"
            secondary-label="Liên hệ tư vấn"
            secondary-icon="mail"
            :whatsapp="$waPhone !== '' ? $waPhone : null"
            :usps="[
                ['icon' => 'expert', 'label' => 'Đặt qua chuyên gia bản địa'],
                ['icon' => 'value', 'label' => 'Giá minh bạch, không phí ẩn'],
                ['icon' => 'support', 'label' => 'Hỗ trợ 24/7 suốt hành trình'],
            ]"
        />
    </div>
</div>

@if (count($related) > 0)
    <section class="container-site section-band--sm" aria-label="Dịch vụ liên quan">
        <x-shared.section-heading title="Dịch vụ liên quan" />
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
