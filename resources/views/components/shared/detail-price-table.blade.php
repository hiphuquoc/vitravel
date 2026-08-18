@props([
    'table' => null,
    'title' => 'Bảng giá chi tiết',
])

@php
    $table = is_array($table) ? $table : [];
    $periods = $table['periods'] ?? [];
    $guestTypes = $table['guestTypes'] ?? [];
    $unitLabel = $table['unitLabel'] ?? null;
    $notes = trim((string) ($table['notes'] ?? ''));
    $firstPeriodId = isset($periods[0]['id']) ? (int) $periods[0]['id'] : null;
@endphp

@if ($periods !== [] && $guestTypes !== [])
    <section
        id="bang-gia"
        class="detail-section"
        aria-label="{{ $title }}"
        x-data="{ period: {{ $firstPeriodId }} }"
    >
        <div class="detail-section__head">
            <h2 class="detail-section__title">{{ $title }}</h2>
            @if ($unitLabel)
                <p class="price-table__unit">Đơn vị: {{ $unitLabel }}</p>
            @endif
        </div>

        @if (count($periods) > 1)
            <div class="price-table__periods" role="tablist" aria-label="Giai đoạn giá">
                @foreach ($periods as $period)
                    <button
                        type="button"
                        class="price-table__period"
                        role="tab"
                        :class="period === {{ (int) $period['id'] }} && 'is-active'"
                        :aria-selected="period === {{ (int) $period['id'] }}"
                        @click="period = {{ (int) $period['id'] }}"
                    >
                        {{ $period['label'] }}
                        @if (! empty($period['isPromo']))
                            <span class="price-table__promo">Ưu đãi</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif

        @foreach ($periods as $period)
            <div
                class="price-table__panel"
                x-show="period === {{ (int) $period['id'] }}"
                x-cloak
                role="tabpanel"
            >
                @if (count($periods) === 1)
                    <p class="price-table__range">
                        {{ $period['label'] }}
                        @if (! empty($period['isPromo']))
                            <span class="price-table__promo">Ưu đãi</span>
                        @endif
                    </p>
                @elseif (($period['dateLabel'] ?? '') !== ($period['label'] ?? ''))
                    <p class="price-table__range">{{ $period['dateLabel'] }}</p>
                @endif

                <div class="price-table__scroll">
                    <table class="price-table">
                        <thead>
                            <tr>
                                <th scope="col">Tuỳ chọn</th>
                                @foreach ($guestTypes as $guest)
                                    <th scope="col">{{ $guest['name'] }}</th>
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
                                        @endphp
                                        <td>
                                            @if ($cell)
                                                @if (! empty($cell['compareAtFormatted']))
                                                    <span class="price-table__was">{{ $cell['compareAtFormatted'] }}</span>
                                                @endif
                                                <span class="price-table__amount">{{ $cell['formatted'] }}</span>
                                            @else
                                                <span class="price-table__empty">—</span>
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
            <p class="price-table__notes">{{ $notes }}</p>
        @endif
    </section>
@endif
