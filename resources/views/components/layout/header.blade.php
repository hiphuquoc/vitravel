@php
    $destinations = view_data()->countries();
    $cruiseTypes = view_data()->cruiseTypes();
    $guideCountries = array_values(array_filter($destinations, fn ($c) => $c['slug'] !== 'tour-ket-hop'));
    $searchDestinations = array_map(fn ($c) => [
        'name' => $c['name'],
        'slug' => $c['slug'],
        'tagline' => $c['tagline'] ?? '',
        'count' => $c['tourCount'] ?? 0,
        'url' => locale_route('tours.index', $c['slug']),
    ], $destinations);
    $searchTours = array_map(fn ($t) => [
        'title' => $t['title'],
        'country' => $t['country'] ?? '',
        'duration' => $t['duration'] ?? '',
        'url' => locale_route('tours.show', ['country' => $t['countrySlug'], 'slug' => $t['slug']]),
    ], array_slice(view_data()->featuredTours(6), 0, 6));
    $searchKeywords = array_slice(view_data()->popularKeywords(), 0, 8);
    $companyContact = view_data()->companyContact();
    $hotlineDisplay = $companyContact['phone'] ?? '+84 24 3999 8888';
    $hotlineTel = preg_replace('/[^\d+]/', '', $hotlineDisplay) ?: $hotlineDisplay;
    $hotlineLabel = $companyContact['hotline_label'] ?? 'Hotline';
@endphp

