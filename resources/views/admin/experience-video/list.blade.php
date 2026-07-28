@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Quản lý video trải nghiệm — thumbnail, YouTube/Vimeo, hiển thị trang chủ.',
            'icon' => '<polygon points="5 3 19 12 5 21 5 3"/>',
            'actionUrl' => route('admin.videos.view'),
            'actionText' => 'Thêm video',
        ])

        <form method="get" action="{{ route('admin.videos.list') }}" class="adminContentPage_filters" style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-bottom:1.25rem;">
            <input type="search" name="q" value="{{ $q }}" placeholder="Tìm tiêu đề / YouTube…" class="adminFormField_input" style="max-width:16rem;">
            <select name="status" class="adminFormField_input" style="max-width:10rem;">
                <option value="">Tất cả trạng thái</option>
                <option value="published" @selected($status === 'published')>Published</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
            <button type="submit" class="adminFormActions_button">Lọc</button>
        </form>

        <div class="companyManagementPage_section companyManagementPage_section--tracked">
            <div class="companyManagementPage_section_body">
                @if ($videos->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($videos as $video)
                            @php $imageUrl = $video->thumbnailUrl('thumb'); @endphp
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_imageWrapper">
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $video->title }}" class="adminContentPage_card_image" loading="lazy">
                                    @else
                                        <div class="adminContentPage_card_placeholder">▶</div>
                                    @endif
                                    <div class="adminContentPage_card_actions">
                                        <a href="{{ route('admin.videos.view', ['id' => $video->id]) }}" class="adminContentPage_card_action" title="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.videos.delete', ['id' => $video->id]) }}" class="adminContentPage_card_action" title="Xóa" onclick="return confirm('Xóa video này?')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/></svg>
                                        </a>
                                    </div>
                                    @if ($video->status !== 'published')
                                        <span class="adminContentPage_card_badge adminContentPage_card_badge--muted">Draft</span>
                                    @elseif ($video->show_on_home)
                                        <span class="adminContentPage_card_badge">Trang chủ</span>
                                    @endif
                                    @if ($video->duration)
                                        <span class="adminContentPage_card_badge" style="left:auto;right:0.5rem;bottom:0.5rem;top:auto;">{{ $video->duration }}</span>
                                    @endif
                                </div>
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $video->title ?: 'Video #'.$video->id }}</h3>
                                    <p class="adminContentPage_card_desc">
                                        {{ strtoupper($video->provider() ?? '—') }}
                                        @if ($video->tag) · {{ $video->tag }} @endif
                                        · #{{ $video->sort }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có video nào. Thêm video đầu tiên để hiển thị trên trang chủ.</div>
                @endif

                @if ($videos->hasPages())
                    <div class="adminContentPage_pagination">{{ $videos->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
