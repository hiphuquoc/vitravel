@extends('layouts.admin')
@section('title', $title)
@section('content')
<form action="{{ route('admin.referencePersons.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $person?->id }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Thông tin liên hệ + ảnh chân dung đại diện.',
                'icon' => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                'backUrl' => route('admin.referencePersons.list'),
                'backText' => 'Quay lại',
            ])
            @if ($errors->any())
                <div class="adminFormPage_errors"><div class="adminFormPage_errors_content"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif
            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_header"><div class="adminFormSection_header_info">
                            <h2 class="adminFormSection_title">Ảnh chân dung</h2>
                        </div></div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formImageUpload', [
                                'label' => 'Ảnh',
                                'name' => 'image',
                                'currentImage' => $person?->photoUrl('thumb'),
                                'removeName' => 'remove_image',
                                'aspectRatio' => '1/1',
                                'maxKb' => $uploadMaxKb,
                                'hint' => 'JPG, PNG, WebP — tối đa '.$uploadMaxLabel.'.',
                            ])
                        </div>
                    </div>
                    <div class="adminFormSection">
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', ['label'=>'Họ tên','name'=>'name','required'=>true,'value'=>old('name',$person?->name)])
                                @php
                                    $opts = ['' => '— Chọn quốc gia —'];
                                    foreach ($countries as $c) { $opts[$c->id] = $c->name ?: '#'.$c->id; }
                                @endphp
                                @include('admin.components.formField', ['label'=>'Quốc gia','name'=>'country_id','type'=>'select','options'=>$opts,'value'=>old('country_id',$person?->country_id)])
                                @include('admin.components.formField', ['label'=>'Email','name'=>'email','type'=>'email','value'=>old('email',$person?->email)])
                                @include('admin.components.formField', ['label'=>'Điện thoại','name'=>'phone','value'=>old('phone',$person?->phone)])
                                @include('admin.components.formField', ['label'=>'Skype','name'=>'skype','value'=>old('skype',$person?->skype)])
                                @include('admin.components.formField', ['label'=>'Thứ tự','name'=>'sort','type'=>'number','value'=>old('sort',$person?->sort ?? 0)])
                                @include('admin.components.formField', ['label'=>'Trạng thái','name'=>'is_active','type'=>'checkbox','value'=>old('is_active',$person?->is_active ?? true),'checkboxLabel'=>'Đang hiển thị'])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar"><div class="adminFormSidebar_sticky">
                        @include('admin.components.formActions', ['backRoute'=>'admin.referencePersons.list'])
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
