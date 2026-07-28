@props(['section' => null])

@php
    $platforms = view_data()->reviewPlatforms();
    $data = $section ?? view_data()->homeSection('review_platforms');
@endphp

{{-- 3 card nền tảng đánh giá (TripAdvisor / Google / Trustpilot) --}}
<section {{ $attributes->merge(['class' => 'cv-auto section-band']) }} aria-label="{{ $data['title'] ?? 'Đánh giá trên các nền tảng' }}">
    <div class="container-site">
        <x-shared.section-heading :title="$data['title'] ?? ''" />
        <div class="grid site-gap md:grid-cols-3">
            @foreach ($platforms as $p)
                <article class="card review-card flex flex-col items-center text-center transition hover:shadow-(--shadow-card-hover)">
                    <span class="review-card__brand">
                        {{ $p['name'] }}
                    </span>
                    <div class="card-inner w-full flex-1">
                        <x-shared.stars :rating="$p['rating'] ?? 5" />
                        <p class="body-text flex-1">{{ $p['quote'] }}</p>
                    </div>
                    <a href="{{ $p['url'] ?? '#' }}" class="btn-ghost card-footer" @if(($p['url'] ?? '#') !== '#') target="_blank" rel="noopener noreferrer" @endif>{{ $p['link'] }} <x-icon name="arrow-right" class="size-4" /></a>
                </article>
            @endforeach
        </div>
    </div>
</section>
