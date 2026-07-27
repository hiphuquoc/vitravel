@extends('layouts.app')

@section('title', 'Cảm nhận khách hàng — ViTravel')
@section('meta_description', 'Hơn 5.000 du khách đã đồng hành cùng ViTravel — đọc cảm nhận thật của họ về từng hành trình.')

@section('content')
    <x-layout.page-header title="Cảm nhận khách hàng"
        subtitle="Những trải nghiệm chân thật — không chỉnh sửa, không kịch bản"
        :breadcrumbs="[['label' => 'Cảm nhận khách hàng']]" banner-label="Ảnh banner: khách hàng ViTravel" />

    <section class="container-site py-12" aria-label="Tất cả cảm nhận">
        <div class="mb-8 flex flex-col items-center gap-2 text-center">
            <p class="font-display text-5xl font-bold text-primary-600">5.0</p>
            <x-shared.stars :rating="5" aria-label="5 trên 5 sao" />
            <p class="text-sm text-muted">Tổng hợp từ TripAdvisor, Google và Trustpilot</p>
        </div>

        <div class="columns-1 gap-6 space-y-6 sm:columns-2 lg:columns-3">
            @foreach ($testimonials as $t)
                <article class="card break-inside-avoid p-6">
                    <div class="flex items-center gap-3">
                        <x-ph class="size-12 rounded-full" icon="user" icon-class="size-6" :label="null" />
                        <div>
                            <p class="text-base font-bold">{{ $t['name'] }}</p>
                            <p class="mt-0.5 text-sm text-muted">{{ $t['flag'] }} {{ $t['country'] }} · {{ $t['trip'] }}</p>
                        </div>
                    </div>
                    <x-shared.rating :rating="$t['rating']" class="mt-3" />
                    <p class="body-text mt-3">{{ $t['quote'] }}</p>
                    <div class="mt-4 flex gap-2">
                        <x-ph class="h-16 flex-1 rounded-lg" icon="photo" icon-class="size-4" />
                        <div class="img-ph relative h-16 flex-1 rounded-lg">
                            <span class="absolute inset-0 flex items-center justify-center rounded-lg bg-ink/50 text-xs font-bold text-white">+{{ $t['photos'] }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <x-shared.pagination class="mt-10" />
    </section>

    <x-shared.review-platforms class="pt-0" />
@endsection
