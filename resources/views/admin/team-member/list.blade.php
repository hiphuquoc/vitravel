@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Quản lý thành viên đội ngũ — ảnh chân dung và thông tin hiển thị public.',
            'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'actionUrl' => route('admin.team.view'),
            'actionText' => 'Thêm thành viên',
        ])

        <div class="companyManagementPage_section companyManagementPage_section--tracked">
            <div class="companyManagementPage_section_body">
                @if ($members->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($members as $member)
                            @php $imageUrl = $member->avatarUrl(); @endphp
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_imageWrapper">
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $member->name }}" class="adminContentPage_card_image" loading="lazy">
                                    @else
                                        <div class="adminContentPage_card_placeholder">{{ strtoupper(mb_substr($member->name ?: 'T', 0, 1)) }}</div>
                                    @endif
                                    <div class="adminContentPage_card_actions">
                                        <a href="{{ route('admin.team.view', ['id' => $member->id]) }}" class="adminContentPage_card_action" title="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.team.delete', ['id' => $member->id]) }}" class="adminContentPage_card_action" title="Xóa" onclick="return confirm('Xóa thành viên này?')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/></svg>
                                        </a>
                                    </div>
                                    @if (! $member->is_active)
                                        <span class="adminContentPage_card_badge adminContentPage_card_badge--muted">Ẩn</span>
                                    @elseif ($member->show_on_home)
                                        <span class="adminContentPage_card_badge">Trang chủ</span>
                                    @endif
                                </div>
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $member->name ?: 'Thành viên #'.$member->id }}</h3>
                                    <p class="adminContentPage_card_desc">
                                        {{ $member->role ?: '—' }}
                                        @if ($member->department)
                                            · {{ $member->department }}
                                        @endif
                                        · #{{ $member->sort }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có thành viên nào. Thêm người đầu tiên cho đội ngũ.</div>
                @endif

                @if ($members->hasPages())
                    <div class="adminContentPage_pagination">{{ $members->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
