@php
    $itemSeo = is_array($itemSeo ?? null) ? $itemSeo : [
        'slug' => $itemSeo->slug ?? '',
        'slug_full' => $itemSeo->slug_full ?? '',
        'seo_title' => $itemSeo->seo_title ?? '',
        'seo_description' => $itemSeo->seo_description ?? '',
        'keywords' => $itemSeo->keywords ?? '',
        'parent_id' => $itemSeo->parent_id ?? ($seoEntry->parent_id ?? null),
        'rating_aggregate_count' => $seoEntry->rating_aggregate_count ?? null,
        'rating_aggregate_star' => $seoEntry->rating_aggregate_star ?? null,
    ];
    $language = $language ?? default_locale();
    $titleFieldId = $titleFieldId ?? 'title';
    $parents = $parents ?? collect();
    $showParent = $showParent ?? true;
    $selectedParentId = old('seo_parent_id', $itemSeo['parent_id'] ?? null);

    $parentOptions = ['' => '— Không chọn (trang gốc) —'];
    $parentSlugMap = ['' => ''];
    foreach ($parents as $parentEntry) {
        $trans = $parentEntry->translation($language) ?? $parentEntry->translation(default_locale());
        $label = ($trans?->title ?: ($trans?->seo_title ?: ('#'.$parentEntry->id)));
        $slugFull = $trans?->slug_full ?? '';
        $parentOptions[$parentEntry->id] = $label.($slugFull ? ' — '.$slugFull : '');
        $parentSlugMap[(string) $parentEntry->id] = $slugFull;
    }

    $currentParentSlug = $parentSlugMap[(string) ($selectedParentId ?? '')] ?? '';
@endphp

