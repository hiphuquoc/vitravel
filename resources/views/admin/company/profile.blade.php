@extends('layouts.admin')
@section('title', $title)
@section('content')
<form action="{{ route('admin.company.save') }}" method="POST" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Email / điện thoại / WhatsApp / slogan dùng ở footer và schema.',
                'icon' => '<path d="M3 21h18M5 21V7l7-4 7 4v14"/>',
            ])
            @if (session('success'))
                <div class="adminFormPage_message adminFormPage_message--success"><div class="adminFormPage_message_content">{{ session('success') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="adminFormPage_errors"><div class="adminFormPage_errors_content"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif
            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">Liên hệ (footer)</h2>
                            <p class="adminFormSection_description">Hiển thị ở strip liên hệ footer toàn site. Địa chỉ chi tiết quản lý tại Văn phòng.</p>
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
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar"><div class="adminFormSidebar_sticky">
                        <button type="submit" class="adminFormActions_button" style="width:100%;">Lưu</button>
                        <p style="margin-top:0.75rem;font-size:13px;color:#64748b;">Cột link footer (Tour / Điểm đến / Cẩm nang) đang lấy từ cấu hình mẫu — có thể mở CMS sau nếu cần.</p>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
