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
    filterGroups: Object.keys(opts.filters || {}),
    filters: Object.fromEntries(
        Object.entries(opts.filters || {}).map(([key, values]) => [
            key,
            [...(values || [])].map((v) => String(v)),
        ]),
    ),
    syncUrl: Boolean(opts.syncUrl),
    debounceMs: opts.debounceMs ?? 220,
    initialLimit: Number(opts.initialLimit || 5),
    batchLimit: Number(opts.batchLimit || 10),
    cursor: null,
    hasMore: false,
    loading: true,
    loadingMore: false,
    eagerLoaded: false,
    count: null,
    error: null,
    drawer: false,
    _sentinelObserver: null,
    _scrollHandler: null,
    _timer: null,
    _abort: null,

    init() {
        const isRelated = this.fixedParams && (this.fixedParams.exclude || this.endpoint.includes('related'));
        if (isRelated && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    observer.disconnect();
                    this.fetchResults();
                }
            }, { rootMargin: '350px' });
            observer.observe(this.$el);
        } else {
            this.$nextTick(() => {
                this.fetchResults();
                this.setupScrollTriggers();
            });
        }
    },

    setupScrollTriggers() {
        // 1. IntersectionObserver đón đầu từ rất xa (1800px trước khi tới đáy)
        if ('IntersectionObserver' in window) {
            if (this._sentinelObserver) {
                this._sentinelObserver.disconnect();
            }
            this._sentinelObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    if (! this.loading && ! this.loadingMore && this.hasMore && this.eagerLoaded) {
                        this.loadNextBatch(this.batchLimit);
                    }
                }
            }, {
                rootMargin: '1800px 0px 1200px 0px',
                threshold: 0,
            });

            this.$nextTick(() => {
                if (this.$refs.sentinel) {
                    this._sentinelObserver.observe(this.$refs.sentinel);
                }
            });
        }

        // 2. Passive scroll listener đón đầu cực sớm khi cách đáy dưới 1800px
        if (! this._scrollHandler) {
            this._scrollHandler = () => {
                if (this.loading || this.loadingMore || ! this.hasMore || ! this.eagerLoaded) return;
                const scrollY = window.scrollY || window.pageYOffset;
                const viewportHeight = window.innerHeight;
                const fullHeight = document.documentElement.scrollHeight;
                if (fullHeight - (scrollY + viewportHeight) < 1800) {
                    this.loadNextBatch(this.batchLimit);
                }
            };
            window.addEventListener('scroll', this._scrollHandler, { passive: true });
        }
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
        const locale = document.documentElement?.lang || '';
        if (locale && ! q.has('locale')) {
            q.set('locale', locale);
        }
        return q;
    },

    async fetchResults() {
        if (! this.endpoint) return;

        this.loading = true;
        this.loadingMore = false;
        this.eagerLoaded = false;
        this.error = null;
        this.cursor = null;
        this.hasMore = false;

        if (this._abort) this._abort.abort();
        this._abort = new AbortController();

        const q = this.buildQuery();
        // Lần đầu tải 5 khách sạn đầu tiên
        q.set('limit', String(this.initialLimit));
        q.set('is_append', '0');

        if (this.syncUrl) {
            const url = new URL(window.location.href);
            [...url.searchParams.keys()].forEach((k) => {
                if (/^(country|duration|style|type|category|q)(\[\])?$/.test(k)) {
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
            this.hasMore = Boolean(data.has_more);
            this.cursor = data.next_cursor || data.cursor;

            await this.$nextTick();
            if (this.$refs.results) {
                const host = this.$refs.results;
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
                mount.innerHTML = data.html || '';
                if (window.Alpine?.initTree) {
                    window.Alpine.initTree(mount);
                }
            }

            // Ngay sau khi 5 khách sạn render xong -> tải tiếp ngay 10 khách sạn qua con trỏ
            if (this.hasMore && this.cursor) {
                setTimeout(() => {
                    this.loadNextBatch(this.batchLimit, { isEager: true });
                }, 60);
            } else {
                this.eagerLoaded = true;
            }
        } catch (e) {
            if (e?.name === 'AbortError') return;
            this.error = 'Không tải được danh sách. Thử lại.';
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    async loadNextBatch(limit = 10, { isEager = false } = {}) {
        if (this.loading || this.loadingMore || ! this.hasMore || ! this.endpoint || ! this.cursor) return;

        this.loadingMore = true;
        this.error = null;

        const q = this.buildQuery();
        // Keyset Cursor Seek: gửi con trỏ tuần tự 'after'
        q.set('after', String(this.cursor));
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
                    container.appendChild(node);
                    if (window.Alpine?.initTree) {
                        window.Alpine.initTree(node);
                    }
                });
            }
        } catch (e) {
            console.error('Error loading next batch:', e);
        } finally {
            this.loadingMore = false;
            if (isEager) {
                this.eagerLoaded = true;
            }
            this.$nextTick(() => this.setupScrollTriggers());
        }
    },

    loadMore() {
        this.loadNextBatch(this.batchLimit);
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
