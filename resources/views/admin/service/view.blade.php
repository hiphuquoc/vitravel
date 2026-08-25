@extends('layouts.admin')
@section('title', $title)

@push('headCustom')
<style>
.adminMultiSelectBox {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: #fff;
    border: 1px solid var(--admin-gray-200, #e5e7eb);
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.adminMultiSelect_chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    min-height: 32px;
    align-items: center;
}
.adminMultiSelect_chip {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    background: var(--admin-gray-50, #f9fafb);
    border: 1px solid var(--admin-gray-300, #d1d5db);
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--admin-gray-800, #1f2937);
    transition: all 0.15s ease;
}
.adminMultiSelect_chip.is-primary {
    background: #fefce8;
    border-color: #fde047;
    color: #854d0e;
}
.adminMultiSelect_chip_star {
    font-size: 0.6875rem;
    font-weight: 700;
    color: #ca8a04;
    background: #fef08a;
    padding: 1px 6px;
    border-radius: 4px;
}
.adminMultiSelect_chip_remove {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    margin-left: 2px;
    border-radius: 4px;
    transition: color 0.15s;
    background: none;
    border: 0;
    padding: 0;
}
.adminMultiSelect_chip_remove:hover {
    color: #ef4444;
}
.adminMultiSelect_dropdown {
    border: 1px solid var(--admin-gray-200, #e5e7eb);
    border-radius: 8px;
    background: #fafafa;
    overflow: hidden;
}
.adminMultiSelect_searchWrap {
    position: relative;
    border-bottom: 1px solid var(--admin-gray-200, #e5e7eb);
    background: #fff;
}
.adminMultiSelect_searchIcon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: #9ca3af;
}
.adminMultiSelect_searchInput {
    width: 100%;
    padding: 0.625rem 0.75rem 0.625rem 2.25rem;
    border: 0;
    font-size: 0.8125rem;
    outline: none;
    background: transparent;
}
.adminMultiSelect_searchInput:focus {
    background: #fff;
}
.adminMultiSelect_list {
    max-height: 220px;
    overflow-y: auto;
    padding: 0.375rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.adminMultiSelect_item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.4375rem 0.625rem;
    border-radius: 6px;
    transition: background 0.15s;
}
.adminMultiSelect_item:hover {
    background: #f3f4f6;
}
.adminMultiSelect_item.is-selected {
    background: #eff6ff;
}
.adminMultiSelect_item_check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    flex: 1;
    min-width: 0;
}
.adminMultiSelect_item_checkbox {
    width: 16px;
    height: 16px;
    accent-color: #2563eb;
    cursor: pointer;
}
.adminMultiSelect_item_name {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #1f2937;
}
.adminMultiSelect_item_slug {
    font-size: 0.75rem;
    color: #6b7280;
}
.adminMultiSelect_starBtn {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: #fff;
    font-size: 0.6875rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.15s;
}
.adminMultiSelect_starBtn:hover {
    border-color: #fde047;
    color: #ca8a04;
}
.adminMultiSelect_starBtn.is-primary {
    background: #fefce8;
    border-color: #fde047;
    color: #854d0e;
}
.adminMultiSelect_starIcon {
    width: 14px;
    height: 14px;
}
</style>
@endpush

@section('content')
@php
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale ?? 'vi') : null;
    $countryOptions = collect($countries)->mapWithKeys(fn ($c) => [$c->id => ($c->translation($locale)?->name ?? $c->translation()?->name ?? $c->code)])->all();

    // Dữ liệu danh mục đã chọn
    $selectedCategoryIds = old(
        'service_category_ids',
        $service?->relationLoaded('categories') && $service->categories->isNotEmpty()
            ? $service->categories->pluck('id')->all()
            : ($service?->service_category_id ? [$service->service_category_id] : [])
    );
    $primaryCatId = old('service_category_id', $service?->service_category_id ?? ($selectedCategoryIds[0] ?? null));

    // Dữ liệu loại hình lưu trú (property_types)
    $serviceAttrs = is_array($service?->attrs) ? $service->attrs : [];
    $rawPropType = $serviceAttrs['property_type'] ?? 'hotel';
    $rawPropTypes = $serviceAttrs['property_types'] ?? [$rawPropType];
    $selectedPropertyTypes = old('property_types', (array)$rawPropTypes);
    $primaryPropertyType = old('property_type', $rawPropType ?: ($selectedPropertyTypes[0] ?? 'hotel'));
    $propertyTypeOptions = $propertyTypeOptions ?? config('stay.property_types', []);
@endphp

<form id="formAction" action="{{ route('admin.services.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $service?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Sản phẩm dịch vụ — cha là danh mục hoặc hub cụm.',
                'icon' => '<path d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12"/>',
                'backUrl' => route('admin.services.list', array_filter(['cluster' => $cluster ?: null])),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    {{-- 1. THÔNG TIN CHUNG --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin chung</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', [
                                    'label' => 'Cụm dịch vụ',
                                    'name' => 'cluster',
                                    'type' => 'select',
                                    'required' => true,
                                    'value' => old('cluster', $service?->cluster ?? $cluster),
                                    'options' => $clusterOptions,
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Tiêu đề',
                                    'name' => 'title',
                                    'required' => true,
                                    'value' => old('title', $translation?->title),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Mã',
                                    'name' => 'code',
                                    'value' => old('code', $service?->code),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Địa điểm hiển thị',
                                    'name' => 'location_label',
                                    'value' => old('location_label', $translation?->location_label),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Quốc gia',
                                    'name' => 'country_id',
                                    'type' => 'select',
                                    'value' => old('country_id', $service?->country_id),
                                    'options' => ['' => '— Không chọn —'] + $countryOptions,
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Giá từ',
                                    'name' => 'price_from',
                                    'type' => 'number',
                                    'value' => old('price_from', $service?->price_from),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Tiền tệ',
                                    'name' => 'currency',
                                    'value' => old('currency', $service?->currency ?? 'VND'),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Hạng sao (KS)',
                                    'name' => 'star_rating',
                                    'type' => 'number',
                                    'value' => old('star_rating', $service?->star_rating),
                                    'tooltip' => '1–5 cho khách sạn/resort.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trạng thái',
                                    'name' => 'status',
                                    'type' => 'select',
                                    'required' => true,
                                    'value' => old('status', $service?->status ?? 'draft'),
                                    'options' => ['draft' => 'Nháp', 'published' => 'Xuất bản', 'archived' => 'Lưu trữ'],
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $service?->sort ?? 0),
                                ])
                                <div class="adminFormField adminFormField--checkbox">
                                    <label class="adminFormField_checkbox">
                                        <input type="checkbox" class="adminFormField_checkbox_input" name="is_featured" value="1"
                                            @checked(old('is_featured', $service?->is_featured))>
                                        <span class="adminFormField_checkbox_label">Nổi bật (trang chủ / hub)</span>
                                    </label>
                                </div>
                                <div class="adminFormField adminFormField--checkbox">
                                    <label class="adminFormField_checkbox">
                                        <input type="checkbox" class="adminFormField_checkbox_input" name="is_hot_deal" value="1"
                                            @checked(old('is_hot_deal', $service?->is_hot_deal))>
                                        <span class="adminFormField_checkbox_label">Hot deal</span>
                                    </label>
                                </div>

                                {{-- Custom Multi-Select: DANH MỤC LƯU TRÚ (SỐ NHIỀU) --}}
                                <div class="adminFormField adminFormField--full" style="grid-column: 1 / -1;">
                                    <label class="adminFormField_label">
                                        <span>Danh mục / Khu vực lưu trú (Chọn nhiều)</span>
                                        <span class="adminFormField_tooltip" title="Khách sạn có thể thuộc nhiều danh mục/khu vực. Danh mục có biểu tượng ngôi sao là danh mục chính (Primary) dùng làm trang cha SEO & URL.">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
                                        </span>
                                    </label>

                                    <div class="adminMultiSelectBox" id="adminCatMultiSelect">
                                        {{-- Dải thẻ danh mục đã chọn (Selected Chips) --}}
                                        <div class="adminMultiSelect_chips" id="catChipsContainer"></div>

                                        {{-- Khung tìm kiếm và danh sách chọn --}}
                                        <div class="adminMultiSelect_dropdown">
                                            <div class="adminMultiSelect_searchWrap">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="adminMultiSelect_searchIcon">
                                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                                </svg>
                                                <input type="text" id="catSearchInput" placeholder="Tìm nhanh danh mục / khu vực..." class="adminMultiSelect_searchInput" autocomplete="off" />
                                            </div>

                                            <div class="adminMultiSelect_list" id="catOptionsList">
                                                @foreach ($categories as $cat)
                                                    @php
                                                        $isSelected = in_array($cat->id, $selectedCategoryIds, false);
                                                        $isPrimary = ((int)$primaryCatId === (int)$cat->id);
                                                    @endphp
                                                    <div class="adminMultiSelect_item {{ $isSelected ? 'is-selected' : '' }}"
                                                         data-id="{{ $cat->id }}"
                                                         data-name="{{ $cat->name }}"
                                                         data-slug="{{ $cat->slug }}">
                                                        <label class="adminMultiSelect_item_check">
                                                            <input type="checkbox"
                                                                class="adminMultiSelect_item_checkbox js-cat-check"
                                                                value="{{ $cat->id }}"
                                                                data-name="{{ $cat->name }}"
                                                                @checked($isSelected)
                                                            />
                                                            <span class="adminMultiSelect_item_name">{{ $cat->name }}</span>
                                                            <span class="adminMultiSelect_item_slug">/{{ $cat->slug }}</span>
                                                        </label>

                                                        <button type="button"
                                                            class="adminMultiSelect_starBtn js-cat-primary-btn {{ $isPrimary ? 'is-primary' : '' }}"
                                                            data-id="{{ $cat->id }}"
                                                            title="{{ $isPrimary ? 'Danh mục chính (Primary SEO)' : 'Đặt làm danh mục chính' }}">
                                                            <svg viewBox="0 0 24 24" fill="{{ $isPrimary ? '#eab308' : 'none' }}" stroke="{{ $isPrimary ? '#ca8a04' : 'currentColor' }}" stroke-width="2" class="adminMultiSelect_starIcon">
                                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                            </svg>
                                                            <span class="adminMultiSelect_starText">{{ $isPrimary ? 'Chính' : 'Đặt chính' }}</span>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Hidden inputs để submit form --}}
                                        <input type="hidden" name="service_category_id" id="hidden_primary_cat_id" value="{{ $primaryCatId }}" />
                                        <div id="hiddenCatInputsContainer">
                                            @foreach ($selectedCategoryIds as $cid)
                                                <input type="hidden" name="service_category_ids[]" value="{{ $cid }}" />
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('service_category_ids')
                                        <p class="adminFormField_error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. THÔNG TIN LƯU TRÚ (HIỂN THỊ KHI CỤM LÀ STAY / KHÁCH SẠN) --}}
                    @if ($cluster === 'stay' || ($service?->cluster ?? '') === 'stay')
                        <div class="adminFormSection">
                            <div class="adminFormSection_header">
                                <div class="adminFormSection_header_info">
                                    <h2 class="adminFormSection_title">Thông tin lưu trú & Loại hình khách sạn</h2>
                                </div>
                            </div>
                            <div class="adminFormSection_body">
                                <div class="adminFormGrid adminFormGrid--2cols">
                                    {{-- Custom Multi-Select: LOẠI HÌNH KHÁCH SẠN (SỐ NHIỀU) --}}
                                    <div class="adminFormField adminFormField--full" style="grid-column: 1 / -1;">
                                        <label class="adminFormField_label">
                                            <span>Loại hình khách sạn / Chỗ nghỉ (Chọn nhiều)</span>
                                            <span class="adminFormField_tooltip" title="Chọn một hoặc nhiều loại hình (Resort, Khách sạn, Villa, Homestay...). Ngôi sao chỉ định loại hình đại diện chính.">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
                                            </span>
                                        </label>

                                        <div class="adminMultiSelectBox" id="adminPropTypeMultiSelect">
                                            {{-- Dải thẻ loại hình đã chọn (Selected Chips) --}}
                                            <div class="adminMultiSelect_chips" id="propTypeChipsContainer"></div>

                                            {{-- Khung tìm kiếm và danh sách chọn loại hình --}}
                                            <div class="adminMultiSelect_dropdown">
                                                <div class="adminMultiSelect_searchWrap">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="adminMultiSelect_searchIcon">
                                                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                                    </svg>
                                                    <input type="text" id="propTypeSearchInput" placeholder="Tìm kiếm loại hình (Resort, Khách sạn, Villa...)..." class="adminMultiSelect_searchInput" autocomplete="off" />
                                                </div>

                                                <div class="adminMultiSelect_list" id="propTypeOptionsList">
                                                    @foreach ($propertyTypeOptions as $ptKey => $ptLabel)
                                                        @php
                                                            $isPtSelected = in_array((string)$ptKey, array_map('strval', $selectedPropertyTypes), false);
                                                            $isPtPrimary = ((string)$primaryPropertyType === (string)$ptKey);
                                                        @endphp
                                                        <div class="adminMultiSelect_item {{ $isPtSelected ? 'is-selected' : '' }}"
                                                             data-id="{{ $ptKey }}"
                                                             data-name="{{ $ptLabel }}">
                                                            <label class="adminMultiSelect_item_check">
                                                                <input type="checkbox"
                                                                    class="adminMultiSelect_item_checkbox js-pt-check"
                                                                    value="{{ $ptKey }}"
                                                                    data-name="{{ $ptLabel }}"
                                                                    @checked($isPtSelected)
                                                                />
                                                                <span class="adminMultiSelect_item_name">{{ $ptLabel }}</span>
                                                                <span class="adminMultiSelect_item_slug">({{ $ptKey }})</span>
                                                            </label>

                                                            <button type="button"
                                                                class="adminMultiSelect_starBtn js-pt-primary-btn {{ $isPtPrimary ? 'is-primary' : '' }}"
                                                                data-id="{{ $ptKey }}"
                                                                title="{{ $isPtPrimary ? 'Loại hình đại diện chính' : 'Đặt làm loại hình chính' }}">
                                                                <svg viewBox="0 0 24 24" fill="{{ $isPtPrimary ? '#eab308' : 'none' }}" stroke="{{ $isPtPrimary ? '#ca8a04' : 'currentColor' }}" stroke-width="2" class="adminMultiSelect_starIcon">
                                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                                </svg>
                                                                <span class="adminMultiSelect_starText">{{ $isPtPrimary ? 'Chính' : 'Đặt chính' }}</span>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Hidden inputs loại hình để submit form --}}
                                            <input type="hidden" name="property_type" id="hidden_primary_pt_id" value="{{ $primaryPropertyType }}" />
                                            <div id="hiddenPtInputsContainer">
                                                @foreach ($selectedPropertyTypes as $ptVal)
                                                    <input type="hidden" name="property_types[]" value="{{ $ptVal }}" />
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    @include('admin.components.formField', [
                                        'label' => 'Giờ nhận phòng (Check-in)',
                                        'name' => 'checkin_from',
                                        'value' => old('checkin_from', $serviceAttrs['checkin_from'] ?? '14:00'),
                                        'placeholder' => '14:00',
                                    ])
                                    @include('admin.components.formField', [
                                        'label' => 'Giờ trả phòng (Check-out)',
                                        'name' => 'checkout_until',
                                        'value' => old('checkout_until', $serviceAttrs['checkout_until'] ?? '12:00'),
                                        'placeholder' => '12:00',
                                    ])
                                    @include('admin.components.formField', [
                                        'label' => 'Địa chỉ chi tiết',
                                        'name' => 'address',
                                        'value' => old('address', $serviceAttrs['address'] ?? ($translation?->location_label ?? '')),
                                        'placeholder' => 'Đường Trần Hưng Đạo, Dương Đông, Phú Quốc...',
                                    ])
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 3. NỘI DUNG & ĐẶC TÍNH --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nội dung & Đặc tính</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Tóm tắt',
                                'name' => 'summary',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('summary', $translation?->summary),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Nội dung chi tiết',
                                'name' => 'content',
                                'type' => 'textarea',
                                'rows' => 8,
                                'value' => old('content', $translation?->content),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Điểm nổi bật (mỗi dòng một ý)',
                                'name' => 'highlights',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('highlights', is_array($translation?->highlights) ? implode("\n", $translation->highlights) : ''),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Bao gồm (mỗi dòng một ý)',
                                'name' => 'inclusions',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('inclusions', is_array($translation?->inclusions) ? implode("\n", $translation->inclusions) : ''),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Không bao gồm (mỗi dòng một ý)',
                                'name' => 'exclusions',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('exclusions', is_array($translation?->exclusions) ? implode("\n", $translation->exclusions) : ''),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Lưu ý (mỗi dòng một ý)',
                                'name' => 'notes',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('notes', is_array($translation?->notes) ? implode("\n", $translation->notes) : ''),
                            ])
                        </div>
                    </div>

                    {{-- 4. SEO & PARENT URL --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin SEO</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formSeo', [
                                'itemSeo' => [
                                    'slug' => old('seo_slug', $seoTranslation?->slug),
                                    'slug_full' => $seoTranslation?->slug_full,
                                    'seo_title' => old('seo_title', $seoTranslation?->seo_title),
                                    'seo_description' => old('seo_description', $seoTranslation?->seo_description),
                                    'keywords' => old('seo_keywords', $seoTranslation?->keywords),
                                    'parent_id' => old('seo_parent_id', $service?->seoEntry?->parent_id ?? $defaultParentId),
                                    'rating_aggregate_count' => $service?->seoEntry?->rating_aggregate_count ?? $service?->review_count,
                                    'rating_aggregate_star' => $service?->seoEntry?->rating_aggregate_star ?? $service?->rating,
                                ],
                                'seoEntry' => $service?->seoEntry,
                                'language' => $locale ?? 'vi',
                                'parents' => $parents,
                                'showParent' => true,
                                'titleFieldId' => 'title',
                            ])
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.services.list',
                                'viewUrl' => $viewUrl,
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'image',
                                'label' => 'Ảnh cover',
                                'currentImage' => $service?->coverUrl('lg') ?: $service?->coverUrl(),
                                'removeName' => 'remove_image',
                                'aspectRatio' => '4/3',
                                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB.',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scriptCustom')
