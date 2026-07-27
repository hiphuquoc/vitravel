@extends('layouts.admin')

@section('title', 'Bài viết')
@section('page_title', 'Bài viết')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bài viết</h1>
            <p class="mt-1 text-sm text-slate-500">Quản lý cẩm nang du lịch và blog.</p>
        </div>
        <a href="{{ route('admin.articles.view') }}" class="admin-btn admin-btn--primary">+ Thêm mới</a>
    </div>

    <form method="GET" class="admin-card mb-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="admin-form-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" class="admin-form-input">
            </div>
            <div>
                <label class="admin-form-label">Chuyên mục</label>
                <select name="blog_category_id" class="admin-form-input">
                    <option value="">Tất cả</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('blog_category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
                <a href="{{ route('admin.articles.list') }}" class="admin-btn admin-btn--secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Chuyên mục</th>
                    <th>Tác giả</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $article)
                    <tr>
                        <td>{{ $article->id }}</td>
                        <td class="font-medium">{{ $article->title }}</td>
                        <td>{{ $article->blogCategory?->name }}</td>
                        <td>{{ $article->author_name ?? '—' }}</td>
                        <td>@include('admin.partials.status-badge', ['status' => $article->status])</td>
                        <td class="text-right">
                            <a href="{{ route('admin.articles.view', ['id' => $article->id]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.articles.delete', ['id' => $article->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa bài viết này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-500">Chưa có dữ liệu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($articles->hasPages())
        <div class="mt-4">{{ $articles->links() }}</div>
    @endif
@endsection
