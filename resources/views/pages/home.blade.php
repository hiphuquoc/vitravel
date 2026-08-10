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
                    'params' => ['limit' => 12],
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
                    'params' => ['limit' => 12],
                ]))">
                    <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>
                    <div x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
                        <x-tour.listing-skeleton :count="3" variant="compact" />
                    </div>
                </div>
            </div>
        </section>
    @endunless

    {{-- ── Vé tàu cao tốc / phà nổi bật (train hoặc ferry theo dự án) ── --}}
    @php
        $trainsSection = view_data()->homeSection('featured_trains');
        $transportCluster = view_data()->featuredTransportCluster();
    @endphp
    @unless ($trainsSection['hidden'] ?? false)
        <section class="cv-auto section-band" aria-label="{{ $trainsSection['title'] ?? 'Vé tàu cao tốc' }}">
            <div class="container-site">
                <x-shared.section-heading
                    :eyebrow="$trainsSection['eyebrow'] ?? null"
                    :title="$trainsSection['title'] ?? ''"
                    :subtitle="$trainsSection['subtitle'] ?? null"
                />
                <div x-data="listingGrid(@js([
                    'endpoint' => route('api.listings.featured-services'),
                    'params' => ['cluster' => $transportCluster, 'limit' => 12],
                ]))">
                    <div x-show="error" x-cloak class="listing-error site-mb" x-text="error"></div>
                    <div x-ref="results" :class="loading && 'opacity-60'" :aria-busy="loading ? 'true' : 'false'">
                        <x-tour.listing-skeleton :count="3" variant="compact" />
                    </div>
                </div>
            </div>
        </section>
    @endunless

    {{-- ── Dịch vụ bổ trợ: curated cards hoặc hub lưu trú / vui chơi / hỗ trợ ── --}}
    @php
        $supportSection = view_data()->homeSection('support_services');
        $supportFeatured = view_data()->featuredSupportServices(12);
        $homeSoloClusters = collect(view_data()->serviceClusters())
            ->filter(fn ($c) => in_array($c['code'] ?? '', ['stay', 'experience', 'other'], true))
            ->values()
            ->all();
        $soloBlurbs = [
            'stay' => 'Resort và khách sạn chọn lọc theo điểm đến.',
            'experience' => 'Vé công viên, cáp treo và hoạt động trong ngày.',
            'other' => 'Thuê xe, spa và hướng dẫn viên riêng.',
        ];
    @endphp
    @unless ($supportSection['hidden'] ?? false)
        @if (count($supportFeatured) > 0)
            <section class="cv-auto section-band" aria-label="{{ $supportSection['title'] ?? 'Dịch vụ bổ trợ' }}">
                <div class="container-site">
                    <x-shared.section-heading
                        :eyebrow="$supportSection['eyebrow'] ?? null"
                        :title="$supportSection['title'] ?? ''"
                        :subtitle="$supportSection['subtitle'] ?? null"
                    />
                    @include('partials.listing-cards', [
                        'items' => $supportFeatured,
                        'kind' => 'service',
                        'variant' => 'compact',
                    ])
                </div>
            </section>
        @elseif (count($homeSoloClusters) > 0)
            <section class="container-site section-band" aria-label="{{ $supportSection['title'] ?? 'Dịch vụ bổ trợ' }}">
                <x-shared.section-heading
                    :eyebrow="$supportSection['eyebrow'] ?? null"
                    :title="$supportSection['title'] ?? ''"
                    :subtitle="$supportSection['subtitle'] ?? null"
                />
                <div class="home-svc-solo">
                    @foreach ($homeSoloClusters as $sc)
                        @php $code = $sc['code'] ?? ''; @endphp
                        <a href="{{ locale_route('services.hub', ['cluster' => $code]) }}" class="home-svc-solo__link">
                            <span class="home-svc-solo__icon" aria-hidden="true">
                                <x-icon :name="$sc['icon'] ?? 'sparkles'" class="home-svc-solo__glyph" />
                            </span>
                            <span class="home-svc-solo__text">
                                <span class="item-title home-svc-solo__label">{{ $sc['label'] ?? $sc['nav_label'] }}</span>
                                <span class="body-text home-svc-solo__desc">{{ $soloBlurbs[$code] ?? '' }}</span>
                            </span>
                            <x-icon name="chevron-right" class="home-svc-solo__chevron" />
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endunless

    {{-- ── Điểm đến yêu thích: hero cinemascape + strip mosaic ── --}}
    @php $destinationsSection = view_data()->homeSection('destinations'); @endphp
    @unless ($destinationsSection['hidden'] ?? false)
        <x-home.destinations :countries="$countries" :section="$destinationsSection" />
    @endunless

    {{-- ── Video trải nghiệm (ngay dưới điểm đến) ── --}}
    @php $videosSection = view_data()->homeSection('videos'); @endphp
    @unless ($videosSection['hidden'] ?? false)
        <x-shared.video-showcase :section="$videosSection" :home-only="true" :limit="12" />
    @endunless

    {{-- ── Đội ngũ → đánh giá nền tảng → khách hàng kể lại ── --}}
    @php
        $teamSection = view_data()->homeSection('team');
        $reviewPlatformsSection = view_data()->homeSection('review_platforms');
        $testimonialsSection = view_data()->homeSection('testimonials');
    @endphp
    @unless ($teamSection['hidden'] ?? false)
        <x-shared.team-grid :team="view_data()->teamForHome()" :section="$teamSection" />
    @endunless
    @unless ($reviewPlatformsSection['hidden'] ?? false)
        <x-shared.review-platforms :section="$reviewPlatformsSection" />
    @endunless
    @unless ($testimonialsSection['hidden'] ?? false)
        <x-shared.testimonial-carousel :section="$testimonialsSection" :limit="12" />
    @endunless
@endsection
