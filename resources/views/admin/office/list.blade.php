@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Địa chỉ văn phòng hiển thị ở footer và trang Liên hệ.',
            'icon' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
            'actionUrl' => route('admin.offices.view'),
            'actionText' => 'Thêm văn phòng',
        ])
        <div class="companyManagementPage_section">
            <div class="companyManagementPage_section_body">
                @if ($offices->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($offices as $office)
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $office->city_label ?: 'VP #'.$office->id }}</h3>
                                    <p class="adminContentPage_card_desc">{{ $office->address_line }} · {{ $office->phone }} · #{{ $office->sort }}</p>
                                    <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                                        <a href="{{ route('admin.offices.view', ['id' => $office->id]) }}" class="adminFormActions_button">Sửa</a>
                                        <a href="{{ route('admin.offices.delete', ['id' => $office->id]) }}" class="adminFormActions_button adminFormActions_button--secondary" onclick="return confirm('Xóa?')">Xóa</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có văn phòng.</div>
                @endif
                @if ($offices->hasPages())
                    <div class="adminContentPage_pagination">{{ $offices->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
