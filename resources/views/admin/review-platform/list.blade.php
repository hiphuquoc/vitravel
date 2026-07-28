@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Tripadvisor / Google / Trustpilot… — chọn hiển thị trên trang chủ tại Nội dung trang chủ.',
            'icon' => '<polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9"/>',
            'actionUrl' => route('admin.reviewPlatforms.view'),
            'actionText' => 'Thêm nền tảng',
        ])
        <div class="companyManagementPage_section">
            <div class="companyManagementPage_section_body">
                @if ($platforms->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($platforms as $p)
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $p->name }}</h3>
                                    <p class="adminContentPage_card_desc">
                                        {{ $p->code }} · {{ $p->rating }}/5 ({{ $p->review_count }})
                                        @if (! $p->is_active) · Ẩn @endif
                                    </p>
                                    <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                                        <a href="{{ route('admin.reviewPlatforms.view', ['id' => $p->id]) }}" class="adminFormActions_button">Sửa</a>
                                        <a href="{{ route('admin.reviewPlatforms.delete', ['id' => $p->id]) }}" class="adminFormActions_button adminFormActions_button--secondary" onclick="return confirm('Xóa?')">Xóa</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có nền tảng.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
