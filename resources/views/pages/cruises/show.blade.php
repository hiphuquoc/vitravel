@extends('layouts.app')

@section('title', seo_page_title($cruise['title']))
@section('meta_description', $cruise['highlightsIntro'])

@section('content')
    <x-tour.detail :item="$cruise" type="cruise"
        :breadcrumbs="[
            ['label' => 'Du thuyền', 'url' => locale_route('cruises.hub')],
            ['label' => $cruise['typeName'], 'url' => locale_route('cruises.index', $cruise['typeSlug'])],
            ['label' => $cruise['title']],
        ]" />
@endsection
