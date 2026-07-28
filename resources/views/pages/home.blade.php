@extends('layouts.app')

@section('title', 'ViTravel — Tour trọn gói & du thuyền Đông Nam Á, thiết kế bởi chuyên gia bản địa')

@section('content')
    <x-home.hero-slider :slides="$slides" :pills="$pills" :countries="$countries" />

    {{-- ── 4 USP ── --}}
    <section class="container-site section-band" aria-label="Cam kết dịch vụ">
        <x-shared.usp-badges />
    </section>

    {{-- ── Giới thiệu công ty ── --}}
    @php $companyIntro = view_data()->homeSection('company_intro'); @endphp
    @unless ($companyIntro['hidden'] ?? false)
        <x-shared.company-intro :section="$companyIntro" />
    @endunless

    {{-- ── Tour nổi bật ── --}}
    @php $featuredSection = view_data()->homeSection('featured_tours'); @endphp
    @unless ($featuredSection['hidden'] ?? false)
        <section class="cv-auto section-band" aria-label="{{ $featuredSection['title'] ?? 'Tour được yêu cầu nhiều nhất' }}">
            <div class="container-site">
                <x-shared.section-heading
                    :eyebrow="$featuredSection['eyebrow'] ?? null"
                    :title="$featuredSection['title'] ?? ''"
                    :subtitle="$featuredSection['subtitle'] ?? null"
                />
                <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredTours as $tour)
                        <x-tour.card-compact :item="$tour" :href="route('tours.show', ['country' => $tour['countrySlug'], 'slug' => $tour['slug']])" />
                    @endforeach
                </div>
            </div>
        </section>
    @endunless

    {{-- ── Du thuyền nổi bật ── --}}
    @php $cruisesSection = view_data()->homeSection('featured_cruises'); @endphp
    @unless ($cruisesSection['hidden'] ?? false)
        <section class="cv-auto section-band" aria-label="{{ $cruisesSection['title'] ?? 'Du thuyền nổi bật' }}">
            <div class="container-site">
                <x-shared.section-heading
                    :eyebrow="$cruisesSection['eyebrow'] ?? null"
                    :title="$cruisesSection['title'] ?? ''"
                    :subtitle="$cruisesSection['subtitle'] ?? null"
                />
                <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredCruises as $cruise)
                        <x-tour.card-compact :item="$cruise" :href="route('cruises.show', ['type' => $cruise['typeSlug'], 'slug' => $cruise['slug']])" />
                    @endforeach
                </div>
            </div>
        </section>
    @endunless

    {{-- ── Điểm đến yêu thích: hero cinemascape + strip mosaic ── --}}
    @php $destinationsSection = view_data()->homeSection('destinations'); @endphp
    @unless ($destinationsSection['hidden'] ?? false)
        <x-home.destinations :countries="$countries" :section="$destinationsSection" />
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
        <x-shared.video-showcase :section="$videosSection" :home-only="true" :limit="4" />
    @endunless
@endsection
