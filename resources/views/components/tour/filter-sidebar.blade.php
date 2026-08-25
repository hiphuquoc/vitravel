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

{{-- FAB mobile: Nút mở drawer bộ lọc cao cấp cố định góc dưới --}}
<button
    type="button"
    @click="drawer = true"
    x-show="!drawer"
    x-cloak
    class="filter-sidebar__fab"
    aria-label="Mở bộ lọc">
    <x-icon name="filter" class="filter-sidebar__fab-icon" />
    <span class="text-sm font-bold pl-1.5 pr-0.5">Bộ lọc</span>
    <span
        x-show="totalActiveFilterCount > 0"
        x-text="totalActiveFilterCount"
        class="inline-flex items-center justify-center size-5 ml-1 rounded-full bg-primary-600 text-white text-xs font-bold shadow-sm"
    ></span>
</button>

{{-- Backdrop Overlay cho Mobile Drawer --}}
<div x-cloak x-show="drawer" class="filter-sidebar__overlay lg:hidden" @click="drawer = false"></div>

{{-- Main Filter Panel --}}
<div
    x-cloak
    :class="drawer ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="filter-sidebar__panel"
    role="search"
    aria-label="Bộ lọc tìm kiếm">

    {{-- Header bộ lọc: Thiết kế thoáng, nút Đặt lại dạng Icon tròn đặt về góc phải --}}
    <div class="filter-sidebar__head flex items-center justify-between gap-2 pb-3.5 mb-3.5 border-b border-line">
        <div class="flex items-center gap-2 min-w-0">
            <h2 class="filter-sidebar__title flex items-center gap-2 font-bold text-ink text-base whitespace-nowrap">
                <x-icon name="filter" class="size-4.5 text-primary-600 shrink-0" />
                <span>Bộ lọc tìm kiếm</span>
            </h2>
            <span
                x-show="totalActiveFilterCount > 0"
                x-cloak
                x-text="totalActiveFilterCount"
                class="inline-flex items-center justify-center min-w-5.5 h-5.5 px-1.5 rounded-full bg-primary-100 text-primary-800 text-xs font-bold shrink-0"
            ></span>
        </div>

        <div class="flex items-center gap-1 shrink-0">
            {{-- Nút Đặt lại dạng icon tròn thanh thoát góc phải --}}
            <button
                type="button"
                x-show="hasActiveFilters"
                x-cloak
                @click="clearAllFilters()"
                class="group relative flex items-center justify-center size-8.5 rounded-xl text-muted hover:text-accent-600 hover:bg-accent-50/80 border border-transparent hover:border-accent-200/60 transition-all cursor-pointer"
                title="Đặt lại toàn bộ bộ lọc"
                aria-label="Đặt lại toàn bộ bộ lọc"
            >
                <svg class="size-4.5 transition-transform duration-300 group-hover:-rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                    <path d="M3 3v5h5" />
                </svg>
            </button>
            <button type="button" @click="drawer = false" class="filter-sidebar__close flex items-center justify-center size-8.5 rounded-xl text-muted hover:text-ink hover:bg-page transition-colors cursor-pointer lg:hidden" aria-label="Đóng bộ lọc">
                <x-icon name="close" class="size-4.5" />
            </button>
        </div>
    </div>

    {{-- 1. KHU VỰC / ĐIỂM ĐẾN (KHÁCH SẠN / DỊCH VỤ) --}}
    @if ($showCategoryFilter && count($categories))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true, searchCat: '' }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>{{ $categoryLegend }}</span>
                    <span
                        x-show="activeFilterCount('category') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('category') + ')'"
                        class="text-primary-600 text-sm font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm">
                @if (count($categories) > 6)
                    <div class="relative mb-2.5">
                        <input
                            type="text"
                            x-model="searchCat"
                            placeholder="Tìm khu vực..."
                            class="w-full text-sm px-3 py-2 pl-8.5 rounded-xl border border-line bg-page-soft/60 focus:bg-white focus:outline-none focus:border-primary-500 transition-colors"
                        />
                        <x-icon name="search" class="size-4 text-muted absolute left-2.5 top-1/2 -translate-y-1/2" />
                    </div>
                @endif

                <div class="max-h-64 overflow-y-auto pr-1 space-y-1 vt-scrollbar">
                    @foreach ($categories as $cat)
                        @php
                            $slug = $cat['slug'] ?? '';
                            $name = $cat['name'] ?? $cat['title'] ?? $slug;
                            $count = (int) ($cat['count'] ?? 0);
                        @endphp
                        @if ($slug === '')
                            @continue
                        @endif
                        <label
                            class="vt-check hover:bg-page-soft/80 p-2 rounded-xl transition-colors cursor-pointer"
                            x-show="!searchCat || @js(mb_strtolower($name)).includes(searchCat.toLowerCase())"
                        >
                            <input type="checkbox"
                                class="vt-check__input"
                                value="{{ $slug }}"
                                :checked="isChecked('category', @js($slug))"
                                @change="toggleFilter('category', @js($slug))">
                            <span class="vt-check__box" aria-hidden="true">
                                <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="vt-check__text flex-1 flex items-center justify-between text-sm text-ink-soft">
                                <span class="font-medium text-ink">{{ $name }}</span>
                                @if ($count > 0)
                                    <span class="text-muted text-xs font-normal">({{ $count }})</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </fieldset>
    @endif

    {{-- 2. KHOẢNG GIÁ (THANH KÉO DUAL RANGE SLIDER CAO CẤP) --}}
    @if ($showPriceRangeFilter)
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="font-bold text-ink text-sm sm:text-[15px]">Khoảng giá</span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-3.5 pt-1">
                {{-- Box hiển thị 2 đầu khoảng giá trực quan --}}
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2.5 rounded-xl bg-page-soft/90 border border-line flex flex-col">
                        <span class="text-[11px] font-bold text-muted uppercase tracking-wider">Từ</span>
                        <span class="text-sm font-bold text-ink mt-0.5" x-text="formatMoneyVND(selectedMinPrice)"></span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-page-soft/90 border border-line flex flex-col text-right">
                        <span class="text-[11px] font-bold text-muted uppercase tracking-wider">Đến</span>
                        <span class="text-sm font-bold text-primary-700 mt-0.5" x-text="selectedMaxPrice >= priceMax ? formatMoneyVND(priceMax) + '+' : formatMoneyVND(selectedMaxPrice)"></span>
                    </div>
                </div>

                {{-- Interactive Dual-Thumb Range Slider --}}
                <div class="relative flex items-center h-8 px-1 select-none my-1">
                    {{-- Base Track --}}
                    <div class="h-2 w-full rounded-full bg-line/80 relative overflow-hidden">
                        {{-- Active Highlight Segment --}}
                        <div
                            class="absolute top-0 bottom-0 bg-primary-600 rounded-full transition-all duration-75"
                            :style="`left: ${((selectedMinPrice - priceMin) / (priceMax - priceMin)) * 100}%; right: ${100 - (((selectedMaxPrice - priceMin) / (priceMax - priceMin)) * 100)}%`"
                        ></div>
                    </div>

                    {{-- Min Price Thumb --}}
                    <input
                        type="range"
                        :min="priceMin"
                        :max="priceMax"
                        :step="priceStep"
                        x-model.number="selectedMinPrice"
                        @input="if (selectedMinPrice > selectedMaxPrice - priceStep) selectedMinPrice = selectedMaxPrice - priceStep; scheduleFetch();"
                        class="vt-range-thumb pointer-events-none absolute inset-0 w-full h-full appearance-none bg-transparent z-20 m-0"
                        aria-label="Giá tối thiểu"
                    />

                    {{-- Max Price Thumb --}}
                    <input
                        type="range"
                        :min="priceMin"
                        :max="priceMax"
                        :step="priceStep"
                        x-model.number="selectedMaxPrice"
                        @input="if (selectedMaxPrice < selectedMinPrice + priceStep) selectedMaxPrice = selectedMinPrice + priceStep; scheduleFetch();"
                        class="vt-range-thumb pointer-events-none absolute inset-0 w-full h-full appearance-none bg-transparent z-20 m-0"
                        aria-label="Giá tối đa"
                    />
                </div>

                {{-- Nút chọn nhanh (Quick Preset Chips) --}}
                <div class="flex flex-wrap gap-1.5 pt-0.5">
                    <button
                        type="button"
                        @click="setPriceRange(0, 1000000)"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer"
                        :class="(selectedMinPrice === 0 && selectedMaxPrice === 1000000) ? 'bg-primary-600 text-white border-primary-600 shadow-2xs font-bold' : 'bg-white text-ink-soft border-line hover:border-primary-300 hover:bg-page-soft'"
                    >
                        &lt; 1 triệu
                    </button>
                    <button
                        type="button"
                        @click="setPriceRange(1000000, 2500000)"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer"
                        :class="(selectedMinPrice === 1000000 && selectedMaxPrice === 2500000) ? 'bg-primary-600 text-white border-primary-600 shadow-2xs font-bold' : 'bg-white text-ink-soft border-line hover:border-primary-300 hover:bg-page-soft'"
                    >
                        1 – 2.5 tr
                    </button>
                    <button
                        type="button"
                        @click="setPriceRange(2500000, 5000000)"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer"
                        :class="(selectedMinPrice === 2500000 && selectedMaxPrice === 5000000) ? 'bg-primary-600 text-white border-primary-600 shadow-2xs font-bold' : 'bg-white text-ink-soft border-line hover:border-primary-300 hover:bg-page-soft'"
                    >
                        2.5 – 5 tr
                    </button>
                    <button
                        type="button"
                        @click="setPriceRange(5000000, priceMax)"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer"
                        :class="(selectedMinPrice === 5000000 && selectedMaxPrice === priceMax) ? 'bg-primary-600 text-white border-primary-600 shadow-2xs font-bold' : 'bg-white text-ink-soft border-line hover:border-primary-300 hover:bg-page-soft'"
                    >
                        &gt; 5 triệu
                    </button>
                </div>
            </div>
        </fieldset>
    @endif

    {{-- 3. HẠNG SAO & TIÊU CHUẨN (THIẾT KẾ GỌN GÀNG 5★, 4★, 3★) --}}
    @if ($showStarFilter && count($stars))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>Hạng sao & Tiêu chuẩn</span>
                    <span
                        x-show="activeFilterCount('star') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('star') + ')'"
                        class="text-primary-600 text-sm font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1.5">
                @foreach ($stars as $key => $st)
                    @php
                        $keyStr = (string) $key;
                        $label = $st['label'] ?? $keyStr;
                        $badge = $st['badge'] ?? '';
                        $starBadge = match($keyStr) {
                            '5_star', '5' => '5★',
                            '4_star', '4' => '4★',
                            '3_star', '3' => '3★',
                            'homestay' => 'Eco',
                            default => $badge ?: '★',
                        };
                        $starTitle = match($keyStr) {
                            '5_star', '5' => '5 sao & Luxury Resort',
                            '4_star', '4' => '4 sao cao cấp',
                            '3_star', '3' => '3 sao tiêu chuẩn',
                            'homestay' => 'Homestay & Bungalow',
                            default => $label,
                        };
                    @endphp
                    <label
                        class="vt-check flex items-center justify-between p-2 rounded-xl border border-transparent hover:border-line hover:bg-page-soft/80 transition-all cursor-pointer select-none"
                        :class="isChecked('star', @js($keyStr)) ? 'bg-primary-50/70 border-primary-300 font-semibold' : ''"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <input
                                type="checkbox"
                                class="vt-check__input"
                                value="{{ $keyStr }}"
                                :checked="isChecked('star', @js($keyStr))"
                                @change="toggleFilter('star', @js($keyStr))">
                            <span class="vt-check__box shrink-0" aria-hidden="true">
                                <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-ink truncate">{{ $starTitle }}</span>
                        </div>

                        <span
                            class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-md text-xs shrink-0"
                            :class="@js($keyStr === 'homestay') ? 'bg-primary-100 text-primary-800' : 'bg-amber-100/80 text-amber-800'"
                        >
                            {{ $starBadge }}
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 4. LOẠI HÌNH LƯU TRÚ (RESORT, HOTEL, VILLA...) --}}
    @if ($showPropertyTypeFilter && count($propertyTypes))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>Loại hình lưu trú</span>
                    <span
                        x-show="activeFilterCount('property_type') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('property_type') + ')'"
                        class="text-primary-600 text-sm font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($propertyTypes as $pt)
                    @php $slug = $pt['slug'] ?? ''; @endphp
                    @if ($slug === '')
                        @continue
                    @endif
                    <label class="vt-check hover:bg-page-soft/80 p-2 rounded-xl transition-colors cursor-pointer">
                        <input type="checkbox"
                            class="vt-check__input"
                            value="{{ $slug }}"
                            :checked="isChecked('property_type', @js($slug))"
                            @change="toggleFilter('property_type', @js($slug))">
                        <span class="vt-check__box" aria-hidden="true">
                            <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="vt-check__text text-sm font-medium text-ink">{{ $pt['name'] ?? $slug }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 5. TIỆN ÍCH NỔI BẬT (HỒ BƠI, BÃI BIỂN, BUFFET SÁNG...) --}}
    @if ($showAmenityFilter && count($amenities))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>Tiện ích nổi bật</span>
                    <span
                        x-show="activeFilterCount('amenity') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('amenity') + ')'"
                        class="text-primary-600 text-sm font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($amenities as $key => $am)
                    <label class="vt-check hover:bg-page-soft/80 p-2 rounded-xl transition-colors cursor-pointer">
                        <input type="checkbox"
                            class="vt-check__input"
                            value="{{ $key }}"
                            :checked="isChecked('amenity', @js((string) $key))"
                            @change="toggleFilter('amenity', @js((string) $key))">
                        <span class="vt-check__box" aria-hidden="true">
                            <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="vt-check__text text-sm font-medium text-ink">{{ $am['label'] ?? $key }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 6. QUỐC GIA / ĐIỂM ĐẾN (TOUR) --}}
    @if ($showCountryFilter && count($countries))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true, searchCountry: '' }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>Quốc gia / điểm đến</span>
                    <span
                        x-show="activeFilterCount('country') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('country') + ')'"
                        class="text-primary-600 text-xs font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm">
                @if (count($countries) > 6)
                    <div class="relative mb-2.5">
                        <input
                            type="text"
                            x-model="searchCountry"
                            placeholder="Tìm quốc gia / điểm đến..."
                            class="w-full text-sm px-3 py-2 pl-8.5 rounded-xl border border-line bg-page-soft/60 focus:bg-white focus:outline-none focus:border-primary-500 transition-colors"
                        />
                        <x-icon name="search" class="size-4 text-muted absolute left-2.5 top-1/2 -translate-y-1/2" />
                    </div>
                @endif

                <div class="max-h-64 overflow-y-auto pr-1 space-y-1 vt-scrollbar">
                    @foreach ($countries as $country)
                        @php
                            $slug = $country['slug'] ?? '';
                            $name = $country['name'] ?? $slug;
                        @endphp
                        @if ($slug === '')
                            @continue
                        @endif
                        <label
                            class="vt-check hover:bg-page-soft/80 p-2 rounded-xl transition-colors cursor-pointer"
                            x-show="!searchCountry || @js(mb_strtolower($name)).includes(searchCountry.toLowerCase())"
                        >
                            <input type="checkbox"
                                class="vt-check__input"
                                value="{{ $slug }}"
                                :checked="isChecked('country', @js($slug))"
                                @change="toggleFilter('country', @js($slug))">
                            <span class="vt-check__box" aria-hidden="true">
                                <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="vt-check__text text-sm font-medium text-ink">{{ $name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </fieldset>
    @endif

    {{-- 7. LOẠI DU THUYỀN --}}
    @if ($showTypeFilter && count($types))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true, searchType: '' }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>{{ $typeLegend }}</span>
                    <span
                        x-show="activeFilterCount('type') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('type') + ')'"
                        class="text-primary-600 text-sm font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm">
                @if (count($types) > 6)
                    <div class="relative mb-2.5">
                        <input
                            type="text"
                            x-model="searchType"
                            placeholder="Tìm {{ strtolower($typeLegend) }}..."
                            class="w-full text-sm px-3 py-2 pl-8.5 rounded-xl border border-line bg-page-soft/60 focus:bg-white focus:outline-none focus:border-primary-500 transition-colors"
                        />
                        <x-icon name="search" class="size-4 text-muted absolute left-2.5 top-1/2 -translate-y-1/2" />
                    </div>
                @endif

                <div class="max-h-64 overflow-y-auto pr-1 space-y-1 vt-scrollbar">
                    @foreach ($types as $cruiseType)
                        @php
                            $slug = $cruiseType['slug'] ?? '';
                            $name = $cruiseType['name'] ?? $slug;
                            $count = isset($cruiseType['count']) ? (int) $cruiseType['count'] : null;
                        @endphp
                        @if ($slug === '')
                            @continue
                        @endif
                        <label
                            class="vt-check hover:bg-page-soft/80 p-2 rounded-xl transition-colors cursor-pointer"
                            x-show="!searchType || @js(mb_strtolower($name)).includes(searchType.toLowerCase())"
                        >
                            <input type="checkbox"
                                class="vt-check__input"
                                value="{{ $slug }}"
                                :checked="isChecked('type', @js($slug))"
                                @change="toggleFilter('type', @js($slug))">
                            <span class="vt-check__box" aria-hidden="true">
                                <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="vt-check__text flex-1 flex items-center justify-between text-sm font-medium text-ink">
                                <span>{{ $name }}</span>
                                @if ($count !== null && $count > 0)
                                    <span class="text-muted text-xs font-normal">({{ $count }})</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </fieldset>
    @endif

    {{-- 8. THỜI LƯỢNG --}}
    @if ($showDurationFilter && count($durations))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>Thời lượng</span>
                    <span
                        x-show="activeFilterCount('duration') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('duration') + ')'"
                        class="text-primary-600 text-sm font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>
            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($durations as $key => $label)
                    <label class="vt-check hover:bg-page-soft/80 p-2 rounded-xl transition-colors cursor-pointer">
                        <input type="checkbox"
                            class="vt-check__input"
                            value="{{ $key }}"
                            :checked="isChecked('duration', @js((string) $key))"
                            @change="toggleFilter('duration', @js((string) $key))">
                        <span class="vt-check__box" aria-hidden="true">
                            <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="vt-check__text text-sm font-medium text-ink">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 9. PHONG CÁCH DU LỊCH --}}
    @if ($showStyleFilter && count($styles))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1.5" @click="open = !open">
                <span class="flex items-center gap-2 font-bold text-ink text-sm sm:text-[15px]">
                    <span>Phong cách du lịch</span>
                    <span
                        x-show="activeFilterCount('style') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('style') + ')'"
                        class="text-primary-600 text-sm font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>
            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($styles as $key => $label)
                    <label class="vt-check hover:bg-page-soft/80 p-2 rounded-xl transition-colors cursor-pointer">
                        <input type="checkbox"
                            class="vt-check__input"
                            value="{{ $key }}"
                            :checked="isChecked('style', @js((string) $key))"
                            @change="toggleFilter('style', @js((string) $key))">
                        <span class="vt-check__box" aria-hidden="true">
                            <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="vt-check__text text-sm font-medium text-ink">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- Mobile drawer sticky bottom apply button --}}
    <div class="lg:hidden sticky bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md pt-3 pb-2 mt-4 border-t border-line flex items-center gap-2">
        <button
            type="button"
            x-show="hasActiveFilters"
            @click="clearAllFilters()"
            class="flex-1 py-2.5 px-3 rounded-xl border border-line text-sm font-bold text-ink text-center hover:bg-page-soft transition-colors cursor-pointer"
        >
            Đặt lại
        </button>
        <button
            type="button"
            @click="drawer = false"
            class="flex-2 py-2.5 px-4 rounded-xl bg-primary-600 text-white text-sm font-bold text-center shadow-md hover:bg-primary-700 transition-colors cursor-pointer"
        >
            Áp dụng (<span x-text="count !== null ? count : '...'"></span> {{ $unitLabel }})
        </button>
    </div>
</div>