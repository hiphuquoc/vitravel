@php
    $destinations = view_data()->countries();
    $cruiseTypes = view_data()->cruiseTypes();
    $guideCountries = array_values(array_filter($destinations, fn ($c) => $c['slug'] !== 'tour-ket-hop'));
    $searchDestinations = array_map(fn ($c) => [
        'name' => $c['name'],
        'slug' => $c['slug'],
        'tagline' => $c['tagline'] ?? '',
        'count' => $c['tourCount'] ?? 0,
        'url' => route('tours.index', $c['slug']),
    ], $destinations);
    $searchTours = array_map(fn ($t) => [
        'title' => $t['title'],
        'country' => $t['country'] ?? '',
        'duration' => $t['duration'] ?? '',
        'url' => route('tours.show', ['country' => $t['countrySlug'], 'slug' => $t['slug']]),
    ], array_slice(view_data()->featuredTours(6), 0, 6));
    $searchKeywords = array_slice(view_data()->popularKeywords(), 0, 8);
@endphp

<div
    x-data="{
        mobileOpen: false,
        openMenu: null,
        searchOpen: false,
        scrolled: false,
        q: '',
        destinations: @js($searchDestinations),
        tours: @js($searchTours),
        keywords: @js($searchKeywords),
        get qNorm() { return (this.q || '').trim().toLowerCase(); },
        get filteredDestinations() {
            const n = this.qNorm;
            if (!n) return this.destinations.slice(0, 6);
            return this.destinations.filter(d =>
                (d.name + ' ' + d.tagline + ' ' + d.slug).toLowerCase().includes(n)
            ).slice(0, 6);
        },
        get filteredTours() {
            const n = this.qNorm;
            if (!n) return this.tours.slice(0, 4);
            return this.tours.filter(t =>
                (t.title + ' ' + t.country).toLowerCase().includes(n)
            ).slice(0, 5);
        },
        openSearch() {
            this.searchOpen = true;
            this.openMenu = null;
            document.body.classList.add('is-searchOpen');
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },
        closeSearch() {
            this.searchOpen = false;
            document.body.classList.remove('is-searchOpen');
        },
        toggleSearch() {
            this.searchOpen ? this.closeSearch() : this.openSearch();
        },
    }"
    @scroll.window.passive="scrolled = window.scrollY > 12"
    @keydown.window.slash.prevent="if (!['INPUT','TEXTAREA'].includes(document.activeElement?.tagName)) openSearch()"
