@extends('layouts.app')

@section('title', 'Đội ngũ của chúng tôi — ViTravel')
@section('meta_description', 'Gặp gỡ đội ngũ chuyên gia bản địa của ViTravel — những người trực tiếp thiết kế và đồng hành cùng hành trình của bạn.')

@section('content')
    <x-layout.page-header title="Đội ngũ của chúng tôi"
        subtitle="Những người bản địa yêu nghề, trực tiếp thiết kế và chăm chút từng hành trình"
        :breadcrumbs="[
            ['label' => 'Về chúng tôi', 'url' => route('about')],
            ['label' => 'Đội ngũ'],
        ]" banner-label="Ảnh banner: đội ngũ ViTravel" />

    <x-shared.team-grid :team="$team" :show-more="false" />
    <x-shared.testimonial-carousel class="pt-0" />
@endsection
