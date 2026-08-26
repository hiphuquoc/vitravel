import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

/**
 * Carousel dựa trên scroll-snap: prev/next cuộn theo bề rộng viewport của track.
 */
Alpine.data('carousel', () => ({
    canPrev: false,
    canNext: true,
    init() {
        this.$nextTick(() => this.update());
        this.$refs.track.addEventListener('scroll', () => this.update(), { passive: true });
    },
    update() {
        const t = this.$refs.track;
        this.canPrev = t.scrollLeft > 8;
        this.canNext = t.scrollLeft + t.clientWidth < t.scrollWidth - 8;
    },
    go(dir) {
        const t = this.$refs.track;
        t.scrollBy({ left: dir * t.clientWidth * 0.9, behavior: 'smooth' });
    },
}));

/**
 * Anchor tabs sticky (Tour Detail) + highlight mục đang xem.
 */
Alpine.data('scrollSpy', (ids = []) => ({
    active: ids[0] ?? null,
    init() {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) this.active = e.target.id;
                });
            },
            { rootMargin: '-30% 0px -60% 0px' }
        );
        ids.forEach((id) => {
            const el = document.getElementById(id);
            if (el) observer.observe(el);
        });
    },
}));

/**
 * Mục lục bài blog: scroll-spy + đóng/mở đầu bài + FAB/drawer khi đã scroll qua TOC.
 */
Alpine.data('articleToc', (ids = []) => ({
    ids,
    active: ids[0] ?? null,
    open: true,
    drawer: false,
    fabVisible: false,
    labels: {},

    get activeLabel() {
        return this.labels[this.active] || '';
    },

    init() {
        if (window.matchMedia('(max-width: 767px)').matches) {
            this.open = false;
        }

        this.ids.forEach((id) => {
            const el = document.getElementById(id);
            if (! el) return;
            this.labels[id] = (el.textContent || '').trim();
        });

        const spy = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) this.active = e.target.id;
                });
            },
            { rootMargin: '-28% 0px -55% 0px' }
        );
        this.ids.forEach((id) => {
            const el = document.getElementById(id);
            if (el) spy.observe(el);
        });

        this.$nextTick(() => {
            const toc = this.$refs.tocInline;
            if (! toc || ! ('IntersectionObserver' in window)) return;
            const io = new IntersectionObserver(
                ([entry]) => {
                    this.fabVisible = ! entry.isIntersecting;
                },
                { root: null, threshold: 0, rootMargin: '-64px 0px 0px 0px' }
            );
            io.observe(toc);
        });
    },

    openDrawer() {
        this.drawer = true;
        document.body.classList.add('is-toc-drawer-open');
        this.$nextTick(() => {
            this.$root.querySelector('.article-toc-drawer__close')?.focus();
        });
    },

    closeDrawer() {
        if (! this.drawer) return;
        this.drawer = false;
        document.body.classList.remove('is-toc-drawer-open');
    },

    onNavigate() {
        this.closeDrawer();
    },
}));

/**
 * Stepper số khách (Customize Tour).
 */
Alpine.data('stepper', (initial = 0, min = 0, max = 30) => ({
    value: initial,
    dec() {
        this.value = Math.max(min, this.value - 1);
    },
    inc() {
        this.value = Math.min(max, this.value + 1);
    },
}));

/**
 * Custom select form — pattern adminCustomSelect (liendoan), single + search.
 * opts: { value, label, options: [{value,label}], placeholder?, searchable?, required? }
 */
