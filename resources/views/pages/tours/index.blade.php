@extends('layouts.app')

@section('title', $listing['seoTitle'] ?: seo_page_title($listing['title'] ?? 'Tour'))
@section('meta_description', $listing['seoDescription'] ?? '')

@section('content')
    @include('partials.listing-catalog', ['listing' => $listing])
@endsection
