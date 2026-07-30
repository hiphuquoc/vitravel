@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Bốn giá trị cốt lõi trên sơ đồ vòng tròn trang Về chúng tôi.',
            'icon' => '<path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/>',
            'actionUrl' => route('admin.values.view'),
            'actionText' => 'Thêm giá trị',
        ])
        <div class="companyManagementPage_section">
            <div class="companyManagementPage_section_body">
                @if ($values->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($values as $value)
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $value->name ?: 'Giá trị #'.$value->id }}</h3>
                                    <p class="adminContentPage_card_desc">{{ $value->description }} · #{{ $value->sort }} · {{ $value->is_active ? 'Hiện' : 'Ẩn' }}</p>
                                    <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                                        <a href="{{ route('admin.values.view', ['id' => $value->id]) }}" class="adminFormActions_button">Sửa</a>
                                        <a href="{{ route('admin.values.delete', ['id' => $value->id]) }}" class="adminFormActions_button adminFormActions_button--secondary" onclick="return confirm('Xóa?')">Xóa</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có giá trị cốt lõi.</div>
                @endif
                @if ($values->hasPages())
                    <div class="adminContentPage_pagination">{{ $values->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
