@extends('layouts.app')

@section('title', $chrome['seo_title'] ?? seo_page_title('Video trải nghiệm'))
@section('meta_description', $chrome['seo_description'] ?? apply_site_brand('Video hành trình thật từ :brand.'))

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
