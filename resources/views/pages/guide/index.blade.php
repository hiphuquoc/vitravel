@extends('layouts.app')

@php
    $pageTitle = $country ? 'Cẩm nang du lịch ' . $country['name'] : 'Cẩm nang du lịch';
@endphp

@section('title', $pageTitle . ' — kinh nghiệm, mẹo hay & lịch trình | ViTravel')
@section('meta_description', 'Tổng hợp kinh nghiệm du lịch' . ($country ? ' ' . $country['name'] : ' Đông Nam Á') . ' từ chuyên gia bản địa: nên đi mùa nào, ăn gì, ở đâu và chọn tour nào phù hợp.')

@section('content')
    <div class="container-site pt-8">
        {{-- Breadcrumb thường (không card overlay — khác Tour Listing) --}}
        <x-layout.breadcrumb :items="array_filter([
            ['label' => 'Cẩm nang du lịch', 'url' => $country ? route('guide.index') : null],
            $country ? ['label' => 'Cẩm nang ' . $country['name']] : null,
        ])" />

        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-3xl font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl">{{ $pageTitle }}</h1>
            <x-shared.sort-dropdown :options="['Bài mới nhất', 'Xem nhiều nhất', 'Đánh giá cao nhất']" />
        </div>
        <p class="body-text mt-2 max-w-2xl">
            Kinh nghiệm thật từ những chuyến khảo sát của đội ngũ ViTravel — cập nhật liên tục để bạn lên kế hoạch dễ dàng hơn.
        </p>
    </div>

    {{-- Layout blog: nội dung TRÁI + sidebar PHẢI (ngược với Tour Listing) --}}
    <div class="container-site grid items-start gap-8 py-8 lg:grid-cols-[1fr_300px]">
        <div class="min-w-0">
            @if (count($articles))
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach ($articles as $article)
                        <x-blog.card :article="$article" />
                    @endforeach
                </div>
            @else
                <div class="card flex flex-col items-center gap-3 p-12 text-center">
                    <x-icon name="compass" class="size-10 text-muted" />
                    <p class="font-semibold">Chưa có bài viết cho chuyên mục này.</p>
                    <a href="{{ route('guide.index') }}" class="btn-outline mt-2">Xem tất cả bài viết</a>
                </div>
            @endif

            <x-shared.pagination class="mt-10" />

            <x-shared.faq :faqs="$faqs" class="mt-12"
                title="Câu hỏi thường gặp{{ $country ? ' về du lịch ' . $country['name'] : '' }}" />

            {{-- Đoạn SEO cuối trang --}}
            <div class="prose-travel mt-12 border-t border-line pt-8">
                <p>
                    <strong>Cẩm nang du lịch {{ $country['name'] ?? 'Đông Nam Á' }}</strong> của ViTravel được viết bởi chính
                    những chuyên gia thiết kế tour — người đã trực tiếp khảo sát từng điểm đến, ăn từng quán và ngủ thử từng homestay
                    trước khi đưa vào lịch trình. Bạn sẽ tìm thấy câu trả lời cho những câu hỏi quen thuộc: nên đi mùa nào,
                    chi phí bao nhiêu, di chuyển thế nào và <a href="{{ route('tours.index', $country['slug'] ?? 'viet-nam') }}">chọn tour nào phù hợp</a>.
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
