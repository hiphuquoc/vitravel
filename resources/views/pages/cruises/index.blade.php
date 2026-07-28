@extends('layouts.app')

@section('title', $type['name'] . ' — Danh sách du thuyền | ViTravel')
@section('meta_description', 'Tuyển chọn ' . strtolower($type['name']) . ' tốt nhất với đánh giá thật từ khách hàng. Đặt cabin qua chuyên gia bản địa, nhận báo giá trong 24 giờ.')

@section('content')
    <x-layout.page-header :title="$type['name']" subtitle="Tuyển chọn những du thuyền đáng trải nghiệm nhất, được kiểm chứng bởi chính khách hàng của chúng tôi"
        :breadcrumbs="[
            ['label' => 'Du thuyền', 'url' => route('cruises.index', 'du-thuyen-ha-long')],
            ['label' => $type['name']],
        ]" />

    {{-- Pill chuyển nhanh giữa các tuyến du thuyền --}}
    <div class="container-site mt-6 flex flex-wrap gap-2.5">
        @foreach ($types as $t)
            <a href="{{ route('cruises.index', $t['slug']) }}"
                class="btn-chip {{ $t['slug'] === $type['slug'] ? 'is-active' : '' }}">
                {{ $t['name'] }} <span class="opacity-70">({{ $t['count'] }})</span>
            </a>
        @endforeach
    </div>

    <div class="container-site grid items-start gap-8 py-8 lg:grid-cols-[280px_1fr]">
        <x-tour.filter-sidebar :durations="$durations" :styles="$styles" />

        <div class="min-w-0">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-leaf-500 px-4 py-1.5 text-sm font-bold text-white">
                    <x-icon name="cruise" class="size-4" /> {{ count($cruises) }} du thuyền tuyển chọn
                </span>
                <x-shared.sort-dropdown />
            </div>

            @if (count($cruises))
                <div class="space-y-6">
                    @foreach ($cruises as $cruise)
                        <x-tour.card :item="$cruise" :href="route('cruises.show', ['type' => $cruise['typeSlug'], 'slug' => $cruise['slug']])" />
                    @endforeach
                </div>
            @else
                <div class="card flex flex-col items-center gap-3 p-12 text-center">
                    <x-icon name="cruise" class="size-10 text-muted" />
                    <p class="font-semibold">Chưa có du thuyền nào trong tuyến này.</p>
                    <a href="{{ route('customize') }}" class="btn-primary mt-2">
                        <x-icon name="sparkles" class="size-4" /> Nhờ chuyên gia tư vấn
                    </a>
                </div>
            @endif

            <div class="prose-travel mt-10 border-t border-line pt-8">
                <p>
                    Ngủ đêm trên <strong>{{ strtolower($type['name']) }}</strong> là trải nghiệm không thể thay thế:
                    thức dậy giữa làn nước xanh ngọc, đón bình minh ngay trên boong tàu và dùng bữa tối dưới bầu trời sao.
                    ViTravel làm việc trực tiếp với từng nhà thuyền — không qua trung gian — nên bạn luôn nhận được
                    <strong>giá tốt nhất kèm ưu đãi riêng</strong> cho khách đặt sớm.
                </p>
            </div>

            <x-shared.faq :faqs="$faqs" class="mt-10" title="Câu hỏi thường gặp về {{ strtolower($type['name']) }}" />
        </div>
    </div>
@endsection
