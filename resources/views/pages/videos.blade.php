@extends('layouts.app')

@section('title', 'Video trải nghiệm — ViTravel')
@section('meta_description', 'Video hành trình thật do khách hàng và đội ngũ ViTravel ghi lại trên khắp Đông Nam Á.')

@section('content')
    <x-layout.page-header title="Video trải nghiệm"
        subtitle="Xem tận mắt những hành trình chúng tôi đã thực hiện"
        :breadcrumbs="[['label' => 'Video trải nghiệm']]" banner-label="Ảnh banner: video trải nghiệm" />

    <x-shared.video-showcase
        :home-only="false"
        :limit="24"
        :show-cta="false"
        :section="[
            'eyebrow' => 'Thư viện video',
            'title' => 'Mọi khoảnh khắc đáng nhớ',
            'subtitle' => 'Những thước phim chân thật từ hành trình cùng ViTravel — chọn một video để xem toàn màn hình.',
            'ctaLabel' => null,
            'ctaUrl' => null,
        ]"
    />

@endsection
