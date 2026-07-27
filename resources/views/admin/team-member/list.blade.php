@extends('layouts.admin')

@section('title', 'Đội ngũ')
@section('page_title', 'Đội ngũ')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Đội ngũ</h1>
            <p class="mt-1 text-sm text-slate-500">Quản lý thành viên đội ngũ công ty.</p>
        </div>
        <a href="{{ route('admin.team.view') }}" class="admin-btn admin-btn--primary">+ Thêm mới</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Vai trò</th>
                    <th>Phòng ban</th>
                    <th>Trang chủ</th>
                    <th>Thứ tự</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td class="font-medium">{{ $member->name }}</td>
                        <td>{{ $member->role }}</td>
                        <td>{{ $member->department ?? '—' }}</td>
                        <td>{{ $member->show_on_home ? 'Có' : 'Không' }}</td>
                        <td>{{ $member->sort }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.team.view', ['id' => $member->id]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.team.delete', ['id' => $member->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa thành viên này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-500">Chưa có thành viên.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($members->hasPages())
        <div class="mt-4">{{ $members->links() }}</div>
    @endif
@endsection
