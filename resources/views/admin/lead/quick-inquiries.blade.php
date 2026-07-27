@extends('layouts.admin')

@section('title', 'Yêu cầu nhanh')
@section('page_title', 'Yêu cầu nhanh')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Yêu cầu nhanh</h1>
        <p class="mt-1 text-sm text-slate-500">Quản lý lead từ form yêu cầu nhanh trên website.</p>
    </div>

    <form method="GET" class="admin-card mb-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="admin-form-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" class="admin-form-input" placeholder="Tên, email, SĐT...">
            </div>
            <div>
                <label class="admin-form-label">Trạng thái</label>
                <select name="status" class="admin-form-input">
                    <option value="">Tất cả</option>
                    @foreach (['new', 'contacted', 'quoted', 'closed', 'spam'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
                <a href="{{ route('admin.leads.quickInquiries') }}" class="admin-btn admin-btn--secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Liên hệ</th>
                    <th>Gói liên quan</th>
                    <th>Nội dung</th>
                    <th>Ngày</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leads as $lead)
                    <tr>
                        <td>{{ $lead->id }}</td>
                        <td class="font-medium">{{ $lead->name }}</td>
                        <td>
                            <div>{{ $lead->email }}</div>
                            <div class="text-slate-500">{{ $lead->phone }}</div>
                        </td>
                        <td>{{ $lead->relatedPackage?->title ?? '—' }}</td>
                        <td class="max-w-xs truncate" title="{{ $lead->message }}">{{ Str::limit($lead->message, 60) }}</td>
                        <td>{{ $lead->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.leads.quickInquiries.status') }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="id" value="{{ $lead->id }}">
                                <select name="status" class="admin-form-input !py-1 text-xs" onchange="this.form.submit()">
                                    @foreach (['new', 'contacted', 'quoted', 'closed', 'spam'] as $st)
                                        <option value="{{ $st }}" @selected($lead->status === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-500">Chưa có yêu cầu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($leads->hasPages())
        <div class="mt-4">{{ $leads->links() }}</div>
    @endif
@endsection
