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
                Cây SEO cha → con (Hitour): parent · level · slug · slug_full. Hub Tour = cấp 1.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.countries.view') }}" class="admin-btn admin-btn--primary">+ Thêm điểm đến</a>
        </div>
    </div>

    <form method="GET" class="admin-card mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="min-w-[240px] flex-1">
                <label class="admin-form-label">Tìm kiếm điểm đến</label>
                <input type="text" name="search" value="{{ request('search') }}" class="admin-form-input" placeholder="Tên điểm đến...">
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
                {{-- Hub cấp 1 --}}
                @unless (request()->filled('search'))
                    <tr class="bg-slate-50">
                        <td class="font-semibold text-emerald-800">1</td>
                        <td><code>0</code></td>
                        <td class="font-semibold">{{ $hubTrans?->title ?: 'Tour (tất cả)' }}</td>
                        <td>{{ $hubSeo->level ?? 1 }}</td>
                        <td><code>{{ $hubTrans?->slug ?: 'tours' }}</code></td>
                        <td><code>{{ $hubTrans?->slug_full ?: '/tours' }}</code></td>
                        <td><span class="text-xs text-emerald-700">Hub</span></td>
                        <td class="text-right">
                            <a href="{{ route('admin.listingHub.edit', ['hubKey' => 'tours_hub']) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ url('/tours') }}" class="ml-3 text-slate-500 hover:underline" target="_blank" rel="noopener">Xem</a>
                        </td>
                    </tr>
                @endunless

                @forelse ($countries as $country)
                    @php
                        $seo = $country->seoEntry;
                        $seoTrans = $seo?->translation($locale) ?? $seo?->translation();
                        $parentId = $seo?->parent_id;
                    @endphp
                    <tr>
                        <td class="pl-6 text-slate-500">└ 2</td>
                        <td><code>{{ $parentId ?: 0 }}</code></td>
                        <td class="font-medium">{{ $country->name }}</td>
                        <td>{{ $seo?->level ?? '—' }}</td>
                        <td><code>{{ $seoTrans?->slug ?? $country->slug }}</code></td>
                        <td><code>{{ $seoTrans?->slug_full ?? '—' }}</code></td>
                        <td>
                            <span class="text-xs {{ $country->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $country->is_active ? 'Hoạt động' : 'Ẩn' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.countries.view', ['id' => $country->id]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.countries.delete', ['id' => $country->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa điểm đến này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-slate-500">Chưa có quốc gia con.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
