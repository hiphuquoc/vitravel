@extends('layouts.admin')

@section('title', $title)

@section('content')
<form id="formAction" action="{{ route('admin.homeSections.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Quản lý nội dung tĩnh trang chủ: điều hướng nhanh slider, banner USP, tiêu đề section, CTA.',
                'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => new stdClass(),
                    'language' => $language,
                    'routeName' => 'admin.homeSections.edit',
                    'routeParams' => [],
                ])
            </div>

            @if (session('success'))
                <div class="adminFormPage_success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="adminFormPage_errors">
                    <div class="adminFormPage_errors_content">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif

            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    @include('admin.components.homeRepeaterHeroPills', [
                        'data' => $heroPills,
                        'oldData' => old('pills'),
                        'locale' => $locale,
                        'linkOptions' => $heroPillLinkOptions,
                    ])

                    {{-- ── 4 banner cam kết (USP) ── --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">4 banner cam kết dịch vụ</h2>
                                <p class="adminFormSection_description">Hiển thị ngay dưới slider hero — 4 icon + tiêu đề + mô tả ngắn.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @foreach ($usps as $index => $usp)
                                @php $uspTranslation = $usp->translation($locale); @endphp
                                <input type="hidden" name="usps[{{ $index }}][id]" value="{{ $usp->id }}">
                                <div class="adminFormGrid adminFormGrid--2cols {{ $index > 0 ? 'adminFormGrid--divider' : '' }}">
                                        @include('admin.components.formField', [
                                            'label' => 'Icon',
                                            'name' => "usps[{$index}][icon]",
                                            'type' => 'select',
                                            'required' => true,
                                            'value' => old("usps.{$index}.icon", $usp->icon),
                                            'options' => $uspIconOptions,
                                        ])
                                        @include('admin.components.formField', [
                                            'label' => 'Tiêu đề',
                                            'name' => "usps[{$index}][title]",
                                            'value' => old("usps.{$index}.title", $uspTranslation?->title),
                                        ])
                                        @include('admin.components.formField', [
                                            'label' => 'Mô tả ngắn',
                                            'name' => "usps[{$index}][description]",
                                            'type' => 'textarea',
                                            'rows' => 2,
                                            'class' => 'adminFormGrid__full',
                                            'value' => old("usps.{$index}.description", $uspTranslation?->description),
                                        ])
                                    </div>
                                @endforeach
                        </div>
                    </div>

                    {{-- ── Các section tiêu đề / nội dung ── --}}
                    @foreach ($sections as $sectionKey => $section)
                        @php
                            $translation = $section->translation($locale);
                            $fields = \App\Models\HomeSection::fieldsForKey($sectionKey);
                            $prefix = "sections[{$sectionKey}]";
                        @endphp
                        <div class="adminFormSection">
                            <div class="adminFormSection_header">
                                <div class="adminFormSection_header_info">
                                    <h2 class="adminFormSection_title">{{ $section->label() }}</h2>
                                    <p class="adminFormSection_description">
                                        @if ($sectionKey === 'featured_tours')
                                            Tiêu đề/mô tả section và danh sách tour hiển thị — chọn từ chương trình tour có sẵn bên dưới.
                                        @elseif (in_array($sectionKey, ['destinations', 'testimonials', 'review_platforms', 'team', 'videos'], true))
                                            Chỉ tiêu đề/mô tả — dữ liệu danh sách load từ module khác.
                                        @else
                                            Khối nội dung đầy đủ trên trang chủ.
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="adminFormSection_body">
                                <input type="hidden" name="{{ $prefix }}[id]" value="{{ $section->id }}">

                                @if (in_array('image', $fields, true))
                                    <div class="adminFormGrid adminFormGrid--2cols adminFormGrid--mb">
                                        @include('admin.components.formImageUpload', [
                                            'label' => 'Ảnh minh hoạ',
                                            'name' => "{$prefix}[image]",
                                            'currentImage' => $section->imageUrl(),
                                            'removeName' => "{$prefix}[remove_image]",
                                            'aspectRatio' => '4/3',
                                            'hint' => 'Tuỳ chọn. Tự tối ưu về WebP ≤1920px.',
                                        ])
                                        @if (in_array('image_alt', $fields, true))
                                            @include('admin.components.formField', [
                                                'label' => 'Alt text ảnh',
                                                'name' => "{$prefix}[image_alt]",
                                                'value' => old("sections.{$sectionKey}.image_alt", $translation?->image_alt ?? $section->image?->alt),
                                            ])
                                        @endif
                                    </div>
                                @endif

                                <div class="adminFormGrid adminFormGrid--2cols">
                                    @if (in_array('eyebrow', $fields, true))
                                        @include('admin.components.formField', [
                                            'label' => 'Kicker / Eyebrow',
                                            'name' => "{$prefix}[eyebrow]",
                                            'value' => old("sections.{$sectionKey}.eyebrow", $translation?->eyebrow),
                                        ])
                                    @endif
                                    @if (in_array('title', $fields, true))
                                        @include('admin.components.formField', [
                                            'label' => 'Tiêu đề',
                                            'name' => "{$prefix}[title]",
                                            'value' => old("sections.{$sectionKey}.title", $translation?->title),
                                        ])
                                    @endif
                                    @if (in_array('subtitle', $fields, true))
                                        @include('admin.components.formField', [
                                            'label' => 'Mô tả phụ',
                                            'name' => "{$prefix}[subtitle]",
                                            'type' => 'textarea',
                                            'rows' => 2,
                                            'class' => 'adminFormGrid__full',
                                            'value' => old("sections.{$sectionKey}.subtitle", $translation?->subtitle),
                                        ])
                                    @endif
                                    @if (in_array('body', $fields, true))
                                        @include('admin.components.formField', [
                                            'label' => 'Nội dung chi tiết',
                                            'name' => "{$prefix}[body]",
                                            'type' => 'textarea',
                                            'rows' => 5,
                                            'class' => 'adminFormGrid__full',
                                            'value' => old("sections.{$sectionKey}.body", $translation?->body),
                                            'hint' => 'Có thể dùng HTML cơ bản như <strong>.',
                                        ])
                                    @endif
                                    @if (in_array('meta_line', $fields, true))
                                        @include('admin.components.formField', [
                                            'label' => 'Dòng meta (giấy phép...)',
                                            'name' => "{$prefix}[meta_line]",
                                            'class' => 'adminFormGrid__full',
                                            'value' => old("sections.{$sectionKey}.meta_line", $translation?->meta_line),
                                        ])
                                    @endif
                                    @if (in_array('cta_label', $fields, true))
                                        @include('admin.components.formField', [
                                            'label' => 'Nút CTA — nhãn',
                                            'name' => "{$prefix}[cta_label]",
                                            'value' => old("sections.{$sectionKey}.cta_label", $translation?->cta_label),
                                        ])
                                    @endif
                                    @if (in_array('cta_url', $fields, true))
                                        @include('admin.components.formField', [
                                            'label' => 'Nút CTA — URL',
                                            'name' => "{$prefix}[cta_url]",
                                            'value' => old("sections.{$sectionKey}.cta_url", $translation?->cta_url),
                                            'hint' => 'VD: /ve-chung-toi',
                                        ])
                                    @endif
                                    @include('admin.components.formField', [
                                        'label' => 'Hiển thị section',
                                        'name' => "{$prefix}[is_active]",
                                        'type' => 'checkbox',
                                        'value' => old("sections.{$sectionKey}.is_active", $section->is_active),
                                        'checkboxLabel' => 'Đang hiển thị trên trang chủ',
                                    ])
                                </div>

                                @if ($sectionKey === 'featured_tours')
                                    @include('admin.components.homeRepeaterFeaturedTours', [
                                        'data' => $featuredTours,
                                        'oldData' => old('featured_tours'),
                                        'tourOptions' => $featuredTourOptions,
                                    ])
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.dashboard',
                                'viewUrl' => route('home'),
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@include('admin.components.repeaterInit')
@endsection
