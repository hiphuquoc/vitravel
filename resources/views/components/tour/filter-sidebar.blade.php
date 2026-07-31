@props([
    'durations' => [],
    'styles' => [],
    'countries' => [],
    'types' => [],
    'categories' => [],
    'showCountryFilter' => false,
    'showTypeFilter' => false,
    'showCategoryFilter' => false,
    'showDurationFilter' => true,
    'showStyleFilter' => true,
    'categoryLegend' => 'Danh mục',
    'typeLegend' => 'Loại du thuyền',
])

{{-- FAB mobile: mở drawer bộ lọc — cố định bottom-left, đối xứng site-fab --}}
<button
    type="button"
    @click="drawer = true"
    x-show="!drawer"
    x-cloak
    class="filter-sidebar__fab"
    aria-label="Mở bộ lọc">
    <x-icon name="filter" class="filter-sidebar__fab-icon" />
</button>

<div x-cloak x-show="drawer" class="filter-sidebar__overlay lg:hidden" @click="drawer = false"></div>

<div
    x-cloak
    :class="drawer ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="filter-sidebar__panel"
    role="search"
    aria-label="Bộ lọc">

    <div class="filter-sidebar__head">
        <h2 class="filter-sidebar__title">
            <x-icon name="filter" class="size-5 text-primary-600" /> Lọc theo
        </h2>
        <button type="button" @click="drawer = false" class="filter-sidebar__close" aria-label="Đóng bộ lọc">
            <x-icon name="close" class="size-4.5" />
        </button>
    </div>

    @if ($showCountryFilter && count($countries))
        <fieldset class="filter-sidebar__fieldset">
            <legend class="filter-legend">Quốc gia / điểm đến</legend>
            <div class="filter-sidebar__options">
                @foreach ($countries as $country)
                    @php $slug = $country['slug'] ?? ''; @endphp
                    @if ($slug === '')
                        @continue
                    @endif
                    <label class="vt-check">
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
                        <span class="vt-check__text">{{ $country['name'] ?? $slug }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($showTypeFilter && count($types))
        <fieldset class="filter-sidebar__fieldset">
            <legend class="filter-legend">{{ $typeLegend }}</legend>
            <div class="filter-sidebar__options">
                @foreach ($types as $cruiseType)
                    @php $slug = $cruiseType['slug'] ?? ''; @endphp
                    @if ($slug === '')
                        @continue
                    @endif
                    <label class="vt-check">
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
                        <span class="vt-check__text">
                            {{ $cruiseType['name'] ?? $slug }}
                            @if (isset($cruiseType['count']))
                                <span class="opacity-60">({{ $cruiseType['count'] }})</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($showCategoryFilter && count($categories))
        <fieldset class="filter-sidebar__fieldset">
            <legend class="filter-legend">{{ $categoryLegend }}</legend>
            <div class="filter-sidebar__options">
                @foreach ($categories as $cat)
                    @php
                        $slug = $cat['slug'] ?? '';
                        $count = (int) ($cat['count'] ?? 0);
                    @endphp
                    @if ($slug === '' || $count <= 0)
                        @continue
                    @endif
                    <label class="vt-check">
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
                        <span class="vt-check__text">
                            {{ $cat['name'] ?? $slug }}
                            <span class="opacity-60">({{ $count }})</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($showDurationFilter && count($durations))
    <fieldset class="filter-sidebar__fieldset">
        <legend class="filter-legend">Thời lượng</legend>
        <div class="filter-sidebar__options">
            @foreach ($durations as $key => $label)
                <label class="vt-check">
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
                    <span class="vt-check__text">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>
    @endif

    @if ($showStyleFilter && count($styles))
    <fieldset class="filter-sidebar__fieldset">
        <legend class="filter-legend">Phong cách du lịch</legend>
        <div class="filter-sidebar__options">
            @foreach ($styles as $key => $label)
                <label class="vt-check">
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
                    <span class="vt-check__text">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>
    @endif
</div>
