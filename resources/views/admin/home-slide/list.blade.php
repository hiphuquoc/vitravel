@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Quản lý slider hero trang chủ — ảnh lưu trên Google Cloud Storage.',
            'icon' => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
            'actionUrl' => route('admin.homeSlides.view'),
            'actionText' => 'Thêm slide',
        ])

        <div class="companyManagementPage_section companyManagementPage_section--tracked">
            <div class="companyManagementPage_section_body">
                <form method="GET" class="adminContentPage_searchBar">
                    <div class="adminContentPage_searchBar_inputWrapper">
                        <input type="text" class="adminContentPage_searchBar_input" name="search" placeholder="Tìm theo tiêu đề..." value="{{ request('search') }}">
                        <select name="status" class="adminFormField_input adminFormField_input--select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" @selected(request('status') === 'active')>Đang hiển thị</option>
                            <option value="hidden" @selected(request('status') === 'hidden')>Ẩn</option>
                        </select>
                        <button type="submit" class="adminContentPage_searchBar_button"><span>Lọc</span></button>
                    </div>
                </form>

                @if ($slides->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($slides as $slide)
                            @php
                                $translation = $slide->translation();
                                $imageUrl = $slide->imageUrl();
                            @endphp
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_imageWrapper">
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="" class="adminContentPage_card_image" loading="lazy">
                                    @else
                                        <div class="adminContentPage_card_placeholder">{{ strtoupper(mb_substr($translation?->title ?? 'S', 0, 1)) }}</div>
                                    @endif
                                    <div class="adminContentPage_card_actions">
                                        <a href="{{ route('admin.homeSlides.view', ['id' => $slide->id]) }}" class="adminContentPage_card_action" title="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.homeSlides.delete', ['id' => $slide->id]) }}" class="adminContentPage_card_action" title="Xóa" onclick="return confirm('Xóa slide này?')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/></svg>
                                        </a>
                                    </div>
                                    @if (! $slide->is_active)
                                        <span class="adminContentPage_card_badge adminContentPage_card_badge--muted">Ẩn</span>
                                    @endif
                                </div>
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">
                                        {{ $translation?->title ?: 'Slide #'.$slide->id }}
                                        @if ($translation?->title_accent)
                                            <span class="text-muted">{{ $translation->title_accent }}</span>
                                        @endif
                                    </h3>
                                    <p class="adminContentPage_card_desc">
                                        Thứ tự {{ $slide->sort }} · {{ $alignOptions[$slide->text_align] ?? $slide->text_align }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có slide nào. Thêm slide đầu tiên cho trang chủ.</div>
                @endif

                @if ($slides->hasPages())
                    <div class="adminContentPage_pagination">{{ $slides->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
