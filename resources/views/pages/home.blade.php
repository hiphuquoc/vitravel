@extends('layouts.app')

@section('title', seo_home_title())
@section('meta_description', seo_default_description())

@section('content')
    @php
        /** @var array<string, array<string, mixed>> $homeSections */
        $homeSections = view_data()->homeSections();
        $homeSec = static function (string $key) use ($homeSections): array {
            return $homeSections[$key] ?? view_data()->homeSection($key);
        };
    @endphp

    <x-home.hero-slider :slides="$slides" :pills="$pills" :countries="$countries" />

    {{-- ── 4 USP ── --}}
    <section class="container-site section-band" aria-label="Cam kết dịch vụ">
        <x-shared.usp-badges />
    </section>

    {{-- ── Giới thiệu công ty ── --}}
    @php $companyIntro = $homeSec('company_intro'); @endphp
    @unless ($companyIntro['hidden'] ?? false)
        <x-shared.company-intro :section="$companyIntro" />
    @endunless

    {{-- ── Tour nổi bật — lazy IO, skeleton carousel đồng bộ card --}}
    @php $featuredSection = $homeSec('featured_tours'); @endphp
    @unless ($featuredSection['hidden'] ?? false)
        <section class="cv-auto section-band" aria-label="{{ $featuredSection['title'] ?? 'Tour được yêu cầu nhiều nhất' }}">
            <div class="container-site">
                <x-shared.section-heading
                    :eyebrow="$featuredSection['eyebrow'] ?? null"
                    :title="$featuredSection['title'] ?? ''"
                    :subtitle="$featuredSection['subtitle'] ?? null"
                />
                @include('partials.home-featured', [
                    'endpoint' => route('api.listings.featured-tours'),
                    'params' => ['limit' => 12],
                    'kind' => 'tour',
                ])
            </div>
        </section>
    @endunless

    {{-- ── Du thuyền nổi bật ── --}}
    @php $cruisesSection = $homeSec('featured_cruises'); @endphp
    @unless ($cruisesSection['hidden'] ?? false)
        <section class="cv-auto section-band" aria-label="{{ $cruisesSection['title'] ?? 'Du thuyền nổi bật' }}">
            <div class="container-site">
                <x-shared.section-heading
                    :eyebrow="$cruisesSection['eyebrow'] ?? null"
                    :title="$cruisesSection['title'] ?? ''"
                    :subtitle="$cruisesSection['subtitle'] ?? null"
                />
                @include('partials.home-featured', [
                    'endpoint' => route('api.listings.featured-cruises'),
                    'params' => ['limit' => 12],
                    'kind' => 'cruise',
                ])
            </div>
        </section>
    @endunless

    {{-- ── Vé tàu cao tốc / phà nổi bật ── --}}
    @php
        $trainsSection = $homeSec('featured_trains');
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
                @include('partials.home-featured', [
                    'endpoint' => route('api.listings.featured-services'),
                    'params' => ['cluster' => $transportCluster, 'limit' => 12],
                    'kind' => 'service',
                ])
            </div>
        </section>
    @endunless

    {{-- ── Dịch vụ bổ trợ: AJAX deferred hoặc hub links ── --}}
    @php
        $supportSection = $homeSec('support_services');
        $hasSupportFeatured = view_data()->hasFeaturedSupportServices();
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
        @if ($hasSupportFeatured)
            <section class="cv-auto section-band" aria-label="{{ $supportSection['title'] ?? 'Dịch vụ bổ trợ' }}">
                <div class="container-site">
                    <x-shared.section-heading
                        :eyebrow="$supportSection['eyebrow'] ?? null"
                        :title="$supportSection['title'] ?? ''"
                        :subtitle="$supportSection['subtitle'] ?? null"
                    />
                    @include('partials.home-featured', [
                        'endpoint' => route('api.listings.featured-support'),
                        'params' => ['limit' => 12],
                        'kind' => 'service',
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

    {{-- ── Điểm đến yêu thích ── --}}
    @php $destinationsSection = $homeSec('destinations'); @endphp
    @unless ($destinationsSection['hidden'] ?? false)
        <x-home.destinations :countries="$countries" :section="$destinationsSection" />
    @endunless

    {{-- ── Video trải nghiệm ── --}}
    @php $videosSection = $homeSec('videos'); @endphp
    @unless ($videosSection['hidden'] ?? false)
        <x-shared.video-showcase :section="$videosSection" :home-only="true" :limit="12" />
    @endunless

    {{-- ── Đội ngũ → đánh giá nền tảng → khách hàng kể lại ── --}}
    @php
        $teamSection = $homeSec('team');
        $reviewPlatformsSection = $homeSec('review_platforms');
        $testimonialsSection = $homeSec('testimonials');
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
