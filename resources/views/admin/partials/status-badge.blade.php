@php
    $statusLabels = [
        'draft' => 'Nháp',
        'published' => 'Đã xuất bản',
        'archived' => 'Lưu trữ',
        'new' => 'Mới',
        'contacted' => 'Đã liên hệ',
        'quoted' => 'Đã báo giá',
        'closed' => 'Đã đóng',
        'spam' => 'Spam',
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];
@endphp

<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
    @if(in_array($status, ['published', 'approved', 'closed'])) bg-emerald-100 text-emerald-800
    @elseif(in_array($status, ['new', 'pending', 'draft'])) bg-amber-100 text-amber-800
    @elseif($status === 'rejected' || $status === 'spam') bg-red-100 text-red-800
    @else bg-slate-100 text-slate-700 @endif">
    {{ $statusLabels[$status] ?? $status }}
</span>
