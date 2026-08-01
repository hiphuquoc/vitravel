@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
@php
    $hubTrans = $hubSeo?->translation($locale) ?? $hubSeo?->translation();
@endphp
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                Hub dịch vụ (cấp 1) → danh mục (cấp 2) → sản phẩm. Chọn cụm để sửa hub và danh mục con.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.serviceCategories.view', array_filter(['cluster' => $cluster ?: null])) }}" class="admin-btn admin-btn--primary">+ Thêm danh mục</a>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.serviceCategories.list') }}"
           class="rounded-full px-3 py-1.5 text-sm {{ $cluster === '' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            Tất cả
        </a>
        @foreach ($clusters as $key => $cfg)
            <a href="{{ route('admin.serviceCategories.list', ['cluster' => $key]) }}"
               class="rounded-full px-3 py-1.5 text-sm {{ $cluster === $key ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ $cfg['label'] ?? $key }}
            </a>
        @endforeach
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        @if ($cluster !== '')
            <input type="hidden" name="cluster" value="{{ $cluster }}">
        @endif
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên / slug..." class="adminFormField_input max-w-xs">
        <button type="submit" class="admin-btn">Lọc</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cấp</th>
                    <th>Cụm</th>
                    <th>Tên</th>
                    <th>Level</th>
                    <th>Slug</th>
                    <th>Slug full</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @if ($hubSeo && $hubKey)
                    <tr class="bg-slate-50">
                        <td class="font-semibold text-emerald-800">1</td>
                        <td><code>{{ $cluster }}</code></td>
                        <td class="font-semibold">{{ $hubTrans?->title ?: ($clusters[$cluster]['label'] ?? $cluster) }}</td>
                        <td>{{ $hubSeo->level ?? 1 }}</td>
                        <td><code>{{ $hubTrans?->slug }}</code></td>
                        <td><code>{{ $hubTrans?->slug_full }}</code></td>
                        <td><span class="text-xs text-emerald-700">Hub</span></td>
                        <td class="text-right">
                            <a href="{{ route('admin.listingHub.edit', ['hubKey' => $hubKey]) }}" class="text-emerald-700 hover:underline">Sửa hub</a>
                            @if ($hubTrans?->slug_full)
                                <a href="{{ seo_public_url($hubTrans, $locale) }}" class="ml-3 text-slate-500 hover:underline" target="_blank" rel="noopener">Xem</a>
                            @endif
                        </td>
                    </tr>
                @elseif ($cluster === '')
                    @foreach ($clusters as $key => $cfg)
                        @php $hk = $cfg['hub_key'] ?? null; @endphp
                        @if ($hk)
                            <tr class="bg-slate-50">
                                <td class="font-semibold text-emerald-800">1</td>
                                <td><code>{{ $key }}</code></td>
                                <td class="font-semibold">{{ $cfg['label'] ?? $key }}</td>
                                <td>—</td>
                                <td colspan="2"><span class="text-xs text-slate-500">Hub listing</span></td>
                                <td><span class="text-xs text-emerald-700">Hub</span></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.listingHub.edit', ['hubKey' => $hk]) }}" class="text-emerald-700 hover:underline">Sửa hub</a>
                                    <a href="{{ route('admin.serviceCategories.list', ['cluster' => $key]) }}" class="ml-3 text-slate-500 hover:underline">Danh mục</a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                @forelse ($categories as $category)
                    @php
                        $seo = $category->seoEntry;
                        $seoTrans = $seo?->translation($locale) ?? $seo?->translation();
                        $clusterLabel = $clusters[$category->cluster]['label'] ?? $category->cluster;
                    @endphp
                    <tr>
                        <td class="pl-6 text-slate-500">└ 2</td>
                        <td><code>{{ $category->cluster }}</code></td>
                        <td class="font-medium">{{ $category->name }}</td>
                        <td>{{ $seo?->level ?? '—' }}</td>
                        <td><code>{{ $seoTrans?->slug ?? $category->slug }}</code></td>
                        <td><code>{{ $seoTrans?->slug_full ?? '—' }}</code></td>
                        <td>
                            <span class="text-xs {{ $category->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $category->is_active ? 'Hoạt động' : 'Ẩn' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.serviceCategories.view', ['id' => $category->id, 'cluster' => $category->cluster]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.services.list', ['cluster' => $category->cluster, 'service_category_id' => $category->id]) }}" class="ml-3 text-slate-500 hover:underline">SP</a>
                            <a href="{{ route('admin.serviceCategories.delete', ['id' => $category->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-slate-500">Chưa có danh mục dịch vụ{{ $cluster ? ' trong cụm này' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
