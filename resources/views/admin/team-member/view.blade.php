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
                'desc' => 'Hồ sơ CV thành viên — hiển thị trên /doi-ngu và trang chi tiết.',
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
                                <p class="adminFormSection_description">Ảnh chân dung hình vuông — avatar public.</p>
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
                                    'tooltip' => 'Ảnh hiển thị trên danh sách và hồ sơ CV.',
                                    'hint' => 'JPG, PNG, WebP, GIF — tối đa '.$uploadMaxLabel.'. Nên dùng ảnh vuông rõ mặt.',
                                ])
                            </div>
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
                                'rows' => 3,
                                'value' => old('short_bio', $translation?->short_bio),
                                'placeholder' => 'Mô tả ngắn trên card đội ngũ…',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Giới thiệu đầy đủ (HTML)',
                                'name' => 'bio_html',
                                'type' => 'textarea',
                                'rows' => 8,
                                'value' => old('bio_html', $translation?->bio_html),
                                'placeholder' => '<p>Đoạn giới thiệu dài trên trang hồ sơ…</p>',
                                'helpText' => 'Cho phép HTML cơ bản (p, strong, em, ul…).',
                            ])
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Thông tin cá nhân &amp; thống kê</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', [
                                    'label' => 'Khu vực',
                                    'name' => 'area',
                                    'value' => old('area', $member?->area),
                                    'placeholder' => 'VD: Hà Nội, Việt Nam',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Số năm kinh nghiệm',
                                    'name' => 'years_experience',
                                    'type' => 'number',
                                    'value' => old('years_experience', $member?->years_experience),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Email',
                                    'name' => 'email',
                                    'type' => 'email',
                                    'value' => old('email', $member?->email),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Điện thoại',
                                    'name' => 'phone',
                                    'value' => old('phone', $member?->phone),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Ngôn ngữ (mỗi dòng một ngôn ngữ)',
                                    'name' => 'languages',
                                    'type' => 'textarea',
                                    'rows' => 3,
                                    'class' => 'adminFormGrid__full',
                                    'value' => old('languages', is_array($member?->languages) ? implode("\n", $member->languages) : ''),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Khách đồng hành',
                                    'name' => 'stat_clients',
                                    'type' => 'number',
                                    'value' => old('stat_clients', $member?->stat_clients ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Tour dẫn dắt',
                                    'name' => 'stat_tours',
                                    'type' => 'number',
                                    'value' => old('stat_tours', $member?->stat_tours ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Giải thưởng',
                                    'name' => 'stat_awards',
                                    'type' => 'number',
                                    'value' => old('stat_awards', $member?->stat_awards ?? 0),
                                ])
                            </div>
                        </div>
                    </div>

                    @include('admin.components.teamRepeaterAchievements', ['member' => $member])
                    @include('admin.components.teamRepeaterSkills', ['member' => $member])
                    @include('admin.components.teamRepeaterDegrees', ['member' => $member])
                    @include('admin.components.teamRepeaterExperiences', ['member' => $member])

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">SEO &amp; URL hồ sơ</h2>
                                <p class="adminFormSection_description">Cha mặc định là hub Đội ngũ — URL dạng /doi-ngu/{slug}.</p>
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
                                    'parent_id' => old('seo_parent_id', $member?->seoEntry?->parent_id ?? $defaultParentId),
                                    'rating_aggregate_count' => old('rating_aggregate_count', $member?->seoEntry?->rating_aggregate_count),
                                    'rating_aggregate_star' => old('rating_aggregate_star', $member?->seoEntry?->rating_aggregate_star),
                                ],
                                'seoEntry' => $member?->seoEntry,
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
                                @include('admin.components.formField', [
                                    'label' => 'Xác minh',
                                    'name' => 'is_verified',
                                    'type' => 'checkbox',
                                    'value' => old('is_verified', $member?->is_verified ?? true),
                                    'checkboxLabel' => 'Huy hiệu đã xác minh',
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
@include('admin.components.repeaterInit')
@endsection
