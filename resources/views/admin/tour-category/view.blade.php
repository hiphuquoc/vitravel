@extends('layouts.admin')

@section('title', $title)

@section('content')
@php
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale) : null;
    $countryOptions = $countries->mapWithKeys(fn ($c) => [$c->id => $c->name])->all();
@endphp

<form id="formAction" action="{{ route('admin.tourCategories.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $category?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Danh mục tour dùng cho trang listing (VD: Tour 10 ngày Việt Nam). URL thường nằm dưới quốc gia.',
                'icon' => '<path d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125v-1.5M3.375 19.5h-.375A1.125 1.125 0 012.25 18.375v-1.5m15.75 0h.375a1.125 1.125 0 001.125-1.125v-1.5m-15.75 0v-3.375A1.125 1.125 0 014.125 12h15.75a1.125 1.125 0 011.125 1.125v3.375"/>',
                'backUrl' => route('admin.tourCategories.list'),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $category ?? new \App\Models\TourCategory(),
                    'language' => $language,
                    'routeName' => 'admin.tourCategories.view',
                ])
            </div>

            @if ($errors->any())
                <div class="adminFormPage_errors">
                    <div class="adminFormPage_errors_icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <div class="adminFormPage_errors_content">
                        <h3>Có lỗi xảy ra:</h3>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin chung</h2>
                                <p class="adminFormSection_description">Cấu hình loại danh mục và quốc gia liên kết.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', [
                                    'label' => 'Quốc gia',
                                    'name' => 'country_id',
                                    'type' => 'select',
                                    'value' => old('country_id', $category?->country_id),
                                    'options' => $countryOptions,
                                    'placeholder' => '— Chọn quốc gia —',
                                    'tooltip' => 'Quốc gia của danh mục. Mặc định dùng làm trang cha SEO.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Loại danh mục',
                                    'name' => 'type',
                                    'type' => 'select',
                                    'required' => true,
                                    'value' => old('type', $category?->type ?? \App\Models\TourCategory::TYPE_THEME),
                                    'options' => $typeOptions,
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $category?->sort ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Hoạt động',
                                    'name' => 'is_active',
                                    'type' => 'checkbox',
                                    'value' => old('is_active', $category?->is_active ?? true),
                                    'checkboxLabel' => 'Hiển thị danh mục',
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    <path d="M2 12h20"/>
                                </svg>
                            </div>
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin SEO & URL phân tầng</h2>
                                <p class="adminFormSection_description">Chọn trang cha (thường là quốc gia) để URL dạng /tours/{quoc-gia}/{slug-danh-muc}.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formSeo', [
                                'itemSeo' => [
                                    'slug' => old('seo_slug', $seoTranslation?->slug ?? $translation?->slug),
                                    'slug_full' => $seoTranslation?->slug_full,
                                    'seo_title' => old('seo_title', $seoTranslation?->seo_title),
                                    'seo_description' => old('seo_description', $seoTranslation?->seo_description),
                                    'keywords' => old('seo_keywords', $seoTranslation?->keywords),
                                    'parent_id' => old('seo_parent_id', $category?->seoEntry?->parent_id ?? $category?->country?->seoEntry?->id),
                                    'rating_aggregate_count' => old('rating_aggregate_count', $category?->seoEntry?->rating_aggregate_count),
                                    'rating_aggregate_star' => old('rating_aggregate_star', $category?->seoEntry?->rating_aggregate_star),
                                ],
                                'seoEntry' => $category?->seoEntry,
                                'language' => $locale,
                                'parents' => $parents,
                                'showParent' => true,
                                'titleFieldId' => 'name',
                            ])
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5"/>
                                </svg>
                            </div>
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nội dung ({{ strtoupper($locale) }})</h2>
                                <p class="adminFormSection_description">Tên, mô tả và đoạn SEO intro hiển thị trên trang listing.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Tên danh mục',
                                'name' => 'name',
                                'type' => 'textarea',
                                'required' => true,
                                'rows' => 2,
                                'charCount' => true,
                                'maxLength' => 255,
                                'value' => old('name', $translation?->name),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Slug nội dung',
                                'name' => 'slug',
                                'required' => true,
                                'value' => old('slug', $translation?->slug),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Mô tả',
                                'name' => 'description',
                                'type' => 'textarea',
                                'rows' => 5,
                                'value' => old('description', $translation?->description),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'SEO intro (dưới danh sách tour)',
                                'name' => 'seo_intro',
                                'type' => 'textarea',
                                'rows' => 6,
                                'value' => old('seo_intro', $translation?->seo_intro),
                                'tooltip' => 'Đoạn văn SEO ngắn hiển thị cuối trang listing tour.',
                            ])
                        </div>
                    </div>
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.tourCategories.list',
                                'viewUrl' => $viewUrl,
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'image',
                                'label' => 'Ảnh đại diện',
                                'currentImage' => $category?->coverUrl(),
                                'removeName' => 'remove_image',
                                'aspectRatio' => '16/10',
                                'tooltip' => 'Ảnh đại diện danh mục tour.',
                                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Tự tối ưu về WebP ≤1920px.',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