Alpine.data('formSelect', (opts = {}) => ({
    open: false,
    value: opts.value ?? '',
    label: opts.label ?? '',
    placeholder: opts.placeholder ?? '- Lựa chọn -',
    options: Array.isArray(opts.options) ? opts.options : [],
    searchable: opts.searchable !== false,
    required: Boolean(opts.required),
    query: '',
    highlight: -1,
    filtered: [],

    init() {
        this.filtered = this.options.slice();
        this.syncValidity();
        this.$watch('value', () => this.syncValidity());
    },

    get hasValue() {
        return this.options.some((o) => String(o.value) === String(this.value));
    },

    get displayLabel() {
        if (this.hasValue && this.label) return this.label;
        if (this.label && this.label !== this.placeholder) return this.label;
        return this.placeholder;
    },

    syncValidity() {
        const el = this.$refs.hidden;
        if (! el || ! this.required) return;
        if (this.value === null || this.value === undefined || String(this.value) === '') {
            el.setCustomValidity('Vui lòng chọn một mục');
        } else {
            el.setCustomValidity('');
        }
    },

    toggle() {
        if (this.open) this.close();
        else this.openList();
    },

    onDisplayClick(e) {
        if (e.target.closest('.vt-select__dropdown')) return;
        this.toggle();
    },

    openList() {
        this.open = true;
        this.query = '';
        this.filtered = this.options.slice();
        const idx = this.filtered.findIndex((o) => String(o.value) === String(this.value));
        this.highlight = idx >= 0 ? idx : 0;
        this.$nextTick(() => {
            if (this.searchable) this.$refs.search?.focus();
        });
    },

    close() {
        this.open = false;
        this.query = '';
        this.filtered = this.options.slice();
        this.highlight = -1;
    },

    onSearch() {
        const q = (this.query || '').trim().toLowerCase();
        this.filtered = ! q
            ? this.options.slice()
            : this.options.filter((o) => String(o.label).toLowerCase().includes(q));
        this.highlight = this.filtered.length ? 0 : -1;
    },

    move(dir) {
        if (! this.open) {
            this.openList();
            return;
        }
        if (! this.filtered.length) return;
        const len = this.filtered.length;
        this.highlight = (this.highlight + dir + len) % len;
        this.$nextTick(() => {
            const opts = this.$root.querySelectorAll('.vt-select__option');
            opts[this.highlight]?.scrollIntoView({ block: 'nearest' });
        });
    },

    chooseHighlighted() {
        if (this.highlight < 0 || ! this.filtered[this.highlight]) return;
        this.select(this.filtered[this.highlight]);
    },

    select(opt) {
        this.value = String(opt?.value ?? '');
        this.label = String(opt?.label ?? '');
        this.close();
        this.$dispatch('vt-select-change', {
            name: this.$refs.hidden?.name,
            value: this.value,
        });
    },
}));

/**
 * Hero slider trang chủ — first slide paint ngay (CSS bg), slide sau tải trì hoãn.
 */
Alpine.data('heroSlider', (total = 1) => ({
    active: 0,
    total: Math.max(1, Number(total) || 1),
    timer: null,
    loaded: {},

    init() {
        this.loaded[0] = true;
        this.applyBg(0);
        this.startAutoplay();
        const prefetch = () => {
            for (let i = 1; i < this.total; i += 1) this.ensureLoaded(i);
        };
        if (typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(prefetch, { timeout: 2800 });
        } else {
            setTimeout(prefetch, 1800);
        }
    },

    startAutoplay() {
        if (this.total <= 1) return;
        this.stopAutoplay();
        this.timer = setInterval(() => this.goNext(), 6000);
    },

    stopAutoplay() {
        clearInterval(this.timer);
        this.timer = null;
    },

    goTo(i) {
        const next = ((i % this.total) + this.total) % this.total;
        this.ensureLoaded(next);
        this.active = next;
        this.stopAutoplay();
        this.startAutoplay();
    },

    goPrev() {
        this.goTo(this.active - 1);
    },

    goNext() {
        this.goTo(this.active + 1);
    },

    ensureLoaded(i) {
        if (this.loaded[i]) return;
        this.applyBg(i);
        this.loaded[i] = true;
    },

    applyBg(i) {
        const el = this.$root.querySelector(`[data-hero-slide="${i}"]`);
        if (! el) return;
        const media = el.querySelector('.hero-slide__media');
        if (! media || media.dataset.bgReady === '1') return;
        const desktop = media.getAttribute('data-bg') || '';
        const mobile = media.getAttribute('data-bg-mobile') || '';
        if (! desktop && ! mobile) return;
        if (desktop) media.style.setProperty('--hero-bg', `url("${desktop}")`);
        if (mobile) media.style.setProperty('--hero-bg-mobile', `url("${mobile}")`);
        media.classList.add('hero-slide__media--ready');
        media.dataset.bgReady = '1';
        // Warm decode without blocking LCP of slide 0
        if (i > 0 && desktop && typeof window.Image === 'function') {
            const img = new Image();
            img.decoding = 'async';
            img.src = desktop;
        }
    },
}));

/**
 * Form demo: chặn submit, hiển thị confirmation inline (chưa nối API).
 */
Alpine.data('demoForm', () => ({
    sent: false,
    submit() {
        this.sent = true;
        this.$nextTick(() => this.$root.querySelector('[data-confirm]')?.scrollIntoView({ block: 'center', behavior: 'smooth' }));
    },
}));

/**
 * Lightbox xem full media (video / ảnh) — dùng chung home showcase + trang thư viện.
 */
