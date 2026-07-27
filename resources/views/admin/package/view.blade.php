@extends('layouts.admin')

@section('title', $title)

@section('content')
@php
    $viewUrl = $seoTranslation?->slug_full ? seo_public_url($seoTranslation, $locale) : null;
    $countryOptions = $countries->mapWithKeys(fn ($c) => [$c->id => $c->name])->all();
@endphp

<form id="formAction" action="{{ route($saveRoute) }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $package?->id }}">
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Nhập thông tin gói '.($type === 'cruise' ? 'cruise' : 'tour').' và bản dịch theo ngôn ngữ.',
                'icon' => '<path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
                'backUrl' => route($listRoute),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $package ?? new \App\Models\Package(),
                    'language' => $language,
                    'routeName' => $viewRoute,
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
                    {{-- Thông tin chung --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin chung</h2>
                                <p class="adminFormSection_description">Thông tin cấu hình gói, không phụ thuộc ngôn ngữ.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', [
                                    'label' => 'Quốc gia',
                                    'name' => 'country_id',
                                    'type' => 'select',
                                    'required' => true,
                                    'value' => old('country_id', $package?->country_id),
                                    'options' => $countryOptions,
                                    'tooltip' => 'Quốc gia của gói. SEO parent mặc định sẽ lấy từ quốc gia này nếu không chọn trang cha.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Mã gói',
                                    'name' => 'code',
                                    'value' => old('code', $package?->code),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Số ngày',
                                    'name' => 'duration_days',
                                    'type' => 'number',
                                    'required' => true,
                                    'min' => 1,
                                    'value' => old('duration_days', $package?->duration_days ?? 1),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Số đêm',
                                    'name' => 'duration_nights',
                                    'type' => 'number',
                                    'min' => 0,
                                    'value' => old('duration_nights', $package?->duration_nights ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Giá từ',
                                    'name' => 'price_from',
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'value' => old('price_from', $package?->price_from),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Tiền tệ',
                                    'name' => 'currency',
                                    'value' => old('currency', $package?->currency ?? 'VND'),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trạng thái',
                                    'name' => 'status',
                                    'type' => 'select',
                                    'required' => true,
                                    'value' => old('status', $package?->status ?? 'draft'),
                                    'options' => ['draft' => 'Nháp', 'published' => 'Xuất bản', 'archived' => 'Lưu trữ'],
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $package?->sort ?? 0),
                                    'tooltip' => 'Số càng nhỏ càng ưu tiên cao.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Điểm đánh giá',
                                    'name' => 'rating',
                                    'type' => 'number',
                                    'step' => '0.1',
                                    'min' => 0,
                                    'max' => 5,
                                    'value' => old('rating', $package?->rating ?? 0),
                                    'helpText' => 'Hiển thị trên card tour và trang chi tiết.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Số lượt đánh giá',
                                    'name' => 'review_count',
                                    'type' => 'number',
                                    'min' => 0,
                                    'value' => old('review_count', $package?->review_count ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Nhãn ưu đãi',
                                    'name' => 'discount_badge',
                                    'value' => old('discount_badge', $package?->discount_badge),
                                    'helpText' => 'VD: Ưu đãi đặc biệt — hiển thị trên ảnh card và sidebar.',
                                ])
                            </div>

                            @if ($type === 'cruise')
                                <div class="adminFormGrid adminFormGrid--2cols" style="margin-top:1rem;">
                                    @include('admin.components.formField', [
                                        'label' => 'Loại cruise',
                                        'name' => 'cruise_type',
                                        'value' => old('cruise_type', $package?->cruise_type),
                                    ])
                                    @include('admin.components.formField', [
                                        'label' => 'Cảng khởi hành',
                                        'name' => 'departure_port',
                                        'value' => old('departure_port', $package?->departure_port),
                                    ])
                                    @include('admin.components.formField', [
                                        'label' => 'Hạng tàu',
                                        'name' => 'boat_class',
                                        'value' => old('boat_class', $package?->boat_class),
                                    ])
                                    @include('admin.components.formField', [
                                        'label' => 'Đêm trên tàu',
                                        'name' => 'nights_on_board',
                                        'type' => 'number',
                                        'value' => old('nights_on_board', $package?->nights_on_board),
                                    ])
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SEO + Parent URL --}}
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
                                <p class="adminFormSection_description">Chọn trang cha để tạo slug_full theo cấp (Hitour pattern).</p>
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
                                    'parent_id' => old('seo_parent_id', $package?->seoEntry?->parent_id ?? $package?->country?->seoEntry?->id),
                                    'rating_aggregate_count' => old('rating_aggregate_count', $package?->seoEntry?->rating_aggregate_count),
                                    'rating_aggregate_star' => old('rating_aggregate_star', $package?->seoEntry?->rating_aggregate_star),
                                ],
                                'seoEntry' => $package?->seoEntry,
                                'language' => $locale,
                                'parents' => $parents,
                                'showParent' => true,
                                'titleFieldId' => 'title',
                            ])
                        </div>
                    </div>

                    {{-- Nội dung theo ngôn ngữ --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h7.5"/>
                                </svg>
                            </div>
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nội dung ({{ strtoupper($locale) }})</h2>
                                <p class="adminFormSection_description">Bản dịch theo ngôn ngữ đang chọn ở trên.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Tiêu đề',
                                'name' => 'title',
                                'type' => 'textarea',
                                'required' => true,
                                'rows' => 2,
                                'charCount' => true,
                                'maxLength' => 255,
                                'value' => old('title', $translation?->title),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Điểm khởi hành',
                                'name' => 'start_location',
                                'value' => old('start_location', $translation?->start_location),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Điểm kết thúc',
                                'name' => 'end_location',
                                'value' => old('end_location', $translation?->end_location),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Điểm tham quan (mỗi dòng một địa điểm)',
                                'name' => 'places_to_visit',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('places_to_visit', is_array($translation?->places_to_visit) ? implode("\n", $translation->places_to_visit) : ''),
                                'helpText' => 'Hiển thị trên card tour và mục tổng quan trang chi tiết.',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Trích dẫn khách hàng',
                                'name' => 'featured_quote_text',
                                'type' => 'textarea',
                                'rows' => 2,
                                'value' => old('featured_quote_text', $translation?->featured_quote_text),
                                'helpText' => 'Hiển thị trên card danh mục tour.',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Tác giả trích dẫn',
                                'name' => 'featured_quote_author',
                                'value' => old('featured_quote_author', $translation?->featured_quote_author),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Tóm tắt',
                                'name' => 'summary',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('summary', $translation?->summary),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Mở đầu điểm nhấn',
                                'name' => 'highlights_intro',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('highlights_intro', $translation?->highlights_intro),
                                'helpText' => 'Đoạn giới thiệu trước danh sách điểm nhấn trên trang chi tiết.',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Điểm nổi bật (mỗi dòng một mục)',
                                'name' => 'highlight_bullets',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('highlight_bullets', is_array($translation?->highlight_bullets) ? implode("\n", $translation->highlight_bullets) : ''),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Bao gồm (mỗi dòng một mục)',
                                'name' => 'inclusions',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('inclusions', is_array($translation?->inclusions) ? implode("\n", $translation->inclusions) : ''),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Không bao gồm (mỗi dòng một mục)',
                                'name' => 'exclusions',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('exclusions', is_array($translation?->exclusions) ? implode("\n", $translation->exclusions) : ''),
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Lưu ý (mỗi dòng một mục)',
                                'name' => 'notes',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('notes', is_array($translation?->notes) ? implode("\n", $translation->notes) : ''),
                            ])
                        </div>
                    </div>

                    {{-- Phong cách du lịch --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Phong cách du lịch</h2>
                                <p class="adminFormSection_description">Dùng cho bộ lọc trên trang danh mục tour.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @foreach ($travelStyles as $style)
                                    <label class="adminFormField_checkbox">
                                        <input type="checkbox" class="adminFormField_checkbox_input" name="travel_style_ids[]" value="{{ $style->id }}"
                                            @checked(in_array($style->id, old('travel_style_ids', $package?->travelStyles?->pluck('id')->all() ?? [])))>
                                        <span class="adminFormField_checkbox_label">{{ $style->translation($locale)?->name ?? $style->code }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @include('admin.components.packageRepeaterItinerary', [
                        'days' => $package?->itineraryDays,
                        'oldData' => old('itinerary'),
                        'locale' => $locale,
                    ])

                    @include('admin.components.packageRepeaterFaqs', [
                        'faqs' => $package?->faqs,
                        'oldData' => old('faqs'),
                        'locale' => $locale,
                    ])
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => $listRoute,
                                'viewUrl' => $viewUrl,
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'image',
                                'label' => 'Ảnh đại diện',
                                'currentImage' => $package?->coverUrl(),
                                'removeName' => 'remove_image',
                                'aspectRatio' => '3/2',
                                'tooltip' => 'Ảnh card tour / trang chi tiết / chia sẻ mạng xã hội.',
                                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Tự tối ưu về WebP ≤1920px.',
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
