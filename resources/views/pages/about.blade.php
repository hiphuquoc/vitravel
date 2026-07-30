@extends('layouts.app')

@section('title', $about['seo_title'] ?? 'Về chúng tôi — ViTravel')
@section('meta_description', $about['seo_description'] ?? '')

@section('content')
    <x-layout.page-header
        :title="$about['page_title'] ?? 'Về chúng tôi'"
        :subtitle="$about['page_subtitle'] ?? null"
        :breadcrumbs="[['label' => $about['page_title'] ?? 'Về chúng tôi']]"
        :banner-src="$about['banner']['src'] ?? null"
        :banner-srcset="$about['banner']['srcset'] ?? null"
        :banner-alt="$about['banner']['alt'] ?? null"
        banner-label="Ảnh banner: đội ngũ ViTravel"
    />

    {{-- Giới thiệu công ty (dùng chung với Home) — ẩn CTA vì đã ở trang Về chúng tôi --}}
    <x-shared.company-intro :show-cta="false" />

    {{-- Đội ngũ (dùng chung) --}}
    <x-shared.team-grid :team="$team" class="pt-0" />

    {{-- Sứ mệnh / Tầm nhìn — 2 block biển gỗ --}}
    <section class="cv-auto section-band" aria-label="{{ ($about['mission']['title'] ?? '') . ' / ' . ($about['vision']['title'] ?? '') }}">
        <div class="container-site grid site-gap lg:grid-cols-2">
            @foreach (['mission' => 'compass', 'vision' => 'eye'] as $key => $icon)
                @php $block = $about[$key] ?? []; @endphp
                <div class="card signpost-card">
                    @if (! empty($block['image']))
                        <x-img
                            :src="$block['image']"
                            :srcset="$block['imageSrcset'] ?? null"
                            preset="section"
                            :alt="$block['title'] ?? ''"
                            class="signpost-card__media object-cover"
                        />
                    @else
                        <x-ph class="signpost-card__media" :label="'Ảnh: ' . ($block['title'] ?? '')" :icon="$icon" icon-class="size-9" />
                    @endif
                    <div class="signpost-card__body">
                        <h2 class="signpost-card__title">{{ $block['title'] ?? '' }}</h2>
                        <p class="body-text">{{ $block['text'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Sơ đồ vòng tròn 4 giá trị cốt lõi --}}
    @php
        $valuesSection = $about['values_section'] ?? [];
        $valuesTitle = $valuesSection['title'] ?? 'Cam kết với giá trị cốt lõi';
        $valuesHub = $valuesSection['hub_label'] ?? $valuesTitle;
    @endphp
    <section class="cv-auto section-band" aria-label="{{ $valuesTitle }}">
        <div class="container-site">
            <x-shared.section-heading :title="$valuesTitle" />
            <div class="values-diagram">
                <div class="values-diagram__side values-diagram__side--left">
                    @foreach (array_slice($values, 0, 2) as $v)
                        <div>
                            <h3 class="values-diagram__item-title">
                                <span class="values-diagram__item-icon" aria-hidden="true"><x-icon name="check" class="size-4" /></span>
                                {{ $v['name'] }}
                            </h3>
                            <p class="body-text values-diagram__item-desc">{{ $v['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="values-diagram__hub-wrap">
                    <div class="values-diagram__hub">
                        <span class="values-diagram__hub-ring" aria-hidden="true"></span>
                        <span class="values-diagram__hub-ring values-diagram__hub-ring--inner" aria-hidden="true"></span>
                        <div class="values-diagram__hub-core">
                            <x-icon name="sparkles" class="size-7" />
                            <p class="values-diagram__hub-label">{{ $valuesHub }}</p>
                        </div>
                        @foreach ($values as $v)
                            @php
                                $pos = [
                                    0 => 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2',
                                    1 => 'top-1/2 left-0 -translate-x-1/2 -translate-y-1/2',
                                    2 => 'top-1/2 right-0 translate-x-1/2 -translate-y-1/2',
                                    3 => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2',
                                ][$loop->index] ?? '';
                            @endphp
                            @if ($pos !== '')
                                <span class="values-diagram__orbit-label {{ $pos }}">{{ $v['name'] }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="values-diagram__side">
                    @foreach (array_slice($values, 2, 2) as $v)
                        <div>
                            <h3 class="values-diagram__item-title">
                                <span class="values-diagram__item-icon" aria-hidden="true"><x-icon name="check" class="size-4" /></span>
                                {{ $v['name'] }}
                            </h3>
                            <p class="body-text values-diagram__item-desc">{{ $v['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Chính sách bán hàng --}}
    @php
        $policy = $about['sales_policy'] ?? [];
        $policyCtaUrl = filled($policy['cta_url'] ?? null)
            ? $policy['cta_url']
            : locale_route('contact');
    @endphp
    <section class="cv-auto section-band" aria-label="{{ $policy['title'] ?? 'Chính sách bán hàng' }}">
        <div class="container-site">
            <div class="card grid overflow-hidden lg:grid-cols-[1fr_minmax(0,20rem)]">
                <div class="site-pad">
                    <h2 class="section-title">{{ $policy['title'] ?? '' }}</h2>
                    <p class="body-text about-policy__lead">{{ $policy['content'] ?? '' }}</p>
                    @if (! empty($policy['cta_label']))
                        <a href="{{ $policyCtaUrl }}" class="btn-ghost about-policy__cta">
                            {{ $policy['cta_label'] }}
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                    @endif
                </div>
                @if (! empty($policy['image']))
                    <x-img
                        :src="$policy['image']"
                        :srcset="$policy['imageSrcset'] ?? null"
                        preset="section"
                        :alt="$policy['title'] ?? ''"
                        class="min-h-52 w-full object-cover lg:min-h-full"
                    />
                @else
                    <x-ph class="min-h-52 lg:min-h-full" label="Ảnh: gia đình đi du lịch" icon="users" icon-class="size-10" />
                @endif
            </div>
        </div>
    </section>

    {{-- Vì sao chọn chúng tôi --}}
    @php
        $reasonsSection = $about['reasons_section'] ?? [];
        $reasonsCtaUrl = filled($reasonsSection['cta_url'] ?? null)
            ? $reasonsSection['cta_url']
            : locale_route('customize');
    @endphp
    <section class="cv-auto section-band" aria-label="{{ $reasonsSection['title'] ?? 'Vì sao chọn chúng tôi' }}">
        <div class="container-site grid items-center site-gap-lg lg:grid-cols-[minmax(0,24rem)_1fr]">
            @if (! empty($reasonsSection['image']))
                <x-img
                    :src="$reasonsSection['image']"
                    :srcset="$reasonsSection['imageSrcset'] ?? null"
                    preset="section"
                    :alt="$reasonsSection['title'] ?? ''"
                    class="about-mockup object-cover"
                />
            @else
                <x-ph class="about-mockup" label="Ảnh: điện thoại hiển thị website ViTravel" icon="photo" icon-class="size-12" />
            @endif
            <div>
                <h2 class="section-title">{{ $reasonsSection['title'] ?? '' }}</h2>
                <ol class="reason-list">
                    @foreach ($reasons as $r)
                        <li class="reason-list__item">
                            <span class="reason-list__index">{{ $loop->iteration }}</span>
                            <div>
                                <h3 class="reason-list__title">{{ $r['title'] }}</h3>
                                <p class="body-text reason-list__desc">{{ $r['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                @if (! empty($reasonsSection['cta_label']))
                    <a href="{{ $reasonsCtaUrl }}" class="btn-primary reason-list__cta">
                        <x-icon name="route" class="size-5 shrink-0" />
                        {{ $reasonsSection['cta_label'] }}
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- USP + trust dùng chung --}}
    <section class="container-site section-band" aria-label="Cam kết dịch vụ">
        <x-shared.usp-badges />
    </section>
    <x-shared.review-platforms class="pt-0" />
    <x-shared.testimonial-carousel class="pt-0" />

    {{-- Người đại diện tại nước ngoài --}}
    @php $refSection = $about['reference_section'] ?? []; @endphp
    <section class="cv-auto section-band" aria-label="{{ $refSection['title'] ?? 'Người đại diện tại nước ngoài' }}">
        <div class="container-site">
            <x-shared.section-heading
                :title="$refSection['title'] ?? ''"
                :subtitle="$refSection['subtitle'] ?? null"
            />
            <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($referencePersons as $p)
                    <article class="card ref-person-card">
                        @if (! empty($p['image']))
                            <x-img
                                :src="$p['image']"
                                :srcset="$p['imageSrcset'] ?? null"
                                preset="avatar"
                                :alt="$p['name']"
                                class="ref-person-card__avatar object-cover"
                            />
                        @else
                            <x-ph class="ref-person-card__avatar" icon="user" icon-class="size-10" :label="null" />
                        @endif
                        <h3 class="ref-person-card__name">{{ $p['name'] }}</h3>
                        <p class="ref-person-card__country">{{ $p['country'] }}</p>
                        <ul class="ref-person-card__contacts">
                            <li class="ref-person-card__contact"><x-icon name="mail" class="size-4 text-primary-600" /> {{ $p['email'] }}</li>
                            <li class="ref-person-card__contact"><x-icon name="phone" class="size-4 text-primary-600" /> {{ $p['phone'] }}</li>
                            <li class="ref-person-card__contact"><x-icon name="skype" class="size-4 text-primary-600" /> {{ $p['skype'] }}</li>
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <x-shared.video-showcase :home-only="true" :limit="4" />
@endsection
