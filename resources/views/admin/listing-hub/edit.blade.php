@extends('layouts.admin')
@section('title', $title)
@section('content')
<form id="formAction" action="{{ route('admin.listingHub.save', ['hubKey' => $hubKey]) }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="language" value="{{ $locale }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Trang cấp 1 (parent = 0, level = 1). Con chọn hub này → slug_full = {hub.slug_full}/{slug}.',
                'icon' => '<path d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
                'backUrl' => route($backRoute, $backParams ?? []),
                'backText' => 'Quay lại',
            ])
            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $page,
                    'language' => $locale,
                    'routeName' => 'admin.listingHub.edit',
                    'routeParams' => ['hubKey' => $hubKey],
                ])
            </div>
            @if ($errors->any())
                <div class="adminFormPage_errors"><div class="adminFormPage_errors_content"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif
            @if (session('success'))
                <div class="adminFormPage_errors" style="border-color:#86efac;background:#f0fdf4;"><div class="adminFormPage_errors_content" style="color:#166534;">{{ session('success') }}</div></div>
            @endif
            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nội dung ({{ strtoupper($locale) }})</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Tiêu đề (H1)',
                                'name' => 'title',
                                'required' => true,
                                'value' => old('title', $translation?->title),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Mô tả ngắn (subtitle header)',
                                'name' => 'body',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('body', $translation?->body),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Đoạn SEO cuối listing',
                                'name' => 'seo_body',
                                'type' => 'textarea',
                                'rows' => 5,
                                'helpText' => 'Hiển thị dưới lưới tour/dịch vụ. Để trống = ẩn khối này.',
                                'value' => old('seo_body', $translation?->seo_body),
                            ])
                        </div>
                    </div>
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">SEO</h2>
                                <p class="adminFormSection_description">
                                    parent = 0 · level = {{ $seoEntry?->level ?? 1 }} ·
                                    slug_full = {{ $seoTranslation?->slug_full ?? ('/'.($cfg['default_slug'] ?? '')) }}
                                </p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formSeo', [
                                'itemSeo' => [
                                    'slug' => old('seo_slug', $seoTranslation?->slug ?? ($cfg['default_slug'] ?? '')),
                                    'slug_full' => $seoTranslation?->slug_full,
                                    'seo_title' => old('seo_title', $seoTranslation?->seo_title),
                                    'seo_description' => old('seo_description', $seoTranslation?->seo_description),
                                    'keywords' => old('seo_keywords', $seoTranslation?->keywords),
                                    'parent_id' => null,
                                    'rating_aggregate_count' => $seoEntry?->rating_aggregate_count,
                                    'rating_aggregate_star' => $seoEntry?->rating_aggregate_star,
                                ],
                                'seoEntry' => $seoEntry,
                                'language' => $locale,
                                'parents' => collect(),
                                'showParent' => false,
                                'titleFieldId' => 'title',
                            ])
                        </div>
                    </div>
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => $backRoute,
                                'viewUrl' => $viewUrl,
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'cover',
                                'label' => 'Ảnh đại diện',
                                'currentImage' => $page->coverUrl('card') ?: $page->coverUrl(),
                                'removeName' => 'remove_cover',
                                'aspectRatio' => '3/2',
                                'tooltip' => 'Thumbnail card / chia sẻ — khác banner listing.',
                                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB.',
                            ])
                            <div style="margin-top:1.25rem;">
                                @include('admin.components.formImageUpload', [
                                    'name' => 'image',
                                    'label' => 'Ảnh banner listing',
                                    'currentImage' => $page->bannerUrl('lg') ?: $page->bannerUrl('full'),
                                    'removeName' => 'remove_image',
                                    'aspectRatio' => '21/9',
                                    'tooltip' => 'Banner first-view trang hub.',
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
