@props([
    'item',              // tour hoặc cruise
    'type' => 'tour',    // 'tour' | 'cruise'
    'related' => [],
    'breadcrumbs' => [],
])

@php
    $isCruise = $type === 'cruise';
    $hasPriceTable = ! empty($item['priceTable']['periods']);
    $sectionIds = ['tong-quan', 'diem-nhan', 'lich-trinh', 'bao-gom', 'danh-gia', 'faq'];
    if ($isCruise && ! empty($item['cabinTypes'])) {
        array_splice($sectionIds, 2, 0, 'hang-cabin');
    }
    if ($hasPriceTable) {
        $baoGomAt = array_search('bao-gom', $sectionIds, true);
        array_splice($sectionIds, $baoGomAt === false ? count($sectionIds) : $baoGomAt, 0, 'bang-gia');
    }
    $tabs = [
        'tong-quan' => 'Tổng quan',
        'diem-nhan' => 'Điểm nhấn',
        ...($isCruise && ! empty($item['cabinTypes']) ? ['hang-cabin' => 'Hạng cabin'] : []),
        'lich-trinh' => 'Lịch trình',
        ...($hasPriceTable ? ['bang-gia' => 'Bảng giá'] : []),
        'bao-gom' => 'Bao gồm',
        'danh-gia' => 'Đánh giá',
        'faq' => 'FAQ',
    ];
    $reviews = array_slice(view_data()->testimonials(), 0, 3);

    $coverSrc = $item['imageDetail'] ?? $item['image'] ?? null;
    $coverSrcset = $item['imageDetailSrcset'] ?? $item['imageSrcset'] ?? null;

    $itineraryDays = array_values(array_column($item['itinerary'] ?? [], 'day'));
    $contact = view_data()->companyContact();
    $waPhone = preg_replace('/\D/', '', $contact['whatsapp'] ?? '');

    $metaItems = [
        ['icon' => 'tag', 'label' => 'Mã '.($isCruise ? 'du thuyền' : 'tour'), 'value' => $item['tourCode'] ?? null],
        ['icon' => 'calendar', 'label' => 'Thời lượng', 'value' => $item['duration'] ?? null],
        ['icon' => 'map-pin', 'label' => 'Khởi hành', 'value' => $item['start'] ?? null],
        ['icon' => 'flag', 'label' => 'Kết thúc', 'value' => $item['end'] ?? null],
    ];
    if ($isCruise) {
        $metaItems[] = ['icon' => 'cruise', 'label' => 'Cảng đi', 'value' => $item['departurePort'] ?? null];
        $metaItems[] = ['icon' => 'sparkles', 'label' => 'Hạng tàu', 'value' => $item['boatClass'] ?? null];
    }
    $places = $item['places'] ?? [];
    if ($places !== []) {
        $metaItems[] = [
            'icon' => 'map-pin',
            'label' => 'Điểm tham quan',
            'value' => implode(' · ', $places),
            'wide' => true,
        ];
    }
    $metaItems = array_values(array_filter($metaItems, fn ($row) => filled($row['value'] ?? null)));

    $sidebarMeta = array_values(array_filter([
        ['icon' => 'calendar', 'label' => 'Thời lượng', 'value' => $item['duration'] ?? null],
        ['icon' => 'tag', 'label' => 'Mã', 'value' => $item['tourCode'] ?? null],
    ], fn ($row) => filled($row['value'] ?? null)));

    $sidebarPrice = (! empty($item['priceFrom']) && (float) $item['priceFrom'] > 0)
        ? ($item['priceFormatted'] ?? null)
        : null;
@endphp

<x-shared.detail-gallery
    :title="$item['title']"
    :cover-src="$coverSrc"
    :cover-srcset="$coverSrcset"
    :gallery="$item['gallery'] ?? []"
    :gallery-count="$item['galleryCount'] ?? 0"
    :breadcrumbs="$breadcrumbs"
    :rating="$item['rating'] ?? null"
    :review-count="$item['reviewCount'] ?? 0"
/>

