@extends('layouts.admin')

@section('title', 'Bình luận')
@section('page_title', 'Bình luận')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Bình luận bài viết</h1>
        <p class="mt-1 text-sm text-slate-500">Duyệt hoặc từ chối bình luận từ độc giả.</p>
    </div>

    <form method="GET" class="admin-card mb-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="admin-form-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" class="admin-form-input">
            </div>
            <div>
                <label class="admin-form-label">Trạng thái</label>
                <select name="status" class="admin-form-input">
                    <option value="">Tất cả</option>
                    @foreach (['pending', 'approved', 'rejected'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
                <a href="{{ route('admin.leads.comments') }}" class="admin-btn admin-btn--secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người gửi</th>
                    <th>Bài viết</th>
                    <th>Nội dung</th>
                    <th>Ngày</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comments as $comment)
                    <tr>
                        <td>{{ $comment->id }}</td>
                        <td>
                            <div class="font-medium">{{ $comment->full_name }}</div>
                            <div class="text-xs text-slate-500">{{ $comment->email }}</div>
                        </td>
                        <td>{{ $comment->article?->title ?? '—' }}</td>
                        <td class="max-w-sm">{{ Str::limit($comment->content, 100) }}</td>
                        <td>{{ $comment->created_at?->format('d/m/Y H:i') }}</td>
                        <td>@include('admin.partials.status-badge', ['status' => $comment->status])</td>
                        <td class="text-right whitespace-nowrap">
                            @if ($comment->status !== 'approved')
                                <a href="{{ route('admin.comments.approve', ['id' => $comment->id]) }}" class="text-emerald-700 hover:underline">Duyệt</a>
                            @endif
                            @if ($comment->status !== 'rejected')
                                <a href="{{ route('admin.comments.reject', ['id' => $comment->id]) }}" class="ml-3 text-red-600 hover:underline">Từ chối</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-500">Chưa có bình luận.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($comments->hasPages())
        <div class="mt-4">{{ $comments->links() }}</div>
    @endif
@endsection