const mediaLightboxFactory = (items = []) => ({
    items,
    playlist: [],
    drawerOpen: false,
    viewerActive: null,
    active: null,
    get activeItem() {
        return this.viewerActive === null ? null : this.items[this.viewerActive] ?? null;
    },
    get activeLabel() {
        if (this.viewerActive === null || ! this.items.length) return '';
        const n = String(this.viewerActive + 1).padStart(2, '0');
        const total = String(this.items.length).padStart(2, '0');

        return `${n} / ${total}`;
    },
    // Trả về danh sách thumbnail có cửa sổ trượt căn giữa xung quanh active item (Windowed Thumbnails)
    get visibleThumbnails() {
        if (! this.items.length) return [];
        const total = this.items.length;
        const current = this.viewerActive ?? 0;
        const windowSize = 13; // Hiển thị 13 ảnh thumbnail kích thước gọn gàng
        const half = Math.floor(windowSize / 2);
        
        let start = Math.max(0, current - half);
        let end = Math.min(total, start + windowSize);
        if (end - start < windowSize) {
            start = Math.max(0, end - windowSize);
        }
        
        const result = [];
        for (let i = start; i < end; i++) {
            result.push({
                index: i,
                item: this.items[i],
                isActive: i === current
            });
        }
        return result;
    },
    open(index = 0) {
        this.drawerOpen = true;
        this.viewerActive = null;
        this.active = null;
        document.body.classList.add('stay-room-lock');
    },
    close() {
        this.drawerOpen = false;
        this.viewerActive = null;
        this.active = null;
        document.body.classList.remove('stay-room-lock');
    },
    openViewer(index) {
        // Trên mobile (<= 768px), grid 1 cột to rõ đã là chế độ xem trọn vẹn, không cần mở viewer full nữa
        if (window.innerWidth <= 768) {
            return;
        }
        this.viewerActive = Number.isInteger(index) ? index : 0;
        this.active = this.viewerActive;
    },
    closeViewer() {
        this.viewerActive = null;
        this.active = null;
    },
    selectViewer(idx) {
        this.viewerActive = idx;
        this.active = idx;
    },
    prev() {
        if (! this.items.length) return;
        this.viewerActive = (this.viewerActive - 1 + this.items.length) % this.items.length;
        this.active = this.viewerActive;
    },
    next() {
        if (! this.items.length) return;
        this.viewerActive = (this.viewerActive + 1) % this.items.length;
        this.active = this.viewerActive;
    },
});

Alpine.data('mediaLightbox', mediaLightboxFactory);
Alpine.data('videoGallery', mediaLightboxFactory);

/**
 * Điểm đến yêu thích — reveal khi section vào viewport.
 */
Alpine.data('destShowcase', () => ({
    inview: false,
    init() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.inview = true;
            return;
        }
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) {
                        this.inview = true;
                        observer.disconnect();
                    }
                });
            },
            { rootMargin: '0px 0px -12% 0px', threshold: 0.15 },
        );
        observer.observe(this.$el);
    },
}));

/**
 * Quick inquiry — teaser thu gọn, form letter trong modal.
 */
Alpine.data('quickInquiry', (opts = {}) => ({
    open: Boolean(opts.openOnLoad),
    init() {
        if (this.open) {
            document.documentElement.style.overflow = 'hidden';
            this.$nextTick(() => this.$refs.firstInput?.focus());
        }
    },
    openModal() {
        this.open = true;
        document.documentElement.style.overflow = 'hidden';
        this.$nextTick(() => this.$refs.firstInput?.focus());
    },
    closeModal() {
        this.open = false;
        document.documentElement.style.overflow = '';
    },
}));

/**
 * Listing grid — skeleton → fetch HTML (tours/cruises filter + featured + related).
 * opts: {
 *   endpoint: string,
 *   params?: object,          // fixed query (type, exclude, limit…)
 *   filters?: {               // live filter groups { country: string[], duration: [], style: [] }
 *     country?: string[],
 *     duration?: string[],
 *     style?: string[],
 *   },
 *   syncUrl?: boolean,        // replaceState query on listing pages
 *   debounceMs?: number,
 * }
 */
