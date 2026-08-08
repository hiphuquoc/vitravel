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
    $searchTours = array_values(array_filter(array_map(function (array $t) {
        $slug = (string) ($t['slug'] ?? '');
        if ($slug === '') {
            return null;
        }
        $country = (string) ($t['countrySlug'] ?? '');
        $url = filled($country)
            ? locale_route('tours.show', ['country' => $country, 'slug' => $slug])
            : (filled($t['slugFull'] ?? null)
                ? seo_url((string) $t['slugFull'])
                : locale_route('tours.show', ['slug' => $slug]));
        if ($url === '' || $url === url('/')) {
            return null;
        }

        return [
            'title' => $t['title'],
            'country' => $t['country'] ?? '',
            'duration' => $t['duration'] ?? '',
            'url' => $url,
        ];
    }, array_slice(view_data()->featuredTours(6), 0, 6))));
    $searchKeywords = array_slice(view_data()->popularKeywords(), 0, 8);
    $companyContact = view_data()->companyContact();
    $nav = view_data()->siteNav();
    $brandName = $nav['brand'] ?? ($companyContact['name'] ?? 'ViTravel');
    $brandTagline = $nav['tagline'] ?? '';
    $cruiseNav = $nav['cruise'] ?? [];
    $hotlineDisplay = $companyContact['phone'] ?? '+84 24 3999 8888';
    $hotlineTel = preg_replace('/[^\d+]/', '', $hotlineDisplay) ?: $hotlineDisplay;
    $hotlineLabel = $companyContact['hotline_label'] ?? 'Hotline';
    $serviceClusters = view_data()->serviceClusters();
    $serviceCatsByCluster = [];
    foreach ($serviceClusters as $sc) {
        $serviceCatsByCluster[$sc['code']] = view_data()->serviceCategories($sc['code']);
    }
    $trainHub = collect($serviceClusters)->firstWhere('code', 'train');
    $ferryHub = collect($serviceClusters)->firstWhere('code', 'ferry');
    $transportHub = $ferryHub ?? $trainHub;
    $transportCluster = $transportHub['code'] ?? view_data()->featuredTransportCluster();
    $flightHub = collect($serviceClusters)->firstWhere('code', 'flight');
    $trainServiceCount = view_data()->serviceCount($transportCluster);
    $flightServiceCount = view_data()->serviceCount('flight');
    $transportNavLabel = ($transportCluster === 'ferry') ? 'Vé tàu cao tốc / phà' : 'Vé tàu hỏa';
@endphp

