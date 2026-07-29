@extends('layouts.app')

@php
    $pageTitle = $country ? 'Cẩm nang du lịch ' . $country['name'] : 'Cẩm nang du lịch';
@endphp

@section('title', $pageTitle . ' — kinh nghiệm, mẹo hay & lịch trình | ViTravel')
@section('meta_description', 'Tổng hợp kinh nghiệm du lịch' . ($country ? ' ' . $country['name'] : ' Đông Nam Á') . ' từ chuyên gia bản địa: nên đi mùa nào, ăn gì, ở đâu và chọn tour nào phù hợp.')

@section('content')
    <div class="container-site blog-page-intro">
        <x-layout.breadcrumb :items="array_filter([
            ['label' => 'Cẩm nang du lịch', 'url' => $country ? locale_route('guide.index') : null],
            $country ? ['label' => 'Cẩm nang ' . $country['name']] : null,
        ])" class="breadcrumb--page" />

        <div class="blog-toolbar">
            <h1 class="blog-page-title">{{ $pageTitle }}</h1>
            <x-shared.sort-dropdown :options="['Bài mới nhất', 'Xem nhiều nhất', 'Đánh giá cao nhất']" />
        </div>
        <p class="body-text site-mt max-w-2xl">
            Kinh nghiệm thật từ những chuyến khảo sát của đội ngũ ViTravel — cập nhật liên tục để bạn lên kế hoạch dễ dàng hơn.
        </p>
    </div>

    <div class="container-site blog-layout section-band--sm">
        <div class="min-w-0">
            @if (count($articles))
                <div class="grid site-gap sm:grid-cols-2">
                    @foreach ($articles as $article)
                        <x-blog.card :article="$article" />
                    @endforeach
                </div>
            @else
                <div class="card blog-empty">
                    <x-icon name="compass" class="size-10 text-muted" />
                    <p class="font-semibold">Chưa có bài viết cho chuyên mục này.</p>
                    <a href="{{ locale_route('guide.index') }}" class="btn-outline">Xem tất cả bài viết</a>
                </div>
            @endif

            <x-shared.pagination class="page-follow" />

            <x-shared.faq :faqs="$faqs" class="page-follow"
                title="Câu hỏi thường gặp{{ $country ? ' về du lịch ' . $country['name'] : '' }}" />

            <div class="prose-travel blog-seo">
                <p>
                    <strong>Cẩm nang du lịch {{ $country['name'] ?? 'Đông Nam Á' }}</strong> của ViTravel được viết bởi chính
                    những chuyên gia thiết kế tour — người đã trực tiếp khảo sát từng điểm đến, ăn từng quán và ngủ thử từng homestay
                    trước khi đưa vào lịch trình. Bạn sẽ tìm thấy câu trả lời cho những câu hỏi quen thuộc: nên đi mùa nào,
                    chi phí bao nhiêu, di chuyển thế nào và <a href="{{ locale_route('tours.index', $country['slug'] ?? 'viet-nam') }}">chọn tour nào phù hợp</a>.
                </p>
                <p>
                    Nếu cần lời khuyên cho hành trình cụ thể của riêng bạn, đừng ngại <a href="{{ route('customize') }}">gửi yêu cầu tư vấn miễn phí</a> —
                    như một <strong>đại lý du lịch bản địa</strong>, chúng tôi luôn sẵn lòng chia sẻ, kể cả khi bạn chưa đặt tour.
                </p>
            </div>
        </div>

        <x-blog.sidebar :categories="$categories" :content-tags="$contentTags" :keywords="$keywords" />
    </div>
@endsection
