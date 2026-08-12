@extends('layouts.app')

@section('title', $chrome['seo_title'] ?? seo_page_title('Cảm nhận khách hàng'))
@section('meta_description', $chrome['seo_description'] ?? apply_site_brand('Cảm nhận thật từ khách hàng :brand.'))

@section('content')
    <x-layout.page-header
        :title="$chrome['page_title'] ?? 'Cảm nhận khách hàng'"
        :subtitle="$chrome['page_subtitle'] ?? null"
        :breadcrumbs="[['label' => $chrome['page_title'] ?? 'Cảm nhận khách hàng']]"
        :banner-label="$chrome['banner_label'] ?? null"
    />

    <section class="container-site section-band" aria-label="{{ $chrome['section_title'] ?? $chrome['page_title'] ?? 'Cảm nhận' }}">
        <x-shared.section-heading
            :eyebrow="$chrome['eyebrow'] ?? null"
            :title="$chrome['section_title'] ?? ($chrome['page_title'] ?? '')"
            :subtitle="$chrome['section_subtitle'] ?? null"
        />
        @php
            $avg = count($testimonials)
                ? round(array_sum(array_column($testimonials, 'rating')) / count($testimonials), 1)
                : 5.0;
            $isVi = app()->getLocale() === 'vi';
        @endphp
        <div class="reviews-summary">
            <p class="reviews-summary__score">{{ number_format($avg, 1) }}</p>
            <x-shared.stars :rating="$avg" aria-label="{{ $avg }} trên 5 sao" />
            <p class="reviews-summary__meta">
                {{ count($testimonials) }}
                {{ $isVi ? 'cảm nhận từ khách hàng ViTravel' : 'reviews from ViTravel guests' }}
            </p>
        </div>

        <div class="columns-1 site-gap space-y-[var(--space-gap)] sm:columns-2 lg:columns-3">
            @forelse ($testimonials as $t)
                <article class="card review-masonry-card">
                    <div class="flex items-center gap-3">
                        @if (! empty($t['avatar']))
                            <x-img
                                :src="$t['avatar']"
                                :srcset="$t['avatarSrcset'] ?? null"
                                preset="avatar"
                                :alt="$t['name']"
                                class="size-12 rounded-full object-cover"
                            />
                        @else
                            <x-ph class="size-12 rounded-full" icon="user" icon-class="size-6" :label="null" />
                        @endif
                        <div>
                            <p class="text-base font-bold">{{ $t['name'] }}</p>
                            <p class="text-sm text-muted">{{ $t['flag'] ?? '' }} {{ $t['country'] ?? '' }}@if (! empty($t['trip'])) · {{ $t['trip'] }}@endif</p>
                        </div>
                    </div>
                    <x-shared.rating :rating="$t['rating']" class="site-mt" />
                    <p class="body-text site-mt">{{ $t['quote'] }}</p>
                    @php
                        $urls = $t['photoUrls'] ?? [];
                        $srcsets = $t['photoSrcsets'] ?? [];
                        $photoCount = (int) ($t['photos'] ?? 0);
                    @endphp
                    @if ($photoCount > 0 || count($urls) > 0)
                        <div class="site-mt flex gap-2">
                            @if (! empty($urls[0]))
                                <x-img
                                    :src="$urls[0]"
                                    :srcset="$srcsets[0] ?? null"
                                    preset="gallery"
                                    alt=""
                                    class="h-16 flex-1 rounded-lg object-cover"
                                />
                            @else
                                <x-ph class="h-16 flex-1 rounded-lg" icon="photo" icon-class="size-4" />
                            @endif
                            <div class="img-ph relative h-16 flex-1 overflow-hidden rounded-lg">
                                @if (! empty($urls[1]))
                                    <x-img
                                        :src="$urls[1]"
                                        :srcset="$srcsets[1] ?? null"
                                        preset="gallery"
                                        alt=""
                                        class="absolute inset-0 h-full w-full object-cover"
                                    />
                                @endif
                                <span class="absolute inset-0 flex items-center justify-center rounded-lg bg-ink/50 text-xs font-bold text-white">+{{ max($photoCount, count($urls)) }}</span>
                            </div>
                        </div>
                    @endif
                </article>
            @empty
                <div class="card listing-empty col-span-full">
                    <p class="font-semibold">{{ $isVi ? 'Chưa có cảm nhận nào được xuất bản.' : 'No published reviews yet.' }}</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
