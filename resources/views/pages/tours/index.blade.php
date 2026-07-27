@extends('layouts.app')

@section('title', 'Tour ' . $country['name'] . ' trọn gói — ViTravel')
@section('meta_description', 'Danh sách tour ' . $country['name'] . ' trọn gói được thiết kế bởi chuyên gia bản địa: ' . $country['tagline'] . '. Nhận báo giá miễn phí trong 24 giờ.')

@section('content')
    <x-layout.page-header :title="'Tour ' . $country['name']" :subtitle="$country['tagline']"
        :breadcrumbs="[
            ['label' => 'Tour', 'url' => route('tours.index', 'viet-nam')],
            ['label' => 'Tour ' . $country['name']],
        ]" />

    <div class="container-site grid items-start gap-8 py-10 lg:grid-cols-[280px_1fr]">
        {{-- Bộ lọc trái --}}
        <x-tour.filter-sidebar :durations="$durations" :styles="$styles" />

        {{-- Danh sách tour phải --}}
        <div class="min-w-0">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-leaf-500 px-4 py-1.5 text-sm font-bold text-white">
                    <x-icon name="sparkles" class="size-4" /> Top {{ count($tours) }} — {{ date('Y') }}
                </span>
                <x-shared.sort-dropdown />
            </div>

            @if (count($tours))
                <div class="space-y-6">
                    @foreach ($tours as $tour)
                        <x-tour.card :item="$tour" :href="route('tours.show', ['country' => $tour['countrySlug'], 'slug' => $tour['slug']])" />
                    @endforeach
                </div>
            @else
                <div class="card flex flex-col items-center gap-3 p-12 text-center">
                    <x-icon name="compass" class="size-10 text-muted" />
                    <p class="font-semibold">Chưa có tour nào cho điểm đến này.</p>
                    <p class="body-text text-muted">Hãy để chuyên gia của chúng tôi thiết kế hành trình riêng cho bạn.</p>
                    <a href="{{ route('customize') }}" class="btn-primary mt-2">
                        <x-icon name="sparkles" class="size-4" /> Thiết kế tour riêng
                    </a>
                </div>
            @endif

            {{-- Rating tổng danh mục --}}
            <div class="mt-10 flex flex-col items-center gap-2 text-center">
                <p class="font-display text-4xl font-bold text-primary-600">5.0</p>
                <x-shared.stars :rating="5" aria-label="5 trên 5 sao" />
                <p class="text-sm text-muted">{{ array_sum(array_column($tours, 'reviewCount')) }} đánh giá từ khách hàng đã đi tour {{ $country['name'] }}</p>
            </div>

            {{-- Đoạn giới thiệu SEO --}}
            <div class="prose-travel mt-10 border-t border-line pt-8">
                <p>
                    Một <strong>tour {{ $country['name'] }} trọn gói</strong> là cách trọn vẹn nhất để khám phá
                    {{ $country['tagline'] }} mà không phải bận tâm khâu tổ chức. Mỗi lịch trình của ViTravel đều do
                    <strong>chuyên gia bản địa</strong> thiết kế và có thể tuỳ chỉnh 100% theo nhịp đi, ngân sách và sở thích của bạn —
                    từ những di sản nổi tiếng cho tới các bản làng chưa nhiều người biết đến.
                </p>
                <p>
                    Tất cả tour đều bao gồm khách sạn tuyển chọn, xe riêng, hướng dẫn viên chuyên tuyến và các bữa ăn đặc sản địa phương.
                    Nếu chưa tìm thấy hành trình ưng ý, hãy <a href="{{ route('customize') }}">gửi yêu cầu thiết kế tour riêng</a> —
                    chúng tôi sẽ phản hồi kèm lịch trình chi tiết trong vòng 24 giờ làm việc.
                </p>
            </div>

            {{-- FAQ danh mục --}}
            <x-shared.faq :faqs="$faqs" class="mt-10" title="Câu hỏi thường gặp về tour {{ $country['name'] }}" />
        </div>
    </div>

    <x-shared.testimonial-carousel />
    <x-shared.review-platforms class="pt-0" />
@endsection
