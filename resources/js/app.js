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
 * Anchor tabs sticky (Tour Detail) + TOC bài viết: highlight mục đang xem.
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

window.Alpine = Alpine;
Alpine.start();
