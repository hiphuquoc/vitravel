@extends('layouts.admin')

@section('title', $title)

@section('content')
@php
    $language = $locale;
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale) : null;
@endphp

<form id="formAction" action="{{ route('admin.countries.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $country?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Điểm đến / khu vực làm trang cha listing tour — slug_full = /tours/{slug}. Không gắn vé tàu / máy bay.',
                'icon' => '<path d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3"/>',
                'backUrl' => route('admin.countries.list'),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $country ?? new \App\Models\Country(),
                    'language' => $language,
                    'routeName' => 'admin.countries.view',
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
                                    <path d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3"/>
                                </svg>
                            </div>
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin chung</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Mã quốc gia',
                                'name' => 'code',
                                'required' => true,
                                'value' => old('code', $country?->code),
                                'tooltip' => 'Dùng trong URL: /tours/{code}/...',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Kích thước lưới',
                                'name' => 'home_grid_size',
                                'value' => old('home_grid_size', $country?->home_grid_size ?? 'medium'),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Thứ tự',
                                'name' => 'sort',
                                'type' => 'number',
                                'value' => old('sort', $country?->sort ?? 0),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Hoạt động',
                                'name' => 'is_active',
                                'type' => 'checkbox',
                                'value' => old('is_active', $country?->is_active ?? true),
                                'checkboxLabel' => 'Đang hoạt động',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Hiện menu',
                                'name' => 'show_in_menu',
                                'type' => 'checkbox',
                                'value' => old('show_in_menu', $country?->show_in_menu ?? true),
                                'checkboxLabel' => 'Hiển thị trong menu',
                            ])
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M2 12h20"/>
                                </svg>
                            </div>
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin SEO</h2>
                                <p class="adminFormSection_description">
                                    Chọn trang cha (Hub Tour) → level = cha.level+1 → slug_full = {cha.slug_full}/{slug}.
                                </p>
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
                                    'parent_id' => old('seo_parent_id', $country?->seoEntry?->parent_id ?? $defaultParentId),
                                    'rating_aggregate_count' => $country?->seoEntry?->rating_aggregate_count,
                                    'rating_aggregate_star' => $country?->seoEntry?->rating_aggregate_star,
                                ],
                                'seoEntry' => $country?->seoEntry,
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
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Tên',
                                'name' => 'name',
                                'required' => true,
                                'value' => old('name', $translation?->name),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Slug nội dung',
                                'name' => 'slug',
                                'required' => true,
                                'value' => old('slug', $translation?->slug),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Tagline',
                                'name' => 'tagline',
                                'value' => old('tagline', $translation?->tagline),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Giới thiệu ngắn',
                                'name' => 'intro_text',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('intro_text', $translation?->intro_text),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Nội dung dài',
                                'name' => 'long_form_content',
                                'type' => 'textarea',
                                'rows' => 8,
                                'value' => old('long_form_content', $translation?->long_form_content),
                            ])
                        </div>
                    </div>
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.countries.list',
                                'viewUrl' => $viewUrl,
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'image',
                                'label' => 'Ảnh đại diện',
                                'currentImage' => $country?->bannerUrl(),
                                'removeName' => 'remove_image',
                                'aspectRatio' => '16/10',
                                'tooltip' => 'Ảnh thumbnail bento grid trang chủ.',
                                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Tự tối ưu về WebP ≤1920px.',
                            ])
                            <div style="margin-top:1.25rem;">
                                @include('admin.components.formImageUpload', [
                                    'name' => 'listing_banner',
                                    'label' => 'Ảnh banner listing',
                                    'currentImage' => $country?->listingBannerUrl('lg') ?: $country?->listingBannerUrl('full'),
                                    'removeName' => 'remove_listing_banner',
                                    'aspectRatio' => '21/9',
                                    'tooltip' => 'Banner ngang dài first-view trang /tours/{slug}. Khác ảnh đại diện trang chủ.',
                                    'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Nên dùng ảnh ngang (~1920×820).',
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
