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

    $contact = view_data()->companyContact();
    $waPhone = preg_replace('/\D/', '', $contact['whatsapp'] ?? '');
    $coverSrc = $service['imageDetail'] ?? $service['image'] ?? null;
    $coverSrcset = $service['imageDetailSrcset'] ?? $service['imageSrcset'] ?? null;
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

<div class="container-site detail-layout section-band--sm">
    <div class="min-w-0 detail-stack">
        @if (! empty($service['summary']))
            <section class="detail-section" aria-label="Tóm tắt">
                <h2 class="detail-section__title">Tóm tắt</h2>
                <p class="detail-section__lead prose-travel">{{ $service['summary'] }}</p>
            </section>
        @endif

        @php
            $serviceBodyRaw = trim((string) ($service['content'] ?? ''));
            $serviceBodyHtml = $serviceBodyRaw !== '' ? rich_body_html($serviceBodyRaw) : '';
        @endphp
        @if ($serviceBodyHtml !== '')
            <section class="detail-section" aria-label="Nội dung chi tiết">
                <h2 class="detail-section__title">Nội dung chi tiết</h2>
                <div class="prose-travel prose-travel--itinerary">
                    {!! $serviceBodyHtml !!}
                </div>
            </section>
        @endif

        @if (! empty($service['highlights']))
            <section class="detail-section" aria-label="Điểm nhấn">
                <h2 class="detail-section__title">Điểm nhấn</h2>
                <ul class="detail-highlights-list">
                    @foreach ($service['highlights'] as $h)
                        <li>
                            <span class="detail-highlights-list__icon" aria-hidden="true">
                                <x-icon name="check" class="size-3" />
                            </span>
                            <span>{{ $h }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($displayAttrs !== [])
            <section class="detail-section" aria-label="Thông tin chính">
                <h2 class="detail-section__title">Thông tin chính</h2>
                <div class="card detail-meta-card">
                    <dl class="detail-meta-card__grid">
                        @foreach ($displayAttrs as $attr)
                            <div @class([
                                'detail-meta-card__item',
                                'detail-meta-card__item--wide' => in_array($attr['key'], ['operator', 'venue'], true),
                            ])>
                                <dt>
                                    <x-icon :name="$clusterIcon" class="size-4 text-primary-600" />
                                    {{ $attr['label'] }}
                                </dt>
                                <dd>
                                    @if (! empty($attr['isStar']))
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-shared.stars :rating="$attr['value']" />
                                            <span>{{ $attr['value'] }} sao</span>
                                        </span>
                                    @else
                                        {{ $attr['value'] }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                        @if (! empty($service['location']))
                            <div class="detail-meta-card__item detail-meta-card__item--wide">
                                <dt><x-icon name="map-pin" class="size-4 text-primary-600" /> Vị trí</dt>
                                <dd>{{ $service['location'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>
        @endif

        @if (! empty($service['options']))
            <section class="detail-section" aria-label="Tuỳ chọn">
                <h2 class="detail-section__title">Tuỳ chọn &amp; hạng</h2>
                <div class="detail-cabin-grid">
                    @foreach ($service['options'] as $option)
                        <article class="card cabin-card">
                            <div class="cabin-card__body">
                                <h3 class="cabin-card__name item-title">{{ $option['name'] }}</h3>
                                @if (! empty($option['priceFormatted']))
                                    <p class="tour-card-price mt-2">
                                        <span class="tour-card-price__label">Từ</span>
                                        <span class="tour-card-price__value">{{ $option['priceFormatted'] }}</span>
                                    </p>
                                @endif
                                @if (! empty($option['description']))
                                    <p class="body-text mt-2 text-sm text-muted">{{ $option['description'] }}</p>
                                @endif
                                @if (! empty($option['amenities']))
                                    <ul class="mt-3 space-y-1.5 text-sm text-ink-soft">
                                        @foreach ($option['amenities'] as $amenity)
                                            <li class="flex gap-2">
                                                <x-icon name="check" class="mt-0.5 size-3.5 shrink-0 text-leaf-600" />
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

        @if (! empty($service['inclusions']) || ! empty($service['exclusions']) || ! empty($service['notes']))
            <section class="detail-section" aria-label="Bao gồm và lưu ý">
                <h2 class="detail-section__title">Bao gồm &amp; lưu ý</h2>
                <div class="detail-inclusion-grid">
                    @if (! empty($service['inclusions']))
                        <div class="card detail-inclusion-card">
                            <h3 class="detail-inclusion-card__title detail-inclusion-card__title--in">
                                <x-icon name="check" class="size-4" /> Bao gồm
                            </h3>
                            <ul class="detail-inclusion-card__list">
                                @foreach ($service['inclusions'] as $inc)
                                    <li><x-icon name="check" class="size-3.5 shrink-0 text-leaf-600" /> {{ $inc }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="card detail-inclusion-card">
                        @if (! empty($service['exclusions']))
                            <h3 class="detail-inclusion-card__title detail-inclusion-card__title--out">
                                <x-icon name="x-mark" class="size-4" /> Không bao gồm
                            </h3>
                            <ul class="detail-inclusion-card__list">
                                @foreach ($service['exclusions'] as $exc)
                                    <li><x-icon name="x-mark" class="size-3.5 shrink-0 text-primary-500" /> {{ $exc }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if (! empty($service['notes']))
                            <h3 class="detail-inclusion-card__notes-title">Lưu ý</h3>
                            <ul class="detail-inclusion-card__notes">
                                @foreach ($service['notes'] as $note)
                                    <li>{{ $note }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if (! empty($service['faqs']))
            <div class="detail-section">
                <x-shared.faq :faqs="$service['faqs']" title="Câu hỏi thường gặp" />
            </div>
        @endif
    </div>

    <aside class="detail-sidebar" aria-label="Đặt dịch vụ">
        <div class="detail-sidebar__card">
            <div class="detail-sidebar__head">
                <p class="detail-sidebar__kicker">{{ $hub['navLabel'] ?? ($service['clusterLabel'] ?? 'Dịch vụ') }}</p>
                @if (! empty($service['priceFormatted']))
                    <p class="detail-sidebar__price">{{ $service['priceFormatted'] }}</p>
                    <p class="detail-sidebar__sub text-sm opacity-90">Giá tham khảo — báo giá chính xác trong 24h</p>
                @else
                    <p class="detail-sidebar__price">Nhận báo giá trong 24h</p>
                @endif
                @if (! empty($service['duration']))
                    <p class="detail-sidebar__sub">
                        <x-icon name="calendar" class="size-3.5" /> {{ $service['duration'] }}
                    </p>
                @endif
            </div>

            <div class="detail-sidebar__body">
                @if (! empty($service['badge']))
                    <p class="detail-sidebar__badge">
                        <x-icon name="sparkles" class="size-3.5" /> {{ $service['badge'] }}
                    </p>
                @endif

                <div class="detail-sidebar__actions">
                    <a href="{{ locale_route('customize') }}" class="btn-primary w-full">
                        <x-icon name="route" class="size-4" /> Thiết kế hành trình
                    </a>
                    <a href="{{ locale_route('contact') }}" class="btn-outline w-full">
                        <x-icon name="mail" class="size-4" /> Liên hệ tư vấn
                    </a>
                    @if ($waPhone !== '')
                        <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener"
                            class="btn-whatsapp w-full">
                            <x-icon name="whatsapp" class="size-4.5" /> Chat WhatsApp
                        </a>
                    @endif
                </div>

                <ul class="detail-sidebar__usp">
                    <li><x-icon name="check" class="size-4 shrink-0 text-leaf-600" /> Đặt qua chuyên gia bản địa</li>
                    <li><x-icon name="check" class="size-4 shrink-0 text-leaf-600" /> Giá minh bạch, không phí ẩn</li>
                    <li><x-icon name="check" class="size-4 shrink-0 text-leaf-600" /> Hỗ trợ 24/7 suốt hành trình</li>
                </ul>
            </div>
        </div>
    </aside>
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
