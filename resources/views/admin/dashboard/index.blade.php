@extends('layouts.admin')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => 'Bảng điều khiển',
            'desc' => 'Tổng quan nội dung và khách hàng tiềm năng trên hệ thống.',
            'icon' => '<path d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z"/>',
        ])

        <div class="adminDashboardStats">
            @foreach ([
                ['Gói Tour', $stats['tours']],
                ['Gói Cruise', $stats['cruises']],
                ['Quốc gia', $stats['countries']],
                ['Danh mục Tour', $stats['tour_categories']],
                ['Bài viết', $stats['articles']],
                ['Yêu cầu nhanh', $stats['quick_inquiries']],
                ['Tour riêng', $stats['custom_tours']],
                ['Liên hệ', $stats['contacts']],
                ['Bình luận', $stats['comments']],
            ] as [$label, $value])
                <div class="adminDashboardStats_item">
                    <div class="adminDashboardStats_label">{{ $label }}</div>
                    <div class="adminDashboardStats_value">{{ number_format($value) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
