@extends('layouts.app')

@section('title', ($chrome['seo_title'] ?? 'Video trải nghiệm — ViTravel'))
@section('meta_description', ($chrome['seo_description'] ?? 'Video hành trình thật từ ViTravel.'))

@section('content')
    <x-layout.page-header
        :title="$chrome['page_title'] ?? 'Video trải nghiệm'"
        :subtitle="$chrome['page_subtitle'] ?? null"
        :breadcrumbs="[['label' => $chrome['page_title'] ?? 'Video trải nghiệm']]"
        :banner-label="$chrome['banner_label'] ?? null"
    />

    <div class="container-site section-band">
        <x-shared.media-library
            kind="video"
            :items="$videos"
            :eyebrow="$chrome['eyebrow'] ?? null"
            :title="$chrome['section_title'] ?? ($chrome['page_title'] ?? '')"
            :subtitle="$chrome['section_subtitle'] ?? null"
        />
    </div>
@endsection
