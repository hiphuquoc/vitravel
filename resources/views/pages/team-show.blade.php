@extends('layouts.app')

@section('title', ($member['name'] ?? 'Thành viên').' — Đội ngũ ViTravel')
@section('meta_description', $member['short_bio'] ?? ('Hồ sơ '.($member['name'] ?? '').' — đội ngũ chuyên gia bản địa ViTravel.'))

@section('content')
    <x-layout.page-header
        :title="$member['name'] ?? 'Thành viên'"
        :subtitle="$member['role'] ?? null"
        :breadcrumbs="[
            ['label' => 'Đội ngũ', 'url' => locale_route('team')],
            ['label' => $member['name'] ?? 'Hồ sơ'],
        ]"
    />

    <x-team.profile :member="$member" />
@endsection
