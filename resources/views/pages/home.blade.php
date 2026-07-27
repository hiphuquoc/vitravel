@extends('layouts.app')

@section('title', 'ViTravel — Tour trọn gói & du thuyền Đông Nam Á, thiết kế bởi chuyên gia bản địa')

@section('content')
    <x-home.hero-slider :slides="$slides" :pills="$pills" :countries="$countries" />

    {{-- ── 4 USP ── --}}
    <section class="container-site py-14" aria-label="Cam kết dịch vụ">
        <x-shared.usp-badges />
    </section>

    {{-- ── Giới thiệu công ty ── --}}
    @php $companyIntro = view_data()->homeSection('company_intro'); @endphp
    @unless ($companyIntro['hidden'] ?? false)
        <x-shared.company-intro class="py-4" :section="$companyIntro" />
    @endunless

    {{-- ── Tour nổi bật ── --}}
    @php $featuredSection = view_data()->homeSection('featured_tours'); @endphp
    @unless ($featuredSection['hidden'] ?? false)
        <section class="cv-auto py-14" aria-label="{{ $featuredSection['title'] ?? 'Tour được yêu cầu nhiều nhất' }}">
            <div class="container-site">
                <x-shared.section-heading
                    :eyebrow="$featuredSection['eyebrow'] ?? null"
                    :title="$featuredSection['title'] ?? ''"
                    :subtitle="$featuredSection['subtitle'] ?? null"
                />
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredTours as $tour)
                        <x-tour.card-compact :item="$tour" :href="route('tours.show', ['country' => $tour['countrySlug'], 'slug' => $tour['slug']])" />
                    @endforeach
                </div>
            </div>
        </section>
    @endunless

    {{-- ── Bento grid điểm đến ── --}}
    @php $destinationsSection = view_data()->homeSection('destinations'); @endphp
    @unless ($destinationsSection['hidden'] ?? false)
        <section class="cv-auto py-14" aria-label="{{ $destinationsSection['title'] ?? 'Điểm đến được yêu thích' }}">
            <div class="container-site">
                <x-shared.section-heading
                    :title="$destinationsSection['title'] ?? ''"
                    :subtitle="$destinationsSection['subtitle'] ?? null"
                />
                <div class="grid auto-rows-[190px] grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                    @foreach ($countries as $c)
                        <a href="{{ route('tours.index', $c['slug']) }}"
                            class="{{ $c['size'] === 'large' ? 'col-span-2 row-span-2' : '' }} group relative overflow-hidden rounded-2xl">
                            @if (!empty($c['image']))
                                <img src="{{ $c['image'] }}" alt="{{ $c['name'] }}" class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                            @else
                                <x-ph class="absolute inset-0 transition duration-300 group-hover:scale-105" :label="'Ảnh điểm đến: ' . $c['name']"
                                    icon-class="{{ $c['size'] === 'large' ? 'size-12' : 'size-8' }}" />
                            @endif
                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/85 via-ink/35 to-transparent p-4 pt-12 sm:p-5 sm:pt-14">
                                <span class="dest-card-name {{ $c['size'] === 'large' ? 'dest-card-name--large' : '' }}">{{ $c['name'] }}</span>
                                <span class="dest-card-meta">{{ $c['tourCount'] }} tour</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endunless

    {{-- ── Trust & nội dung dùng chung ── --}}
    @php
        $testimonialsSection = view_data()->homeSection('testimonials');
        $reviewPlatformsSection = view_data()->homeSection('review_platforms');
        $teamSection = view_data()->homeSection('team');
        $videosSection = view_data()->homeSection('videos');
    @endphp
    @unless ($testimonialsSection['hidden'] ?? false)
        <x-shared.testimonial-carousel :section="$testimonialsSection" />
    @endunless
    @unless ($reviewPlatformsSection['hidden'] ?? false)
        <x-shared.review-platforms class="pt-0" :section="$reviewPlatformsSection" />
    @endunless
    @unless ($teamSection['hidden'] ?? false)
        <x-shared.team-grid class="pt-0" :section="$teamSection" />
    @endunless
    @unless ($videosSection['hidden'] ?? false)
        <x-shared.video-showcase class="pt-0" :section="$videosSection" />
    @endunless
@endsection
