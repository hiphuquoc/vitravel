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
    'unitLabel' => 'chỗ nghỉ',
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
    <span class="text-xs font-bold pl-1.5 pr-0.5">Bộ lọc</span>
    <span
        x-show="totalActiveFilterCount > 0"
        x-text="totalActiveFilterCount"
        class="inline-flex items-center justify-center size-5 ml-1 rounded-full bg-primary-600 text-white text-[11px] font-bold shadow-sm"
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

    {{-- Header bộ lọc --}}
    <div class="filter-sidebar__head">
        <div class="flex items-center gap-2">
            <h2 class="filter-sidebar__title">
                <x-icon name="filter" class="size-4.5 text-primary-600" />
                <span>Bộ lọc tìm kiếm</span>
            </h2>
            <span
                x-show="totalActiveFilterCount > 0"
                x-cloak
                x-text="totalActiveFilterCount"
                class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-primary-100 text-primary-800 text-xs font-bold"
            ></span>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                x-show="hasActiveFilters"
                x-cloak
                @click="clearAllFilters()"
                class="text-xs font-semibold text-accent-600 hover:text-accent-700 hover:underline cursor-pointer transition-colors"
            >
                Đặt lại
            </button>
            <button type="button" @click="drawer = false" class="filter-sidebar__close" aria-label="Đóng bộ lọc">
                <x-icon name="close" class="size-4.5" />
            </button>
        </div>
    </div>

    {{-- 1. KHU VỰC / ĐIỂM ĐẾN (KHÁCH SẠN / DỊCH VỤ) --}}
    @if ($showCategoryFilter && count($categories))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true, searchCat: '' }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="flex items-center gap-1.5 font-bold text-ink">
                    <span>{{ $categoryLegend }}</span>
                    <span
                        x-show="activeFilterCount('category') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('category') + ')'"
                        class="text-primary-600 text-xs font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm">
                @if (count($categories) > 6)
                    <div class="relative mb-2">
                        <input
                            type="text"
                            x-model="searchCat"
                            placeholder="Tìm khu vực..."
                            class="w-full text-xs px-2.5 py-1.5 pl-7 rounded-lg border border-line bg-page-soft/50 focus:bg-white focus:outline-none focus:border-primary-500 transition-colors"
                        />
                        <x-icon name="search" class="size-3.5 text-muted absolute left-2 top-1/2 -translate-y-1/2" />
                    </div>
                @endif

                <div class="max-h-60 overflow-y-auto pr-1 space-y-1 vt-scrollbar">
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
                            class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer"
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
                            <span class="vt-check__text flex-1 flex items-center justify-between text-xs">
                                <span>{{ $name }}</span>
                                @if ($count > 0)
                                    <span class="text-muted text-[11px]">({{ $count }})</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </fieldset>
    @endif

    {{-- 2. KHOẢNG GIÁ MỖI ĐÊM (DÀNH CHO KHÁCH SẠN / LƯU TRÚ) --}}
    @if ($showPriceRangeFilter && count($priceRanges))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="flex items-center gap-1.5 font-bold text-ink">
                    <span>Khoảng giá mỗi đêm</span>
                    <span
                        x-show="activeFilterCount('price_range') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('price_range') + ')'"
                        class="text-primary-600 text-xs font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($priceRanges as $key => $pr)
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
                        <input type="checkbox"
                            class="vt-check__input"
                            value="{{ $key }}"
                            :checked="isChecked('price_range', @js((string) $key))"
                            @change="toggleFilter('price_range', @js((string) $key))">
                        <span class="vt-check__box" aria-hidden="true">
                            <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="vt-check__text flex flex-col text-xs">
                            <span class="font-semibold text-ink">{{ $pr['label'] ?? $key }}</span>
                            @if (!empty($pr['sub']))
                                <span class="text-[11px] text-muted">{{ $pr['sub'] }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 3. HẠNG SAO & TIÊU CHUẨN --}}
    @if ($showStarFilter && count($stars))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="flex items-center gap-1.5 font-bold text-ink">
                    <span>Hạng sao & Tiêu chuẩn</span>
                    <span
                        x-show="activeFilterCount('star') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('star') + ')'"
                        class="text-primary-600 text-xs font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($stars as $key => $st)
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
                        <input type="checkbox"
                            class="vt-check__input"
                            value="{{ $key }}"
                            :checked="isChecked('star', @js((string) $key))"
                            @change="toggleFilter('star', @js((string) $key))">
                        <span class="vt-check__box" aria-hidden="true">
                            <svg class="vt-check__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="vt-check__text flex items-center justify-between w-full text-xs font-medium">
                            <span>{{ $st['label'] ?? $key }}</span>
                            @if (!empty($st['badge']))
                                <span class="px-1.5 py-0.5 rounded bg-primary-100 text-primary-800 font-bold text-[10px]">{{ $st['badge'] }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 4. LOẠI HÌNH LƯU TRÚ (RESORT, HOTEL, VILLA...) --}}
    @if ($showPropertyTypeFilter && count($propertyTypes))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="flex items-center gap-1.5 font-bold text-ink">
                    <span>Loại hình lưu trú</span>
                    <span
                        x-show="activeFilterCount('property_type') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('property_type') + ')'"
                        class="text-primary-600 text-xs font-bold"
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
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
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
                        <span class="vt-check__text text-xs font-medium">{{ $pt['name'] ?? $slug }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 5. TIỆN ÍCH NỔI BẬT (HỒ BƠI, BÃI BIỂN, BUFFET SÁNG...) --}}
    @if ($showAmenityFilter && count($amenities))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="flex items-center gap-1.5 font-bold text-ink">
                    <span>Tiện ích nổi bật</span>
                    <span
                        x-show="activeFilterCount('amenity') > 0"
                        x-cloak
                        x-text="'(' + activeFilterCount('amenity') + ')'"
                        class="text-primary-600 text-xs font-bold"
                    ></span>
                </span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>

            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($amenities as $key => $am)
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
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
                        <span class="vt-check__text text-xs font-medium">{{ $am['label'] ?? $key }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    {{-- 6. CÁC BỘ LỌC TOUR / DU THUYỀN (QUỐC GIA, LOẠI HÌNH, THỜI LƯỢNG, PHONG CÁCH) --}}
    @if ($showCountryFilter && count($countries))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="font-bold text-ink">Quốc gia / điểm đến</span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>
            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($countries as $country)
                    @php $slug = $country['slug'] ?? ''; @endphp
                    @if ($slug === '')
                        @continue
                    @endif
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
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
                        <span class="vt-check__text text-xs font-medium">{{ $country['name'] ?? $slug }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($showTypeFilter && count($types))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="font-bold text-ink">{{ $typeLegend }}</span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>
            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($types as $cruiseType)
                    @php $slug = $cruiseType['slug'] ?? ''; @endphp
                    @if ($slug === '')
                        @continue
                    @endif
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
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
                        <span class="vt-check__text text-xs font-medium">
                            {{ $cruiseType['name'] ?? $slug }}
                            @if (isset($cruiseType['count']))
                                <span class="opacity-60 text-[11px]">({{ $cruiseType['count'] }})</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($showDurationFilter && count($durations))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="font-bold text-ink">Thời lượng</span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>
            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($durations as $key => $label)
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
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
                        <span class="vt-check__text text-xs font-medium">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($showStyleFilter && count($styles))
        <fieldset class="filter-sidebar__fieldset" x-data="{ open: true }">
            <legend class="filter-legend flex items-center justify-between w-full cursor-pointer select-none py-1" @click="open = !open">
                <span class="font-bold text-ink">Phong cách du lịch</span>
                <x-icon name="chevron-down" class="size-4 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </legend>
            <div x-show="open" x-collapse class="filter-sidebar__options site-mt-sm space-y-1">
                @foreach ($styles as $key => $label)
                    <label class="vt-check hover:bg-page-soft/80 p-1.5 rounded-lg transition-colors cursor-pointer">
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
                        <span class="vt-check__text text-xs font-medium">{{ $label }}</span>
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
            class="flex-1 py-2.5 px-3 rounded-xl border border-line text-xs font-bold text-ink text-center hover:bg-page-soft transition-colors cursor-pointer"
        >
            Đặt lại
        </button>
        <button
            type="button"
            @click="drawer = false"
            class="flex-2 py-2.5 px-4 rounded-xl bg-primary-600 text-white text-xs font-bold text-center shadow-md hover:bg-primary-700 transition-colors cursor-pointer"
        >
            Áp dụng (<span x-text="count !== null ? count : '...'"></span> {{ $unitLabel }})
        </button>
    </div>
</div>
