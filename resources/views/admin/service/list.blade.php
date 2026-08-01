@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Sản phẩm trong 5 cụm dịch vụ (tàu, máy bay, lưu trú, vui chơi, dịch vụ khác).',
            'icon' => '<path d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H17.25v-.008zm0 3h.008v.008H17.25v-.008zm0 3h.008v.008H17.25v-.008z"/>',
            'actionUrl' => route('admin.services.view', array_filter(['cluster' => $cluster ?: null])),
            'actionText' => 'Thêm mới',
        ])

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.services.list') }}"
               class="rounded-full px-3 py-1.5 text-sm {{ $cluster === '' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Tất cả
            </a>
            @foreach ($clusters as $key => $cfg)
                <a href="{{ route('admin.services.list', ['cluster' => $key]) }}"
                   class="rounded-full px-3 py-1.5 text-sm {{ $cluster === $key ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $cfg['label'] ?? $key }}
                </a>
            @endforeach
        </div>

        <div class="companyManagementPage_section companyManagementPage_section--tracked">
            <div class="companyManagementPage_section_body">
                <form method="GET" class="adminContentPage_searchBar">
                    <div class="adminContentPage_searchBar_inputWrapper">
                        @if ($cluster !== '')
                            <input type="hidden" name="cluster" value="{{ $cluster }}">
                        @endif
                        <input type="text" class="adminContentPage_searchBar_input" name="search" placeholder="Tìm theo tiêu đề / mã..." value="{{ request('search') }}">
                        <select name="service_category_id" class="adminFormField_input adminFormField_input--select">
                            <option value="">Tất cả danh mục</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('service_category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="adminFormField_input adminFormField_input--select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="published" @selected(request('status') === 'published')>Xuất bản</option>
                            <option value="draft" @selected(request('status') === 'draft')>Nháp</option>
                            <option value="archived" @selected(request('status') === 'archived')>Lưu trữ</option>
                        </select>
                        <button type="submit" class="adminContentPage_searchBar_button"><span>Lọc</span></button>
                    </div>
                </form>

                @if ($services->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($services as $item)
                            @php
                                $seo = $item->seoEntry?->translation($locale) ?? $item->seoEntry?->translation();
                                $titleText = $item->translation($locale)?->title ?? $item->translation()?->title ?? $item->code;
                                $clusterLabel = $clusters[$item->cluster]['label'] ?? $item->cluster;
                            @endphp
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_imageWrapper">
                                    @if ($item->coverUrl('card'))
                                        <img src="{{ $item->coverUrl('card') }}" alt="" class="adminContentPage_card_image">
                                    @else
                                        <div class="adminContentPage_card_placeholder">{{ strtoupper(mb_substr($titleText ?? 'S', 0, 1)) }}</div>
                                    @endif
                                    <div class="adminContentPage_card_actions">
                                        @if ($seo?->slug_full)
                                            <a href="{{ seo_public_url($seo, $locale) }}" target="_blank" class="adminContentPage_card_action" title="Xem trang">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.services.view', ['id' => $item->id, 'cluster' => $item->cluster]) }}" class="adminContentPage_card_action" title="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.services.delete', ['id' => $item->id]) }}" class="adminContentPage_card_action" title="Xóa" onclick="return confirm('Xóa sản phẩm này?')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"/></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $titleText }}</h3>
                                    <p class="adminContentPage_card_desc">
                                        {{ $clusterLabel }}
                                        @if ($item->category) · {{ $item->category->name }} @endif
                                        · {{ $item->status }}
                                    </p>
                                    @if ($seo?->slug_full)
                                        <p class="adminContentPage_card_url">{{ $seo->slug_full }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có sản phẩm dịch vụ.</div>
                @endif

                @if ($services->hasPages())
                    <div class="adminContentPage_pagination">{{ $services->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