<div class="detail-body" x-data="scrollSpy(@js($sectionIds))">
    <nav class="detail-tabs" aria-label="Điều hướng trong trang">
        <div class="container-site detail-tabs__inner">
            @foreach ($tabs as $id => $label)
                <a href="#{{ $id }}"
                    class="detail-tabs__link"
                    :class="active === '{{ $id }}' ? 'is-active' : ''">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    <div class="container-site detail-layout section-band--sm">
        <div class="min-w-0 detail-stack">

            <section id="tong-quan" class="detail-section" aria-label="Tổng quan">
                <h2 class="detail-section__title">Tổng quan</h2>
                @if ($metaItems !== [])
                    <dl class="detail-facts">
                        @foreach ($metaItems as $fact)
                            <div @class(['detail-facts__item', 'detail-facts__item--wide' => ! empty($fact['wide'])])>
                                <dt>
                                    <x-icon :name="$fact['icon']" class="size-4" />
                                    {{ $fact['label'] }}
                                </dt>
                                <dd>{{ $fact['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>

            <section id="diem-nhan" class="detail-section" aria-label="Điểm nhấn hành trình">
                <h2 class="detail-section__title">Điểm nhấn hành trình</h2>
                @if (! empty($item['highlightsIntro']))
                    <p class="detail-section__lead body-text prose-travel">{{ $item['highlightsIntro'] }}</p>
                @endif
                <ul class="detail-checklist">
                    @foreach ($item['highlights'] ?? [] as $h)
                        <li>
                            <span class="detail-checklist__mark" aria-hidden="true">
                                <x-icon name="check" class="size-3.5" />
                            </span>
                            <span>{{ $h }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($isCruise && ! empty($item['cabinTypes']))
                <section id="hang-cabin" class="detail-section" aria-label="Hạng cabin">
                    <h2 class="detail-section__title">Hạng cabin</h2>
                    <div class="detail-option-grid">
                        @foreach ($item['cabinTypes'] as $cabin)
                            <article class="detail-option">
                                <div class="detail-option__media">
                                    <x-ph class="absolute inset-0" :label="'Cabin ' . $cabin['name']" icon-class="size-8" />
                                </div>
                                <div class="detail-option__body">
                                    <h3 class="detail-option__name">{{ $cabin['name'] }}</h3>
                                    <p class="detail-option__meta">
                                        <x-icon name="users" class="size-3.5" />
                                        Tối đa {{ $cabin['capacity'] }} khách
                                        @if (! empty($cabin['note'])) · {{ $cabin['note'] }}@endif
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="lich-trinh" class="detail-section" aria-label="Lịch trình chi tiết"
                x-data="{ opened: @js($itineraryDays), all: true,
                    toggle(d) {
                        if (this.opened.includes(d)) {
                            this.opened = this.opened.filter(x => x !== d);
                        } else {
                            this.opened.push(d);
                        }
                        this.all = this.opened.length === @js(count($itineraryDays));
                    },
                    toggleAll() {
                        this.all = ! this.all;
                        this.opened = this.all ? @js($itineraryDays) : [];
                    } }">
                <div class="detail-section__head">
                    <h2 class="detail-section__title">Lịch trình từng ngày</h2>
                    <button type="button" @click="toggleAll"
                        class="btn-ghost shrink-0" x-text="all ? 'Thu gọn tất cả' : 'Mở rộng tất cả'"></button>
                </div>
                <ol class="detail-timeline">
                    @foreach ($item['itinerary'] ?? [] as $day)
                        <li class="detail-timeline__item" :class="opened.includes({{ $day['day'] }}) && 'is-open'">
                            <h3>
                                <button type="button" @click="toggle({{ $day['day'] }})"
                                    class="detail-timeline__trigger"
                                    :aria-expanded="opened.includes({{ $day['day'] }})">
                                    <span class="detail-timeline__day">
                                        <span class="detail-timeline__day-label">Ngày</span>
                                        <span class="detail-timeline__day-num">{{ $day['day'] }}</span>
                                    </span>
                                    <span class="detail-timeline__summary min-w-0 flex-1">
                                        <span class="detail-timeline__title">{{ $day['title'] }}</span>
                                        @php
                                            $mealParts = array_values(array_filter(array_map(
                                                'trim',
                                                preg_split('/[;,|\/]+/', (string) ($day['meals'] ?? '')) ?: []
                                            )));
                                        @endphp
                                        @if (count($mealParts))
                                            <span class="detail-timeline__meals" aria-label="Bữa ăn gồm">
                                                @foreach ($mealParts as $meal)
                                                    <span class="detail-timeline__chip">{{ $meal }}</span>
                                                @endforeach
                                            </span>
                                        @endif
                                    </span>
                                    <x-icon name="chevron-down" class="detail-timeline__chevron size-4 shrink-0"
                                        ::class="opened.includes({{ $day['day'] }}) ? 'is-rotated' : ''" />
                                </button>
                            </h3>
                            <div x-show="opened.includes({{ $day['day'] }})" x-collapse x-cloak>
                                <div class="detail-timeline__body">
                                    @php $dayHtml = rich_body_html($day['content'] ?? null); @endphp
                                    @if ($dayHtml !== '')
                                        <div class="detail-timeline__content prose-travel prose-travel--itinerary">
                                            {!! $dayHtml !!}
                                        </div>
                                    @endif
                                    @if (! empty($day['overnight']))
                                        <p class="detail-timeline__overnight">
                                            <x-icon name="map-pin" class="size-3.5" />
                                            Nghỉ đêm: {{ $day['overnight'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <x-shared.detail-price-table :table="$item['priceTable'] ?? null" />

            <x-shared.detail-inclusions
                title="Giá đã bao gồm những gì?"
                :inclusions="$item['inclusions'] ?? []"
                :exclusions="$item['exclusions'] ?? []"
                :notes="$item['notes'] ?? []"
            />

            <section id="danh-gia" class="detail-section" aria-label="Đánh giá của khách hàng">
                <h2 class="detail-section__title">Khách hàng nói gì về hành trình này</h2>
                <div class="detail-quote-list">
                    @foreach ($reviews as $r)
                        <article class="detail-quote">
                            <header class="detail-quote__head">
                                <x-ph class="detail-quote__avatar" icon="user" icon-class="size-5" :label="null" />
                                <div class="min-w-0">
                                    <p class="detail-quote__name">
                                        {{ $r['name'] }}
                                        @if (! empty($r['flag']))
                                            <span class="detail-quote__flag">{{ $r['flag'] }}</span>
                                        @endif
                                    </p>
                                    <x-shared.stars :rating="$r['rating'] ?? 5" class="mt-0.5" />
                                </div>
                                @if (! empty($r['trip']))
                                    <span class="detail-quote__trip">{{ $r['trip'] }}</span>
                                @endif
                            </header>
                            <p class="detail-quote__text">{{ $r['quote'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <div id="faq" class="detail-section detail-section--faq">
                <x-shared.faq :faqs="$item['faqs'] ?? []" title="Câu hỏi thường gặp về {{ $isCruise ? 'du thuyền' : 'tour' }} này" />
            </div>
        </div>

        <x-shared.detail-booking-sidebar
            :aria-label="'Đặt '.($isCruise ? 'du thuyền' : 'tour')"
            :price="$sidebarPrice"
            price-label="Giá từ"
            price-hint="Giá tham khảo / khách — báo giá chính xác trong 24h"
            fallback-price="Nhận báo giá trong 24h"
            :meta-items="$sidebarMeta"
            :badge="$item['badge'] ?? null"
            :primary-href="route('customize')"
            primary-label="Yêu cầu báo giá"
            primary-icon="sparkles"
            :whatsapp="$waPhone !== '' ? $waPhone : null"
            :usps="[
                ['icon' => 'expert', 'label' => 'Chuyên gia bản địa thiết kế riêng'],
                ['icon' => 'refund', 'label' => 'Cam kết hoàn tiền minh bạch'],
                ['icon' => 'value', 'label' => 'Giá trị vượt trội, không phí ẩn'],
                ['icon' => 'support', 'label' => 'Hỗ trợ 24/7 suốt hành trình'],
            ]"
            trust="Được đề xuất trên TripAdvisor"
        />
    </div>
</div>

<section class="cv-auto container-site section-band--sm" aria-label="Hành trình tương tự"
    x-data="listingGrid(@js([
        'endpoint' => route('api.listings.related'),
        'params' => [
            'kind' => $isCruise ? 'cruise' : 'tour',
            'type' => $item['typeSlug'] ?? '',
            'country' => $item['countrySlug'] ?? '',
            'exclude' => $item['slug'] ?? '',
            'limit' => 3,
        ],
    ]))">
    <x-shared.section-heading title="Hành trình tương tự bạn có thể thích" />
    <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>
    <div x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
        <x-tour.listing-skeleton :count="3" variant="compact" />
    </div>
</section>

@php
    $tripUrl = $isCruise
        ? locale_route('cruises.show', ['type' => $item['typeSlug'], 'slug' => $item['slug']])
        : locale_route('tours.show', array_filter([
            'country' => $item['countrySlug'] ?? null,
            'slug' => $item['slug'] ?? null,
        ]));
    $styleLabels = array_values(array_intersect_key(view_data()->travelStyles(), array_flip($item['styles'] ?? [])));
    $tripItem = array_merge($item, ['styles' => $styleLabels]);
@endphp
{!! schema_ld(schema()->touristTrip($tripItem, $tripUrl, $isCruise)) !!}
