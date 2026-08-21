@props([
    'rooms' => [],
])

@if ($rooms !== [])
    <section id="phong" class="detail-section" aria-label="Hạng phòng" x-data="stayRooms(@js($rooms))">
        <h2 class="detail-section__title">Hạng phòng &amp; giá</h2>
        <div class="stay-rooms">
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
                @endphp
                <article class="stay-hprt stay-hprt--clickable" @click="handleCardClick($event, {{ $i }})">
                    <div class="stay-hprt__room">
                        @if (filled($firstPhoto))
                            <div class="stay-hprt__thumb-wrapper">
                                <img
                                    src="{{ $firstPhoto }}"
                                    alt="{{ $room['name'] }}"
                                    class="stay-hprt__thumb"
                                    loading="lazy"
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

                        <div class="stay-hprt__meta">
                            @if (! empty($room['capacity']))
                                <div class="stay-hprt__meta-row">
                                    <x-icon name="users" class="size-3.5" />
                                    <span>Sức chứa: {{ $room['capacity'] }} người lớn</span>
                                </div>
                            @endif
                            @if (! empty($room['bedLabel']))
                                <div class="stay-hprt__meta-row">
                                    <x-icon name="bed" class="size-3.5" />
                                    <span>{{ $room['bedLabel'] }}</span>
                                </div>
                            @endif
                            @if (! empty($room['view']))
                                <div class="stay-hprt__meta-row">
                                    <x-icon name="eye" class="size-3.5" />
                                    <span>{{ $room['view'] }}</span>
                                </div>
                            @endif
                            @if (! empty($room['smoking']))
                                @php
                                    $smokeAllowed = $room['smokingAllowed'] ?? null;
                                    $smokeMod = $smokeAllowed === true
                                        ? 'stay-hprt__smoke--allowed'
                                        : ($smokeAllowed === false ? 'stay-hprt__smoke--free' : 'stay-hprt__smoke--neutral');
                                    $smokeIcon = $smokeAllowed === false ? 'ban' : ($smokeAllowed === true ? 'alert' : 'info');
                                @endphp
                                <div @class(['stay-hprt__meta-row', 'stay-hprt__smoke', $smokeMod])>
                                    <x-icon :name="$smokeIcon" class="size-3.5" />
                                    <span>{{ $room['smoking'] }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($displayTags !== [])
                            <ul class="stay-hprt__tags">
                                @foreach ($displayTags as $tag)
                                    @php
                                        $tagLabel = is_string($tag) ? $tag : (string) ($tag['label'] ?? '');
                                        $tagIcon = $tagLabel !== ''
                                            ? view_data()->stayAmenityIcon($tagLabel)
                                            : 'check';
                                    @endphp
                                    @if ($tagLabel !== '')
                                        <li class="stay-hprt__tag">
                                            <x-icon :name="$tagIcon" class="size-3.5" />
                                            <span>{{ $tagLabel }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="stay-hprt__rates">
                        @forelse ($allRates as $rate)
                            @php
                                $priceNow = $rate['priceFormatted'] ?? null;
                                if (! filled($priceNow)) {
                                    $priceNow = $rate['priceTotalFormatted'] ?? null;
                                }
                                $isNonRefundable = ($rate['cancellationRefundable'] ?? null) === false
                                    || (
                                        filled($rate['cancellationTitle'] ?? null)
                                        && preg_match('/không\s+hoàn|non[\s-]?refund/ui', (string) $rate['cancellationTitle'])
                                    );
                            @endphp
                            <div class="stay-hprt__rate">
                                <div class="stay-hprt__price">
                                    @if (! empty($rate['priceStrikeFormatted']))
                                        <s>{{ $rate['priceStrikeFormatted'] }}</s>
                                    @endif
                                    @if (filled($priceNow))
                                        <div class="stay-hprt__price-now">{{ $priceNow }}</div>
                                        @if (! empty($rate['priceFormatted']))
                                            <span class="stay-hprt__price-unit">/ đêm</span>
                                        @endif
                                    @endif
                                    @if (! empty($rate['taxesIncluded']))
                                        <p class="stay-hprt__tax">Đã bao gồm thuế và phí</p>
                                    @endif
                                    @if (! empty($rate['savePercent']) || ! empty($rate['dealLabel']))
                                        <div class="stay-hprt__deals">
                                            @if (! empty($rate['savePercent']))
                                                <span class="stay-hprt__deal stay-hprt__deal--save">Tiết kiệm {{ (int) $rate['savePercent'] }}%</span>
                                            @endif
                                            @if (! empty($rate['dealLabel']))
                                                <span class="stay-hprt__deal">{{ $rate['dealLabel'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="stay-hprt__offer">
                                    <div class="stay-hprt__conds">
                                        @php
                                            $meals = array_values(array_filter(array_map(
                                                static fn ($m) => trim((string) $m),
                                                $rate['mealsDetail'] ?? [],
                                            )));
                                            $breakfastRaw = trim((string) ($rate['breakfastLabel'] ?? ''));
                                            $breakfastIncluded = ! empty($rate['breakfastIncluded']);
                                            if ($breakfastIncluded) {
                                                $breakfastShow = mb_strlen($breakfastRaw) <= 34 && $breakfastRaw !== ''
                                                    ? $breakfastRaw
                                                    : 'Bao gồm bữa sáng';
                                                $breakfastTipParts = [];
                                                if ($breakfastRaw !== '' && $breakfastRaw !== $breakfastShow) {
                                                    $breakfastTipParts[] = $breakfastRaw;
                                                }
                                                $breakfastTipParts = array_merge($breakfastTipParts, $meals);
                                            } elseif ($breakfastRaw !== '') {
                                                $breakfastShow = preg_match('/^(.+?)\s*[—\-|]\s*.+/u', $breakfastRaw, $bm)
                                                    ? trim($bm[1]).' (trả thêm)'
                                                    : \Illuminate\Support\Str::limit($breakfastRaw, 36, '…');
                                                $breakfastTipParts = array_merge(
                                                    $breakfastRaw !== $breakfastShow ? [$breakfastRaw] : [],
                                                    $meals,
                                                );
                                            } else {
                                                $breakfastShow = 'Chỉ phòng';
                                                $breakfastTipParts = $meals;
                                            }
                                            $breakfastTip = implode(' · ', array_values(array_unique(array_filter($breakfastTipParts))));

                                            $cancelRaw = trim((string) ($rate['cancellationTitle'] ?? ''));
                                            $cancelDesc = trim((string) ($rate['cancellationDescription'] ?? ''));
                                            $cancelShow = $cancelRaw;
                                            $cancelTip = $cancelDesc !== '' && $cancelDesc !== $cancelShow ? $cancelDesc : '';

                                            $prepayRaw = trim((string) ($rate['prepaymentTitle'] ?? ''));
                                            $prepayDesc = trim((string) ($rate['prepaymentDescription'] ?? ''));
                                            $prepayShow = $prepayRaw;
                                            $prepayTip = $prepayDesc !== '' && $prepayDesc !== $prepayShow ? $prepayDesc : '';
                                        @endphp

                                        <div @class(['stay-hprt__cond', 'stay-hprt__cond--good' => $breakfastIncluded])>
                                            <x-icon name="coffee" class="size-3.5" />
                                            <span class="stay-hprt__cond-text">{{ $breakfastShow }}</span>
                                            <x-stay.rate-tip :tip="$breakfastTip" label="Chi tiết bữa sáng" />
                                        </div>

                                        @if ($cancelShow !== '')
                                            <div @class(['stay-hprt__cond', 'stay-hprt__cond--bad' => $isNonRefundable])>
                                                <x-icon :name="$isNonRefundable ? 'x-mark' : 'check'" class="size-3.5" />
                                                <span class="stay-hprt__cond-text">{{ $cancelShow }}</span>
                                                <x-stay.rate-tip :tip="$cancelTip" label="Chi tiết hủy phòng" />
                                            </div>
                                        @endif

                                        @if ($prepayShow !== '')
                                            <div class="stay-hprt__cond">
                                                <x-icon name="check" class="size-3.5" />
                                                <span class="stay-hprt__cond-text">{{ $prepayShow }}</span>
                                                <x-stay.rate-tip :tip="$prepayTip" label="Chi tiết thanh toán" />
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn-primary stay-hprt__cta" @click.stop="bookRoom('{{ addslashes($room['name']) }}')">Đặt phòng</button>
                                </div>
                            </div>
                        @empty
                            @if ($hasFallbackRate)
                                <div class="stay-hprt__rate">
                                    <div class="stay-hprt__price">
                                        <div class="stay-hprt__price-now">{{ $room['priceFormatted'] }}</div>
                                        <span class="stay-hprt__price-unit">/ đêm</span>
                                    </div>
                                    <div class="stay-hprt__offer">
                                        <div class="stay-hprt__conds"></div>
                                        <button type="button" class="btn-primary stay-hprt__cta" @click.stop="bookRoom('{{ addslashes($room['name']) }}')">Đặt phòng</button>
                                    </div>
                                </div>
                            @else
                                <div class="stay-hprt__rate">
                                    <div class="stay-hprt__price">
                                        <div class="stay-hprt__price-now stay-hprt__price-now--quote">Liên hệ báo giá</div>
                                    </div>
                                    <div class="stay-hprt__offer">
                                        <div class="stay-hprt__conds">
                                            <div class="stay-hprt__cond">
                                                <x-icon name="check" class="size-3.5" />
                                                <span>Chưa có bảng giá cố định cho hạng này</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-primary stay-hprt__cta" @click.stop="bookRoom('{{ addslashes($room['name']) }}')">Đặt phòng</button>
                                    </div>
                                </div>
                            @endif
                        @endforelse
                    </div>
                </article>
            @endforeach
        </div>

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
                    <div class="stay-room-modal__gallery" x-show="room && room.photos && room.photos.length">
                        <div class="stay-room-modal__hero">
                            <img
                                x-show="room && room.photos[photo]"
                                :src="room && room.photos[photo] ? room.photos[photo].url : ''"
                                :alt="(room && room.photos[photo] && room.photos[photo].alt) || (room ? room.name : '')"
                                referrerpolicy="no-referrer"
                            />
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
                                    <div class="stay-room-modal__dots" aria-hidden="true">
                                        <template x-for="(_, di) in (room ? room.photos : [])" :key="'dot-' + di">
                                            <button
                                                type="button"
                                                class="stay-room-modal__dot"
                                                :class="di === photo ? 'is-active' : ''"
                                                @click="photo = di"
                                            ></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="stay-room-modal__thumbs vt-scrollbar" x-show="room && room.photos.length > 1">
                            <template x-for="(p, pi) in (room ? room.photos : [])" :key="pi">
                                <button
                                    type="button"
                                    class="stay-room-modal__thumb"
                                    :class="pi === photo ? 'is-active' : ''"
                                    @click="photo = pi"
                                >
                                    <img :src="p.url" :alt="p.alt || room.name" loading="lazy" referrerpolicy="no-referrer" />
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="stay-room-modal__detail vt-scrollbar">
                        <p class="kicker" x-show="room && room.unitTypeLabel" x-text="room.unitTypeLabel"></p>
                        <h2 class="stay-room-modal__title" x-text="room ? room.name : ''"></h2>

                        <ul class="stay-hprt__tags stay-room-modal__tags" x-show="room && ((room.displayTags && room.displayTags.length) || (room.highlights && room.highlights.length))">
                            <template x-for="(tag, ti) in (room ? (room.displayTags || room.highlights || []) : [])" :key="'tag-' + ti">
                                <li class="stay-hprt__tag">
                                    <x-icon name="check" class="size-3.5" />
                                    <span x-text="typeof tag === 'string' ? tag : (tag.label || '')"></span>
                                </li>
                            </template>
                        </ul>

                        <div class="stay-room-modal__facts" x-show="room">
                            <p class="stay-room-modal__facts-kicker">Thông số phòng</p>
                            <ul class="stay-room-modal__facts-grid">
                                <template x-if="room && room.sizeSqm">
                                    <li class="stay-room-modal__fact">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="maximize" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Diện tích</span>
                                            <strong><span x-text="room.sizeSqm"></span> m²</strong>
                                        </span>
                                    </li>
                                </template>
                                <template x-if="room && room.capacity">
                                    <li class="stay-room-modal__fact">
                                        <span class="stay-room-modal__fact-icon" aria-hidden="true">
                                            <x-icon name="users" class="size-4" />
                                        </span>
                                        <span class="stay-room-modal__fact-copy">
                                            <span class="stay-room-modal__fact-label">Sức chứa</span>
                                            <strong><span x-text="room.capacity"></span> người lớn</strong>
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