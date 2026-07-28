@extends('layouts.admin')

@section('title', $title)

@section('content')
<form id="formAction" action="{{ route('admin.team.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $member?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Ảnh chân dung + thông tin hiển thị trên trang chủ / Về chúng tôi.',
                'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                'backUrl' => route('admin.team.list'),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $member ?? new \App\Models\TeamMember(),
                    'language' => $locale,
                    'routeName' => 'admin.team.view',
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
                                <h2 class="adminFormSection_title">Ảnh đại diện</h2>
                                <p class="adminFormSection_description">Ảnh chân dung hình vuông — hiển thị avatar trên public.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formImageUpload', [
                                    'label' => 'Ảnh chân dung',
                                    'name' => 'image',
                                    'currentImage' => $member?->avatarUrl(),
                                    'removeName' => 'remove_image',
                                    'aspectRatio' => '1/1',
                                    'maxKb' => $uploadMaxKb,
                                    'tooltip' => 'Ảnh hiển thị trên trang chủ và trang giới thiệu đội ngũ.',
                                    'hint' => 'JPG, PNG, WebP, GIF — tối đa '.$uploadMaxLabel.'. Nên dùng ảnh vuông rõ mặt.',
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nội dung ({{ strtoupper($locale) }})</h2>
                                <p class="adminFormSection_description">Họ tên, vai trò và giới thiệu theo ngôn ngữ đang chọn.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Họ tên',
                                'name' => 'name',
                                'required' => true,
                                'value' => old('name', $translation?->name),
                                'placeholder' => 'VD: Phạm Thu Trang',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Vai trò',
                                'name' => 'role',
                                'value' => old('role', $translation?->role),
                                'placeholder' => 'VD: Giám đốc điều hành',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Giới thiệu ngắn',
                                'name' => 'short_bio',
                                'type' => 'textarea',
                                'rows' => 4,
                                'value' => old('short_bio', $translation?->short_bio),
                                'placeholder' => 'Mô tả ngắn về thành viên…',
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
                                    'label' => 'Phòng ban',
                                    'name' => 'department',
                                    'value' => old('department', $member?->department),
                                    'placeholder' => 'VD: Điều hành / Tư vấn',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $member?->sort ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trạng thái',
                                    'name' => 'is_active',
                                    'type' => 'checkbox',
                                    'value' => old('is_active', $member?->is_active ?? true),
                                    'checkboxLabel' => 'Đang hoạt động',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trang chủ',
                                    'name' => 'show_on_home',
                                    'type' => 'checkbox',
                                    'value' => old('show_on_home', $member?->show_on_home ?? false),
                                    'checkboxLabel' => 'Hiển thị trên trang chủ',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.team.list',
                            ])
                            @if ($member)
                                <div class="mt-3">
                                    <a href="{{ route('admin.team.delete', ['id' => $member->id]) }}"
                                       class="adminFormActions_button adminFormActions_button--secondary"
                                       style="color:#b91c1c;width:100%;justify-content:center;"
                                       onclick="return confirm('Xóa thành viên này?')">
                                        Xóa thành viên
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
