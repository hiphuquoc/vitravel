@extends('layouts.app')

@section('title', 'Thư viện khoảnh khắc — ViTravel')
@section('meta_description', 'Album ảnh trải nghiệm thật từ những chuyến đi của khách hàng ViTravel trên khắp Đông Nam Á.')

@section('content')
    <x-layout.page-header title="Thư viện khoảnh khắc"
        subtitle="Album ảnh từ những chuyến đi có thật của khách hàng chúng tôi"
        :breadcrumbs="[['label' => 'Thư viện khoảnh khắc']]" banner-label="Ảnh banner: thư viện khoảnh khắc" />

    <section class="container-site section-band" aria-label="Album ảnh trải nghiệm">
        <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($albums as $album)
                <article class="card group cursor-pointer overflow-hidden transition hover:-translate-y-1 hover:shadow-(--shadow-card-hover)">
                    <div class="relative">
                        <div class="relative aspect-[4/3]">
                            <x-ph class="absolute inset-0 transition duration-300 group-hover:scale-105" :label="'Album: ' . $album['title']" />
                        </div>
                        <span class="gallery-card__badge">
                            <x-icon name="photo" class="size-3.5" /> {{ $album['photos'] }} ảnh
                        </span>
                    </div>
                    <div class="gallery-card__body">
                        <h2 class="item-title line-clamp-2 transition group-hover:text-primary-600">{{ $album['title'] }}</h2>
                        <p class="card-meta-row text-xs text-muted">
                            <x-icon name="calendar" class="size-3.5" /> Tháng {{ $album['date'] }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
        <x-shared.pagination class="page-follow" />
    </section>
@endsection
