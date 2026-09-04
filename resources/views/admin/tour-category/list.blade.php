@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Trang danh mục tour theo thời lượng, vùng, tính chất — dùng cho listing và drawer menu Tour.',
            'icon' => '<path d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125v-1.5M3.375 19.5h-.375A1.125 1.125 0 012.25 18.375v-1.5m15.75 0h.375a1.125 1.125 0 001.125-1.125v-1.5m-15.75 0v-3.375A1.125 1.125 0 014.125 12h15.75a1.125 1.125 0 011.125 1.125v3.375"/>',
            'actionUrl' => route('admin.tourCategories.view'),
            'actionText' => 'Thêm mới',
        ])

        <div class="companyManagementPage_section companyManagementPage_section--tracked">
            <div class="companyManagementPage_section_body">
                <form method="GET" class="adminContentPage_searchBar">
                    <div class="adminContentPage_searchBar_inputWrapper">
                        <input type="text" class="adminContentPage_searchBar_input" name="search" placeholder="Tìm theo tên..." value="{{ request('search') }}">
                        <select name="country_id" class="adminFormField_input adminFormField_input--select">
                            <option value="">Tất cả điểm đến</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected(request('country_id') == $country->id)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        <select name="type" class="adminFormField_input adminFormField_input--select">
                            <option value="">Tất cả loại</option>
                            @foreach ($typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="adminContentPage_searchBar_button"><span>Lọc</span></button>
                    </div>
                </form>

                @if ($categories->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($categories as $category)
                            @php
                                $seo = $category->seoEntry?->translation();
                            @endphp
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_imageWrapper">
                                    <div class="adminContentPage_card_placeholder">{{ strtoupper(mb_substr($category->name ?? 'D', 0, 1)) }}</div>
                                    <div class="adminContentPage_card_actions">
                                        @if ($seo?->slug_full)
                                            <a href="{{ seo_public_url($seo) }}" target="_blank" class="adminContentPage_card_action" title="Xem trang">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.tourCategories.view', ['id' => $category->id]) }}" class="adminContentPage_card_action" title="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.tourCategories.delete', ['id' => $category->id]) }}" class="adminContentPage_card_action" title="Xóa" onclick="return confirm('Xóa danh mục này?')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $category->name }}</h3>
                                    <p class="adminContentPage_card_desc">
                                        {{ $typeOptions[$category->type] ?? $category->type }}
                                        @if ($category->country)
                                            · {{ $category->country->name }}
                                        @endif
                                    </p>
                                    @if ($seo?->slug_full)
                                        <p class="adminContentPage_card_url">{{ $seo->slug_full }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có danh mục tour.</div>
                @endif

                @if ($categories->hasPages())
                    <div class="adminContentPage_pagination">{{ $categories->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
