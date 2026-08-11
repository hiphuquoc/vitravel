@props([
    'item',              // tour hoặc cruise
    'type' => 'tour',    // 'tour' | 'cruise'
    'related' => [],
    'breadcrumbs' => [],
])

@php
    $isCruise = $type === 'cruise';
    $sectionIds = ['tong-quan', 'diem-nhan', 'lich-trinh', 'bao-gom', 'danh-gia', 'faq'];
    if ($isCruise && ! empty($item['cabinTypes'])) {
        array_splice($sectionIds, 2, 0, 'hang-cabin');
    }
    $tabs = [
        'tong-quan' => 'Tổng quan',
        'diem-nhan' => 'Điểm nhấn',
        ...($isCruise && ! empty($item['cabinTypes']) ? ['hang-cabin' => 'Hạng cabin'] : []),
        'lich-trinh' => 'Lịch trình',
        'bao-gom' => 'Bao gồm',
        'danh-gia' => 'Đánh giá',
        'faq' => 'FAQ',
    ];
    $reviews = array_slice(view_data()->testimonials(), 0, 3);

    $coverSrc = $item['imageDetail'] ?? $item['image'] ?? null;
    $coverSrcset = $item['imageDetailSrcset'] ?? $item['imageSrcset'] ?? null;
    $gallery = $item['gallery'] ?? [];

    // Thumbs = gallery only — không nhét lại ảnh cover (tránh tải trùng)
    $normUrl = static function (?string $url): string {
        if (! $url) {
            return '';
        }
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return preg_replace('/-(thumb|card|sm|md|lg|xl)(\.[a-z0-9]+)$/i', '$2', $path) ?: $path;
    };
    $coverNorm = $normUrl($coverSrc);
    $uniqueThumbs = collect($gallery)
        ->filter(function ($t) use ($normUrl, $coverNorm) {
            $src = $t['src'] ?? null;
            if (! filled($src)) {
                return false;
            }
            if ($coverNorm === '') {
                return true;
            }

            return $normUrl($src) !== $coverNorm;
        })
        ->values()
        ->all();

    // Luôn giữ 4 ô bên phải (placeholder xám nếu chưa có ảnh) — không dùng lại ảnh cover
    $thumbSlots = 4;
    $thumbs = array_slice($uniqueThumbs, 0, $thumbSlots);
    while (count($thumbs) < $thumbSlots) {
        $thumbs[] = null;
    }
    $galleryCount = max(
        (int) ($item['galleryCount'] ?? 0),
        count($uniqueThumbs),
    );

    $itineraryDays = array_values(array_column($item['itinerary'] ?? [], 'day'));
    $contact = view_data()->companyContact();
    $waPhone = preg_replace('/\D/', '', $contact['whatsapp'] ?? '');
@endphp

