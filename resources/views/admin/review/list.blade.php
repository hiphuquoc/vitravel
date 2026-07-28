@extends('layouts.admin')

@section('title', 'Cảm nhận khách hàng')
@section('page_title', 'Cảm nhận khách hàng')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Cảm nhận khách hàng</h1>
            <p class="mt-1 text-sm text-slate-500">Quản lý đánh giá hiển thị trên trang chủ và trang cảm nhận.</p>
        </div>
        <a href="{{ route('admin.reviews.view') }}" class="admin-btn admin-btn--primary">+ Thêm mới</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[220px] flex-1">
            <label class="admin-form-label">Tìm kiếm</label>
            <input type="text" name="search" value="{{ request('search') }}" class="admin-form-input" placeholder="Tên, quốc gia, nội dung…">
        </div>
        <div>
            <label class="admin-form-label">Trạng thái</label>
            <select name="status" class="admin-form-input">
                <option value="">Tất cả</option>
                <option value="published" @selected(request('status') === 'published')>Đã xuất bản</option>
                <option value="draft" @selected(request('status') === 'draft')>Nháp</option>
                <option value="hidden" @selected(request('status') === 'hidden')>Ẩn</option>
            </select>
        </div>
        <button type="submit" class="admin-btn admin-btn--secondary">Lọc</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Tour / chuyến đi</th>
                    <th>Sao</th>
                    <th>Trang chủ</th>
                    <th>Trạng thái</th>
                    <th>Thứ tự</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>{{ $review->id }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                @if ($review->avatarUrl())
                                    <img src="{{ $review->avatarUrl() }}" alt="" class="size-9 rounded-full object-cover">
                                @else
                                    <span class="flex size-9 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-500">
                                        {{ mb_substr($review->author_name, 0, 1) }}
                                    </span>
                                @endif
                                <div>
                                    <p class="font-medium">{{ $review->author_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $review->author_country ?: '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="max-w-[220px] truncate">{{ $review->question_title ?: '—' }}</td>
                        <td>{{ $review->rating }}/5</td>
                        <td>{{ $review->show_on_home ? 'Có' : 'Không' }}</td>
                        <td>
                            @php
                                $statusClass = match ($review->status) {
                                    'published' => 'bg-emerald-50 text-emerald-700',
                                    'draft' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                                $statusLabel = match ($review->status) {
                                    'published' => 'Xuất bản',
                                    'draft' => 'Nháp',
                                    'hidden' => 'Ẩn',
                                    default => $review->status,
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td>{{ $review->sort }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.reviews.view', ['id' => $review->id]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.reviews.delete', ['id' => $review->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa cảm nhận này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-slate-500">Chưa có cảm nhận nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($reviews->hasPages())
        <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
@endsection
