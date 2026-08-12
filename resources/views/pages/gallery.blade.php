@extends('layouts.app')

@section('title', $chrome['seo_title'] ?? seo_page_title('Thư viện khoảnh khắc'))
@section('meta_description', $chrome['seo_description'] ?? apply_site_brand('Album ảnh trải nghiệm từ chuyến đi cùng :brand.'))

@section('content')
    <x-layout.page-header
        :title="$chrome['page_title'] ?? 'Thư viện khoảnh khắc'"
        :subtitle="$chrome['page_subtitle'] ?? null"
        :breadcrumbs="[['label' => $chrome['page_title'] ?? 'Thư viện khoảnh khắc']]"
        :banner-label="$chrome['banner_label'] ?? null"
    />

    <div class="container-site section-band">
        <x-shared.media-library
            kind="photo"
            :items="$albums"
            :eyebrow="$chrome['eyebrow'] ?? null"
            :title="$chrome['section_title'] ?? ($chrome['page_title'] ?? '')"
            :subtitle="$chrome['section_subtitle'] ?? null"
        />
    </div>
@endsection