<div
    class="site-header-root"
    x-data="{
        mobileOpen: false,
        openMenu: null,
        searchOpen: false,
        scrolled: false,
        topVisible: true,
        topPinned: true,
        lastScrollY: 0,
        headerHeight: 104,
        topHeight: 36,
        mainHeight: 54,
        _ignoreScroll: false,
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
        measureParts() {
            // Đo cả khối headerTop (không chỉ inner) — tránh lệch 1–2px so với translateY
            const topEl = this.$refs.headerTop;
            const mainRow = this.$refs.headerMain?.querySelector?.('.container-site') || this.$refs.headerMain;
            if (topEl) {
                const th = Math.ceil(topEl.getBoundingClientRect().height);
                if (th > 0 && th < 96) this.topHeight = th;
            }
            if (mainRow) {
                const mh = Math.ceil(mainRow.getBoundingClientRect().height);
                if (mh > 0 && mh < 120) this.mainHeight = mh;
            }
            this.syncSpacer();
        },
        syncSpacer() {
            const top = this.topPinned ? this.topHeight : 0;
            this.headerHeight = Math.min(Math.max(this.mainHeight + top, 56), 140);
            // Offset sticky nội dung (sidebar chi tiết…) = chiều cao header đang chiếm viewport
            try {
                document.documentElement.style.setProperty('--site-header-offset', this.headerHeight + 'px');
            } catch (e) {}
        },
        /**
         * Ghim/thu headerTop + bù spacer.
         * Không ép scrollTo(0) khi ghim — tránh giật / không chạm trần được lúc scroll lên.
         */
        applyPinned(pinned) {
            if (pinned) {
                if (this.topPinned && this.topVisible) return;
            } else if (! this.topPinned && ! this.topVisible) {
                return;
            }

            const y0 = window.scrollY || 0;
            const oldH = this.headerHeight;

            this.topPinned = pinned;
            this.topVisible = pinned ? true : false;
            this.syncSpacer();

            const diff = this.headerHeight - oldH;
            if (diff === 0) {
                this.lastScrollY = window.scrollY || 0;
                return;
            }

            this._ignoreScroll = true;
            requestAnimationFrame(() => {
                if (pinned) {
                    // Phình spacer khi đã gần/đúng trần: giữ y≈0, không nhảy ép về 0 giữa gesture
                    if (y0 <= 2) {
                        this.lastScrollY = window.scrollY || 0;
                        this.scrolled = this.lastScrollY > 8;
                    } else {
                        const nextY = Math.max(0, y0 + diff);
                        window.scrollTo({ top: nextY, left: 0, behavior: 'auto' });
                        this.lastScrollY = window.scrollY || 0;
                        this.scrolled = this.lastScrollY > 8;
                    }
                } else {
                    // Thu spacer: bù layout + đẩy khỏi vùng ghim lại ngay
                    const nextY = Math.max(this.topHeight + 12, y0 + diff);
                    window.scrollTo({ top: nextY, left: 0, behavior: 'auto' });
                    this.lastScrollY = window.scrollY || 0;
                    this.scrolled = this.lastScrollY > 8;
                }
                requestAnimationFrame(() => {
                    this._ignoreScroll = false;
                    this.lastScrollY = window.scrollY || 0;
                });
            });
        },
        onScroll() {
            if (this._ignoreScroll) {
                this.lastScrollY = window.scrollY || 0;
                return;
            }

            const y = window.scrollY || 0;
            const delta = y - this.lastScrollY;
            this.scrolled = y > 8;

            if (this.searchOpen || this.openMenu || this.mobileOpen) {
                this.topVisible = true;
                this.lastScrollY = y;
                return;
            }

            // Chỉ ghim khi chạm trần thật — không pin sớm (gây giật / khó lên đầu trang)
            if (y <= 1) {
                if (! this.topPinned) {
                    this.applyPinned(true);
                } else {
                    this.topVisible = true;
                    this.lastScrollY = y;
                }
                return;
            }

            // Đang ghim: chỉ thu khi đã scroll xuống rõ ràng khỏi vùng top
            if (this.topPinned) {
                if (delta > 4 && y > this.topHeight + 20) {
                    this.applyPinned(false);
                    return;
                }
                this.lastScrollY = y;
                return;
            }

            // Giữa trang: overlay theo hướng (không đổi spacer)
            if (Math.abs(delta) < 6) {
                this.lastScrollY = y;
                return;
            }
            this.topVisible = delta < 0;
            this.lastScrollY = y;
        },
        init() {
            this.lastScrollY = window.scrollY || 0;
            this.$nextTick(() => {
                this.measureParts();
                requestAnimationFrame(() => this.measureParts());
            });
            window.addEventListener('resize', () => this.measureParts(), { passive: true });
        },
        openSearch() {
            this.searchOpen = true;
            this.openMenu = null;
            this.topVisible = true;
            document.body.classList.add('is-searchOpen');
            this.$nextTick(() => {
                this.measureParts();
                this.$refs.searchInput?.focus();
            });
        },
        closeSearch() {
            this.searchOpen = false;
            document.body.classList.remove('is-searchOpen');
            this.$nextTick(() => this.measureParts());
        },
        toggleSearch() {
            this.searchOpen ? this.closeSearch() : this.openSearch();
        },
    }"
    @scroll.window.passive="onScroll()"
    @keydown.window.slash.prevent="if (!['INPUT','TEXTAREA'].includes(document.activeElement?.tagName)) openSearch()"
>
    {{-- Spacer: giữ chỗ khi .site-header fixed bằng JS --}}
    <div class="site-header-spacer" :style="'height:' + headerHeight + 'px'" aria-hidden="true"></div>

<div
    class="site-header site-header--fixed"
    x-ref="headerBar"
    :class="{
        'site-header--scrolled': scrolled,
        'site-header--top-hidden': !topVisible,
        'site-header--top-pinned': topPinned,
    }"
    :style="{ '--header-top-h': topHeight + 'px' }"
