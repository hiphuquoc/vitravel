@extends('layouts.admin')

@section('title', $title)

@section('content')
<form id="formAction" action="{{ route('admin.homeSlides.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $slide?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Upload ảnh desktop + mobile (tuỳ chọn). Ảnh lưu trên disk: '.$mediaDisk.'.',
                'icon' => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
                'backUrl' => route('admin.homeSlides.list'),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $slide ?? new \App\Models\HomeSlide(),
                    'language' => $language,
                    'routeName' => 'admin.homeSlides.view',
                ])
            </div>

            @if ($errors->any())
                <div class="adminFormPage_errors">
                    <div class="adminFormPage_errors_content">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif

            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Hình ảnh</h2>
                                <p class="adminFormSection_description">Ảnh nền hero — desktop bắt buộc khi tạo mới.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formImageUpload', [
                                    'label' => 'Ảnh desktop',
                                    'name' => 'image',
                                    'currentImage' => $slide?->imageUrl(),
                                    'removeName' => 'remove_image',
                                    'aspectRatio' => '21/9',
                                    'hint' => '1920×800px hoặc tỉ lệ 21:9. Tự tối ưu về WebP ≤1920px.',
                                ])
                                @include('admin.components.formImageUpload', [
                                    'label' => 'Ảnh mobile (tuỳ chọn)',
                                    'name' => 'image_mobile',
                                    'currentImage' => $slide?->imageMobileUrl(),
                                    'removeName' => 'remove_image_mobile',
                                    'aspectRatio' => '3/4',
                                    'hint' => '768×900px. Nếu bỏ trống sẽ dùng ảnh desktop.',
                                ])
                            </div>
                            @include('admin.components.formField', [
                                'label' => 'Alt text ảnh (SEO)',
                                'name' => 'image_alt',
                                'value' => old('image_alt', $translation?->image_alt ?? $slide?->image?->alt),
                            ])
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nội dung ({{ strtoupper($locale) }})</h2>
                                <p class="adminFormSection_description">Text hiển thị đè lên ảnh slider — để trống nếu chỉ muốn ảnh.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Tiêu đề chính (H1)',
                                'name' => 'title',
                                'value' => old('title', $translation?->title),
                                'placeholder' => 'VD: Miền Bắc Việt Nam',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Tiêu đề accent (dòng phụ)',
                                'name' => 'title_accent',
                                'value' => old('title_accent', $translation?->title_accent),
                                'placeholder' => 'VD: theo cách của bạn',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Mô tả ngắn',
                                'name' => 'description',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('description', $translation?->description),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Nút CTA',
                                'name' => 'button_label',
                                'value' => old('button_label', $translation?->button_label),
                                'placeholder' => 'VD: Khám phá tour',
                            ])
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Cấu hình</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', [
                                    'label' => 'Căn text',
                                    'name' => 'text_align',
                                    'type' => 'select',
                                    'required' => true,
                                    'value' => old('text_align', $slide?->text_align ?? 'center'),
                                    'options' => $alignOptions,
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Link khi click CTA / slide',
                                    'name' => 'link_url',
                                    'value' => old('link_url', $slide?->link_url),
                                    'placeholder' => '/tours/viet-nam hoặc https://...',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $slide?->sort ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Hiển thị',
                                    'name' => 'is_active',
                                    'type' => 'checkbox',
                                    'value' => old('is_active', $slide?->is_active ?? true),
                                    'checkboxLabel' => 'Slide đang hoạt động',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.homeSlides.list',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
