@extends('layouts.admin')
@section('title', $title)
@section('content')
<form action="{{ route('admin.values.save') }}" method="POST" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $value?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Tên và mô tả ngắn — đa ngôn ngữ.',
                'icon' => '<path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/>',
                'backUrl' => route('admin.values.list'),
                'backText' => 'Quay lại',
            ])
            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $value ?? new \App\Models\CompanyValue(),
                    'language' => $locale,
                    'routeName' => 'admin.values.view',
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
                                @include('admin.components.formField', ['label'=>'Tên','name'=>'name','required'=>true,'value'=>old('name',$translation?->name)])
                                @include('admin.components.formField', ['label'=>'Thứ tự','name'=>'sort','type'=>'number','value'=>old('sort',$value?->sort ?? 0)])
                                @include('admin.components.formField', ['label'=>'Mô tả','name'=>'description','type'=>'textarea','rows'=>3,'class'=>'adminFormGrid__full','value'=>old('description',$translation?->description)])
                                @include('admin.components.formField', ['label'=>'Trạng thái','name'=>'is_active','type'=>'checkbox','value'=>old('is_active',$value?->is_active ?? true),'checkboxLabel'=>'Đang hiển thị'])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar"><div class="adminFormSidebar_sticky">
                        @include('admin.components.formActions', ['backRoute'=>'admin.values.list'])
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
