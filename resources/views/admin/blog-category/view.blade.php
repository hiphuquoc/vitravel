@extends('layouts.admin')

@section('title', $title)

@section('content')
@php
    $language = $locale;
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale) : null;
@endphp

<form id="formAction" action="{{ route('admin.blogCategories.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $category?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Chuyên mục blog — mặc định cha = Hub Cẩm nang → slug_full nối theo parent.',
                'icon' => '<path d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625"/>',
                'backUrl' => route('admin.blogCategories.list'),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $category ?? new \App\Models\BlogCategory(),
                    'language' => $language,
                    'routeName' => 'admin.blogCategories.view',
                ])
            </div>

            @if ($errors->any())
                <div class="adminFormPage_errors">
                    <div class="adminFormPage_errors_content">
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
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin chung</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @php
                                $countryOptions = ['' => '— Không gắn quốc gia —'];
                                foreach ($countries as $country) {
                                    $countryOptions[$country->id] = $country->translation($locale)?->name ?? $country->code;
                                }
                            @endphp
                            @include('admin.components.formField', [
                                'label' => 'Quốc gia (tuỳ chọn)',
                                'name' => 'country_id',
                                'type' => 'select',
                                'options' => $countryOptions,
                                'value' => old('country_id', $category?->country_id),
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
                                'checkboxLabel' => 'Đang hoạt động',
                            ])
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin SEO</h2>
                                <p class="adminFormSection_description">
                                    Chọn trang cha (Hub Cẩm nang hoặc chuyên mục khác) → slug_full = {cha.slug_full}/{slug}.
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
                                    'parent_id' => old('seo_parent_id', $category?->seoEntry?->parent_id ?? $defaultParentId),
                                    'rating_aggregate_count' => $category?->seoEntry?->rating_aggregate_count,
                                    'rating_aggregate_star' => $category?->seoEntry?->rating_aggregate_star,
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
                                'label' => 'SEO intro',
                                'name' => 'seo_intro',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('seo_intro', $translation?->seo_intro),
                            ])
                        </div>
                    </div>
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.blogCategories.list',
                                'viewUrl' => $viewUrl,
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
