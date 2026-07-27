@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
        </div>
        <a href="{{ route('admin.team.list') }}" class="admin-btn admin-btn--secondary">← Quay lại</a>
    </div>

    @include('admin.partials.language-tabs', ['languages' => $languages, 'locale' => $locale])

    <form method="POST" action="{{ route('admin.team.save') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="id" value="{{ $member?->id }}">
        <input type="hidden" name="language" value="{{ $locale }}">

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Ảnh đại diện</h2>
            @include('admin.components.formImageUpload', [
                'name' => 'image',
                'label' => 'Ảnh chân dung',
                'currentImage' => $member?->avatarUrl(),
                'removeName' => 'remove_image',
                'aspectRatio' => '1/1',
                'tooltip' => 'Ảnh hiển thị trên trang chủ / trang giới thiệu đội ngũ.',
                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Tự tối ưu về WebP ≤1920px.',
            ])
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Thông tin chung</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Phòng ban</label>
                    <input type="text" name="department" value="{{ old('department', $member?->department) }}" class="admin-form-input">
                </div>
                <div>
                    <label class="admin-form-label">Thứ tự</label>
                    <input type="number" name="sort" value="{{ old('sort', $member?->sort ?? 0) }}" class="admin-form-input" min="0">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member?->is_active ?? true))> Hoạt động</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $member?->show_on_home))> Hiển thị trang chủ</label>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Nội dung ({{ strtoupper($locale) }})</h2>
            <div class="grid gap-4">
                <div>
                    <label class="admin-form-label">Họ tên *</label>
                    <input type="text" name="name" value="{{ old('name', $translation?->name) }}" class="admin-form-input" required>
                </div>
                <div>
                    <label class="admin-form-label">Vai trò</label>
                    <input type="text" name="role" value="{{ old('role', $translation?->role) }}" class="admin-form-input">
                </div>
                <div>
                    <label class="admin-form-label">Giới thiệu ngắn</label>
                    <textarea name="short_bio" rows="4" class="admin-form-input">{{ old('short_bio', $translation?->short_bio) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn admin-btn--primary">Lưu</button>
            @if ($member)
                <a href="{{ route('admin.team.delete', ['id' => $member->id]) }}" class="admin-btn admin-btn--secondary text-red-600" onclick="return confirm('Xóa thành viên này?')">Xóa</a>
            @endif
        </div>
    </form>
@endsection
