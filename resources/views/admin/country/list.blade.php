@extends('layouts.admin')

@section('title', 'Quốc gia')
@section('page_title', 'Quốc gia')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Quốc gia</h1>
            <p class="mt-1 text-sm text-slate-500">Quản lý danh sách quốc gia đích.</p>
        </div>
        <a href="{{ route('admin.countries.view') }}" class="admin-btn admin-btn--primary">+ Thêm mới</a>
    </div>

    <form method="GET" class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="min-w-[240px] flex-1">
                <label class="admin-form-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" class="admin-form-input" placeholder="Tên quốc gia...">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
                <a href="{{ route('admin.countries.list') }}" class="admin-btn admin-btn--secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Mã</th>
                    <th>Slug</th>
                    <th>Trạng thái</th>
                    <th>Thứ tự</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($countries as $country)
                    <tr>
                        <td>{{ $country->id }}</td>
                        <td class="font-medium">{{ $country->name }}</td>
                        <td>{{ $country->code }}</td>
                        <td>{{ $country->slug }}</td>
                        <td>
                            <span class="text-xs {{ $country->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $country->is_active ? 'Hoạt động' : 'Ẩn' }}
                            </span>
                        </td>
                        <td>{{ $country->sort }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.countries.view', ['id' => $country->id]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.countries.delete', ['id' => $country->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa quốc gia này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-500">Chưa có dữ liệu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($countries->hasPages())
        <div class="mt-4">{{ $countries->links() }}</div>
    @endif
@endsection
