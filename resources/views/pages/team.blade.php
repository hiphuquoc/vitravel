@extends('layouts.app')

@section('title', ($chrome['seo_title'] ?? 'Đội ngũ của chúng tôi — ViTravel'))
@section('meta_description', ($chrome['seo_description'] ?? 'Gặp gỡ đội ngũ chuyên gia bản địa của ViTravel.'))

@section('content')
    <x-layout.page-header
        :title="$chrome['page_title'] ?? 'Đội ngũ của chúng tôi'"
        :subtitle="$chrome['page_subtitle'] ?? null"
        :breadcrumbs="[
            ['label' => app()->getLocale() === 'vi' ? 'Về chúng tôi' : 'About us', 'url' => locale_route('about')],
            ['label' => $chrome['page_title'] ?? 'Đội ngũ'],
        ]"
        :banner-label="$chrome['banner_label'] ?? null"
    />

    <x-shared.team-grid
        :team="$team"
        :show-more="false"
        :section="[
            'eyebrow' => $chrome['eyebrow'] ?? null,
            'title' => $chrome['section_title'] ?? ($chrome['page_title'] ?? null),
            'subtitle' => $chrome['section_subtitle'] ?? null,
        ]"
    />
@endsection