Alpine.data('listingGrid', (opts = {}) => ({
    endpoint: opts.endpoint || '',
    fixedParams: opts.params || {},
    labelMap: opts.labelMap || {},
    filters: Object.fromEntries(
        Object.entries(opts.filters || {}).map(([key, values]) => [
            key,
            [...(values || [])].map((v) => String(v)),
        ]),
    ),
    syncUrl: Boolean(opts.syncUrl),
    debounceMs: opts.debounceMs ?? 180,
    initialLimit: Number(opts.initialLimit || opts.skeletonCount || 5),
    eagerLimit: Number(opts.eagerLimit || 10),
    scrollLimit: Number(opts.scrollLimit || opts.batchLimit || 20),
    maxScrollAutoLoads: Number(opts.maxScrollAutoLoads || 2),
    scrollAutoCount: 0,
    cursor: opts.seeded ? (opts.seedCursor || null) : null,
    hasMore: Boolean(opts.seeded && opts.seedHasMore),
    loading: ! Boolean(opts.seeded),
    loadingMore: false,
    count: opts.seeded ? (opts.seedCount ?? null) : null,
    error: null,
    drawer: false,
    seededBoot: Boolean(opts.seeded),
    skeletonCount: Number(opts.skeletonCount || opts.initialLimit || 5),
    cardKind: String(opts.cardKind || 'tour'),

    // Dual Range Price Slider State
    priceMin: Number(opts.priceMin ?? 0),
    priceMax: Number(opts.priceMax ?? 10000000),
    priceStep: Number(opts.priceStep ?? 100000),
    selectedMinPrice: Number(opts.selectedMinPrice ?? opts.priceMin ?? 0),
    selectedMaxPrice: Number(opts.selectedMaxPrice ?? opts.priceMax ?? 10000000),
    sort: String(opts.sort || 'popular'),

    // Stage tracking & concurrency mutex
    stage: 'INITIAL',
    _isFetching: false,
    _sentinelObserver: null,
    _scrollHandler: null,
    _timer: null,
    _abort: null,
    _loadedKeys: new Set(),
    _seedOpts: {
        count: opts.seedCount ?? null,
        cursor: opts.seedCursor || null,
        hasMore: Boolean(opts.seedHasMore),
    },

    get requireManualClick() {
        return this.scrollAutoCount >= this.maxScrollAutoLoads;
    },

    get loadedCount() {
        return this._loadedKeys.size;
    },

    get progressPercent() {
        if (! this.count || this.count <= 0) return 0;
        return Math.min(100, Math.round((this.loadedCount / this.count) * 100));
    },

    get remainingCount() {
        if (! this.count) return 0;
        return Math.max(0, this.count - this.loadedCount);
    },

    get isPriceFiltered() {
        return this.selectedMinPrice > this.priceMin || this.selectedMaxPrice < this.priceMax;
    },

    get priceRangeLabel() {
        if (! this.isPriceFiltered) return '';
        return `${this.formatMoneyVND(this.selectedMinPrice)} – ${this.formatMoneyVND(this.selectedMaxPrice)}`;
    },

    isPricePresetActive(min, max) {
        const lo = Number(min);
        const hi = max === null || max === undefined ? this.priceMax : Number(max);
        return this.selectedMinPrice === lo && this.selectedMaxPrice === hi;
    },

    formatMoneyVND(amount) {
        if (! amount && amount !== 0) return '0 đ';
        if (amount >= 1000000) {
            const tr = amount / 1000000;
            const str = tr % 1 === 0 ? tr.toString() : tr.toFixed(1).replace('.', ',');
            return `${str} triệu`;
        }
        if (amount >= 1000) {
            const k = amount / 1000;
            const str = k % 1 === 0 ? k.toString() : k.toFixed(0);
            return `${str}k đ`;
        }
        return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
    },

    formatFullVND(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount || 0) + ' đ';
    },

    setPriceRange(min, max) {
        this.selectedMinPrice = Math.max(this.priceMin, Math.min(min, this.priceMax));
        this.selectedMaxPrice = Math.min(this.priceMax, Math.max(max, this.priceMin));
        if (this.filters.price_range) {
            this.filters.price_range = [];
        }
        this.scheduleFetch();
    },

    resetPriceRange() {
        this.selectedMinPrice = this.priceMin;
        this.selectedMaxPrice = this.priceMax;
        if (this.filters.price_range) {
            this.filters.price_range = [];
        }
        this.scheduleFetch();
    },

    setSort(value) {
        const next = String(value || 'popular');
        if (this.sort === next) return;
        this.sort = next;
        this.scheduleFetch();
    },

    get hasActiveFilters() {
        return Object.values(this.filters).some((vals) => Array.isArray(vals) && vals.length > 0) || this.isPriceFiltered;
    },

    get totalActiveFilterCount() {
        return Object.values(this.filters).reduce((acc, vals) => acc + (Array.isArray(vals) ? vals.length : 0), 0) + (this.isPriceFiltered ? 1 : 0);
    },

    activeFilterCount(group) {
        return (this.filters[group] || []).length;
    },

    getFilterLabel(group, value) {
        const valStr = String(value);
        if (this.labelMap && this.labelMap[`${group}:${valStr}`]) {
            return this.labelMap[`${group}:${valStr}`];
        }
        if (this.labelMap && this.labelMap[valStr]) {
            return this.labelMap[valStr];
        }
        // Friendly fallback translations
        const fallbacks = {
            'resort': 'Resort & Nghỉ dưỡng',
            'hotel': 'Khách sạn',
            'villa': 'Biệt thự / Villa',
            'boutique': 'Boutique Hotel',
            'homestay': 'Homestay',
            'cabin': 'Cabin nghỉ dưỡng',
            'under_1m': 'Dưới 1 triệu',
            '1m_2m': '1 – 2 triệu',
            '2m_4m': '2 – 4 triệu',
            'above_4m': 'Trên 4 triệu',
            '5_star': '5 sao',
            '4_star': '4 sao',
            '3_star': '3 sao',
            'pool': 'Hồ bơi',
            'beach': 'Bãi biển riêng',
            'breakfast': 'Bao gồm bữa sáng',
            'spa': 'Spa & Massage',
            'gym': 'Phòng Gym',
            'shuttle': 'Đưa đón',
        };
        return fallbacks[valStr] || valStr;
    },

    init() {
        this.$watch('drawer', (open) => {
            document.documentElement.classList.toggle('filter-drawer-open', Boolean(open));
        });

        if (this.syncUrl) {
            const params = new URLSearchParams(window.location.search);
            if (params.has('min_price')) {
                const val = Number(params.get('min_price'));
                if (! isNaN(val)) this.selectedMinPrice = Math.max(this.priceMin, val);
            }
            if (params.has('max_price')) {
                const val = Number(params.get('max_price'));
                if (! isNaN(val)) this.selectedMaxPrice = Math.min(this.priceMax, val);
            }
            if (params.has('sort')) {
                const sort = String(params.get('sort') || '').trim();
                if (sort !== '') this.sort = sort;
            }
        }

        const isRelated = Boolean(
            this.endpoint.includes('related')
            || (this.fixedParams && (this.fixedParams.exclude || this.fixedParams.service_id || this.fixedParams.category_id))
        );
        if (isRelated && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    observer.disconnect();
                    this.fetchInitialBatch();
                }
            }, { rootMargin: '400px' });
            observer.observe(this.$el);
            return;
        }

        // SSR seed: hydrate sau khi refs sẵn sàng — không XHR đợt 1
        if (this.seededBoot) {
            this.$nextTick(() => {
                if (! this.hydrateFromSeed()) {
                    queueMicrotask(() => this.fetchInitialBatch());
                }
            });
            return;
        }

        // Fetch sớm (không chờ paint) — trang chrome đã SSR sẵn
        queueMicrotask(() => this.fetchInitialBatch());
    },

    hydrateFromSeed() {
        const host = this.$refs.results;
        const mount = host?.querySelector?.('[data-listing-mount]');
        if (! mount || ! mount.querySelector('[data-listing-item], [data-listing-container], .listing-empty')) {
            this.seededBoot = false;
            return false;
        }

        this.count = this._seedOpts.count;
        this.cursor = this._seedOpts.cursor;
        this.hasMore = Boolean(this._seedOpts.hasMore && this.cursor);
        this.loading = false;
        this._isFetching = false;
        this.error = null;
        this._loadedKeys.clear();

        mount.querySelectorAll('[data-listing-item]').forEach((el) => {
            const key = el.querySelector('a')?.getAttribute('href') || el.innerText;
            if (key) this._loadedKeys.add(key);
        });

        if (this.hasMore && this.cursor && this.eagerLimit > 0) {
            this.stage = 'EAGER';
            // Eager không chặn tương tác — sau frame đầu
            requestAnimationFrame(() => {
                setTimeout(() => this.loadNextBatch(this.eagerLimit, 'EAGER'), 0);
            });
        } else {
            this.stage = 'COMPLETED';
        }

        return true;
    },

    showSkeleton() {
        const host = this.$refs.results;
        if (! host) return;
        const tpl = this.$refs.skeletonTpl;
        const html = tpl?.innerHTML?.trim();
        if (html) {
            if (window.Alpine?.destroyTree) {
                const mount = host.querySelector('[data-listing-mount]');
                if (mount) window.Alpine.destroyTree(mount);
            }
            host.innerHTML = html;
            return;
        }
        // Fallback tối thiểu nếu thiếu template
        host.innerHTML = `<div class="site-stack" aria-hidden="true" data-listing-skeleton="wide">${
            Array.from({ length: this.skeletonCount }, () => '<div class="card listing-skeleton-card listing-skeleton-card--wide overflow-hidden"><div class="grid sm:grid-cols-[40%_1fr]"><div class="listing-skeleton__media listing-skeleton__media--wide listing-skeleton__shimmer"></div><div class="card-body flex flex-col gap-3"><div class="listing-skeleton__line listing-skeleton__line--title listing-skeleton__shimmer"></div><div class="listing-skeleton__line listing-skeleton__line--places listing-skeleton__shimmer"></div></div></div></div>').join('')
        }</div>`;
    },

    mountResultsHtml(html) {
        const host = this.$refs.results;
        if (! host) return;
        let mount = host.querySelector('[data-listing-mount]');
        if (! mount) {
            host.innerHTML = '';
            mount = document.createElement('div');
            mount.dataset.listingMount = '1';
            host.appendChild(mount);
        }
        if (window.Alpine?.destroyTree) {
            window.Alpine.destroyTree(mount);
        }
        mount.innerHTML = html || '';
        if (window.Alpine?.initTree) {
            window.Alpine.initTree(mount);
        }
        mount.querySelectorAll('[data-listing-item]').forEach((el) => {
            const key = el.querySelector('a')?.getAttribute('href') || el.innerText;
            if (key) this._loadedKeys.add(key);
        });
    },

    disableScrollTriggers() {
        if (this._sentinelObserver) {
            this._sentinelObserver.disconnect();
            this._sentinelObserver = null;
        }
        if (this._scrollHandler) {
            window.removeEventListener('scroll', this._scrollHandler);
            this._scrollHandler = null;
        }
    },

    enableScrollTriggers() {
        if (this.stage !== 'SCROLL_READY' || ! this.hasMore || this.requireManualClick) {
            this.disableScrollTriggers();
            return;
        }

        if ('IntersectionObserver' in window) {
            if (this._sentinelObserver) {
                this._sentinelObserver.disconnect();
            }
            this._sentinelObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    if (this.canTriggerScrollLoad()) {
                        this.loadNextBatch(this.scrollLimit, 'SCROLL');
                    }
                }
            }, {
                rootMargin: '1200px 0px 800px 0px',
                threshold: 0,
            });

            this.$nextTick(() => {
                if (this.$refs.sentinel) {
                    this._sentinelObserver.observe(this.$refs.sentinel);
                }
            });
        }

        if (! this._scrollHandler) {
            this._scrollHandler = () => {
                if (! this.canTriggerScrollLoad()) return;
                const scrollY = window.scrollY || window.pageYOffset;
                const viewportHeight = window.innerHeight;
                const fullHeight = document.documentElement.scrollHeight;
                if (fullHeight - (scrollY + viewportHeight) < 1200) {
                    this.loadNextBatch(this.scrollLimit, 'SCROLL');
                }
            };
            window.addEventListener('scroll', this._scrollHandler, { passive: true });
        }
    },

    canTriggerScrollLoad() {
        return this.stage === 'SCROLL_READY'
            && ! this.requireManualClick
            && ! this._isFetching
            && ! this.loading
            && ! this.loadingMore
            && this.hasMore
            && Boolean(this.cursor);
    },

    loadMore() {
        if (this.loadingMore || this._isFetching || ! this.hasMore) return;
        this.loadNextBatch(this.scrollLimit, 'MANUAL');
    },

    toggleFilter(group, value) {
        if (! this.filters[group]) {
            this.filters[group] = [];
        }
        const list = this.filters[group];
        const needle = String(value);
        const i = list.findIndex((v) => String(v) === needle);
        if (i >= 0) {
            list.splice(i, 1);
        } else {
            list.push(needle);
        }
        this.scheduleFetch();
    },

    clearFilter(group, value) {
        if (! this.filters[group]) return;
        const needle = String(value);
        this.filters[group] = this.filters[group].filter((v) => String(v) !== needle);
        this.scheduleFetch();
    },

    clearGroup(group) {
        if (! this.filters[group]) return;
        this.filters[group] = [];
        this.scheduleFetch();
    },

    clearAllFilters() {
        Object.keys(this.filters).forEach((group) => {
            this.filters[group] = [];
        });
        this.selectedMinPrice = this.priceMin;
        this.selectedMaxPrice = this.priceMax;
        this.scheduleFetch();
    },

    isChecked(group, value) {
        const needle = String(value);
        return (this.filters[group] || []).some((v) => String(v) === needle);
    },

    scheduleFetch() {
        clearTimeout(this._timer);
        this._timer = setTimeout(() => this.fetchInitialBatch(), this.debounceMs);
    },

    buildQuery() {
        const q = new URLSearchParams();
        Object.entries(this.fixedParams).forEach(([k, v]) => {
            if (v === undefined || v === null || v === '') return;
            q.set(k, String(v));
        });

        // Loop over all filters and append ONLY non-empty active values
        Object.entries(this.filters).forEach(([group, values]) => {
            if (! Array.isArray(values) || values.length === 0) return;
            values.forEach((v) => {
                const s = String(v).trim();
                if (s !== '') {
                    q.append(`${group}[]`, s);
                }
            });
        });

        if (this.isPriceFiltered) {
            if (this.selectedMinPrice > this.priceMin) {
                q.set('min_price', String(this.selectedMinPrice));
            }
            if (this.selectedMaxPrice < this.priceMax) {
                q.set('max_price', String(this.selectedMaxPrice));
            }
        }

        if (this.sort && this.sort !== 'popular') {
            q.set('sort', this.sort);
        }

        const locale = document.documentElement?.lang || '';
        if (locale && ! q.has('locale')) {
            q.set('locale', locale);
        }
        return q;
    },

    /**
     * GIAI ĐOẠN 1: Tải batch đầu (hoặc thay thế khi đổi lọc) — skeleton đồng bộ, không chặn chrome.
     */
    async fetchInitialBatch() {
        if (! this.endpoint) return;

        this.stage = 'INITIAL';
        this.scrollAutoCount = 0;
        this._isFetching = true;
        this.loading = true;
        this.seededBoot = false;
        this.loadingMore = false;
        this.error = null;
        this.cursor = null;
        this.hasMore = false;
        this.count = null;
        this._loadedKeys.clear();
        this.disableScrollTriggers();
        this.showSkeleton();

        if (this._abort) this._abort.abort();
        this._abort = new AbortController();

        const q = this.buildQuery();
        q.set('limit', String(this.initialLimit));
        q.set('is_append', '0');

        if (this.syncUrl) {
            const url = new URL(window.location.href);
            const filterParamNames = ['category', 'property_type', 'price_range', 'amenity', 'star', 'duration', 'style', 'type', 'country', 'q', 'min_price', 'max_price', 'sort'];
            [...url.searchParams.keys()].forEach((k) => {
                const cleanKey = k.replace(/\[\]$/, '');
                if (filterParamNames.includes(cleanKey)) {
                    url.searchParams.delete(k);
                }
            });
            Object.entries(this.filters).forEach(([group, values]) => {
                if (Array.isArray(values)) {
                    values.forEach((v) => {
                        const s = String(v).trim();
                        if (s !== '') {
                            url.searchParams.append(`${group}[]`, s);
                        }
                    });
                }
            });
            if (this.isPriceFiltered) {
                if (this.selectedMinPrice > this.priceMin) {
                    url.searchParams.set('min_price', String(this.selectedMinPrice));
                }
                if (this.selectedMaxPrice < this.priceMax) {
                    url.searchParams.set('max_price', String(this.selectedMaxPrice));
                }
            }
            if (this.sort && this.sort !== 'popular') {
                url.searchParams.set('sort', this.sort);
            }
            const qs = url.searchParams.toString();
            history.replaceState({}, '', url.pathname + (qs ? `?${qs}` : ''));
        }

        try {
            const res = await fetch(`${this.endpoint}?${q.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: this._abort.signal,
                credentials: 'same-origin',
            });
            if (! res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            this.count = data.count ?? 0;
            this.hasMore = Boolean(data.has_more);
            this.cursor = data.next_cursor || data.cursor;

            this.mountResultsHtml(data.html || '');

            this.loading = false;
            this._isFetching = false;

            // GIAI ĐOẠN 2: Eager batch nền ngay sau first paint
            if (this.hasMore && this.cursor && this.eagerLimit > 0) {
                this.stage = 'EAGER';
                requestAnimationFrame(() => {
                    setTimeout(() => this.loadNextBatch(this.eagerLimit, 'EAGER'), 0);
                });
            } else {
                this.stage = this.hasMore ? 'SCROLL_READY' : 'COMPLETED';
                if (this.stage === 'SCROLL_READY') {
                    this.$nextTick(() => this.enableScrollTriggers());
                }
            }
        } catch (e) {
            if (e?.name === 'AbortError') return;
            this.error = 'Không tải được danh sách. Thử lại.';
            console.error(e);
            this.loading = false;
            this._isFetching = false;
        }
    },

    /**
     * Tải đợt tiếp theo với cơ chế Khóa Tuần Tự (Mutex Guard) & Chống Trùng Lặp.
     */
    async loadNextBatch(limit = 20, origin = 'SCROLL') {
        if (this._isFetching || ! this.hasMore || ! this.endpoint || ! this.cursor) {
            return;
        }

        this._isFetching = true;
        this.loadingMore = true;
        this.error = null;

        if (origin === 'SCROLL') {
            this.scrollAutoCount++;
        }

        const currentCursor = this.cursor;
        const q = this.buildQuery();
        q.set('after', String(currentCursor));
        q.set('limit', String(limit));
        q.set('is_append', '1');

        try {
            const res = await fetch(`${this.endpoint}?${q.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (! res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            this.count = data.count ?? this.count;
            this.hasMore = Boolean(data.has_more);
            this.cursor = data.next_cursor || data.cursor;

            await this.$nextTick();
            if (this.$refs.results && data.html) {
                const host = this.$refs.results;
                const mount = host.querySelector('[data-listing-mount]') || host;
                const container = mount.querySelector('[data-listing-container]') || mount.querySelector('.site-stack, .grid') || mount;

                const temp = document.createElement('div');
                temp.innerHTML = data.html;

                const newNodes = Array.from(temp.children);
                newNodes.forEach((node) => {
                    const key = node.querySelector('a')?.getAttribute('href') || node.innerText;
                    if (! key || ! this._loadedKeys.has(key)) {
                        if (key) this._loadedKeys.add(key);
                        container.appendChild(node);
                        if (window.Alpine?.initTree) {
                            window.Alpine.initTree(node);
                        }
                    }
                });
            }

            if (origin === 'EAGER') {
                this.stage = this.hasMore ? 'SCROLL_READY' : 'COMPLETED';
            } else if (! this.hasMore) {
                this.stage = 'COMPLETED';
            }
        } catch (e) {
            console.error('Error loading next batch:', e);
        } finally {
            this.loadingMore = false;
            this._isFetching = false;

            if (this.requireManualClick) {
                this.disableScrollTriggers();
            } else if (this.stage === 'SCROLL_READY' && this.hasMore) {
                this.$nextTick(() => this.enableScrollTriggers());
            }
        }
    },
}));

