@extends('layouts.admin')
@section('title', $title)
@section('content')
@php
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale ?? 'vi') : null;
    $categoryOptions = collect($categories)->mapWithKeys(fn ($c) => [$c->id => $c->name])->all();
    $countryOptions = collect($countries)->mapWithKeys(fn ($c) => [$c->id => ($c->translation($locale)?->name ?? $c->translation()?->name ?? $c->code)])->all();
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

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $service ?? new \App\Models\Service(),
                    'language' => $language,
                    'routeName' => 'admin.services.view',
                    'routeParams' => array_filter(['cluster' => $cluster ?: null]),
                ])
            </div>

            @if ($errors->any())
                <div class="adminFormPage_errors"><div class="adminFormPage_errors_content"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif

            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
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
                                    'label' => 'Danh mục',
                                    'name' => 'service_category_id',
                                    'type' => 'select',
                                    'value' => old('service_category_id', $service?->service_category_id),
                                    'options' => ['' => '— Không chọn —'] + $categoryOptions,
                                    'tooltip' => 'Danh mục trong cùng cụm — dùng làm trang cha SEO.',
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
                                @include('admin.components.formField', [
                                    'label' => 'Điểm đánh giá',
                                    'name' => 'rating',
                                    'type' => 'number',
                                    'value' => old('rating', $service?->rating ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Số lượt đánh giá',
                                    'name' => 'review_count',
                                    'type' => 'number',
                                    'value' => old('review_count', $service?->review_count ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Nhãn ưu đãi',
                                    'name' => 'discount_badge',
                                    'value' => old('discount_badge', $service?->discount_badge),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Nổi bật',
                                    'name' => 'is_featured',
                                    'type' => 'checkbox',
                                    'value' => old('is_featured', $service?->is_featured ?? false),
                                    'checkboxLabel' => 'Hiển thị nổi bật',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Hot deal',
                                    'name' => 'is_hot_deal',
                                    'type' => 'checkbox',
                                    'value' => old('is_hot_deal', $service?->is_hot_deal ?? false),
                                    'checkboxLabel' => 'Đánh dấu hot deal',
                                ])
                            </div>
                            <div class="mt-4 space-y-4">
                                @include('admin.components.formField', [
                                    'label' => 'Tóm tắt',
                                    'name' => 'summary',
                                    'type' => 'textarea',
                                    'rows' => 3,
                                    'value' => old('summary', $translation?->summary),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Nội dung',
                                    'name' => 'content',
                                    'type' => 'textarea',
                                    'rows' => 8,
                                    'value' => old('content', $translation?->content),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Điểm nổi bật (mỗi dòng 1 ý)',
                                    'name' => 'highlights',
                                    'type' => 'textarea',
                                    'rows' => 4,
                                    'value' => old('highlights', is_array($translation?->highlights) ? implode("\n", $translation->highlights) : ''),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Bao gồm',
                                    'name' => 'inclusions',
                                    'type' => 'textarea',
                                    'rows' => 4,
                                    'value' => old('inclusions', is_array($translation?->inclusions) ? implode("\n", $translation->inclusions) : ''),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Không bao gồm',
                                    'name' => 'exclusions',
                                    'type' => 'textarea',
                                    'rows' => 4,
                                    'value' => old('exclusions', is_array($translation?->exclusions) ? implode("\n", $translation->exclusions) : ''),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Ghi chú',
                                    'name' => 'notes',
                                    'type' => 'textarea',
                                    'rows' => 3,
                                    'value' => old('notes', is_array($translation?->notes) ? implode("\n", $translation->notes) : ''),
                                ])
                            </div>
                        </div>
                    </div>

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
