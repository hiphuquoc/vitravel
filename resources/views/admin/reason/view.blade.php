@extends('layouts.admin')
@section('title', $title)
@section('content')
<form action="{{ route('admin.reasons.save') }}" method="POST" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $reason?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Tiêu đề + mô tả từng lý do (đa ngôn ngữ). Ảnh mockup chỉnh ở Công ty.',
                'icon' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>',
                'backUrl' => route('admin.reasons.list'),
                'backText' => 'Quay lại',
            ])
            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $reason ?? new \App\Models\ReasonToChooseUs(),
                    'language' => $locale,
                    'routeName' => 'admin.reasons.view',
                ])
            </div>
            @if ($errors->any())
                <div class="adminFormPage_errors"><div class="adminFormPage_errors_content"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif
            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Tiêu đề','name'=>'title','required'=>true,'class'=>'adminFormGrid__full','value'=>old('title',$translation?->title)])
                                @include('admin.components.formField', ['label'=>'Mô tả','name'=>'description','type'=>'textarea','rows'=>3,'class'=>'adminFormGrid__full','value'=>old('description',$translation?->description)])
                                @include('admin.components.formField', ['label'=>'Thứ tự','name'=>'sort','type'=>'number','value'=>old('sort',$reason?->sort ?? 0)])
                                @include('admin.components.formField', ['label'=>'Trạng thái','name'=>'is_active','type'=>'checkbox','value'=>old('is_active',$reason?->is_active ?? true),'checkboxLabel'=>'Đang hiển thị'])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar"><div class="adminFormSidebar_sticky">
                        @include('admin.components.formActions', ['backRoute'=>'admin.reasons.list'])
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
