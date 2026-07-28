@extends('layouts.admin')
@section('title', $title)
@section('content')
<form action="{{ route('admin.offices.save') }}" method="POST" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $office?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">
    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Địa chỉ & liên hệ văn phòng.',
                'icon' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
                'backUrl' => route('admin.offices.list'),
                'backText' => 'Quay lại',
            ])
            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $office ?? new \App\Models\Office(),
                    'language' => $locale,
                    'routeName' => 'admin.offices.view',
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
                                @include('admin.components.formField', ['label'=>'Thành phố / nhãn','name'=>'city_label','required'=>true,'value'=>old('city_label',$translation?->city_label)])
                                @include('admin.components.formField', ['label'=>'Địa chỉ','name'=>'address_line','required'=>true,'type'=>'textarea','rows'=>2,'class'=>'adminFormGrid__full','value'=>old('address_line',$translation?->address_line)])
                                @php
                                    $opts = ['' => '— Không gắn —'];
                                    foreach ($countries as $c) { $opts[$c->id] = $c->name ?: '#'.$c->id; }
                                @endphp
                                @include('admin.components.formField', ['label'=>'Quốc gia','name'=>'country_id','type'=>'select','options'=>$opts,'value'=>old('country_id',$office?->country_id)])
                                @include('admin.components.formField', ['label'=>'Điện thoại','name'=>'phone','value'=>old('phone',$office?->phone)])
                                @include('admin.components.formField', ['label'=>'WhatsApp','name'=>'whatsapp','value'=>old('whatsapp',$office?->whatsapp)])
                                @include('admin.components.formField', ['label'=>'Email','name'=>'email','type'=>'email','value'=>old('email',$office?->email)])
                                @include('admin.components.formField', ['label'=>'Map embed URL','name'=>'map_embed_url','class'=>'adminFormGrid__full','value'=>old('map_embed_url',$office?->map_embed_url)])
                                @include('admin.components.formField', ['label'=>'Thứ tự','name'=>'sort','type'=>'number','value'=>old('sort',$office?->sort ?? 0)])
                                @include('admin.components.formField', ['label'=>'Trạng thái','name'=>'is_active','type'=>'checkbox','value'=>old('is_active',$office?->is_active ?? true),'checkboxLabel'=>'Đang hiển thị'])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar"><div class="adminFormSidebar_sticky">
                        @include('admin.components.formActions', ['backRoute'=>'admin.offices.list'])
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
