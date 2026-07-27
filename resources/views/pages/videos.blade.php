@extends('layouts.app')

@section('title', 'Video trải nghiệm — ViTravel')
@section('meta_description', 'Video hành trình thật do khách hàng và đội ngũ ViTravel ghi lại trên khắp Đông Nam Á.')

@section('content')
    <x-layout.page-header title="Video trải nghiệm"
        subtitle="Xem tận mắt những hành trình chúng tôi đã thực hiện"
        :breadcrumbs="[['label' => 'Video trải nghiệm']]" banner-label="Ảnh banner: video trải nghiệm" />

    <section class="container-site py-12" aria-label="Tất cả video">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($videos as $v)
                <article class="card group cursor-pointer overflow-hidden transition hover:-translate-y-1 hover:shadow-(--shadow-card-hover)">
                    <div class="relative">
                        <div class="relative aspect-video">
                            <x-ph class="absolute inset-0" :label="'Video: ' . $v['title']" icon="play" icon-class="size-8" />
                        </div>
                        <span class="absolute inset-0 m-auto flex size-14 items-center justify-center rounded-full bg-primary-500/95 text-white shadow-(--shadow-float) transition group-hover:scale-110">
                            <x-icon name="play" class="size-6 translate-x-0.5" />
                        </span>
                        <span class="absolute right-3 bottom-3 rounded bg-ink/70 px-1.5 py-0.5 text-xs font-semibold text-white">{{ $v['duration'] }}</span>
                    </div>
                    <div class="p-5">
                        <h2 class="item-title line-clamp-2 text-base transition group-hover:text-primary-600">{{ $v['title'] }}</h2>
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs text-muted">
                            <x-icon name="calendar" class="size-3.5" /> {{ $v['date'] }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <x-shared.testimonial-carousel class="pt-0" />
@endsection