<div class="adminFormSeo">
    @include('admin.components.formField', [
        'label' => 'Tiêu đề SEO',
        'name' => 'seo_title',
        'type' => 'textarea',
        'required' => true,
        'value' => old('seo_title', $itemSeo['seo_title'] ?? ''),
        'tooltip' => 'Tiêu đề hiển thị trên Google. Nên 55–60 ký tự, chứa từ khóa chính.',
        'charCount' => true,
        'maxLength' => 255,
        'rows' => 2,
    ])

    @include('admin.components.formField', [
        'label' => 'Mô tả SEO',
        'name' => 'seo_description',
        'type' => 'textarea',
        'required' => true,
        'value' => old('seo_description', $itemSeo['seo_description'] ?? ''),
        'tooltip' => 'Mô tả hiển thị trên Google. Nên 140–160 ký tự.',
        'charCount' => true,
        'maxLength' => 320,
        'rows' => 4,
    ])

    {{-- Trang cha — Hitour pattern: slug_full = parent.slug_full / slug --}}
    @if ($showParent)
        <div class="adminFormField">
            <div class="adminFormField_labelWrapper">
                <label class="adminFormField_label" for="seo_parent_id">
                    <span class="adminFormField_tooltip" data-tooltip="Trang cha chứa trang hiện tại. URL (slug_full) sẽ nối theo cấp cha → con như Hitour.">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </span>
                    <span>Trang cha (phân tầng URL)</span>
                </label>
            </div>
            <select
                class="adminFormField_input adminFormField_input--select"
                id="seo_parent_id"
                name="seo_parent_id"
                onchange="onParentSeoChange(this)"
            >
                @foreach ($parentOptions as $value => $label)
                    <option
                        value="{{ $value }}"
                        data-slug-full="{{ $parentSlugMap[(string) $value] ?? '' }}"
                        @selected((string) $selectedParentId === (string) $value)
                    >{{ $label }}</option>
                @endforeach
            </select>
            <div class="adminFormField_help">
                Chọn trang cha để URL thành <code>{parent.slug_full}/{slug}</code>. Ví dụ: cha <code>/cruises</code> + slug <code>du-thuyen-ha-long</code> → <code>/cruises/du-thuyen-ha-long</code>; hoặc cha <code>/tours/viet-nam</code> + slug gói → <code>/tours/viet-nam/{slug}</code>.
            </div>
            @if ($parents->isEmpty())
                <div class="adminFormField_error">
                    Chưa có trang cha nào. Hãy tạo/lưu Hub (cấp 1) hoặc entity cha phù hợp trước.
                </div>
            @endif
        </div>
    @endif

    <div class="adminFormField adminFormField--required">
        <div class="adminFormField_labelWrapper">
            <label class="adminFormField_label" for="slug">
                <span class="adminFormField_tooltip" data-tooltip="Segment URL cuối cùng. Viết liền không dấu, ngăn cách bằng dấu gạch ngang.">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </span>
                <span>Đường dẫn tĩnh (slug)</span>
                <span class="adminFormField_required">*</span>
                <button type="button" class="adminFormField_chatgptReload" onclick="buildSlugFromTitle('{{ $titleFieldId }}', 'slug')" title="Tạo từ tiêu đề">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M2 12h20"/>
                    </svg>
                </button>
            </label>
        </div>
        <input
            type="text"
            id="slug"
            class="adminFormField_input"
            name="seo_slug"
            value="{{ old('seo_slug', $itemSeo['slug'] ?? '') }}"
            required
            oninput="updateSlugPreview()"
        >
        <div class="adminFormField_help">
            URL đầy đủ sẽ là:
            <strong id="slugFullPreview">
                @php
                    $previewSlug = old('seo_slug', $itemSeo['slug'] ?? '');
                    $previewFull = $itemSeo['slug_full'] ?? '';
                    if ($previewFull === '' && $previewSlug !== '') {
                        $previewFull = $currentParentSlug
                            ? rtrim($currentParentSlug, '/').'/'.$previewSlug
                            : '/'.$previewSlug;
                    }
                @endphp
                {{ $previewFull ?: '—' }}
            </strong>
            <span id="slugFullPrefix" data-prefix="{{ $currentParentSlug }}" hidden></span>
        </div>
    </div>

    @include('admin.components.formField', [
        'label' => 'Từ khóa',
        'name' => 'seo_keywords',
        'type' => 'text',
        'value' => old('seo_keywords', $itemSeo['keywords'] ?? ''),
    ])

    <div class="adminFormSeo_rating">
        <div class="adminFormSeo_rating_item">
            @include('admin.components.formField', [
                'label' => 'Lượt đánh giá',
                'name' => 'rating_aggregate_count',
                'type' => 'number',
                'value' => old('rating_aggregate_count', $itemSeo['rating_aggregate_count'] ?? ''),
                'tooltip' => 'Số lượt đánh giá hiển thị trên website và Google.',
            ])
        </div>
        <div class="adminFormSeo_rating_item">
            @include('admin.components.formField', [
                'label' => 'Điểm đánh giá',
                'name' => 'rating_aggregate_star',
                'type' => 'text',
                'value' => old('rating_aggregate_star', $itemSeo['rating_aggregate_star'] ?? ''),
                'tooltip' => 'Điểm đánh giá tương ứng (vd: 4.8).',
            ])
        </div>
    </div>
</div>

@push('scriptCustom')
<script>
function onParentSeoChange(selectEl) {
    const option = selectEl.options[selectEl.selectedIndex];
    const prefixEl = document.getElementById('slugFullPrefix');
    if (prefixEl) {
        prefixEl.dataset.prefix = option?.dataset?.slugFull || '';
    }
    updateSlugPreview();
}

function updateSlugPreview() {
    const slugEl = document.getElementById('slug');
    const previewEl = document.getElementById('slugFullPreview');
    const prefixEl = document.getElementById('slugFullPrefix');
    if (!slugEl || !previewEl) return;

    const slug = (slugEl.value || '').trim();
    const prefix = (prefixEl?.dataset?.prefix || '').replace(/\/+$/, '');

    if (!slug) {
        previewEl.textContent = '—';
        return;
    }

    previewEl.textContent = prefix
        ? (prefix.startsWith('/') ? prefix : '/' + prefix) + '/' + slug
        : '/' + slug.replace(/^\/+/, '');
}

document.addEventListener('DOMContentLoaded', updateSlugPreview);
</script>
@endpush
