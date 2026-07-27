@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Quản lý danh sách '.strtolower($title).'.',
            'icon' => '<path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
            'actionUrl' => route($type === 'cruise' ? 'admin.packages.cruises.view' : 'admin.packages.tours.view'),
            'actionText' => 'Thêm mới',
        ])

        <div class="companyManagementPage_section companyManagementPage_section--tracked">
            <div class="companyManagementPage_section_body">
                <form method="GET" class="adminContentPage_searchBar">
                    <div class="adminContentPage_searchBar_inputWrapper">
                        <input type="text" class="adminContentPage_searchBar_input" name="search" placeholder="Tìm theo tiêu đề..." value="{{ request('search') }}">
                        <select name="country_id" class="adminFormField_input adminFormField_input--select">
                            <option value="">Tất cả quốc gia</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected(request('country_id') == $country->id)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="adminContentPage_searchBar_button"><span>Lọc</span></button>
                    </div>
                </form>

                @if ($packages->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($packages as $package)
                            @php
                                $seo = $package->seoEntry?->translation();
                                $viewRoute = $type === 'cruise' ? 'admin.packages.cruises.view' : 'admin.packages.tours.view';
                                $deleteRoute = $type === 'cruise' ? 'admin.packages.cruises.delete' : 'admin.packages.tours.delete';
                            @endphp
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_imageWrapper">
                                    <div class="adminContentPage_card_placeholder">{{ strtoupper(substr($package->title ?? 'T', 0, 1)) }}</div>
                                    <div class="adminContentPage_card_actions">
                                        @if ($seo?->slug_full)
                                            <a href="{{ seo_public_url($seo) }}" target="_blank" class="adminContentPage_card_action" title="Xem trang">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route($viewRoute, ['id' => $package->id]) }}" class="adminContentPage_card_action" title="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="{{ route($deleteRoute, ['id' => $package->id]) }}" class="adminContentPage_card_action" title="Xóa" onclick="return confirm('Xóa gói này?')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $package->title }}</h3>
                                    <p class="adminContentPage_card_desc">{{ $package->country?->name }} · {{ $package->duration_days }} ngày</p>
                                    @if ($seo?->slug_full)
                                        <p class="adminContentPage_card_url">{{ $seo->slug_full }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có dữ liệu.</div>
                @endif

                @if ($packages->hasPages())
                    <div class="adminContentPage_pagination">{{ $packages->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
