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
                Cây SEO cha → con (Hitour): parent · level · slug · slug_full. Hub Du thuyền = cấp 1.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.cruiseTypes.view') }}" class="admin-btn admin-btn--primary">+ Thêm loại</a>
        </div>
    </div>

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
                <tr class="bg-slate-50">
                    <td class="font-semibold text-emerald-800">1</td>
                    <td><code>0</code></td>
                    <td class="font-semibold">{{ $hubTrans?->title ?: 'Du thuyền' }}</td>
                    <td>{{ $hubSeo->level ?? 1 }}</td>
                    <td><code>{{ $hubTrans?->slug ?: 'cruises' }}</code></td>
                    <td><code>{{ $hubTrans?->slug_full ?: '/cruises' }}</code></td>
                    <td><span class="text-xs text-emerald-700">Hub</span></td>
                    <td class="text-right">
                        <a href="{{ route('admin.listingHub.edit', ['hubKey' => 'cruises_hub']) }}" class="text-emerald-700 hover:underline">Sửa</a>
                        <a href="{{ url('/cruises') }}" class="ml-3 text-slate-500 hover:underline" target="_blank" rel="noopener">Xem</a>
                    </td>
                </tr>

                @forelse ($types as $type)
                    @php
                        $seo = $type->seoEntry;
                        $seoTrans = $seo?->translation($locale) ?? $seo?->translation();
                        $parentId = $seo?->parent_id;
                    @endphp
                    <tr>
                        <td class="pl-6 text-slate-500">└ 2</td>
                        <td><code>{{ $parentId ?: 0 }}</code></td>
                        <td class="font-medium">{{ $type->name }}</td>
                        <td>{{ $seo?->level ?? '—' }}</td>
                        <td><code>{{ $seoTrans?->slug ?? $type->slug }}</code></td>
                        <td><code>{{ $seoTrans?->slug_full ?? '—' }}</code></td>
                        <td>
                            <span class="text-xs {{ $type->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $type->is_active ? 'Hoạt động' : 'Ẩn' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.cruiseTypes.view', ['id' => $type->id]) }}" class="text-emerald-700 hover:underline">Sửa</a>
                            <a href="{{ route('admin.cruiseTypes.delete', ['id' => $type->id]) }}" class="ml-3 text-red-600 hover:underline" onclick="return confirm('Xóa loại này?')">Xóa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-slate-500">Chưa có loại du thuyền con.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
