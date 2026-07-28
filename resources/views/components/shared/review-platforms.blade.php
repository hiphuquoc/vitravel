@props(['section' => null])

@php
    $platforms = view_data()->reviewPlatforms();
    $data = $section ?? view_data()->homeSection('review_platforms');
@endphp

{{-- 3 card nền tảng đánh giá (TripAdvisor / Google / Trustpilot) --}}
<section {{ $attributes->merge(['class' => 'cv-auto py-14']) }} aria-label="{{ $data['title'] ?? 'Đánh giá trên các nền tảng' }}">
    <div class="container-site">
        <x-shared.section-heading :title="$data['title'] ?? ''" />
        <div class="grid gap-5 sm:gap-6 md:grid-cols-3">
            @foreach ($platforms as $p)
                <article class="card flex flex-col items-center px-6 py-8 text-center transition hover:shadow-(--shadow-card-hover) sm:p-8">
                    <span class="flex h-12 items-center rounded-lg bg-page px-5 font-display text-xl font-bold text-ink">
                        {{ $p['name'] }}
                    </span>
                    <x-shared.stars :rating="$p['rating'] ?? 5" class="mt-4" />
                    <p class="body-text mt-4 flex-1">{{ $p['quote'] }}</p>
                    <a href="{{ $p['url'] ?? '#' }}" class="btn-ghost mt-5" @if(($p['url'] ?? '#') !== '#') target="_blank" rel="noopener noreferrer" @endif>{{ $p['link'] }} <x-icon name="arrow-right" class="size-4" /></a>
                </article>
            @endforeach
        </div>
    </div>
</section>
