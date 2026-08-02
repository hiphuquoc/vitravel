@extends('layouts.admin')
@section('title', $title)
@section('content')
@php
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale ?? 'vi') : null;
@endphp
<form id="formAction" action="{{ route('admin.cruiseTypes.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $type?->id }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Loại du thuyền — chọn Hub Du thuyền làm cha → slug_full = {hub}/{slug}.',
                'icon' => '<path d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3"/>',
                'backUrl' => route('admin.cruiseTypes.list'),
                'backText' => 'Quay lại',
            ])
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
                                    'label' => 'Tên hiển thị',
                                    'name' => 'name',
                                    'required' => true,
                                    'value' => old('name', $type?->name),
                                    'tooltip' => 'VD: Du thuyền Hạ Long — hiện trên nav và H1 listing.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Slug (đồng bộ SEO)',
                                    'name' => 'slug',
                                    'required' => true,
                                    'value' => old('slug', $type?->slug),
                                    'tooltip' => 'Khớp packages.cruise_type và seo_slug.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $type?->sort ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trạng thái',
                                    'name' => 'is_active',
                                    'type' => 'checkbox',
                                    'value' => old('is_active', $type?->is_active ?? true),
                                    'checkboxLabel' => 'Đang hiển thị',
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin SEO</h2>
                                <p class="adminFormSection_description">
                                    Chọn trang cha (Hub Du thuyền) → level = cha.level+1 → slug_full = {cha.slug_full}/{slug}.
                                </p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formSeo', [
                                'itemSeo' => [
                                    'slug' => old('seo_slug', $seoTranslation?->slug ?? $type?->slug),
                                    'slug_full' => $seoTranslation?->slug_full,
                                    'seo_title' => old('seo_title', $seoTranslation?->seo_title),
                                    'seo_description' => old('seo_description', $seoTranslation?->seo_description),
                                    'keywords' => old('seo_keywords', $seoTranslation?->keywords),
                                    'parent_id' => old('seo_parent_id', $type?->seoEntry?->parent_id ?? $defaultParentId),
                                    'rating_aggregate_count' => $type?->seoEntry?->rating_aggregate_count,
                                    'rating_aggregate_star' => $type?->seoEntry?->rating_aggregate_star,
                                ],
                                'seoEntry' => $type?->seoEntry,
                                'language' => $locale ?? 'vi',
                                'parents' => $parents,
                                'showParent' => true,
                                'titleFieldId' => 'name',
                            ])
                        </div>
                    </div>
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.cruiseTypes.list',
                                'viewUrl' => $viewUrl,
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'cover',
                                'label' => 'Ảnh đại diện',
                                'currentImage' => $type?->coverUrl('card') ?: $type?->coverUrl(),
                                'removeName' => 'remove_cover',
                                'aspectRatio' => '3/2',
                                'tooltip' => 'Thumbnail card / chia sẻ — khác banner listing.',
                                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB.',
                            ])
                            <div style="margin-top:1.25rem;">
                                @include('admin.components.formImageUpload', [
                                    'name' => 'image',
                                    'label' => 'Ảnh banner listing',
                                    'currentImage' => $type?->bannerUrl('lg') ?: $type?->bannerUrl(),
                                    'removeName' => 'remove_image',
                                    'aspectRatio' => '21/9',
                                    'tooltip' => 'Banner first-view trang listing loại du thuyền.',
                                    'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Tự tối ưu WebP + variants.',
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