Alpine.data('stayRooms', (rooms = []) => ({
    rooms,
    open: false,
    index: 0,
    photo: 0,
    toast: {
        show: false,
        title: '',
        message: '',
        timer: null,
    },
    /** Random 1–5 mỗi lần tải trang (ổn định trong session trang; HTML cache vẫn được). */
    scarcityRolls: {},
    init() {
        (this.rooms || []).forEach((room, i) => {
            if (room?.scarcityActive) {
                this.scarcityRolls[i] = this.rollScarcity();
            }
        });
    },
    rollScarcity() {
        const min = 1;
        const max = 5;
        return min + Math.floor(Math.random() * (max - min + 1));
    },
    scarcityText(i) {
        const room = this.rooms[i];
        if (!room?.scarcityActive) return '';
        if (this.scarcityRolls[i] == null) {
            this.scarcityRolls[i] = this.rollScarcity();
        }
        const n = this.scarcityRolls[i];
        const tpl = room.scarcityTemplate || 'Chúng tôi còn {n} phòng';
        return String(tpl).replace(/\{n\}/g, String(n));
    },
    get room() {
        return this.rooms[this.index] || null;
    },
    get photoCount() {
        return this.room?.photos?.length || 0;
    },
    handleCardClick(event, i) {
        const target = event.target;
        if (target.closest('button, a, input, select, textarea, .stay-hprt__tip')) {
            return;
        }
        this.show(i);
    },
    show(i, photoIndex = 0) {
        this.index = i;
        this.photo = Number.isInteger(photoIndex) ? photoIndex : 0;
        this.open = true;
        document.body.classList.add('stay-room-lock');
        this.$nextTick(() => {
            this.$refs.roomClose?.focus();
            if (this.$refs.roomDetail) {
                this.$refs.roomDetail.scrollTop = 0;
            }
            const modalBody = document.querySelector('.stay-room-modal__detail');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        });
    },
    close() {
        this.open = false;
        document.body.classList.remove('stay-room-lock');
    },
    bookRoom(roomName = '') {
        if (this.toast.timer) {
            clearTimeout(this.toast.timer);
        }
        this.toast.title = roomName ? `Đặt phòng: ${roomName}` : 'Tính năng đang hoàn thiện';
        this.toast.message = 'Hệ thống đặt phòng trực tuyến đang được nâng cấp. Vui lòng liên hệ hotline/Zalo của ViTravel để được hỗ trợ đặt giữ phòng ngay!';
        this.toast.show = true;
        this.toast.timer = setTimeout(() => {
            this.toast.show = false;
        }, 6000);
    },
    nextPhoto() {
        if (this.photoCount < 2) return;
        this.photo = (this.photo + 1) % this.photoCount;
    },
    prevPhoto() {
        if (this.photoCount < 2) return;
        this.photo = (this.photo - 1 + this.photoCount) % this.photoCount;
    },
    destroy() {
        document.body.classList.remove('stay-room-lock');
    },
}));

window.Alpine = Alpine;
Alpine.start();
