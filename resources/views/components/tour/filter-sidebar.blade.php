@props([
    'countries' => [],
    'types' => [],
    'categories' => [],
    'durations' => [],
    'styles' => [],
    'propertyTypes' => [],
    'priceRanges' => [],
    'amenities' => [],
    'stars' => [],
    'showCountryFilter' => false,
    'showTypeFilter' => false,
    'showCategoryFilter' => false,
    'showDurationFilter' => true,
    'showStyleFilter' => true,
    'showPropertyTypeFilter' => false,
    'showPriceRangeFilter' => false,
    'showAmenityFilter' => false,
    'showStarFilter' => false,
    'categoryLegend' => 'Khu vực lưu trú',
    'typeLegend' => 'Loại du thuyền',
    'unitLabel' => 'kết quả',
])

@php
    $priceRangeRows = [];
    foreach ($priceRanges as $pk => $pv) {
        $key = (string) $pk;
        $label = is_array($pv) ? (string) ($pv['label'] ?? $key) : (string) $pv;
        $sub = is_array($pv) ? (string) ($pv['sub'] ?? '') : '';
        [$min, $max] = match ($key) {
            'under_1m' => [0, 1_000_000],
            '1m_2m' => [1_000_000, 2_000_000],
            '2m_4m' => [2_000_000, 4_000_000],
            'above_4m' => [4_000_000, null],
            default => [
                is_array($pv) ? (int) ($pv['min'] ?? 0) : 0,
                is_array($pv) && array_key_exists('max', $pv) ? $pv['max'] : null,
            ],
        };
        $priceRangeRows[] = compact('key', 'label', 'sub', 'min', 'max');
    }

    $listLimit = 7;
    $chipLimit = 10;
@endphp

{{-- FAB mobile mở drawer --}}
<button
    type="button"
    class="filter-sidebar__fab"
    @click="drawer = true"
    x-show="!drawer"
    x-cloak
    aria-label="Mở bộ lọc"
>
    <x-icon name="filter" class="filter-sidebar__fab-icon" />
    <span>Bộ lọc</span>
    <span
        class="filter-sidebar__fab-badge"
        x-show="totalActiveFilterCount > 0"
        x-text="totalActiveFilterCount"
        x-cloak
    ></span>
</button>

<div
    class="filter-sidebar__overlay"
    x-cloak
    x-show="drawer"
    x-transition.opacity.duration.180ms
    @click="drawer = false"
    aria-hidden="true"
></div>

<aside
    class="filter-sidebar"
    :class="drawer && 'is-open'"
    role="search"
    aria-label="Bộ lọc kết quả"
