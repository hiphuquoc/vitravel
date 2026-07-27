<!-- jQuery (admin repeater) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>

<script>
(function () {
    const mq = window.matchMedia('(hover: none), (pointer: coarse)');
    const syncHoverClass = () => {
        document.documentElement.classList.toggle('no-hover', mq.matches);
    };
    syncHoverClass();
    mq.addEventListener('change', syncHoverClass);
})();

function toggleAdminMobileMenu() {
    const sidebar = document.getElementById('adminDashboardSidebar');
    const backdrop = document.getElementById('adminMobileMenuBackdrop');
    sidebar?.classList.toggle('adminDashboard_sidebar--mobileOpen');
    backdrop?.classList.toggle('adminDashboard_mobileMenuBackdrop--visible');
}

document.getElementById('adminMobileMenuBackdrop')?.addEventListener('click', toggleAdminMobileMenu);

document.querySelectorAll('input, textarea').forEach((el) => {
    el.addEventListener('input', () => {
        const id = el.getAttribute('id');
        if (! id) return;
        const counter = document.querySelector(`[data-charactor="${id}"]`);
        if (counter) counter.textContent = el.value.length;
    });
});

function showFullLoading() {
    document.getElementById('js_fullLoading_bg')?.classList.add('is-visible');
}

function hideFullLoading() {
    document.getElementById('js_fullLoading_bg')?.classList.remove('is-visible');
}

async function buildSlugFromTitle(titleInputId, slugInputId, parentSlugFull = '') {
    const titleEl = document.getElementById(titleInputId);
    const slugEl = document.getElementById(slugInputId);
    if (! titleEl || ! slugEl) return;

    const response = await fetch('{{ route('admin.helper.convertStrToSlug') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ str: titleEl.value, parent_slug_full: parentSlugFull }),
    });

    const data = await response.json();
    if (data.success) {
        slugEl.value = data.slug;
        slugEl.dispatchEvent(new Event('input'));
        updateSlugPreview();
    }
}

function updateSlugPreview() {
    const slugEl = document.getElementById('slug');
    const previewEl = document.getElementById('slugFullPreview');
    const prefixEl = document.getElementById('slugFullPrefix');
    if (! slugEl || ! previewEl) return;

    const prefix = prefixEl?.dataset.prefix || '';
    const slug = slugEl.value.trim();
    previewEl.textContent = prefix ? `${prefix}/${slug}`.replace(/\/+/g, '/') : `/${slug}`;
}
</script>