{{-- Gallery: ảnh lớn + 4 ô thumb (ảnh thật hoặc placeholder xám) --}}
<section class="container-site detail-gallery" aria-label="Thư viện ảnh">
    <div class="detail-gallery__grid">
        @if ($coverSrc)
            <x-img
                :src="$coverSrc"
                :srcset="$coverSrcset"
                preset="detail"
                :alt="$item['title']"
                loading="eager"
                fetchpriority="high"
                class="detail-gallery__cover"
            />
        @else
            <x-ph class="detail-gallery__cover" :label="'Ảnh chính: ' . $item['title']" icon-class="size-14" />
        @endif

        <div class="detail-gallery__thumbs" role="list">
            @foreach ($thumbs as $i => $thumb)
                <div class="detail-gallery__thumb" role="listitem">
                    @if (! empty($thumb['src']))
                        <x-img
                            :src="$thumb['src']"
                            :srcset="$thumb['srcset'] ?? null"
                            preset="gallery"
                            :alt="$item['title']"
                            class="detail-gallery__thumb-img"
                        />
                    @else
                        <x-ph class="detail-gallery__thumb-ph" icon-class="size-6" :label="null" />
                    @endif
                    @if ($i === $thumbSlots - 1 && $galleryCount > $thumbSlots)
                        <span class="detail-gallery__more" aria-hidden="true">
                            +{{ $galleryCount - $thumbSlots }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="card detail-title-card">
        <div class="detail-title-card__row">
            <div class="detail-title-card__copy min-w-0">
                <x-layout.breadcrumb :items="$breadcrumbs" class="breadcrumb--page" />
                <h1 class="detail-title-card__h1">{{ $item['title'] }}</h1>
            </div>
            <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount']" class="detail-title-card__rating" />
        </div>
    </div>
</section>

<div x-data="scrollSpy(@js($sectionIds))">
    {{-- Tabs: scroll ngang, không sticky --}}
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
                <div class="card detail-meta-card">
                    <dl class="detail-meta-card__grid">
                        <div class="detail-meta-card__item">
                            <dt><x-icon name="tag" class="size-4 text-primary-600" /> Mã {{ $isCruise ? 'du thuyền' : 'tour' }}</dt>
                            <dd>{{ $item['tourCode'] }}</dd>
                        </div>
                        <div class="detail-meta-card__item">
                            <dt><x-icon name="calendar" class="size-4 text-primary-600" /> Thời lượng</dt>
                            <dd>{{ $item['duration'] }}</dd>
                        </div>
                        <div class="detail-meta-card__item">
                            <dt><x-icon name="map-pin" class="size-4 text-primary-600" /> Khởi hành</dt>
                            <dd>{{ $item['start'] }}</dd>
                        </div>
                        <div class="detail-meta-card__item">
                            <dt><x-icon name="flag" class="size-4 text-primary-600" /> Kết thúc</dt>
                            <dd>{{ $item['end'] }}</dd>
                        </div>
                        @if ($isCruise)
                            <div class="detail-meta-card__item">
                                <dt><x-icon name="cruise" class="size-4 text-primary-600" /> Cảng đi</dt>
                                <dd>{{ $item['departurePort'] }}</dd>
                            </div>
                            <div class="detail-meta-card__item">
                                <dt><x-icon name="sparkles" class="size-4 text-primary-600" /> Hạng tàu</dt>
                                <dd>{{ $item['boatClass'] }}</dd>
                            </div>
                        @endif
                        <div class="detail-meta-card__item detail-meta-card__item--wide">
                            <dt><x-icon name="map-pin" class="size-4 text-primary-600" /> Điểm tham quan</dt>
                            <dd>{{ implode(' – ', $item['places'] ?? []) }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section id="diem-nhan" class="detail-section" aria-label="Điểm nhấn hành trình">
                <h2 class="detail-section__title">Điểm nhấn hành trình</h2>
                @if (! empty($item['highlightsIntro']))
                    <p class="detail-section__lead prose-travel">{{ $item['highlightsIntro'] }}</p>
                @endif
                <ul class="detail-highlights-list">
                    @foreach ($item['highlights'] ?? [] as $h)
                        <li>
                            <span class="detail-highlights-list__icon" aria-hidden="true">
                                <x-icon name="check" class="size-3" />
                            </span>
                            <span>{{ $h }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($isCruise && ! empty($item['cabinTypes']))
                <section id="hang-cabin" class="detail-section" aria-label="Hạng cabin">
                    <h2 class="detail-section__title">Hạng cabin</h2>
                    <div class="detail-cabin-grid">
                        @foreach ($item['cabinTypes'] as $cabin)
                            <article class="card cabin-card">
                                <div class="cabin-card__media">
                                    <x-ph class="absolute inset-0" :label="'Cabin ' . $cabin['name']" icon-class="size-8" />
                                </div>
                                <div class="cabin-card__body">
                                    <h3 class="cabin-card__name">{{ $cabin['name'] }}</h3>
                                    <p class="cabin-card__meta">
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
                <ol class="detail-itinerary-list">
                    @foreach ($item['itinerary'] ?? [] as $day)
                        <li class="card detail-itinerary-item overflow-hidden">
                            <h3>
                                <button type="button" @click="toggle({{ $day['day'] }})"
                                    class="detail-itinerary__trigger"
                                    :aria-expanded="opened.includes({{ $day['day'] }})">
                                    <span class="detail-itinerary__day">
                                        <span class="detail-itinerary__day-label">Ngày</span>
                                        <span class="detail-itinerary__day-num">{{ $day['day'] }}</span>
                                    </span>
                                    <span class="detail-itinerary__summary min-w-0 flex-1">
                                        <span class="detail-itinerary__title">{{ $day['title'] }}</span>
                                        @php
                                            $mealParts = array_values(array_filter(array_map(
                                                'trim',
                                                preg_split('/[;,|\/]+/', (string) ($day['meals'] ?? '')) ?: []
                                            )));
                                        @endphp
                                        @if (count($mealParts))
                                            <span class="detail-itinerary__meta">
                                                <span class="detail-itinerary__meals" aria-label="Bữa ăn gồm">
                                                    <span class="detail-itinerary__meals-label">Bữa ăn</span>
                                                    <span class="detail-itinerary__meal-chips">
                                                        @foreach ($mealParts as $meal)
                                                            <span class="detail-itinerary__meal-chip">{{ $meal }}</span>
                                                        @endforeach
                                                    </span>
                                                </span>
                                            </span>
                                        @endif
                                    </span>
                                    <x-icon name="chevron-down" class="size-4 shrink-0 transition"
                                        ::class="opened.includes({{ $day['day'] }}) && 'rotate-180 text-primary-600'" />
                                </button>
                            </h3>
                            <div x-show="opened.includes({{ $day['day'] }})" x-collapse x-cloak>
                                <div class="detail-itinerary__body">
                                    @php $dayHtml = rich_body_html($day['content'] ?? null); @endphp
                                    @if ($dayHtml !== '')
                                        <div class="detail-itinerary__content prose-travel prose-travel--itinerary">
                                            {!! $dayHtml !!}
                                        </div>
                                    @endif
                                    @if (! empty($day['overnight']))
                                        <p class="detail-itinerary__overnight">
                                            <x-icon name="map-pin" class="size-3.5 text-primary-600" />
                                            Nghỉ đêm: {{ $day['overnight'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section id="bao-gom" class="detail-section" aria-label="Giá bao gồm và không bao gồm">
                <h2 class="detail-section__title">Giá đã bao gồm những gì?</h2>
                <div class="detail-inclusion-grid">
                    <div class="card detail-inclusion-card">
                        <h3 class="detail-inclusion-card__title detail-inclusion-card__title--in">
                            <x-icon name="check" class="size-4" /> Bao gồm
                        </h3>
                        <ul class="detail-inclusion-card__list">
                            @foreach ($item['inclusions'] ?? [] as $inc)
                                <li><x-icon name="check" class="size-3.5 shrink-0 text-leaf-600" /> {{ $inc }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card detail-inclusion-card">
                        <h3 class="detail-inclusion-card__title detail-inclusion-card__title--out">
                            <x-icon name="x-mark" class="size-4" /> Không bao gồm
                        </h3>
                        <ul class="detail-inclusion-card__list">
                            @foreach ($item['exclusions'] ?? [] as $exc)
                                <li><x-icon name="x-mark" class="size-3.5 shrink-0 text-primary-500" /> {{ $exc }}</li>
                            @endforeach
                        </ul>
                        @if (! empty($item['notes']))
                            <h3 class="detail-inclusion-card__notes-title">Lưu ý</h3>
                            <ul class="detail-inclusion-card__notes">
                                @foreach ($item['notes'] as $note)
                                    <li>{{ $note }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

            <section id="danh-gia" class="detail-section" aria-label="Đánh giá của khách hàng">
                <h2 class="detail-section__title">Khách hàng nói gì về hành trình này</h2>
                <div class="detail-review-list">
                    @foreach ($reviews as $r)
                        <article class="card detail-review-card">
                            <header class="detail-review-card__head">
                                <x-ph class="detail-review-card__avatar" icon="user" icon-class="size-5" :label="null" />
                                <div class="min-w-0">
                                    <p class="detail-review-card__name">
                                        {{ $r['name'] }}
                                        @if (! empty($r['flag']))
                                            <span class="text-muted">{{ $r['flag'] }}</span>
                                        @endif
                                    </p>
                                    <x-shared.stars :rating="$r['rating'] ?? 5" class="mt-0.5" />
                                </div>
                                @if (! empty($r['trip']))
                                    <span class="detail-review-card__trip">{{ $r['trip'] }}</span>
                                @endif
                            </header>
                            <p class="body-text detail-review-card__quote">{{ $r['quote'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <div id="faq" class="detail-section">
                <x-shared.faq :faqs="$item['faqs'] ?? []" title="Câu hỏi thường gặp về {{ $isCruise ? 'du thuyền' : 'tour' }} này" />
            </div>
        </div>

        {{-- Sidebar booking — sticky theo --site-header-offset (đồng bộ header) --}}
        <aside class="detail-sidebar" aria-label="Đặt {{ $isCruise ? 'du thuyền' : 'tour' }}">
            <div class="detail-sidebar__card">
                <div class="detail-sidebar__head">
                    <p class="detail-sidebar__kicker">Giá trọn gói theo yêu cầu</p>
                    <p class="detail-sidebar__price">Nhận báo giá trong 24h</p>
                    <p class="detail-sidebar__sub">
                        {{ $item['duration'] }}
                        @if (! empty($item['tourCode']))
                            <span aria-hidden="true">·</span> {{ $item['tourCode'] }}
                        @endif
                    </p>
                </div>

                <div class="detail-sidebar__body">
                    @if (! empty($item['badge']))
                        <p class="detail-sidebar__badge">
                            <x-icon name="sparkles" class="size-3.5" /> {{ $item['badge'] }}
                        </p>
                    @endif

                    <div class="detail-sidebar__actions">
                        <a href="{{ route('customize') }}" class="btn-primary w-full">
                            <x-icon name="sparkles" class="size-4" /> Yêu cầu báo giá
                        </a>
                        @if ($waPhone !== '')
                            <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener"
                                class="btn-whatsapp w-full">
                                <x-icon name="whatsapp" class="size-4.5" /> Chat WhatsApp
                            </a>
                        @endif
                    </div>

                    <ul class="detail-sidebar__usp">
                        <li><x-icon name="expert" class="size-4 shrink-0 text-leaf-600" /> Chuyên gia bản địa thiết kế riêng</li>
                        <li><x-icon name="refund" class="size-4 shrink-0 text-leaf-600" /> Cam kết hoàn tiền minh bạch</li>
                        <li><x-icon name="value" class="size-4 shrink-0 text-leaf-600" /> Giá trị vượt trội, không phí ẩn</li>
                        <li><x-icon name="support" class="size-4 shrink-0 text-leaf-600" /> Hỗ trợ 24/7 suốt hành trình</li>
                    </ul>

                    <p class="detail-sidebar__trust">
                        <x-icon name="shield" class="size-4" /> Được đề xuất trên TripAdvisor
                    </p>
                </div>
            </div>
        </aside>
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
