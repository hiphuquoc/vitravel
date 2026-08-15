@extends('layouts.app')

@section('title', seo_page_title($tour['title']))
@section('meta_description', $tour['highlightsIntro'])

@section('content')
    <x-tour.detail :item="$tour" type="tour"
        :breadcrumbs="[
            ['label' => 'Tour', 'url' => locale_route('tours.hub')],
            ['label' => tour_listing_label($tour['country'] ?? ''), 'url' => locale_route('tours.index', $tour['countrySlug'])],
            ['label' => $tour['title']],
        ]" />
@endsection