>
    {{-- Header: tiêu đề + clear bên phải --}}
    <div class="filter-sidebar__head">
        <h2 class="filter-sidebar__title">
            <x-icon name="filter" class="filter-sidebar__title-icon" />
            <span>Bộ lọc</span>
            <span
                class="filter-sidebar__count"
                x-show="totalActiveFilterCount > 0"
                x-text="totalActiveFilterCount"
                x-cloak
            ></span>
        </h2>
        <div class="filter-sidebar__head-actions">
            <button
                type="button"
                class="filter-sidebar__clear"
                x-show="hasActiveFilters"
                x-cloak
                @click="clearAllFilters()"
                title="Xóa toàn bộ bộ lọc"
            >
                <svg class="filter-sidebar__clear-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
                <span>Xóa lọc</span>
            </button>
            <button
                type="button"
                class="filter-sidebar__close"
                @click="drawer = false"
                aria-label="Đóng bộ lọc"
            >
                <x-icon name="close" class="size-4.5" />
            </button>
        </div>
    </div>

    <div class="filter-sidebar__body">
        {{-- Danh mục / khu vực (dịch vụ, lưu trú) --}}
        @if ($showCategoryFilter && count($categories))
            <x-tour.filter-section :title="$categoryLegend" group="category">
                <div class="filter-list" x-data="{ expanded: false, searchCat: '' }">
                    @if (count($categories) > $listLimit)
                        <label class="filter-search">
                            <x-icon name="search" class="filter-search__icon" />
                            <input type="search" class="filter-search__input" x-model="searchCat" placeholder="Tìm trong danh sách…" autocomplete="off">
                        </label>
                    @endif
                    @foreach ($categories as $cat)
                        @php
                            $slug = (string) ($cat['slug'] ?? '');
                            $name = (string) ($cat['name'] ?? $cat['title'] ?? $slug);
                        @endphp
                        @if ($slug === '') @continue @endif
                        <x-tour.filter-option
                            group="category"
                            :value="$slug"
                            :label="$name"
                            :count="$cat['count'] ?? null"
                            search-model="searchCat"
                            :search-haystack="$name"
                            :index="$loop->index"
                            :limit="$listLimit"
                        />
                    @endforeach
                    <x-tour.filter-more :total="count($categories)" :limit="$listLimit" search-model="searchCat" />
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Điểm đến (tour) --}}
        @if ($showCountryFilter && count($countries))
            <x-tour.filter-section title="Điểm đến" group="country">
                <div class="filter-list" x-data="{ expanded: false, searchCountry: '' }">
                    @if (count($countries) > $listLimit)
                        <label class="filter-search">
                            <x-icon name="search" class="filter-search__icon" />
                            <input type="search" class="filter-search__input" x-model="searchCountry" placeholder="Tìm quốc gia…" autocomplete="off">
                        </label>
                    @endif
                    @foreach ($countries as $country)
                        @php
                            $slug = (string) ($country['slug'] ?? '');
                            $name = (string) ($country['name'] ?? $slug);
                        @endphp
                        @if ($slug === '') @continue @endif
                        <x-tour.filter-option
                            group="country"
                            :value="$slug"
                            :label="$name"
                            search-model="searchCountry"
                            :search-haystack="$name"
                            :index="$loop->index"
                            :limit="$listLimit"
                        />
                    @endforeach
                    <x-tour.filter-more :total="count($countries)" :limit="$listLimit" search-model="searchCountry" />
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Loại du thuyền --}}
        @if ($showTypeFilter && count($types))
            <x-tour.filter-section :title="$typeLegend" group="type">
                <div class="filter-list" x-data="{ expanded: false, searchType: '' }">
                    @if (count($types) > $listLimit)
                        <label class="filter-search">
                            <x-icon name="search" class="filter-search__icon" />
                            <input type="search" class="filter-search__input" x-model="searchType" placeholder="Tìm…" autocomplete="off">
                        </label>
                    @endif
                    @foreach ($types as $cruiseType)
                        @php
                            $slug = (string) ($cruiseType['slug'] ?? '');
                            $name = (string) ($cruiseType['name'] ?? $slug);
                        @endphp
                        @if ($slug === '') @continue @endif
                        <x-tour.filter-option
                            group="type"
                            :value="$slug"
                            :label="$name"
                            :count="$cruiseType['count'] ?? null"
                            search-model="searchType"
                            :search-haystack="$name"
                            :index="$loop->index"
                            :limit="$listLimit"
                        />
                    @endforeach
                    <x-tour.filter-more :total="count($types)" :limit="$listLimit" search-model="searchType" />
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Thời lượng — ô 2 cột --}}
        @if ($showDurationFilter && count($durations))
            <x-tour.filter-section title="Thời lượng" group="duration">
                <div class="filter-tiles">
                    @foreach ($durations as $key => $label)
                        <x-tour.filter-chip
                            group="duration"
                            :value="(string) $key"
                            :label="is_array($label) ? ($label['label'] ?? $key) : $label"
                        />
                    @endforeach
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Phong cách du lịch — chip wrap --}}
        @if ($showStyleFilter && count($styles))
            <x-tour.filter-section title="Phong cách" group="style">
                <div class="filter-chips" x-data="{ expanded: false }">
                    @foreach ($styles as $key => $label)
                        <span class="filter-chips__item" x-show="expanded || {{ $loop->index }} < {{ $chipLimit }}">
                            <x-tour.filter-chip
                                group="style"
                                :value="(string) $key"
                                :label="is_array($label) ? ($label['label'] ?? $key) : $label"
                            />
                        </span>
                    @endforeach
                    <x-tour.filter-more :total="count($styles)" :limit="$chipLimit" />
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Khoảng giá — giá căn giữa + slider + preset --}}
        @if ($showPriceRangeFilter)
            <x-tour.filter-section title="Khoảng giá">
                <div class="filter-price">
                    <p class="filter-price__readout" aria-live="polite">
                        <span class="filter-price__value" x-text="formatMoneyVND(selectedMinPrice)"></span>
                        <span class="filter-price__dash" aria-hidden="true">–</span>
                        <span
                            class="filter-price__value"
                            x-text="selectedMaxPrice >= priceMax ? formatMoneyVND(priceMax) + '+' : formatMoneyVND(selectedMaxPrice)"
                        ></span>
                    </p>

                    <div class="filter-price__range">
                        <div class="filter-price__track" aria-hidden="true">
                            <div
                                class="filter-price__fill"
                                :style="`left: ${((selectedMinPrice - priceMin) / (priceMax - priceMin)) * 100}%; right: ${100 - (((selectedMaxPrice - priceMin) / (priceMax - priceMin)) * 100)}%`"
                            ></div>
                        </div>
                        <input
                            type="range"
                            class="vt-range-thumb"
                            :min="priceMin"
                            :max="priceMax"
                            :step="priceStep"
                            x-model.number="selectedMinPrice"
                            @input="if (selectedMinPrice > selectedMaxPrice - priceStep) selectedMinPrice = selectedMaxPrice - priceStep; scheduleFetch();"
                            aria-label="Giá tối thiểu"
                        >
                        <input
                            type="range"
                            class="vt-range-thumb"
                            :min="priceMin"
                            :max="priceMax"
                            :step="priceStep"
                            x-model.number="selectedMaxPrice"
                            @input="if (selectedMaxPrice < selectedMinPrice + priceStep) selectedMaxPrice = selectedMinPrice + priceStep; scheduleFetch();"
                            aria-label="Giá tối đa"
                        >
                    </div>

                    @if (count($priceRangeRows))
                        <div class="filter-presets" role="group" aria-label="Khoảng giá gợi ý">
                            @foreach ($priceRangeRows as $row)
                                @php
                                    $minJs = (int) $row['min'];
                                    $maxJs = $row['max'] === null ? 'priceMax' : (int) $row['max'];
                                    $maxCheck = $row['max'] === null ? 'null' : (int) $row['max'];
                                @endphp
                                <button
                                    type="button"
                                    class="filter-preset"
                                    :class="isPricePresetActive({{ $minJs }}, {{ $maxCheck }}) && 'is-active'"
                                    :aria-pressed="isPricePresetActive({{ $minJs }}, {{ $maxCheck }})"
                                    @click="isPricePresetActive({{ $minJs }}, {{ $maxCheck }}) ? resetPriceRange() : setPriceRange({{ $minJs }}, {{ $maxJs }})"
                                >
                                    <span class="filter-preset__radio" aria-hidden="true"></span>
                                    <span class="filter-preset__copy">
                                        <span class="filter-preset__label">{{ $row['label'] }}</span>
                                        @if ($row['sub'] !== '')
                                            <span class="filter-preset__sub">{{ $row['sub'] }}</span>
                                        @endif
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Hạng sao — badge 5★ / 4★ / Eco, tick trái đồng bộ preset giá --}}
        @if ($showStarFilter && count($stars))
            <x-tour.filter-section title="Hạng sao" group="star">
                <div class="filter-stars">
                    @foreach ($stars as $key => $st)
                        @php
                            $keyStr = (string) $key;
                            $label = is_array($st) ? (string) ($st['label'] ?? $keyStr) : (string) $st;
                            $starCount = match ($keyStr) {
                                '5_star', '5' => 5,
                                '4_star', '4' => 4,
                                '3_star', '3' => 3,
                                default => 0,
                            };
                            $isEco = $starCount === 0;
                        @endphp
                        <button
                            type="button"
                            class="filter-star"
                            :class="isChecked('star', @js($keyStr)) && 'is-active'"
                            :aria-pressed="isChecked('star', @js($keyStr))"
                            @click="toggleFilter('star', @js($keyStr))"
                        >
                            <span class="filter-star__radio" aria-hidden="true"></span>
                            @if ($isEco)
                                <x-stay.star-rating :rating="0" size="sm" :eco="true" />
                            @else
                                <x-stay.star-rating :rating="$starCount" size="sm" />
                            @endif
                            <span class="filter-star__label">{{ $label }}</span>
                        </button>
                    @endforeach
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Loại hình lưu trú --}}
        @if ($showPropertyTypeFilter && count($propertyTypes))
            <x-tour.filter-section title="Loại hình" group="property_type">
                <div class="filter-chips">
                    @foreach ($propertyTypes as $pt)
                        @php $slug = (string) ($pt['slug'] ?? ''); @endphp
                        @if ($slug === '') @continue @endif
                        <x-tour.filter-chip
                            group="property_type"
                            :value="$slug"
                            :label="$pt['name'] ?? $slug"
                        />
                    @endforeach
                </div>
            </x-tour.filter-section>
        @endif

        {{-- Tiện ích nổi bật --}}
        @if ($showAmenityFilter && count($amenities))
            <x-tour.filter-section title="Tiện ích" group="amenity">
                <div class="filter-chips">
                    @foreach ($amenities as $key => $am)
                        <x-tour.filter-chip
                            group="amenity"
                            :value="(string) $key"
                            :label="is_array($am) ? ($am['label'] ?? $key) : $am"
                        />
                    @endforeach
                </div>
            </x-tour.filter-section>
        @endif
    </div>

    {{-- Footer mobile: đặt lại + áp dụng --}}
    <div class="filter-sidebar__foot">
        <button
            type="button"
            class="filter-sidebar__foot-ghost"
            x-show="hasActiveFilters"
            x-cloak
            @click="clearAllFilters()"
        >
            Đặt lại
        </button>
        <button
            type="button"
            class="btn-primary-sm filter-sidebar__foot-apply"
            @click="drawer = false"
        >
            Xem <span x-text="count !== null ? count : '…'"></span> {{ $unitLabel }}
        </button>
    </div>
</aside>
