@extends('layouts.admin')
@section('title', $title)
@section('content')
<form action="{{ route('admin.company.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="language" value="{{ $locale }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Liên hệ footer + nội dung CMS trang Về chúng tôi (đa ngôn ngữ).',
                'icon' => '<path d="M3 21h18M5 21V7l7-4 7 4v14"/>',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $profile,
                    'language' => $locale,
                    'routeName' => 'admin.company.profile',
                ])
            </div>

            @if (session('success'))
                <div class="adminFormPage_message adminFormPage_message--success"><div class="adminFormPage_message_content">{{ session('success') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="adminFormPage_errors"><div class="adminFormPage_errors_content"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif

            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    {{-- 1. Liên hệ footer --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">1. Liên hệ footer</h2>
                            <p class="adminFormSection_description">Không phụ thuộc ngôn ngữ — hiển thị toàn site.</p>
                        </div></div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Email','name'=>'contact_email','type'=>'email','value'=>old('contact_email',$profile->contact_email)])
                                @include('admin.components.formField', ['label'=>'Điện thoại','name'=>'contact_phone','value'=>old('contact_phone',$profile->contact_phone)])
                                @include('admin.components.formField', ['label'=>'WhatsApp','name'=>'contact_whatsapp','value'=>old('contact_whatsapp',$profile->contact_whatsapp)])
                                @include('admin.components.formField', ['label'=>'Slogan','name'=>'slogan','value'=>old('slogan',$profile->slogan),'placeholder'=>'“Hài lòng hơn cả mong đợi”'])
                                @include('admin.components.formField', ['label'=>'Số giấy phép','name'=>'license_number','class'=>'adminFormGrid__full','value'=>old('license_number',$profile->license_number)])
                            </div>
                        </div>
                    </div>

                    {{-- 2. SEO & header --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">2. SEO &amp; header trang Về chúng tôi ({{ strtoupper($locale) }})</h2>
                        </div></div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'SEO title','name'=>'about_seo_title','class'=>'adminFormGrid__full','value'=>old('about_seo_title',$translation?->about_seo_title)])
                                @include('admin.components.formField', ['label'=>'SEO description','name'=>'about_seo_description','type'=>'textarea','rows'=>3,'class'=>'adminFormGrid__full','value'=>old('about_seo_description',$translation?->about_seo_description)])
                                @include('admin.components.formField', ['label'=>'Tiêu đề trang (H1)','name'=>'about_page_title','value'=>old('about_page_title',$translation?->about_page_title)])
                                @include('admin.components.formField', ['label'=>'Subtitle','name'=>'about_page_subtitle','value'=>old('about_page_subtitle',$translation?->about_page_subtitle)])
                                @include('admin.components.formImageUpload', [
                                    'label' => 'Banner trang',
                                    'name' => 'about_banner',
                                    'currentImage' => $profile->exists ? $profile->mediaUrl('aboutBanner', 'card') : null,
                                    'removeName' => 'remove_about_banner',
                                    'aspectRatio' => '16/6',
                                    'maxKb' => $uploadMaxKb,
                                    'hint' => 'JPG, PNG, WebP — tối đa '.$uploadMaxLabel.'.',
                                    'class' => 'adminFormGrid__full',
                                ])
                            </div>
                        </div>
                    </div>

                    {{-- 3. Sứ mệnh --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">3. Sứ mệnh ({{ strtoupper($locale) }})</h2>
                        </div></div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Tiêu đề','name'=>'mission_title','class'=>'adminFormGrid__full','value'=>old('mission_title',$translation?->mission_title)])
                                @include('admin.components.formField', ['label'=>'Nội dung','name'=>'mission_text','type'=>'textarea','rows'=>4,'class'=>'adminFormGrid__full','value'=>old('mission_text',$translation?->mission_text)])
                                @include('admin.components.formImageUpload', [
                                    'label' => 'Ảnh sứ mệnh',
                                    'name' => 'mission_image',
                                    'currentImage' => $profile->exists ? $profile->mediaUrl('missionImage', 'card') : null,
                                    'removeName' => 'remove_mission_image',
                                    'aspectRatio' => '4/3',
                                    'maxKb' => $uploadMaxKb,
                                    'class' => 'adminFormGrid__full',
                                ])
                            </div>
                        </div>
                    </div>

                    {{-- 4. Tầm nhìn --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">4. Tầm nhìn ({{ strtoupper($locale) }})</h2>
                        </div></div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Tiêu đề','name'=>'vision_title','class'=>'adminFormGrid__full','value'=>old('vision_title',$translation?->vision_title)])
                                @include('admin.components.formField', ['label'=>'Nội dung','name'=>'vision_text','type'=>'textarea','rows'=>4,'class'=>'adminFormGrid__full','value'=>old('vision_text',$translation?->vision_text)])
                                @include('admin.components.formImageUpload', [
                                    'label' => 'Ảnh tầm nhìn',
                                    'name' => 'vision_image',
                                    'currentImage' => $profile->exists ? $profile->mediaUrl('visionImage', 'card') : null,
                                    'removeName' => 'remove_vision_image',
                                    'aspectRatio' => '4/3',
                                    'maxKb' => $uploadMaxKb,
                                    'class' => 'adminFormGrid__full',
                                ])
                            </div>
                        </div>
                    </div>

                    {{-- 5. Chính sách bán hàng --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">5. Chính sách bán hàng ({{ strtoupper($locale) }})</h2>
                        </div></div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Tiêu đề','name'=>'sales_policy_title','class'=>'adminFormGrid__full','value'=>old('sales_policy_title',$translation?->sales_policy_title)])
                                @include('admin.components.formField', ['label'=>'Nội dung','name'=>'sales_policy_content','type'=>'textarea','rows'=>5,'class'=>'adminFormGrid__full','value'=>old('sales_policy_content',$translation?->sales_policy_content)])
                                @include('admin.components.formField', ['label'=>'Nhãn CTA','name'=>'sales_policy_cta_label','value'=>old('sales_policy_cta_label',$translation?->sales_policy_cta_label)])
                                @include('admin.components.formField', ['label'=>'URL CTA (trống = trang Liên hệ)','name'=>'sales_policy_cta_url','value'=>old('sales_policy_cta_url',$translation?->sales_policy_cta_url),'placeholder'=>'/lien-he'])
                                @include('admin.components.formImageUpload', [
                                    'label' => 'Ảnh chính sách',
                                    'name' => 'policy_image',
                                    'currentImage' => $profile->exists ? $profile->mediaUrl('policyImage', 'card') : null,
                                    'removeName' => 'remove_policy_image',
                                    'aspectRatio' => '4/3',
                                    'maxKb' => $uploadMaxKb,
                                    'class' => 'adminFormGrid__full',
                                ])
                            </div>
                        </div>
                    </div>

                    {{-- 6. Chrome sections --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">6. Tiêu đề các section ({{ strtoupper($locale) }})</h2>
                            <p class="adminFormSection_description">Giá trị / Lý do chọn / Đại diện — chỉ chrome; danh sách item quản lý ở menu riêng.</p>
                        </div></div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Tiêu đề section giá trị','name'=>'values_section_title','value'=>old('values_section_title',$translation?->values_section_title)])
                                @include('admin.components.formField', ['label'=>'Nhãn hub vòng tròn','name'=>'values_hub_label','value'=>old('values_hub_label',$translation?->values_hub_label)])
                                @include('admin.components.formField', ['label'=>'Tiêu đề section lý do','name'=>'reasons_section_title','value'=>old('reasons_section_title',$translation?->reasons_section_title)])
                                @include('admin.components.formField', ['label'=>'Nhãn CTA lý do','name'=>'reasons_cta_label','value'=>old('reasons_cta_label',$translation?->reasons_cta_label)])
                                @include('admin.components.formField', ['label'=>'URL CTA lý do (trống = Tour riêng)','name'=>'reasons_cta_url','class'=>'adminFormGrid__full','value'=>old('reasons_cta_url',$translation?->reasons_cta_url)])
                                @include('admin.components.formField', ['label'=>'Tiêu đề section đại diện','name'=>'reference_section_title','class'=>'adminFormGrid__full','value'=>old('reference_section_title',$translation?->reference_section_title)])
                                @include('admin.components.formField', ['label'=>'Subtitle section đại diện','name'=>'reference_section_subtitle','type'=>'textarea','rows'=>2,'class'=>'adminFormGrid__full','value'=>old('reference_section_subtitle',$translation?->reference_section_subtitle)])
                            </div>
                        </div>
                    </div>

                    {{-- 7. Ảnh lý do chọn --}}
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">7. Ảnh mockup “Vì sao chọn”</h2>
                            <p class="adminFormSection_description">Ảnh dùng chung cho block lý do (không gắn từng item).</p>
                        </div></div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formImageUpload', [
                                'label' => 'Ảnh mockup',
                                'name' => 'reasons_image',
                                'currentImage' => $profile->exists ? $profile->mediaUrl('reasonsImage', 'card') : null,
                                'removeName' => 'remove_reasons_image',
                                'aspectRatio' => '3/4',
                                'maxKb' => $uploadMaxKb,
                                'hint' => 'VD: điện thoại hiển thị website.',
                            ])
                        </div>
                    </div>
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar"><div class="adminFormSidebar_sticky">
                        <button type="submit" class="adminFormActions_button" style="width:100%;">Lưu</button>
                        <p style="margin-top:0.75rem;font-size:13px;color:#64748b;">Chuyển ngôn ngữ bằng switcher phía trên để nhập bản dịch VI / EN.</p>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
