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
                <div x-data="listingGrid(@js([
                    'endpoint' => route('api.listings.featured-tours'),
                    'params' => ['limit' => 3],
                ]))">
                    <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>
                    <div x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
                        <x-tour.listing-skeleton :count="3" variant="compact" />
                    </div>
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
                <div x-data="listingGrid(@js([
                    'endpoint' => route('api.listings.featured-cruises'),
                    'params' => ['limit' => 3],
                ]))">
                    <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>
                    <div x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
                        <x-tour.listing-skeleton :count="3" variant="compact" />
                    </div>
                </div>
            </div>
        </section>
    @endunless

    {{-- ── Điểm đến yêu thích: hero cinemascape + strip mosaic ── --}}
    @php $destinationsSection = view_data()->homeSection('destinations'); @endphp
    @unless ($destinationsSection['hidden'] ?? false)
        <x-home.destinations :countries="$countries" :section="$destinationsSection" />
    @endunless

    {{-- ── Video trải nghiệm (ngay dưới điểm đến) ── --}}
    @php $videosSection = view_data()->homeSection('videos'); @endphp
    @unless ($videosSection['hidden'] ?? false)
        <x-shared.video-showcase :section="$videosSection" :home-only="true" :limit="4" />
    @endunless

    {{-- ── Đội ngũ → đánh giá nền tảng → khách hàng kể lại ── --}}
    @php
        $teamSection = view_data()->homeSection('team');
        $reviewPlatformsSection = view_data()->homeSection('review_platforms');
        $testimonialsSection = view_data()->homeSection('testimonials');
    @endphp
    @unless ($teamSection['hidden'] ?? false)
        <x-shared.team-grid :section="$teamSection" />
    @endunless
    @unless ($reviewPlatformsSection['hidden'] ?? false)
        <x-shared.review-platforms :section="$reviewPlatformsSection" />
    @endunless
    @unless ($testimonialsSection['hidden'] ?? false)
        <x-shared.testimonial-carousel :section="$testimonialsSection" />
    @endunless
@endsection
