@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-slate-500">Nội dung hiển thị ở carousel “Khách hàng kể lại” và trang cảm nhận.</p>
        </div>
        <a href="{{ route('admin.reviews.list') }}" class="admin-btn admin-btn--secondary">← Quay lại</a>
    </div>

    <form method="POST" action="{{ route('admin.reviews.save') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="id" value="{{ $review?->id }}">

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Ảnh đại diện</h2>
            @include('admin.components.formImageUpload', [
                'name' => 'image',
                'label' => 'Avatar khách hàng',
                'currentImage' => $review?->avatarUrl(),
                'removeName' => 'remove_image',
                'aspectRatio' => '1/1',
                'tooltip' => 'Ảnh tròn trên card cảm nhận.',
                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB.',
            ])
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Thông tin khách hàng</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Họ tên *</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $review?->author_name) }}" class="admin-form-input" required>
                </div>
                <div>
                    <label class="admin-form-label">Quốc gia</label>
                    <input type="text" name="author_country" value="{{ old('author_country', $review?->author_country) }}" class="admin-form-input" placeholder="Việt Nam, Úc…">
                </div>
                <div>
                    <label class="admin-form-label">Mã quốc gia / cờ</label>
                    <select name="author_country_code" class="admin-form-input">
                        <option value="">— Chọn —</option>
                        @foreach ($countryCodes as $code => $label)
                            <option value="{{ $code }}" @selected(old('author_country_code', $review?->author_country_code) === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Ngày đánh giá</label>
                    <input type="date" name="reviewed_on" value="{{ old('reviewed_on', optional($review?->reviewed_on)->format('Y-m-d')) }}" class="admin-form-input">
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Nội dung cảm nhận</h2>
            <div class="grid gap-4">
                <div>
                    <label class="admin-form-label">Tour / chuyến đi</label>
                    <input type="text" name="question_title" value="{{ old('question_title', $review?->question_title) }}" class="admin-form-input" placeholder="VD: Việt Nam 10 ngày">
                </div>
                <div>
                    <label class="admin-form-label">Nội dung *</label>
                    <textarea name="content" rows="5" class="admin-form-input" required>{{ old('content', $review?->content) }}</textarea>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="admin-form-label">Số sao *</label>
                        <select name="rating" class="admin-form-input" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((int) old('rating', $review?->rating ?? 5) === $i)>{{ $i }} sao</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="admin-form-label">Số ảnh (badge +N)</label>
                        <input type="number" name="photos_count" value="{{ old('photos_count', $review?->photos_count ?? 0) }}" class="admin-form-input" min="0" max="99">
                        <p class="mt-1 text-xs text-slate-500">Dùng khi chưa upload gallery thật.</p>
                    </div>
                    <div>
                        <label class="admin-form-label">Thứ tự</label>
                        <input type="number" name="sort" value="{{ old('sort', $review?->sort ?? 0) }}" class="admin-form-input" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Ảnh chuyến đi (gallery)</h2>
            @if ($review && $review->mediaAttachments->where('role', 'gallery')->isNotEmpty())
                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($review->mediaAttachments->where('role', 'gallery') as $att)
                        <label class="relative block overflow-hidden rounded-xl border border-slate-200">
                            <img src="{{ app(\App\Services\MediaService::class)->publicUrl($att->media) }}" alt="" class="aspect-square w-full object-cover">
                            <span class="absolute inset-x-0 bottom-0 flex items-center gap-2 bg-black/55 px-2 py-1.5 text-xs text-white">
                                <input type="checkbox" name="remove_gallery[]" value="{{ $att->id }}"> Xóa
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="admin-form-label">Thêm ảnh</label>
                <input type="file" name="gallery[]" accept="image/*" multiple class="admin-form-input">
                <p class="mt-1 text-xs text-slate-500">Có thể chọn nhiều ảnh. Hiển thị tối đa 2–3 thumbnail trên card public.</p>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Hiển thị</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Trạng thái *</label>
                    <select name="status" class="admin-form-input" required>
                        <option value="published" @selected(old('status', $review?->status ?? 'published') === 'published')>Xuất bản</option>
                        <option value="draft" @selected(old('status', $review?->status) === 'draft')>Nháp</option>
                        <option value="hidden" @selected(old('status', $review?->status) === 'hidden')>Ẩn</option>
                    </select>
                </div>
                <div class="flex flex-col justify-end gap-2 pb-1">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $review?->show_on_home ?? true))> Hiển thị trên trang chủ</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $review?->is_featured ?? true))> Đánh dấu nổi bật</label>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn admin-btn--primary">Lưu</button>
            @if ($review)
                <a href="{{ route('admin.reviews.delete', ['id' => $review->id]) }}" class="admin-btn admin-btn--secondary text-red-600" onclick="return confirm('Xóa cảm nhận này?')">Xóa</a>
            @endif
        </div>
    </form>
@endsection
