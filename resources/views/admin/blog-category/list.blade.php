@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
@php
    $hubTrans = $hubSeo->translation($locale) ?? $hubSeo->translation();
@endphp
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                Cây SEO cha → con (Hitour): parent · level · slug · slug_full. Hub Cẩm nang = cấp 1.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.blogCategories.view') }}" class="admin-btn admin-btn--primary">+ Thêm chuyên mục</a>
        </div>
    </div>

    <form method="GET" class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="min-w-[240px] flex-1">
                <label class="admin-form-label">Tìm kiếm chuyên mục</label>
                <input type="text" name="search" value="{{ request('search') }}" class="admin-form-input" placeholder="Tên chuyên mục...">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="admin-btn admin-btn--primary">Lọc</button>
                <a href="{{ route('admin.blogCategories.list') }}" class="admin-btn admin-btn--secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cấp</th>
                    <th>Parent</th>
                    <th>Tên</th>
                    <th>Level</th>
                    <th>Slug</th>
                    <th>Slug full</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @unless (request()->filled('search'))
                    <tr class="bg-slate-50">
                        <td class="font-semibold text-emerald-800">1</td>
                        <td><code>0</code></td>
                        <td class="font-semibold">{{ $hubTrans?->title ?: 'Cẩm nang du lịch' }}</td>
                        <td>{{ $hubSeo->level ?? 1 }}</td>
                        <td><code>{{ $hubTrans?->slug ?: 'cam-nang-du-lich' }}</code></td>
                        <td><code>{{ $hubTrans?->slug_full ?: '/cam-nang-du-lich' }}</code></td>
                        <td><span class="text-xs text-emerald-700">Hub</span></td>
                        <td class="text-right">
                            <a href="{{ route('admin.listingHub.edit', ['hubKey' => 'guide_hub']) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ url('/cam-nang-du-lich') }}" class="ml-3 text-slate-500 hover:underline" target="_blank" rel="noopener">Xem</a>
                        </td>
                    </tr>
                @endunless

                @forelse ($categories as $category)
                    @php
                        $seo = $category->seoEntry;
                        $seoTrans = $seo?->translation($locale) ?? $seo?->translation();
                        $parentId = $seo?->parent_id;
                        $trans = $category->translation($locale) ?? $category->translation();
                    @endphp
                    <tr>
                        <td class="pl-6 text-slate-500">└ {{ $seo?->level ?? 2 }}</td>
                        <td><code>{{ $parentId ?: 0 }}</code></td>
                        <td class="font-medium">{{ $trans?->name ?? $category->name }}</td>
                        <td>{{ $seo?->level ?? '—' }}</td>
                        <td><code>{{ $seoTrans?->slug ?? $trans?->slug }}</code></td>
                        <td><code>{{ $seoTrans?->slug_full ?? '—' }}</code></td>
                        <td>
                            <span class="text-xs {{ $category->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $category->is_active ? 'Hoạt động' : 'Ẩn' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.blogCategories.view', ['id' => $category->id]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.blogCategories.delete', ['id' => $category->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa chuyên mục này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-slate-500">Chưa có chuyên mục con.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