>
    {{-- ── headerTop: brand bar — hotline | search + language ── --}}
    <div class="headerTop" x-ref="headerTop" :aria-hidden="!topVisible">
        <div class="container-site headerTop__inner">
            <a href="tel:{{ $hotlineTel }}" class="headerTop__hotline" aria-label="Gọi hotline {{ $hotlineDisplay }}">
                <span class="headerTop__hotline-icon" aria-hidden="true">
                    <x-icon name="phone" class="size-3.5" />
                </span>
                <span class="headerTop__hotline-label">{{ $hotlineLabel }}</span>
                <span class="headerTop__hotline-number">{{ $hotlineDisplay }}</span>
            </a>

            <div class="headerTop__actions">
                <button type="button" @click="toggleSearch()"
                    class="headerTop__search"
                    aria-label="Tìm kiếm" :aria-expanded="searchOpen">
                    <x-icon name="search" class="size-4" />
                    <span class="headerTop__search-label">Tìm kiếm</span>
                </button>

                <x-layout.region-switcher variant="desktop" />
                <x-layout.region-switcher variant="mobile" />
            </div>
        </div>
    </div>

    {{-- ── headerMain: logo + nav + CTA ── --}}
    <header class="headerMain" x-ref="headerMain">
    <div class="container-site headerMain__inner">

        {{-- Logo --}}
        <a href="{{ locale_route('home') }}" class="header-wordmark" aria-label="ViTravel — về trang chủ">
            <span class="header-wordmark__mark" aria-hidden="true">
                <x-icon name="compass" class="header-wordmark__icon" />
            </span>
            <span class="header-wordmark__text">
                <span class="header-wordmark__name">ViTravel</span>
                <span class="header-wordmark__tagline">Hài lòng hơn mong đợi</span>
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
                    class="absolute top-full left-0 z-50 w-[580px] pt-2">
                    <div class="rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                    <a href="{{ locale_route('tours.hub') }}" class="nav-panel-row group mb-1 border-b border-line pb-2">
                        <span class="nav-panel-item-row">
                            <span class="nav-panel-lead-mark" aria-hidden="true"></span>
                            <span class="nav-panel-item">Tất cả tour</span>
                        </span>
                        <span class="nav-panel-meta">Xem toàn bộ hành trình Đông Nam Á</span>
                    </a>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                        @foreach ($destinations as $c)
                            <a href="{{ locale_route('tours.index', $c['slug']) }}" class="nav-panel-row group">
                                <span class="nav-panel-item-row">
                                    <span class="nav-panel-item">Tour {{ $c['name'] }}</span>
                                    <x-shared.count-badge :count="$c['tourCount'] ?? 0" />
                                </span>
                                @if (! empty($c['tagline']))
                                    <span class="nav-panel-meta">{{ $c['tagline'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
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
                    class="absolute top-full left-0 z-50 w-[580px] pt-2">
                    <div class="rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                    <a href="{{ locale_route('cruises.hub') }}" class="nav-panel-row group mb-1 border-b border-line pb-2">
                        <span class="nav-panel-item-row">
                            <span class="nav-panel-lead-mark" aria-hidden="true"></span>
                            <span class="nav-panel-item">Tất cả du thuyền</span>
                        </span>
                        <span class="nav-panel-meta">Xem toàn bộ lịch trình du thuyền</span>
                    </a>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                        @foreach ($cruiseTypes as $t)
                            <a href="{{ locale_route('cruises.index', $t['slug']) }}" class="nav-panel-row group">
                                <span class="nav-panel-item-row">
                                    <span class="nav-panel-item">{{ $t['name'] }}</span>
                                    <x-shared.count-badge :count="$t['count'] ?? 0" />
                                </span>
                            </a>
                        @endforeach
                    </div>
                    </div>
                </div>
            </div>

            <div class="relative" @mouseenter="openMenu = 'guide'" @mouseleave="openMenu = null">
                <button type="button"
                    class="nav-link flex cursor-pointer items-center gap-1 whitespace-nowrap"
                    :aria-expanded="openMenu === 'guide'" @click="openMenu = openMenu === 'guide' ? null : 'guide'">
                    Cẩm nang <x-icon name="chevron-down" class="size-3.5" />
                </button>
                <div x-cloak x-show="openMenu === 'guide'" x-transition.opacity.duration.150ms
                    class="absolute top-full left-0 z-50 w-80 pt-2">
                    <div class="rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                    <div class="nav-panel-group">
                        <p class="nav-panel-group__title">Bài viết</p>
                        <a href="{{ locale_route('guide.index') }}" class="nav-panel-link">
                            Tất cả bài viết
                        </a>
                        @foreach ($guideCountries as $c)
                            <a href="{{ locale_route('guide.country', ['country' => $c['slug']]) }}" class="nav-panel-link">
                                Cẩm nang {{ $c['name'] }}
                            </a>
                        @endforeach
                    </div>
                    <div class="nav-panel-group">
                        <p class="nav-panel-group__title">Thư viện</p>
                        <a href="{{ locale_route('videos') }}" class="nav-panel-link">Video trải nghiệm</a>
                        <a href="{{ locale_route('gallery') }}" class="nav-panel-link">Thư viện khoảnh khắc</a>
                    </div>
                    </div>
                </div>
            </div>

            <a href="{{ locale_route('about') }}" class="nav-link">Về chúng tôi</a>
            <a href="{{ locale_route('contact') }}" class="nav-link">Liên hệ</a>
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <a href="{{ locale_route('customize') }}" class="btn-primary-sm hidden whitespace-nowrap sm:inline-flex">
                <x-icon name="route" class="size-5 shrink-0" /> Tour riêng
            </a>

            <button type="button" @click="mobileOpen = true"
                class="headerMain__menuBtn lg:hidden"
                aria-label="Mở menu">
                <x-icon name="menu" class="headerMain__menuIcon" />
            </button>
        </div>
    </div>
    </header>
</div>

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
            <form action="{{ locale_route('search') }}" method="get" class="site-search-bar" role="search"
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
                            <a :href="'{{ locale_route('search') }}?q=' + encodeURIComponent(kw)"
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
                    <a href="{{ locale_route('tours.hub') }}" class="block rounded-lg py-2.5 pl-6 text-base font-medium text-ink-soft hover:text-primary-600">Tất cả tour</a>
                    @foreach ($destinations as $c)
                        <a href="{{ locale_route('tours.index', $c['slug']) }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Tour {{ $c['name'] }}</a>
                    @endforeach
                </div>

                <button type="button" @click="sub = sub === 'cruise' ? null : 'cruise'"
                    class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">
                    Du thuyền <x-icon name="chevron-down" class="size-4 transition" ::class="sub === 'cruise' && 'rotate-180'" />
                </button>
                <div x-show="sub === 'cruise'" x-collapse>
                    <a href="{{ locale_route('cruises.hub') }}" class="block rounded-lg py-2.5 pl-6 text-base font-medium text-ink-soft hover:text-primary-600">Tất cả du thuyền</a>
                    @foreach ($cruiseTypes as $t)
                        <a href="{{ locale_route('cruises.index', $t['slug']) }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">{{ $t['name'] }}</a>
                    @endforeach
                </div>

                <button type="button" @click="sub = sub === 'guide' ? null : 'guide'"
                    class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">
                    Cẩm nang <x-icon name="chevron-down" class="size-4 transition" ::class="sub === 'guide' && 'rotate-180'" />
                </button>
                <div x-show="sub === 'guide'" x-collapse>
                    <p class="nav-panel-group__title !px-0 !pt-2">Bài viết</p>
                    <a href="{{ locale_route('guide.index') }}" class="block rounded-lg py-2.5 pl-6 text-base font-medium text-ink-soft hover:text-primary-600">Tất cả bài viết</a>
                    @foreach ($guideCountries as $c)
                        <a href="{{ locale_route('guide.country', ['country' => $c['slug']]) }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Cẩm nang {{ $c['name'] }}</a>
                    @endforeach
                    <p class="nav-panel-group__title !mt-3 !px-0">Thư viện</p>
                    <a href="{{ locale_route('videos') }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Video trải nghiệm</a>
                    <a href="{{ locale_route('gallery') }}" class="block rounded-lg py-2.5 pl-6 text-base text-ink-soft hover:text-primary-600">Thư viện khoảnh khắc</a>
                </div>

                <a href="{{ locale_route('about') }}" class="block rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">Về chúng tôi</a>
                <a href="{{ locale_route('contact') }}" class="block rounded-lg px-3 py-3 text-base font-semibold hover:bg-page">Liên hệ</a>
            </nav>

            <div class="space-y-3 border-t border-line p-4">
                <a href="{{ locale_route('customize') }}" class="btn-primary w-full">
                    <x-icon name="route" class="size-5 shrink-0" /> Tour riêng
                </a>
            </div>
        </div>
    </div>
</div>
