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

    $highlights = array_values(array_filter($service['highlights'] ?? [], fn ($h) => filled($h)));
    $hasOverview = ! empty($service['summary']) || $displayAttrs !== [] || $highlights !== [];

    $tabs = [];
    $sectionIds = [];
    if ($hasOverview) {
        $tabs['tong-quan'] = 'Tổng quan';
        $sectionIds[] = 'tong-quan';
    }
    if (! empty($service['priceTable']['periods'])) {
        $tabs['bang-gia'] = 'Bảng giá';
        $sectionIds[] = 'bang-gia';
    }
    if ($serviceBodyHtml !== '') {
        $tabs['noi-dung'] = 'Nội dung';
        $sectionIds[] = 'noi-dung';
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
            @if ($hasOverview)
                <section id="tong-quan" class="detail-section" aria-label="Tổng quan">
                    <h2 class="detail-section__title">Tổng quan</h2>
                    @if (! empty($service['summary']))
                        <p class="detail-section__lead body-text prose-travel">{{ $service['summary'] }}</p>
                    @endif
                    @if ($displayAttrs !== [] || $highlights !== [])
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
                                            <x-stay.star-rating :rating="$attr['value']" size="md" />
                                        @else
                                            {{ $attr['value'] }}
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
                </section>
            @endif

            <x-shared.detail-price-table :table="$service['priceTable'] ?? null" />

            <x-shared.detail-content :html="$serviceBodyHtml" />

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

        @php
            $cluster = $service['cluster'] ?? 'other';
            $hasPriceTable = ! empty($service['priceTable']['periods']);

            // Context-aware labels & CTA matching user intent
            $sidebarConfig = match($cluster) {
                'experience' => [
                    'aria' => 'Đặt vé & trải nghiệm',
                    'primaryLabel' => $hasPriceTable ? 'Chọn vé đặt ngay' : 'Đặt vé trải nghiệm',
                    'primaryShort' => 'Đặt vé',
                    'primaryHref' => $hasPriceTable ? '#bang-gia' : locale_route('contact'),
                    'primaryIcon' => 'ticket',
                    'secondaryLabel' => 'Tư vấn nhanh',
                    'secondaryHref' => locale_route('contact'),
                    'priceHint' => 'Giá vé chính thức · Xác nhận tức thì',
                    'fallbackPrice' => 'Liên hệ đặt vé',
                ],
                'flight' => [
                    'aria' => 'Đặt vé máy bay',
                    'primaryLabel' => $hasPriceTable ? 'Xem bảng giá vé' : 'Tư vấn đặt vé',
                    'primaryShort' => 'Đặt vé',
                    'primaryHref' => $hasPriceTable ? '#bang-gia' : locale_route('contact'),
                    'primaryIcon' => 'plane',
                    'secondaryLabel' => 'Hỗ trợ hành trình',
                    'secondaryHref' => locale_route('contact'),
                    'priceHint' => 'Hỗ trợ giữ chỗ & xuất vé 24/7',
                    'fallbackPrice' => 'Liên hệ báo giá vé',
                ],
                'train' => [
                    'aria' => 'Đặt vé tàu hỏa',
                    'primaryLabel' => $hasPriceTable ? 'Xem giá vé tàu' : 'Đặt vé tàu',
                    'primaryShort' => 'Đặt vé',
                    'primaryHref' => $hasPriceTable ? '#bang-gia' : locale_route('contact'),
                    'primaryIcon' => 'train',
                    'secondaryLabel' => 'Hỏi lịch trình',
                    'secondaryHref' => locale_route('contact'),
                    'priceHint' => 'Vé điện tử gửi ngay qua SMS/Email',
                    'fallbackPrice' => 'Liên hệ kiểm tra vé',
                ],
                'ferry' => [
                    'aria' => 'Đặt vé tàu cao tốc',
                    'primaryLabel' => $hasPriceTable ? 'Xem giá vé tàu' : 'Đặt vé tàu cao tốc',
                    'primaryShort' => 'Đặt vé',
                    'primaryHref' => $hasPriceTable ? '#bang-gia' : locale_route('contact'),
                    'primaryIcon' => 'ship',
                    'secondaryLabel' => 'Hỏi lịch tàu',
                    'secondaryHref' => locale_route('contact'),
                    'priceHint' => 'Đảm bảo có vé · Không xếp hàng',
                    'fallbackPrice' => 'Liên hệ đặt vé tàu',
                ],
                default => [
                    'aria' => 'Đặt dịch vụ',
                    'primaryLabel' => $hasPriceTable ? 'Xem bảng giá' : 'Đặt dịch vụ ngay',
                    'primaryShort' => 'Đặt ngay',
                    'primaryHref' => $hasPriceTable ? '#bang-gia' : locale_route('contact'),
                    'primaryIcon' => 'calendar',
                    'secondaryLabel' => 'Tư vấn chi tiết',
                    'secondaryHref' => locale_route('contact'),
                    'priceHint' => 'Giá minh bạch · Hỗ trợ trọn gói',
                    'fallbackPrice' => 'Liên hệ đặt dịch vụ',
                ],
            };
        @endphp

        <x-shared.detail-booking-sidebar
            :aria-label="$sidebarConfig['aria']"
            :price="$sidebarPrice"
            price-label="Giá từ"
            :price-hint="$sidebarConfig['priceHint']"
            :fallback-price="$sidebarConfig['fallbackPrice']"
            :meta-items="$sidebarMeta"
            :badge="$service['badge'] ?? null"
            :primary-href="$sidebarConfig['primaryHref']"
            :primary-label="$sidebarConfig['primaryLabel']"
            :primary-label-short="$sidebarConfig['primaryShort']"
            :primary-icon="$sidebarConfig['primaryIcon']"
            :secondary-href="$sidebarConfig['secondaryHref']"
            :secondary-label="$sidebarConfig['secondaryLabel']"
            secondary-icon="mail"
            :whatsapp="$waPhone !== '' ? $waPhone : null"
            :usps="[
                ['icon' => 'check', 'label' => 'Xác nhận nhanh chóng'],
                ['icon' => 'shield', 'label' => 'Giá minh bạch, không phí ẩn'],
            ]"
            trust="Đảm bảo chất lượng & hỗ trợ tận tâm"
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
