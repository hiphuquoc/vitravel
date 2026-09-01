@props([
    'table' => null,
    'title' => 'Bảng giá',
])

@php
    $table = is_array($table) ? $table : [];
    $periods = $table['periods'] ?? [];
    $guestTypes = $table['guestTypes'] ?? [];
    $notes = detail_price_table_notes($table['notes'] ?? null) ?? '';
    $firstPeriodId = isset($periods[0]['id']) ? (int) $periods[0]['id'] : null;
    $periodIds = array_values(array_map(fn ($p) => (int) $p['id'], $periods));
@endphp

@if ($periods !== [] && $guestTypes !== [])
    <section
        id="bang-gia"
        class="detail-section"
        aria-label="{{ $title }}"
        x-data="{
            period: {{ $firstPeriodId }},
            ids: {{ json_encode($periodIds) }},
            move(delta) {
                const i = this.ids.indexOf(this.period);
                const next = this.ids[(i + delta + this.ids.length) % this.ids.length];
                this.period = next;
                this.$nextTick(() => this.$refs['tab' + next]?.focus());
            }
        }"
    >
        <div class="detail-section__head">
            <h2 class="detail-section__title">{{ $title }}</h2>

            @if (count($periods) > 1)
                <div
                    class="price-table__periods"
                    role="tablist"
                    aria-label="Giai đoạn giá"
                    @keydown.arrow-right.prevent="move(1)"
                    @keydown.arrow-left.prevent="move(-1)"
                >
                    @foreach ($periods as $period)
                        @php $pid = (int) $period['id']; @endphp
                        <button
                            type="button"
                            id="price-tab-{{ $pid }}"
                            class="price-table__period"
                            role="tab"
                            x-ref="tab{{ $pid }}"
                            :class="period === {{ $pid }} && 'is-active'"
                            :aria-selected="period === {{ $pid }}"
                            :tabindex="period === {{ $pid }} ? 0 : -1"
                            aria-controls="price-panel-{{ $pid }}"
                            @click="period = {{ $pid }}"
                        >
                            <span>{{ $period['label'] }}</span>
                            @if (! empty($period['isPromo']))
                                <span class="price-table__promo">Ưu đãi</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @foreach ($periods as $period)
            @php $pid = (int) $period['id']; @endphp
            <div
                id="price-panel-{{ $pid }}"
                class="price-table__panel"
                @if (count($periods) > 1)
                    role="tabpanel"
                    aria-labelledby="price-tab-{{ $pid }}"
                @endif
                x-show="period === {{ $pid }}"
                x-cloak
            >
                <div
                    class="price-table__scroll"
                    role="region"
                    tabindex="0"
                    aria-label="Bảng giá — vuốt ngang nếu chưa thấy hết cột"
                >
                    <table class="price-table">
                        <caption class="price-table__caption">{{ $title }} — {{ $period['label'] }}</caption>
                        <thead>
                            <tr>
                                <th scope="col">Tuỳ chọn</th>
                                @foreach ($guestTypes as $guest)
                                    <th scope="col">
                                        <span class="price-table__guest">{{ $guest['name'] }}</span>
                                        @if (! empty($guest['ageLabel']))
                                            <span class="price-table__age">{{ $guest['ageLabel'] }}</span>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($period['rows'] ?? [] as $row)
                                <tr>
                                    <th scope="row">
                                        <span class="price-table__variant">{{ $row['name'] }}</span>
                                        @if (! empty($row['description']))
                                            <span class="price-table__hint">{{ $row['description'] }}</span>
                                        @endif
                                    </th>
                                    @foreach ($guestTypes as $guest)
                                        @php
                                            $cell = $row['cells'][(string) $guest['id']] ?? null;
                                            $guestLabel = trim($guest['name'].(! empty($guest['ageLabel']) ? ' ('.$guest['ageLabel'].')' : ''));
                                        @endphp
                                        <td data-label="{{ $guestLabel }}">
                                            @if ($cell)
                                                <span class="price-table__cell">
                                                    @if (! empty($cell['compareAtFormatted']))
                                                        <span class="price-table__was">{{ $cell['compareAtFormatted'] }}</span>
                                                    @endif
                                                    <span @class([
                                                        'price-table__amount',
                                                        'price-table__amount--promo' => ! empty($cell['compareAtFormatted']),
                                                    ])>{{ $cell['formatted'] }}</span>
                                                </span>
                                            @else
                                                <span class="price-table__empty" aria-label="Không áp dụng">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @if ($notes !== '')
            <p class="price-table__notes body-text">{{ $notes }}</p>
        @endif
    </section>
@endif
