@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Danh sách lý do trên block “Vì sao chọn” — ảnh section chỉnh ở Công ty.',
            'icon' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>',
            'actionUrl' => route('admin.reasons.view'),
            'actionText' => 'Thêm lý do',
        ])
        <div class="companyManagementPage_section">
            <div class="companyManagementPage_section_body">
                @if ($reasons->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($reasons as $reason)
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $reason->title ?: 'Lý do #'.$reason->id }}</h3>
                                    <p class="adminContentPage_card_desc">{{ \Illuminate\Support\Str::limit($reason->description, 80) }} · #{{ $reason->sort }}</p>
                                    <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                                        <a href="{{ route('admin.reasons.view', ['id' => $reason->id]) }}" class="adminFormActions_button">Sửa</a>
                                        <a href="{{ route('admin.reasons.delete', ['id' => $reason->id]) }}" class="adminFormActions_button adminFormActions_button--secondary" onclick="return confirm('Xóa?')">Xóa</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có lý do.</div>
                @endif
                @if ($reasons->hasPages())
                    <div class="adminContentPage_pagination">{{ $reasons->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
