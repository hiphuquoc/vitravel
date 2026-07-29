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
 * Fullscreen video gallery lightbox (home + /video-trai-nghiem).
 */
Alpine.data('videoGallery', (items = []) => ({
    items,
    active: null,
    get activeItem() {
        return this.active === null ? null : this.items[this.active] ?? null;
    },
    get activeLabel() {
        if (this.active === null) return '';
        const n = String(this.active + 1).padStart(2, '0');
        const total = String(this.items.length).padStart(2, '0');
        return `${n} / ${total}`;
    },
    open(index) {
        this.active = index;
        document.documentElement.style.overflow = 'hidden';
    },
    close() {
        this.active = null;
        document.documentElement.style.overflow = '';
    },
    prev() {
        if (! this.items.length) return;
        this.active = (this.active - 1 + this.items.length) % this.items.length;
    },
    next() {
        if (! this.items.length) return;
        this.active = (this.active + 1) % this.items.length;
    },
}));

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
    filterGroups: Object.keys(opts.filters || {}),
    filters: Object.fromEntries(
        Object.entries(opts.filters || {}).map(([key, values]) => [
            key,
            [...(values || [])].map((v) => String(v)),
        ]),
    ),
    syncUrl: Boolean(opts.syncUrl),
    debounceMs: opts.debounceMs ?? 220,
    loading: true,
    count: null,
    error: null,
    drawer: false,
    _timer: null,
    _abort: null,

    init() {
        // Đợi Alpine gắn x-ref trước khi fetch
        this.$nextTick(() => this.fetchResults());
    },

    toggleFilter(group, value) {
        if (! this.filters[group]) this.filters[group] = [];
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

    isChecked(group, value) {
        const needle = String(value);

        return (this.filters[group] || []).some((v) => String(v) === needle);
    },

    scheduleFetch() {
        clearTimeout(this._timer);
        this._timer = setTimeout(() => this.fetchResults(), this.debounceMs);
    },

    buildQuery() {
        const q = new URLSearchParams();
        Object.entries(this.fixedParams).forEach(([k, v]) => {
            if (v === undefined || v === null || v === '') return;
            q.set(k, String(v));
        });
        this.filterGroups.forEach((group) => {
            const values = this.filters[group] || [];
            if (values.length === 0) {
                q.append(`${group}[]`, '');
                return;
            }
            values.forEach((v) => q.append(`${group}[]`, v));
        });
        // Đồng bộ locale với trang đang xem (API không có prefix /en/…)
        const locale = document.documentElement?.lang || '';
        if (locale && ! q.has('locale')) {
            q.set('locale', locale);
        }
        return q;
    },

    async fetchResults() {
        if (! this.endpoint) return;

        this.loading = true;
        this.error = null;
        if (this._abort) this._abort.abort();
        this._abort = new AbortController();

        const q = this.buildQuery();
        if (this.syncUrl) {
            const url = new URL(window.location.href);
            [...url.searchParams.keys()].forEach((k) => {
                if (/^(country|duration|style|type|q)(\[\])?$/.test(k)) {
                    url.searchParams.delete(k);
                }
            });
            this.filterGroups.forEach((group) => {
                (this.filters[group] || []).forEach((v) => {
                    if (v) url.searchParams.append(`${group}[]`, v);
                });
            });
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
            await this.$nextTick();
            if (this.$refs.results) {
                this.$refs.results.innerHTML = data.html || '';
            }
        } catch (e) {
            if (e?.name === 'AbortError') return;
            this.error = 'Không tải được danh sách. Thử lại.';
            console.error(e);
        } finally {
            this.loading = false;
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
