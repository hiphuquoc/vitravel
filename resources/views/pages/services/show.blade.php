@extends('layouts.app')

@php
    $metaDesc = trim((string) ($service['seoDescription'] ?? ''));
    if ($metaDesc === '') {
        $metaDesc = trim((string) ($service['summary'] ?? ''));
    }
    if ($metaDesc === '') {
        $metaDesc = trim(implode(' — ', array_filter([
            (string) ($service['title'] ?? ''),
            (string) ($service['propertyTypeLabel'] ?? ''),
            (string) ($service['address'] ?? $service['location'] ?? ''),
        ])));
    }
    $metaDesc = \Illuminate\Support\Str::limit(strip_tags($metaDesc), 160, '');
@endphp

@section('title', seo_page_title($service['title']))
@section('meta_description', $metaDesc)

@section('content')
    @if (($cluster ?? '') === 'stay' || ! empty($service['isStay']))
        <x-stay.detail
            :service="$service"
            :hub="$hub"
            :related="$related"
            :breadcrumbs="[
                ['label' => $hub['navLabel'] ?? $hub['title'], 'url' => locale_route('services.hub', ['cluster' => $cluster])],
                ['label' => $service['categoryName'] ?? '', 'url' => locale_route('services.index', ['cluster' => $cluster, 'category' => $service['categorySlug']])],
                ['label' => $service['title']],
            ]" />
    @else
        <x-service.detail
            :service="$service"
            :hub="$hub"
            :related="$related"
            :breadcrumbs="[
                ['label' => $hub['navLabel'] ?? $hub['title'], 'url' => locale_route('services.hub', ['cluster' => $cluster])],
                ['label' => $service['categoryName'] ?? '', 'url' => locale_route('services.index', ['cluster' => $cluster, 'category' => $service['categorySlug']])],
                ['label' => $service['title']],
            ]" />
    @endif
@endsection
