@extends('layouts.app')

@section('title', $cruise['title'] . ' — ViTravel')
@section('meta_description', $cruise['highlightsIntro'])

@section('content')
    <x-tour.detail :item="$cruise" type="cruise" :related="$related"
        :breadcrumbs="[
            ['label' => 'Du thuyền', 'url' => route('cruises.index', 'du-thuyen-ha-long')],
            ['label' => $cruise['typeName'], 'url' => route('cruises.index', $cruise['typeSlug'])],
            ['label' => $cruise['title']],
        ]" />
@endsection