>
<header
    class="sticky top-0 z-50 border-b border-line/60 bg-page-soft/95 backdrop-blur transition-shadow"
    :class="scrolled && 'shadow-(--shadow-card)'">
    <div class="container-site flex h-16 items-center gap-3 lg:h-[72px]">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2" aria-label="ViTravel — về trang chủ">
            <span class="flex size-9 items-center justify-center rounded-full bg-leaf-500 text-white">
                <x-icon name="compass" class="size-5" />
            </span>
            <span class="leading-none">
                <span class="block font-display text-xl font-bold tracking-tight">ViTravel</span>
                <span class="mt-0.5 block text-[10px] font-medium tracking-widest text-muted uppercase">Hài lòng hơn mong đợi</span>
            </span>
        </a>

        {{-- Nav desktop --}}
        <nav class="ml-6 hidden flex-1 items-center gap-0.5 lg:flex" aria-label="Điều hướng chính">
            <div class="relative" @mouseenter="openMenu = 'dest'" @mouseleave="openMenu = null">
                <button type="button"
                    class="nav-link flex cursor-pointer items-center gap-1"
                    :aria-expanded="openMenu === 'dest'" @click="openMenu = openMenu === 'dest' ? null : 'dest'">
                    Điểm đến <x-icon name="chevron-down" class="size-3.5" />
                </button>
                <div x-cloak x-show="openMenu === 'dest'" x-transition.opacity.duration.150ms
                    class="absolute top-full left-0 w-[580px] rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                        @foreach ($destinations as $c)
                            <a href="{{ route('tours.index', $c['slug']) }}" class="nav-panel-row group">
                                <span class="nav-panel-item-row">
                                    <span class="nav-panel-item">Tour {{ $c['name'] }}</span>
                                    <span class="nav-panel-count">{{ $c['tourCount'] }}</span>
                                </span>
                                @if (! empty($c['tagline']))
                                    <span class="nav-panel-meta">{{ $c['tagline'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="relative" @mouseenter="openMenu = 'cruise'" @mouseleave="openMenu = null">
                <button type="button"
                    class="nav-link flex cursor-pointer items-center gap-1"
                    :aria-expanded="openMenu === 'cruise'" @click="openMenu = openMenu === 'cruise' ? null : 'cruise'">
                    Du thuyền <x-icon name="chevron-down" class="size-3.5" />
                </button>
                <div x-cloak x-show="openMenu === 'cruise'" x-transition.opacity.duration.150ms
                    class="absolute top-full left-0 w-80 rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                    @foreach ($cruiseTypes as $t)
                        <a href="{{ route('cruises.index', $t['slug']) }}" class="nav-panel-row group">
                            <span class="nav-panel-item-row">
                                <x-icon name="cruise" class="size-4 shrink-0 text-primary-600" />
                                <span class="nav-panel-item">{{ $t['name'] }}</span>
                                <span class="nav-panel-count">{{ $t['count'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="relative" @mouseenter="openMenu = 'guide'" @mouseleave="openMenu = null">
                <button type="button"
                    class="nav-link flex cursor-pointer items-center gap-1 whitespace-nowrap"
                    :aria-expanded="openMenu === 'guide'" @click="openMenu = openMenu === 'guide' ? null : 'guide'">
                    Cẩm nang <x-icon name="chevron-down" class="size-3.5" />
                </button>
                <div x-cloak x-show="openMenu === 'guide'" x-transition.opacity.duration.150ms
                    class="absolute top-full left-0 w-80 rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                    <div class="nav-panel-group">
                        <p class="nav-panel-group__title">Bài viết</p>
                        <a href="{{ route('guide.index') }}" class="nav-panel-link">
                            Tất cả bài viết
                        </a>
                        @foreach ($guideCountries as $c)
                            <a href="{{ route('guide.country', ['country' => $c['slug']]) }}" class="nav-panel-link">
                                Cẩm nang {{ $c['name'] }}
                            </a>
                        @endforeach
                    </div>
                    <div class="nav-panel-group">
                        <p class="nav-panel-group__title">Thư viện</p>
                        <a href="{{ route('videos') }}" class="nav-panel-link">Video trải nghiệm</a>
                        <a href="{{ route('gallery') }}" class="nav-panel-link">Thư viện khoảnh khắc</a>
                    </div>
                </div>
            </div>

            <a href="{{ route('about') }}" class="nav-link">Về chúng tôi</a>
            <a href="{{ route('contact') }}" class="nav-link">Liên hệ</a>
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <button type="button" @click="toggleSearch()"
                class="site-search-trigger"
                aria-label="Tìm kiếm" :aria-expanded="searchOpen">
                <x-icon name="search" class="size-5" />
            </button>

            <x-layout.region-switcher variant="desktop" />
            <x-layout.region-switcher variant="mobile" />

            <a href="{{ route('customize') }}" class="btn-primary-sm hidden whitespace-nowrap sm:inline-flex">
                <x-icon name="route" class="size-5 shrink-0" /> Tour riêng
            </a>

            <button type="button" @click="mobileOpen = true"
                class="flex size-10 cursor-pointer items-center justify-center rounded-full transition hover:bg-white lg:hidden"
                aria-label="Mở menu">
                <x-icon name="menu" class="size-6" />
            </button>
        </div>
    </div>
</header>

    {{-- Overlay ngoài header (tránh backdrop-filter khóa position:fixed vào khung header) --}}
    <div x-cloak x-show="searchOpen" class="site-search" role="dialog" aria-modal="true" aria-label="Tìm kiếm"
        @keydown.escape.window="closeSearch()">
        <div class="site-search__backdrop" @click="closeSearch()" x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="site-search__panel" x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            @click.stop>
            <form action="{{ route('search') }}" method="get" class="site-search-bar" role="search"
                @submit="if (!(q || '').trim()) { $event.preventDefault(); }">
                <x-icon name="search" class="site-search-bar__icon size-5" />
                <input type="search" name="q" x-model="q" x-ref="searchInput"
                    placeholder="Tìm tour, điểm đến, du thuyền, bài viết…"
                    class="site-search-bar__input" autocomplete="off" enterkeyhint="search">
                <button type="button" class="site-search-bar__clear" x-show="q.length" @click="q = ''; $refs.searchInput.focus()"
                    aria-label="Xóa từ khóa">
                    <x-icon name="close" class="size-4" />
                </button>
                <button type="submit" class="btn-primary-sm site-search-bar__submit">
                    <x-icon name="search" class="size-5 shrink-0" />
                    <span>Tìm kiếm</span>
                </button>
                <button type="button" class="site-search-bar__close" @click="closeSearch()" aria-label="Đóng tìm kiếm">
                    <x-icon name="close" class="size-5" />
                </button>
            </form>

            <div class="site-search__scroll">
                <div class="site-search__body">
                    <div class="site-search__col">
                        <p class="site-search__label">Điểm đến</p>
                        <ul class="site-search__list">
                            <template x-for="d in filteredDestinations" :key="d.slug">
                                <li>
                                    <a :href="d.url" class="site-search__item" @click="closeSearch()">
                                        <span class="site-search__item-icon"><x-icon name="map-pin" class="size-4" /></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="site-search__item-title" x-text="d.name"></span>
                                            <span class="site-search__item-meta" x-text="d.tagline"></span>
                                        </span>
                                        <span class="site-search__item-count" x-text="d.count"></span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <template x-if="filteredDestinations.length === 0">
                            <p class="site-search__empty">Không có điểm đến khớp.</p>
                        </template>
                    </div>

                    <div class="site-search__col">
                        <p class="site-search__label" x-text="qNorm ? 'Tour gợi ý' : 'Tour nổi bật'"></p>
                        <ul class="site-search__list">
                            <template x-for="t in filteredTours" :key="t.url">
                                <li>
                                    <a :href="t.url" class="site-search__item" @click="closeSearch()">
                                        <span class="site-search__item-icon"><x-icon name="compass" class="size-4" /></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="site-search__item-title" x-text="t.title"></span>
                                            <span class="site-search__item-meta" x-text="[t.country, t.duration].filter(Boolean).join(' · ')"></span>
                                        </span>
                                        <x-icon name="arrow-right" class="size-4 shrink-0 text-muted" />
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <template x-if="filteredTours.length === 0">
                            <p class="site-search__empty">Không có tour khớp — nhấn Enter để tìm toàn site.</p>
                        </template>
                    </div>
                </div>

                <div class="site-search__footer" x-show="!qNorm && keywords.length">
                    <p class="site-search__label">Từ khóa phổ biến</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="kw in keywords" :key="kw">
                            <a :href="'{{ route('search') }}?q=' + encodeURIComponent(kw)"
                                class="site-search-chip site-search-chip--soft" x-text="kw" @click="closeSearch()"></a>
                        </template>
                    </div>
                </div>

                <p class="site-search__hint">
                    Nhấn <kbd>Enter</kbd> để xem tất cả kết quả · <kbd>Esc</kbd> để đóng · <kbd>/</kbd> để mở nhanh
                </p>
            </div>
        </div>
    </div>

    {{-- Drawer mobile --}}
    <div x-cloak x-show="mobileOpen" class="fixed inset-0 z-50 lg:hidden" @keydown.escape.window="mobileOpen = false">
        <div class="absolute inset-0 bg-ink/40" @click="mobileOpen = false" x-transition.opacity></div>
        <div x-show="mobileOpen" x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition duration-150"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 flex w-[320px] max-w-[90vw] flex-col overflow-y-auto bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-line p-4">
                <span class="item-title text-lg">Menu</span>
                <button type="button" @click="mobileOpen = false" class="flex size-9 cursor-pointer items-center justify-center rounded-full hover:bg-page" aria-label="Đóng menu">
                    <x-icon name="close" class="size-5" />
                </button>
            </div>

            <div class="border-b border-line p-4">
                <button type="button" @click="mobileOpen = false; openSearch()"
                    class="site-search-bar site-search-bar--compact w-full cursor-pointer text-left">
                    <x-icon name="search" class="site-search-bar__icon size-5" />
                    <span class="site-search-bar__input text-muted">Tìm tour, điểm đến…</span>
                </button>
            </div>

            <nav class="flex-1 p-4" aria-label="Menu di động" x-data="{ sub: null }">
                <button type="button" @click="sub = sub === 'dest' ? null : 'dest'"
                    class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">
                    Điểm đến <x-icon name="chevron-down" class="size-4 transition" ::class="sub === 'dest' && 'rotate-180'" />
                </button>
                <div x-show="sub === 'dest'" x-collapse>
                    @foreach ($destinations as $c)
                        <a href="{{ route('tours.index', $c['slug']) }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Tour {{ $c['name'] }}</a>
                    @endforeach
                </div>

                <button type="button" @click="sub = sub === 'cruise' ? null : 'cruise'"
                    class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">
                    Du thuyền <x-icon name="chevron-down" class="size-4 transition" ::class="sub === 'cruise' && 'rotate-180'" />
                </button>
                <div x-show="sub === 'cruise'" x-collapse>
                    @foreach ($cruiseTypes as $t)
                        <a href="{{ route('cruises.index', $t['slug']) }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">{{ $t['name'] }}</a>
                    @endforeach
                </div>

                <button type="button" @click="sub = sub === 'guide' ? null : 'guide'"
                    class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">
                    Cẩm nang <x-icon name="chevron-down" class="size-4 transition" ::class="sub === 'guide' && 'rotate-180'" />
                </button>
                <div x-show="sub === 'guide'" x-collapse>
                    <p class="nav-panel-group__title !px-0 !pt-2">Bài viết</p>
                    <a href="{{ route('guide.index') }}" class="block rounded-lg py-2.5 pl-6 text-base font-medium text-ink-soft hover:text-primary-600">Tất cả bài viết</a>
                    @foreach ($guideCountries as $c)
                        <a href="{{ route('guide.country', ['country' => $c['slug']]) }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Cẩm nang {{ $c['name'] }}</a>
                    @endforeach
                    <p class="nav-panel-group__title !mt-3 !px-0">Thư viện</p>
                    <a href="{{ route('videos') }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Video trải nghiệm</a>
                    <a href="{{ route('gallery') }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Thư viện khoảnh khắc</a>
                </div>

                <a href="{{ route('about') }}" class="block rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">Về chúng tôi</a>
                <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">Liên hệ</a>
            </nav>

            <div class="space-y-3 border-t border-line p-4">
                <a href="{{ route('customize') }}" class="btn-primary w-full">
                    <x-icon name="route" class="size-5 shrink-0" /> Tour riêng
                </a>
            </div>
        </div>
    </div>
</div>
