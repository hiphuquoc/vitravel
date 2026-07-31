@extends('layouts.app')

@section('title', $service['title'] . ' — ViTravel')
@section('meta_description', $service['summary'] ?? '')

@section('content')
    <x-service.detail
        :service="$service"
        :hub="$hub"
        :related="$related"
        :breadcrumbs="[
            ['label' => $hub['navLabel'] ?? $hub['title'], 'url' => locale_route('services.hub', ['cluster' => $cluster])],
            ['label' => $service['categoryName'] ?? '', 'url' => locale_route('services.index', ['cluster' => $cluster, 'category' => $service['categorySlug']])],
            ['label' => $service['title']],
        ]" />
@endsection