<script>
(function() {
    // 1. SETUP MULTI-SELECT CHO DANH MỤC (CATEGORIES)
    function setupMultiSelect(config) {
        const container = document.getElementById(config.containerId);
        if (!container) return;

        const chipsContainer = document.getElementById(config.chipsContainerId);
        const searchInput = document.getElementById(config.searchInputId);
        const optionsList = document.getElementById(config.optionsListId);
        const hiddenPrimaryInput = document.getElementById(config.hiddenPrimaryInputId);
        const hiddenInputsContainer = document.getElementById(config.hiddenInputsContainerId);

        function render() {
            const selectedCheckboxes = optionsList.querySelectorAll(`.${config.checkboxClass}:checked`);
            chipsContainer.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';

            let currentPrimary = hiddenPrimaryInput.value;
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedIds.length > 0 && (!currentPrimary || !selectedIds.includes(String(currentPrimary)))) {
                currentPrimary = selectedIds[0];
                hiddenPrimaryInput.value = currentPrimary;
            } else if (selectedIds.length === 0) {
                currentPrimary = '';
                hiddenPrimaryInput.value = '';
            }

            if (selectedCheckboxes.length === 0) {
                chipsContainer.innerHTML = `<span style="color: #9ca3af; font-size: 0.8125rem; font-style: italic;">${config.emptyText}</span>`;
            }

            selectedCheckboxes.forEach(cb => {
                const id = cb.value;
                const name = cb.dataset.name || cb.value;
                const isPrimary = String(id) === String(currentPrimary);

                const chip = document.createElement('div');
                chip.className = 'adminMultiSelect_chip' + (isPrimary ? ' is-primary' : '');
                chip.innerHTML = `
                    <span>${name}</span>
                    ${isPrimary ? '<span class="adminMultiSelect_chip_star">★ ' + config.primaryBadgeText + '</span>' : ''}
                    <button type="button" class="adminMultiSelect_chip_remove" data-id="${id}" title="Bỏ chọn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 12px; height: 12px;"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                `;
                chipsContainer.appendChild(chip);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = config.inputName;
                input.value = id;
                hiddenInputsContainer.appendChild(input);
            });

            optionsList.querySelectorAll('.adminMultiSelect_item').forEach(item => {
                const id = item.dataset.id;
                const isChecked = selectedIds.includes(String(id));
                const isPrimary = String(id) === String(currentPrimary);

                item.classList.toggle('is-selected', isChecked);

                const starBtn = item.querySelector(`.${config.primaryBtnClass}`);
                if (starBtn) {
                    starBtn.classList.toggle('is-primary', isPrimary);
                    starBtn.style.display = isChecked ? 'inline-flex' : 'none';
                    const starIcon = starBtn.querySelector('svg');
                    if (starIcon) {
                        starIcon.setAttribute('fill', isPrimary ? '#eab308' : 'none');
                        starIcon.setAttribute('stroke', isPrimary ? '#ca8a04' : 'currentColor');
                    }
                    const starText = starBtn.querySelector('.adminMultiSelect_starText');
                    if (starText) {
                        starText.textContent = isPrimary ? config.primaryBadgeText : config.setPrimaryText;
                    }
                }
            });
        }

        optionsList.addEventListener('change', function(e) {
            if (e.target.classList.contains(config.checkboxClass)) {
                render();
            }
        });

        optionsList.addEventListener('click', function(e) {
            const starBtn = e.target.closest(`.${config.primaryBtnClass}`);
            if (starBtn) {
                e.preventDefault();
                const id = starBtn.dataset.id;
                hiddenPrimaryInput.value = id;
                render();
            }
        });

        chipsContainer.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.adminMultiSelect_chip_remove');
            if (removeBtn) {
                e.preventDefault();
                const id = removeBtn.dataset.id;
                const cb = optionsList.querySelector(`.${config.checkboxClass}[value="${id}"]`);
                if (cb) {
                    cb.checked = false;
                    render();
                }
            }
        });

        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            optionsList.querySelectorAll('.adminMultiSelect_item').forEach(item => {
                const name = (item.dataset.name || '').toLowerCase();
                const id = (item.dataset.id || '').toLowerCase();
                const slug = (item.dataset.slug || '').toLowerCase();
                if (!q || name.includes(q) || id.includes(q) || slug.includes(q)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        render();
    }

    // Khởi tạo Danh mục
    setupMultiSelect({
        containerId: 'adminCatMultiSelect',
        chipsContainerId: 'catChipsContainer',
        searchInputId: 'catSearchInput',
        optionsListId: 'catOptionsList',
        hiddenPrimaryInputId: 'hidden_primary_cat_id',
        hiddenInputsContainerId: 'hiddenCatInputsContainer',
        checkboxClass: 'js-cat-check',
        primaryBtnClass: 'js-cat-primary-btn',
        inputName: 'service_category_ids[]',
        primaryBadgeText: 'Chính',
        setPrimaryText: 'Đặt chính',
        emptyText: 'Chưa chọn danh mục nào (sẽ hiển thị ở tất cả hoặc danh mục mặc định)',
    });

    // Khởi tạo Loại hình lưu trú (Stay Property Types)
    setupMultiSelect({
        containerId: 'adminPropTypeMultiSelect',
        chipsContainerId: 'propTypeChipsContainer',
        searchInputId: 'propTypeSearchInput',
        optionsListId: 'propTypeOptionsList',
        hiddenPrimaryInputId: 'hidden_primary_pt_id',
        hiddenInputsContainerId: 'hiddenPtInputsContainer',
        checkboxClass: 'js-pt-check',
        primaryBtnClass: 'js-pt-primary-btn',
        inputName: 'property_types[]',
        primaryBadgeText: 'Loại hình chính',
        setPrimaryText: 'Đặt chính',
        emptyText: 'Chưa chọn loại hình lưu trú nào',
    });
})();
</script>
@endpush
