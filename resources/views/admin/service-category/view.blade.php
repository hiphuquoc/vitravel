@extends('layouts.admin')
@section('title', $title)
@section('content')
@php
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale ?? 'vi') : null;
@endphp
<form id="formAction" action="{{ route('admin.serviceCategories.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $category?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Danh mục dịch vụ — chọn Hub cụm làm cha → slug_full = {hub}/{slug}.',
                'icon' => '<path d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125v-1.5M3.375 19.5h-.375A1.125 1.125 0 012.25 18.375v-1.5m15.75 0h.375a1.125 1.125 0 001.125-1.125v-1.5m-15.75 0v-3.375A1.125 1.125 0 014.125 12h15.75a1.125 1.125 0 011.125 1.125v3.375"/>',
                'backUrl' => route('admin.serviceCategories.list', array_filter(['cluster' => $cluster ?: null])),
                'backText' => 'Quay lại',
            ])
            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $category ?? new \App\Models\ServiceCategory(),
                    'language' => $locale,
                    'routeName' => 'admin.serviceCategories.view',
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
                                    'value' => old('cluster', $category?->cluster ?? $cluster),
                                    'options' => $clusterOptions,
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Tên hiển thị',
                                    'name' => 'name',
                                    'required' => true,
                                    'value' => old('name', $category?->name),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Slug (đồng bộ SEO)',
                                    'name' => 'slug',
                                    'required' => true,
                                    'value' => old('slug', $category?->slug),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $category?->sort ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trạng thái',
                                    'name' => 'is_active',
                                    'type' => 'checkbox',
                                    'value' => old('is_active', $category?->is_active ?? true),
                                    'checkboxLabel' => 'Đang hiển thị',
                                ])
                            </div>
                            <div class="mt-4">
                                @include('admin.components.formField', [
                                    'label' => 'Mô tả ngắn',
                                    'name' => 'intro',
                                    'type' => 'textarea',
                                    'rows' => 3,
                                    'value' => old('intro', $category?->intro),
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin SEO</h2>
                                <p class="adminFormSection_description">
                                    Chọn trang cha (Hub cụm dịch vụ) → level = cha.level+1 → slug_full = {cha.slug_full}/{slug}.
                                </p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formSeo', [
                                'itemSeo' => [
                                    'slug' => old('seo_slug', $seoTranslation?->slug ?? $category?->slug),
                                    'slug_full' => $seoTranslation?->slug_full,
                                    'seo_title' => old('seo_title', $seoTranslation?->seo_title),
                                    'seo_description' => old('seo_description', $seoTranslation?->seo_description),
                                    'keywords' => old('seo_keywords', $seoTranslation?->keywords),
                                    'parent_id' => old('seo_parent_id', $category?->seoEntry?->parent_id ?? $defaultParentId),
                                    'rating_aggregate_count' => $category?->seoEntry?->rating_aggregate_count,
                                    'rating_aggregate_star' => $category?->seoEntry?->rating_aggregate_star,
                                ],
                                'seoEntry' => $category?->seoEntry,
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
                                'backRoute' => 'admin.serviceCategories.list',
                                'viewUrl' => $viewUrl,
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'image',
                                'label' => 'Ảnh banner listing',
                                'currentImage' => $category?->bannerUrl('lg') ?: $category?->bannerUrl(),
                                'removeName' => 'remove_image',
                                'aspectRatio' => '21/9',
                                'tooltip' => 'Banner first-view trang listing danh mục.',
                                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Tự tối ưu WebP + variants.',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
