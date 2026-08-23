@props([
    'rooms' => [],
])

@if ($rooms !== [])
    <section id="phong" class="detail-section" aria-label="Hạng phòng" x-data="stayRooms(@js($rooms))">
        <h2 class="detail-section__title">Hạng phòng &amp; giá</h2>
        <div class="stay-rooms" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @foreach ($rooms as $i => $room)
                @php
                    $photos = is_array($room['photos'] ?? null) ? array_values(array_filter($room['photos'])) : [];
                    $firstPhoto = $photos[0]['url'] ?? ($room['image'] ?? null);
                    $photoCount = count($photos);
                    $morePhotosCount = max(0, $photoCount - 1);
                    $displayTags = ! empty($room['displayTags'])
                        ? $room['displayTags']
                        : (! empty($room['highlights']) ? $room['highlights'] : []);
                    $allRates = $room['rateOptions'] ?? [];
                    $hasFallbackRate = $allRates === [] && ! empty($room['priceFormatted']);
                    $guests = max(1, min(4, (int) ($room['maxGuests'] ?? 2)));
                @endphp
                <article class="stay-hprt stay-hprt--clickable" @click="handleCardClick($event, {{ $i }})">
                    {{-- Cột trái: Thông tin phòng & Ảnh --}}
                    <div class="stay-hprt__room">
                        @if (filled($firstPhoto))
                            <div class="stay-hprt__thumb-wrapper">
                                <img
                                    src="{{ $firstPhoto }}"
                                    alt="{{ $room['name'] }}"
                                    class="stay-hprt__thumb"
                                    loading="lazy"
                                    decoding="async"
                                    referrerpolicy="no-referrer"
                                />
                                @if ($morePhotosCount > 0)
                                    <span class="stay-hprt__photo-badge">+{{ $morePhotosCount }} ảnh</span>
                                @endif
                            </div>
                        @endif

                        <button
                            type="button"
                            class="stay-hprt__name"
                            @click.stop="show({{ $i }})"
                        >
                            {{ $room['name'] }}
                        </button>

                        @if (! empty($room['scarcityActive']))
                            <p class="stay-hprt__scarcity" x-show="scarcityText({{ $i }})" x-cloak>
                                <x-icon name="alert" class="size-3.5" />
                                <span x-text="scarcityText({{ $i }})"></span>
                            </p>
                        @endif

                        @if ($displayTags !== [])
                            <ul class="stay-hprt__tags" style="margin-top: 0.5rem;">
                                @foreach (array_slice($displayTags, 0, 5) as $tag)
                                    <li class="stay-hprt__tag">
                                        <x-icon name="check" class="size-3.5" />
                                        <span>{{ $tag }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <button
                            type="button"
                            class="stay-hprt__more-link"
                            style="margin-top: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.85rem; font-weight: 600; color: var(--color-ink-soft, #514f45); background: none; border: 0; padding: 0; cursor: pointer;"
                            @click.stop="show({{ $i }})"
                        >
                            <span>Xem chi tiết phòng &amp; tiện ích</span>
                            <x-icon name="chevron-right" class="size-3.5" />
                        </button>
                    </div>

                    {{-- Cột phải: Bảng giá căn chuẩn hoàn hảo 3 cột (Loại giá & Điều kiện | Sức chứa | Giá & Đặt phòng) --}}
                    <div class="stay-hprt__rates" style="display: flex; flex-direction: column; justify-content: center; height: 100%;">
                        @if ($hasFallbackRate)
                            <div style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(4rem, auto) minmax(10rem, 1fr); align-items: center; gap: 1rem; padding: 1.1rem 1.25rem; border-top: 1px solid color-mix(in srgb, var(--color-line, #ddd9c2) 85%, #7aa7d9);">
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; color: var(--color-ink, #272b23);">Giá tiêu chuẩn</strong>
                                    <p style="margin: 0.35rem 0 0; font-size: 0.825rem; color: #16a34a; display: flex; align-items: center; gap: 0.3rem;">
                                        <x-icon name="check" class="size-3.5" />
                                        <span>Đã bao gồm thuế &amp; phí</span>
                                    </p>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <span class="stay-guests" title="Sức chứa {{ $guests }} khách" style="display: flex; gap: 0.2rem;">
                                        @for ($g = 0; $g < min(4, $guests); $g++)
                                            <x-icon name="user" class="size-4 text-ink-soft" />
                                        @endfor
                                    </span>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                                    <div style="text-align: right;">
                                        <span style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 700; color: #d9704f;">{{ $room['priceFormatted'] }}</span>
                                        <span style="font-size: 0.8rem; color: var(--color-muted, #817d6e); display: block;">/ đêm</span>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn-primary"
                                        style="padding: 0.45rem 1.25rem; font-size: 0.88rem;"
                                        @click.stop="bookRoom('{{ addslashes($room['name']) }}')"
                                    >
                                        <span>Đặt phòng</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            @foreach ($allRates as $rIdx => $rate)
                                @php
                                    $rateGuests = max(1, (int) ($rate['guests'] ?? $guests));
                                    $price = $rate['priceFormatted'] ?? (! empty($rate['price']) ? number_format((float) $rate['price'], 0, ',', '.').' ₫' : null);
                                @endphp
                                <div style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(4rem, auto) minmax(10rem, 1fr); align-items: center; gap: 1rem; padding: 1.1rem 1.25rem; @if($rIdx > 0) border-top: 1px solid color-mix(in srgb, var(--color-line, #ddd9c2) 85%, #7aa7d9); @endif">
                                    <div>
                                        <strong style="display: block; font-size: 0.95rem; color: var(--color-ink, #272b23);">{{ $rate['name'] ?? 'Giá tiêu chuẩn' }}</strong>
                                        @if (! empty($rate['mealPlan']))
                                            <p style="margin: 0.35rem 0 0; font-size: 0.825rem; color: #16a34a; display: flex; align-items: center; gap: 0.3rem;">
                                                <x-icon name="utensils" class="size-3.5" />
                                                <span>{{ $rate['mealPlan'] }}</span>
                                            </p>
                                        @endif
                                        @if (! empty($rate['cancellation']))
                                            <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: #16a34a; display: flex; align-items: center; gap: 0.3rem;">
                                                <x-icon name="check" class="size-3.5" />
                                                <span>{{ $rate['cancellation'] }}</span>
                                            </p>
                                        @endif
                                        @if (! empty($rate['payment']))
                                            <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: var(--color-ink-soft, #514f45); display: flex; align-items: center; gap: 0.3rem;">
                                                <x-icon name="info" class="size-3.5" />
                                                <span>{{ $rate['payment'] }}</span>
                                            </p>
                                        @endif
                                    </div>
                                    <div style="display: flex; align-items: center; justify-content: center;">
                                        <span class="stay-guests" title="Tối đa {{ $rateGuests }} khách" style="display: flex; gap: 0.2rem;">
                                            @for ($g = 0; $g < min(4, $rateGuests); $g++)
                                                <x-icon name="user" class="size-4 text-ink-soft" />
                                            @endfor
                                            @if ($rateGuests > 4)
                                                <span style="font-size: 0.75rem; font-weight: 600; margin-left: 2px;">+{{ $rateGuests - 4 }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                                        @if (filled($price))
                                            <div style="text-align: right;">
                                                <span style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 700; color: #d9704f;">{{ $price }}</span>
                                                <span style="font-size: 0.8rem; color: var(--color-muted, #817d6e); display: block;">/ đêm</span>
                                            </div>
                                        @endif
                                        <button
                                            type="button"
                                            class="btn-primary"
                                            style="padding: 0.45rem 1.25rem; font-size: 0.88rem;"
                                            @click.stop="bookRoom('{{ addslashes($room['name']) }} - {{ addslashes($rate['name'] ?? '') }}')"
                                        >
                                            <span>Đặt phòng</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        {{-- MODAL PHÒNG CHUẨN ĐẸP 100% KHÔNG BỊ ĐÈ CLOSE / SCROLLBAR --}}
        <div
            class="stay-room-modal"
            x-cloak
            x-show="open"
            x-transition.opacity.duration.180ms
            @keydown.escape.window="close()"
        >
            <div class="stay-room-modal__backdrop" @click="close()"></div>
            <div
                class="stay-room-modal__panel"
                role="dialog"
                aria-modal="true"
                :aria-label="room ? room.name : 'Chi tiết phòng'"
                @click.stop
                x-show="open"
                x-transition:enter="stay-room-modal__enter"
                x-transition:enter-start="stay-room-modal__enter-start"
                x-transition:enter-end="stay-room-modal__enter-end"
                x-transition:leave="stay-room-modal__leave"
                x-transition:leave-start="stay-room-modal__leave-start"
                x-transition:leave-end="stay-room-modal__leave-end"
            >
                <button
                    type="button"
                    class="stay-room-modal__close"
                    x-ref="roomClose"
                    @click="close()"
                    aria-label="Đóng"
                >
                    <x-icon name="close" class="size-5" />
                </button>

                <div
                    class="stay-room-modal__body"
                    x-show="room"
                    :class="room && room.photos && room.photos.length ? '' : 'stay-room-modal__body--solo'"
                >
                    {{-- Cột Gallery ảnh bên trái: CHỈ TẢI ẢNH KHI MODAL MỞ (open === true) --}}
                    <div class="stay-room-modal__gallery" x-show="room && room.photos && room.photos.length">
                        <div class="stay-room-modal__hero">
                            <template x-if="open && room && room.photos && room.photos[photo]">
                                <img
                                    :src="room.photos[photo].url"
                                    :alt="room.photos[photo].alt || (room ? room.name : '')"
                                    referrerpolicy="no-referrer"
                                    decoding="async"
                                />
                            </template>
                            <template x-if="photoCount > 1">
                                <div>
                                    <button
                                        type="button"
                                        class="stay-room-modal__nav stay-room-modal__nav--prev"
                                        @click="prevPhoto()"
                                        aria-label="Ảnh trước"
                                    >
                                        <x-icon name="chevron-left" class="size-5" />
                                    </button>
                                    <button
                                        type="button"
                                        class="stay-room-modal__nav stay-room-modal__nav--next"
                                        @click="nextPhoto()"
                                        aria-label="Ảnh sau"
                                    >
                                        <x-icon name="chevron-right" class="size-5" />
                                    </button>
                                </div>
                            </template>
                            <span class="stay-room-modal__counter" x-show="photoCount > 1">
                                <span x-text="photo + 1"></span>/<span x-text="photoCount"></span>
                            </span>
                        </div>

                        {{-- Dải ảnh thu nhỏ bên dưới --}}
                        <div class="stay-room-modal__thumbs vt-scrollbar" x-show="photoCount > 1">
                            <template x-if="open">
                                <div style="display: flex; gap: 0.5rem; width: 100%;">
                                    <template x-for="(p, pi) in (room ? (room.photos || []) : [])" :key="pi">
                                        <button
                                            type="button"
                                            class="stay-room-modal__thumb"
                                            :class="{ 'is-active': pi === photo }"
                                            @click="photo = pi"
                                            :aria-label="'Ảnh ' + (pi + 1)"
                                        >
                                            <img
                                                :src="p.url"
                                                :alt="p.alt || (room ? room.name : '')"
                                                loading="lazy"
                                                decoding="async"
                                                referrerpolicy="no-referrer"
                                            />
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Cột Thông tin chi tiết bên phải --}}
                    <div class="stay-room-modal__detail vt-scrollbar" x-ref="roomDetail">
                        <div style="padding-right: 2.75rem;">
                            <h2 class="stay-room-modal__title" x-text="room ? room.name : ''"></h2>
                        </div>

                        <div class="stay-room-modal__facts">
                            <ul class="stay-room-modal__fact-list">
                                <template x-if="room && room.area">
                                    <li class="stay-room-modal__fact">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="maximize" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Diện tích</span>
                                            <strong x-text="room.area"></strong>
                                        </span>
                                    </li>
                                </template>
                                <template x-if="room && room.maxGuests">
                                    <li class="stay-room-modal__fact">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="user" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Khách tối đa</span>
                                            <strong x-text="room.maxGuests + ' người lớn'"></strong>
                                        </span>
                                    </li>
                                </template>
                                <template x-if="room && room.beds && room.beds.length">
                                    <li class="stay-room-modal__fact stay-room-modal__fact--wide">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="bed" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Giường</span>
                                            <strong x-text="room.beds.flatMap(br => (br.items || []).map(i => i.label)).filter(Boolean).join(' · ') || room.bedLabel"></strong>
                                        </span>
                                    </li>
                                </template>
                                <template x-if="room && (!room.beds || !room.beds.length) && room.bedLabel">
                                    <li class="stay-room-modal__fact stay-room-modal__fact--wide">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="bed" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Giường</span>
                                            <strong x-text="room.bedLabel"></strong>
                                        </span>
                                    </li>
                                </template>
                                <template x-if="room && room.view">
                                    <li class="stay-room-modal__fact">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="eye" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">View</span>
                                            <strong x-text="room.view"></strong>
                                        </span>
                                    </li>
                                </template>
                                <template x-if="room && room.bathroomCount">
                                    <li class="stay-room-modal__fact">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="sparkles" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Phòng tắm</span>
                                            <strong x-text="room.bathroomCount"></strong>
                                        </span>
                                    </li>
                                </template>
                                <template x-if="room && room.smoking">
                                    <li
                                        class="stay-room-modal__fact"
                                        :class="{
                                            'stay-room-modal__fact--smoke-free': room.smokingAllowed === false,
                                            'stay-room-modal__fact--smoke-on': room.smokingAllowed === true
                                        }"
                                    >
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <span x-show="room.smokingAllowed === false"><x-icon name="ban" class="size-4" /></span>
                                            <span x-show="room.smokingAllowed === true"><x-icon name="alert" class="size-4" /></span>
                                            <span x-show="room.smokingAllowed !== true && room.smokingAllowed !== false"><x-icon name="info" class="size-4" /></span>
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Hút thuốc</span>
                                            <strong x-text="room.smoking"></strong>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <p class="stay-room-modal__comfort" x-show="room && room.comfortScore">
                            Giường thoải mái
                            <strong x-text="Number(room.comfortScore).toFixed(1)"></strong>
                            <span x-show="room.comfortReviews" x-text="' – dựa trên ' + room.comfortReviews + ' đánh giá'"></span>
                        </p>

                        <p class="stay-room-modal__desc body-text" x-show="room && room.description" x-text="room.description"></p>

                        <p class="stay-room-modal__scarcity" x-show="room && room.scarcityActive" x-cloak>
                            <x-icon name="alert" class="size-3.5" />
                            <span x-text="scarcityText(index)"></span>
                        </p>

                        <template x-for="g in (room ? (room.amenityGroups || []) : [])" :key="g.key">
                            <section class="stay-room-modal__block">
                                <h3 class="stay-panel__title" x-text="g.label"></h3>
                                <ul class="stay-room-modal__amenity-list">
                                    <template x-for="item in g.items" :key="item">
                                        <li>
                                            <x-icon name="check" class="size-3.5" />
                                            <span x-text="item"></span>
                                        </li>
                                    </template>
                                </ul>
                            </section>
                        </template>
                    </div>
                </div>

                <footer class="stay-room-modal__foot" x-show="room && room.priceFormatted">
                    <div class="stay-room-modal__foot-price">
                        <span class="stay-room-modal__foot-from">Giá từ</span>
                        <strong class="stay-room-modal__foot-amount" x-text="room.priceFormatted"></strong>
                        <span class="stay-room-modal__foot-unit">/ đêm</span>
                    </div>
                    <button type="button" class="btn-primary stay-room-modal__foot-cta" @click.stop="bookRoom(room ? room.name : '')">
                        <span>Đặt phòng</span>
                    </button>
                </footer>
            </div>
        </div>
    
        <!-- Toast notification for booking feature -->
        <div
            class="stay-booking-toast"
            x-cloak
            x-show="toast.show"
            x-transition:enter="stay-toast-enter"
            x-transition:enter-start="stay-toast-enter-start"
            x-transition:enter-end="stay-toast-enter-end"
            x-transition:leave="stay-toast-leave"
            x-transition:leave-start="stay-toast-leave-start"
            x-transition:leave-end="stay-toast-leave-end"
        >
            <div class="stay-booking-toast__content">
                <div class="stay-booking-toast__icon">
                    <x-icon name="info" class="size-5" />
                </div>
                <div class="stay-booking-toast__body">
                    <strong class="stay-booking-toast__title" x-text="toast.title"></strong>
                    <p class="stay-booking-toast__desc" x-text="toast.message"></p>
                </div>
                <button type="button" class="stay-booking-toast__close" @click="toast.show = false" aria-label="Đóng thông báo">
                    <x-icon name="close" class="size-4" />
                </button>
            </div>
        </div>
    </section>
@endif
