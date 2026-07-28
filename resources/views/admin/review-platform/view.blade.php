@extends('layouts.admin')
@section('title', $title)
@section('content')
<form action="{{ route('admin.reviewPlatforms.save') }}" method="POST" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $platform?->id }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'backUrl' => route('admin.reviewPlatforms.list'),
                'backText' => 'Quay lại',
                'icon' => '<polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9"/>',
            ])
            @if ($errors->any())
                <div class="adminFormPage_errors"><div class="adminFormPage_errors_content"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif
            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Mã (code)','name'=>'code','required'=>true,'value'=>old('code',$platform?->code),'placeholder'=>'tripadvisor','hint'=>'slug duy nhất, không dấu'])
                                @include('admin.components.formField', ['label'=>'Tên hiển thị','name'=>'name','required'=>true,'value'=>old('name',$platform?->name)])
                                @include('admin.components.formField', ['label'=>'Điểm','name'=>'rating','type'=>'number','value'=>old('rating',$platform?->rating)])
                                @include('admin.components.formField', ['label'=>'Số đánh giá','name'=>'review_count','type'=>'number','value'=>old('review_count',$platform?->review_count)])
                                @include('admin.components.formField', ['label'=>'URL','name'=>'url','class'=>'adminFormGrid__full','value'=>old('url',$platform?->url)])
                                @include('admin.components.formField', ['label'=>'Quote / mô tả','name'=>'quote','type'=>'textarea','rows'=>3,'class'=>'adminFormGrid__full','value'=>old('quote',$platform?->quote)])
                                @include('admin.components.formField', ['label'=>'Nhãn nút','name'=>'link_label','class'=>'adminFormGrid__full','value'=>old('link_label',$platform?->link_label),'placeholder'=>'Đọc đánh giá trên TripAdvisor'])
                                @include('admin.components.formField', ['label'=>'Thứ tự','name'=>'sort','type'=>'number','value'=>old('sort',$platform?->sort ?? 0)])
                                @include('admin.components.formField', ['label'=>'Kích hoạt','name'=>'is_active','type'=>'checkbox','value'=>old('is_active',$platform?->is_active ?? true),'checkboxLabel'=>'Đang hoạt động'])
                                @include('admin.components.formField', ['label'=>'Trang chủ','name'=>'show_on_home','type'=>'checkbox','value'=>old('show_on_home',$platform?->show_on_home ?? true),'checkboxLabel'=>'Ưu tiên khi chưa chọn danh sách curated'])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar"><div class="adminFormSidebar_sticky">
                        @include('admin.components.formActions', ['backRoute'=>'admin.reviewPlatforms.list'])
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
