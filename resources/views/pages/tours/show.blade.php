@extends('layouts.app')

@section('title', $tour['title'] . ' — ViTravel')
@section('meta_description', $tour['highlightsIntro'])

@section('content')
    <x-tour.detail :item="$tour" type="tour" :related="$related"
        :breadcrumbs="[
            ['label' => 'Tour', 'url' => route('tours.index', 'viet-nam')],
            ['label' => 'Tour ' . $tour['country'], 'url' => route('tours.index', $tour['countrySlug'])],
            ['label' => $tour['title']],
        ]" />
@endsection