<div
    class="site-header-root"
    x-data="{
        mobileOpen: false,
        openMenu: null,
        moreOpen: false,
        searchOpen: false,
        mobileSub: null,
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

            if (this.searchOpen || this.openMenu || this.moreOpen || this.mobileOpen) {
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
            this.$watch('mobileOpen', (open) => {
                document.body.classList.toggle('is-mobileNavOpen', open);
            });
        },
        closeMobileNav() {
            this.mobileOpen = false;
            this.mobileSub = null;
        },
        openMobileNav() {
            this.mobileOpen = true;
            this.mobileSub = null;
            this.openMenu = null;
            this.moreOpen = false;
            this.closeSearch();
        },
        toggleMobileSub(key) {
            this.mobileSub = this.mobileSub === key ? null : key;
            if (! this.mobileSub) {
                return;
            }
            this.$nextTick(() => {
                const el = this.$refs['sec-' + key];
                el?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            });
        },
        openSearch() {
            this.searchOpen = true;
            this.openMenu = null;
            this.moreOpen = false;
            this.mobileOpen = false;
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
        toggleMore() {
            this.moreOpen = ! this.moreOpen;
            if (this.moreOpen) {
                this.openMenu = null;
            }
        },
        closeMore() {
            this.moreOpen = false;
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
        <a href="{{ locale_route('home') }}" class="header-wordmark" aria-label="{{ $brandName }} — về trang chủ">
            <span class="header-wordmark__mark" aria-hidden="true">
                <x-icon name="compass" class="header-wordmark__icon" />
            </span>
            <span class="header-wordmark__text">
                <span class="header-wordmark__name">{{ $brandName }}</span>
                @if ($brandTagline !== '')
                    <span class="header-wordmark__tagline">{{ $brandTagline }}</span>
                @endif
            </span>
        </a>

        {{-- Nav desktop — căn giữa giữa logo và CTA --}}
        <nav class="headerMain__nav" aria-label="Điều hướng chính">
            <div class="relative" @mouseenter="openMenu = 'dest'; moreOpen = false" @mouseleave="openMenu = null">
                <button type="button"
                    class="nav-link flex cursor-pointer items-center gap-1 whitespace-nowrap"
                    :aria-expanded="openMenu === 'dest'" @click="openMenu = openMenu === 'dest' ? null : 'dest'; moreOpen = false">
                    Tour trọn gói <x-icon name="chevron-down" class="header-nav-chevron size-3.5 shrink-0" />
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

            <div class="relative" @mouseenter="openMenu = 'cruise'; moreOpen = false" @mouseleave="openMenu = null">
                <button type="button"
                    class="nav-link flex cursor-pointer items-center gap-1 whitespace-nowrap"
                    :aria-expanded="openMenu === 'cruise'" @click="openMenu = openMenu === 'cruise' ? null : 'cruise'; moreOpen = false">
                    {{ $cruiseNav['label'] ?? 'Du thuyền' }} <x-icon name="chevron-down" class="header-nav-chevron size-3.5 shrink-0" />
                </button>
                <div x-cloak x-show="openMenu === 'cruise'" x-transition.opacity.duration.150ms
                    class="absolute top-full left-0 z-50 w-[580px] pt-2">
                    <div class="rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                    <a href="{{ locale_route('cruises.hub') }}" class="nav-panel-row group mb-1 border-b border-line pb-2">
                        <span class="nav-panel-item-row">
                            <span class="nav-panel-lead-mark" aria-hidden="true"></span>
                            <span class="nav-panel-item">{{ $cruiseNav['all_label'] ?? 'Tất cả du thuyền' }}</span>
                        </span>
                        <span class="nav-panel-meta">{{ $cruiseNav['all_meta'] ?? 'Xem toàn bộ lịch trình du thuyền' }}</span>
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

            @foreach ($serviceClusters as $sc)
                @if (in_array($sc['code'] ?? '', ['train', 'flight'], true))
                    @continue
                @endif
                @php
                    $svcKey = 'svc-' . $sc['code'];
                    $svcCats = $serviceCatsByCluster[$sc['code']] ?? [];
                @endphp
                <div class="relative" @mouseenter="openMenu = '{{ $svcKey }}'; moreOpen = false" @mouseleave="openMenu = null">
                    <button type="button"
                        class="nav-link flex cursor-pointer items-center gap-1 whitespace-nowrap"
                        :aria-expanded="openMenu === '{{ $svcKey }}'"
                        @click="openMenu = openMenu === '{{ $svcKey }}' ? null : '{{ $svcKey }}'; moreOpen = false">
                        {{ $sc['nav_label'] }} <x-icon name="chevron-down" class="header-nav-chevron size-3.5 shrink-0" />
                    </button>
                    <div x-cloak x-show="openMenu === '{{ $svcKey }}'" x-transition.opacity.duration.150ms
                        class="absolute top-full left-0 z-50 w-80 max-w-[min(20rem,calc(100vw-2rem))] pt-2">
                        <div class="rounded-2xl border border-line bg-white p-3 shadow-(--shadow-card-hover)">
                            <a href="{{ locale_route('services.hub', ['cluster' => $sc['code']]) }}" class="nav-panel-row group mb-1 border-b border-line pb-2">
                                <span class="nav-panel-item-row">
                                    <span class="nav-panel-lead-mark" aria-hidden="true"></span>
                                    <span class="nav-panel-item">Tất cả {{ strtolower($sc['nav_label']) }}</span>
                                </span>
                                <span class="nav-panel-meta">{{ $sc['label'] ?? '' }}</span>
                            </a>
                            <div class="nav-panel-group">
                                @if (($sc['code'] ?? '') === 'other')
                                    @if ($transportHub)
                                        <a href="{{ locale_route('services.hub', ['cluster' => $transportCluster]) }}" class="nav-panel-link">
                                            <span class="nav-panel-item-row">
                                                <span>{{ $transportNavLabel }}</span>
                                                <x-shared.count-badge :count="$trainServiceCount" />
                                            </span>
                                        </a>
                                    @endif
                                    @if ($flightHub)
                                        <a href="{{ locale_route('services.hub', ['cluster' => 'flight']) }}" class="nav-panel-link">
                                            <span class="nav-panel-item-row">
                                                <span>Vé máy bay</span>
                                                <x-shared.count-badge :count="$flightServiceCount" />
                                            </span>
                                        </a>
                                    @endif
                                @endif
                                @foreach ($svcCats as $cat)
                                    <a href="{{ locale_route('services.index', ['cluster' => $sc['code'], 'category' => $cat['slug']]) }}" class="nav-panel-link">
                                        <span class="nav-panel-item-row">
                                            <span>{{ $cat['name'] }}</span>
                                            @if (($cat['count'] ?? 0) > 0)
                                                <x-shared.count-badge :count="$cat['count']" />
                                            @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="relative" @mouseleave="moreOpen = false" @click.outside="closeMore()">
                <button type="button"
                    class="header-more-btn"
                    :aria-expanded="moreOpen"
                    aria-label="Thêm: Về chúng tôi và cẩm nang"
                    aria-controls="header-more-panel"
                    @click="toggleMore()">
                    <x-icon name="list" class="header-more-btn__glyph" />
                </button>
                <div x-cloak x-show="moreOpen" x-transition.opacity.duration.150ms
                    id="header-more-panel"
                    class="header-more-panel absolute top-full right-0 z-50 pt-2"
                    role="region"
                    aria-label="Về chúng tôi và cẩm nang">
                    <div class="header-more-panel__card">
                        <div class="header-more-panel__scroll vt-scrollbar">
                            <div class="nav-panel-group">
                                <p class="nav-panel-group__title">{{ $nav['about_group'] ?? ('Về '.$brandName) }}</p>
                                <a href="{{ locale_route('about') }}" class="nav-panel-link">Về chúng tôi</a>
                                <a href="{{ locale_route('contact') }}" class="nav-panel-link">Liên hệ</a>
                                @if (Route::has('team'))
                                    <a href="{{ locale_route('team') }}" class="nav-panel-link">Đội ngũ</a>
                                @endif
                                @if (Route::has('reviews'))
                                    <a href="{{ locale_route('reviews') }}" class="nav-panel-link">Cảm nhận khách hàng</a>
                                @endif
                            </div>
                            <div class="nav-panel-group">
                                <p class="nav-panel-group__title">Cẩm nang</p>
                                <a href="{{ locale_route('guide.index') }}" class="nav-panel-link">Tất cả bài viết</a>
                                @foreach ($guideCountries as $c)
                                    <a href="{{ locale_route('guide.country', ['country' => $c['slug']]) }}" class="nav-panel-link">
                                        Cẩm nang {{ $c['name'] }}
                                    </a>
                                @endforeach
                            </div>
                            <div class="nav-panel-group">
                                <p class="nav-panel-group__title">Thư viện</p>
                                <a href="{{ locale_route('videos') }}" class="nav-panel-link">Video</a>
                                <a href="{{ locale_route('gallery') }}" class="nav-panel-link">Ảnh</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="headerMain__actions">
            <a href="{{ locale_route('customize') }}" class="btn-primary-sm hidden whitespace-nowrap sm:inline-flex">
                <x-icon name="route" class="size-5 shrink-0" /> Tour riêng
            </a>

            <button type="button" @click="openMobileNav()"
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
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 max-lg:translate-y-full lg:-translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 max-lg:translate-y-full lg:-translate-y-2"
            @click.stop>
            <div class="site-search__sheet-head">
                <div class="site-search__handle" aria-hidden="true"></div>
                <div class="site-search__sheet-title-row">
                    <div>
                        <h2 class="site-search__sheet-title item-title">Tìm kiếm</h2>
                        <p class="site-search__sheet-sub">{{ $cruiseNav['search_hint'] ?? 'Tour, điểm đến, du thuyền, cẩm nang…' }}</p>
                    </div>
                    <button type="button" class="site-search__sheet-close" @click="closeSearch()" aria-label="Đóng tìm kiếm">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>
            </div>

            <form action="{{ locale_route('search') }}" method="get" class="site-search-bar" role="search"
                @submit="if (!(q || '').trim()) { $event.preventDefault(); }">
                <x-icon name="search" class="site-search-bar__icon size-5" />
                <input type="search" name="q" x-model="q" x-ref="searchInput"
                    placeholder="{{ $cruiseNav['search_placeholder'] ?? 'Tìm tour, điểm đến, du thuyền, bài viết…' }}"
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

    {{-- Menu di động (drawer phải) --}}
    <div x-cloak x-show="mobileOpen" class="mobile-nav-drawer lg:hidden" role="dialog" aria-modal="true" aria-label="Menu điều hướng"
        @keydown.escape.window="closeMobileNav()">
        <div class="mobile-nav-drawer__backdrop" @click="closeMobileNav()" x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="mobile-nav-drawer__panel" x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">
            <header class="mobile-nav-drawer__head">
                <a href="{{ locale_route('home') }}" class="header-wordmark header-wordmark--drawer" @click="closeMobileNav()" aria-label="{{ $brandName }} — về trang chủ">
                    <span class="header-wordmark__mark" aria-hidden="true">
                        <x-icon name="compass" class="header-wordmark__icon" />
                    </span>
                    <span class="header-wordmark__text">
                        <span class="header-wordmark__name">{{ $brandName }}</span>
                        @if ($brandTagline !== '')
                            <span class="header-wordmark__tagline">{{ $brandTagline }}</span>
                        @endif
                    </span>
                </a>
                <button type="button" class="mobile-nav-drawer__close" @click="closeMobileNav()" aria-label="Đóng menu">
                    <x-icon name="close" class="size-5" />
                </button>
            </header>

            <nav class="mobile-nav-drawer__body vt-scrollbar" aria-label="Menu di động">
                <div class="mobile-nav-drawer__section" x-ref="sec-dest">
                    <button type="button" class="mobile-nav-drawer__trigger"
                        :aria-expanded="mobileSub === 'dest'"
                        @click="toggleMobileSub('dest')">
                        <span class="mobile-nav-drawer__trigger-icon" aria-hidden="true"><x-icon name="map-pin" class="size-4" /></span>
                        <span class="mobile-nav-drawer__trigger-label">Tour trọn gói</span>
                        <x-icon name="chevron-down" class="mobile-nav-drawer__chevron size-4" ::class="mobileSub === 'dest' && 'is-open'" />
                    </button>
                    <div class="mobile-nav-drawer__sub" x-show="mobileSub === 'dest'" x-collapse>
                        <ul class="mobile-nav-drawer__tree">
                            <li>
                                <a href="{{ locale_route('tours.hub') }}" class="mobile-nav-drawer__tree-link mobile-nav-drawer__tree-link--lead" @click="closeMobileNav()">
                                    <span class="mobile-nav-drawer__tree-link-title item-title">Tất cả tour</span>
                                    <span class="mobile-nav-drawer__tree-link-meta">Xem toàn bộ hành trình</span>
                                </a>
                            </li>
                            @foreach ($destinations as $c)
                                <li @class(['mobile-nav-drawer__tree-item--last' => $loop->last])>
                                    <a href="{{ locale_route('tours.index', $c['slug']) }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">
                                        <span class="mobile-nav-drawer__tree-link-row">
                                            <span class="mobile-nav-drawer__tree-link-title">Tour {{ $c['name'] }}</span>
                                            <x-shared.count-badge :count="$c['tourCount'] ?? 0" />
                                        </span>
                                        @if (! empty($c['tagline']))
                                            <span class="mobile-nav-drawer__tree-link-meta">{{ $c['tagline'] }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="mobile-nav-drawer__section" x-ref="sec-cruise">
                    <button type="button" class="mobile-nav-drawer__trigger"
                        :aria-expanded="mobileSub === 'cruise'"
                        @click="toggleMobileSub('cruise')">
                        <span class="mobile-nav-drawer__trigger-icon" aria-hidden="true"><x-icon name="cruise" class="size-4" /></span>
                        <span class="mobile-nav-drawer__trigger-label">{{ $cruiseNav['label'] ?? 'Du thuyền' }}</span>
                        <x-icon name="chevron-down" class="mobile-nav-drawer__chevron size-4" ::class="mobileSub === 'cruise' && 'is-open'" />
                    </button>
                    <div class="mobile-nav-drawer__sub" x-show="mobileSub === 'cruise'" x-collapse>
                        <ul class="mobile-nav-drawer__tree">
                            <li>
                                <a href="{{ locale_route('cruises.hub') }}" class="mobile-nav-drawer__tree-link mobile-nav-drawer__tree-link--lead" @click="closeMobileNav()">
                                    <span class="mobile-nav-drawer__tree-link-title item-title">{{ $cruiseNav['all_label'] ?? 'Tất cả du thuyền' }}</span>
                                    <span class="mobile-nav-drawer__tree-link-meta">Lịch trình &amp; loại tàu</span>
                                </a>
                            </li>
                            @foreach ($cruiseTypes as $t)
                                <li @class(['mobile-nav-drawer__tree-item--last' => $loop->last])>
                                    <a href="{{ locale_route('cruises.index', $t['slug']) }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">
                                        <span class="mobile-nav-drawer__tree-link-row">
                                            <span class="mobile-nav-drawer__tree-link-title">{{ $t['name'] }}</span>
                                            <x-shared.count-badge :count="$t['count'] ?? 0" />
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                @foreach ($serviceClusters as $sc)
                    @if (in_array($sc['code'] ?? '', ['train', 'flight'], true))
                        @continue
                    @endif
                    @php
                        $svcMobileKey = 'svc-' . $sc['code'];
                        $svcCats = $serviceCatsByCluster[$sc['code']] ?? [];
                    @endphp
                    <div class="mobile-nav-drawer__section" x-ref="sec-{{ $svcMobileKey }}">
                        <button type="button" class="mobile-nav-drawer__trigger"
                            :aria-expanded="mobileSub === '{{ $svcMobileKey }}'"
                            @click="toggleMobileSub('{{ $svcMobileKey }}')">
                            <span class="mobile-nav-drawer__trigger-icon" aria-hidden="true">
                                <x-icon :name="$sc['icon'] ?? 'briefcase'" class="size-4" />
                            </span>
                            <span class="mobile-nav-drawer__trigger-label">{{ $sc['nav_label'] }}</span>
                            <x-icon name="chevron-down" class="mobile-nav-drawer__chevron size-4" ::class="mobileSub === '{{ $svcMobileKey }}' && 'is-open'" />
                        </button>
                        <div class="mobile-nav-drawer__sub" x-show="mobileSub === '{{ $svcMobileKey }}'" x-collapse>
                            <ul class="mobile-nav-drawer__tree">
                                <li>
                                    <a href="{{ locale_route('services.hub', ['cluster' => $sc['code']]) }}" class="mobile-nav-drawer__tree-link mobile-nav-drawer__tree-link--lead" @click="closeMobileNav()">
                                        <span class="mobile-nav-drawer__tree-link-title item-title">Tất cả {{ strtolower($sc['nav_label']) }}</span>
                                        <span class="mobile-nav-drawer__tree-link-meta">{{ $sc['label'] ?? '' }}</span>
                                    </a>
                                </li>
                                @if (($sc['code'] ?? '') === 'other')
                                    @if ($transportHub)
                                        <li>
                                            <a href="{{ locale_route('services.hub', ['cluster' => $transportCluster]) }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">
                                                <span class="mobile-nav-drawer__tree-link-row">
                                                    <span class="mobile-nav-drawer__tree-link-title">{{ $transportNavLabel }}</span>
                                                    <x-shared.count-badge :count="$trainServiceCount" />
                                                </span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($flightHub)
                                        <li>
                                            <a href="{{ locale_route('services.hub', ['cluster' => 'flight']) }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">
                                                <span class="mobile-nav-drawer__tree-link-row">
                                                    <span class="mobile-nav-drawer__tree-link-title">Vé máy bay</span>
                                                    <x-shared.count-badge :count="$flightServiceCount" />
                                                </span>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                                @foreach ($svcCats as $cat)
                                    <li @class(['mobile-nav-drawer__tree-item--last' => $loop->last])>
                                        <a href="{{ locale_route('services.index', ['cluster' => $sc['code'], 'category' => $cat['slug']]) }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">
                                            <span class="mobile-nav-drawer__tree-link-row">
                                                <span class="mobile-nav-drawer__tree-link-title">{{ $cat['name'] }}</span>
                                                @if (($cat['count'] ?? 0) > 0)
                                                    <x-shared.count-badge :count="$cat['count']" />
                                                @endif
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach

                <div class="mobile-nav-drawer__section" x-ref="sec-info">
                    <button type="button" class="mobile-nav-drawer__trigger"
                        :aria-expanded="mobileSub === 'info'"
                        @click="toggleMobileSub('info')">
                        <span class="mobile-nav-drawer__trigger-icon" aria-hidden="true"><x-icon name="list" class="size-5" /></span>
                        <span class="mobile-nav-drawer__trigger-label">Về chúng tôi &amp; cẩm nang</span>
                        <x-icon name="chevron-down" class="mobile-nav-drawer__chevron size-4" ::class="mobileSub === 'info' && 'is-open'" />
                    </button>
                    <div class="mobile-nav-drawer__sub" x-show="mobileSub === 'info'" x-collapse>
                        <p class="mobile-nav-drawer__tree-group-title">{{ $nav['about_group'] ?? ('Về '.$brandName) }}</p>
                        <ul class="mobile-nav-drawer__tree">
                            <li>
                                <a href="{{ locale_route('about') }}" class="mobile-nav-drawer__tree-link mobile-nav-drawer__tree-link--lead" @click="closeMobileNav()">Về chúng tôi</a>
                            </li>
                            <li>
                                <a href="{{ locale_route('contact') }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">Liên hệ</a>
                            </li>
                            @if (Route::has('team'))
                                <li>
                                    <a href="{{ locale_route('team') }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">Đội ngũ</a>
                                </li>
                            @endif
                            @if (Route::has('reviews'))
                                <li class="mobile-nav-drawer__tree-item--last">
                                    <a href="{{ locale_route('reviews') }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">Cảm nhận khách hàng</a>
                                </li>
                            @endif
                        </ul>
                        <p class="mobile-nav-drawer__tree-group-title">Cẩm nang</p>
                        <ul class="mobile-nav-drawer__tree">
                            <li>
                                <a href="{{ locale_route('guide.index') }}" class="mobile-nav-drawer__tree-link mobile-nav-drawer__tree-link--lead" @click="closeMobileNav()">Tất cả bài viết</a>
                            </li>
                            @foreach ($guideCountries as $c)
                                <li @class(['mobile-nav-drawer__tree-item--last' => $loop->last])>
                                    <a href="{{ locale_route('guide.country', ['country' => $c['slug']]) }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">Cẩm nang {{ $c['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mobile-nav-drawer__tree-group-title">Thư viện</p>
                        <ul class="mobile-nav-drawer__tree">
                            <li>
                                <a href="{{ locale_route('videos') }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">Video</a>
                            </li>
                            <li class="mobile-nav-drawer__tree-item--last">
                                <a href="{{ locale_route('gallery') }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">Ảnh</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <footer class="mobile-nav-drawer__foot">
                <a href="tel:{{ $hotlineTel }}" class="mobile-nav-drawer__hotline">
                    <span class="mobile-nav-drawer__hotline-icon" aria-hidden="true"><x-icon name="phone" class="size-4" /></span>
                    <span class="min-w-0">
                        <span class="mobile-nav-drawer__hotline-label">{{ $hotlineLabel }}</span>
                        <span class="mobile-nav-drawer__hotline-number">{{ $hotlineDisplay }}</span>
                    </span>
                </a>
                <a href="{{ locale_route('customize') }}" class="btn-primary mobile-nav-drawer__cta" @click="closeMobileNav()">
                    <x-icon name="route" class="size-5 shrink-0" /> Tour riêng
                </a>
            </footer>
        </div>
    </div>
</div>
