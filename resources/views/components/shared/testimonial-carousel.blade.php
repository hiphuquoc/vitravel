@props(['limit' => 6, 'section' => null])

@php
    $testimonials = array_slice(view_data()->testimonials(), 0, $limit);
    $data = $section ?? view_data()->homeSection('testimonials');
@endphp

{{-- Carousel cảm nhận khách hàng — trust block dùng chung nhiều trang --}}
<section {{ $attributes->merge(['class' => 'cv-auto py-14']) }} aria-label="{{ $data['title'] ?? 'Cảm nhận khách hàng' }}">
    <div class="container-site">
        <x-shared.section-heading
            :eyebrow="$data['eyebrow'] ?? null"
            :title="$data['title'] ?? ''"
            :subtitle="$data['subtitle'] ?? null"
        />

        <div x-data="carousel" class="relative">
            <div x-ref="track" class="snap-carousel" role="list">
                @foreach ($testimonials as $t)
                    <article role="listitem"
                        class="card flex w-[calc((100%-1.25rem)/1.5)] shrink-0 flex-col p-5 transition hover:shadow-(--shadow-card-hover) sm:w-[calc((100%-2.5rem)/2.5)] sm:p-6 lg:w-[calc((100%-3.75rem)/3.5)]">
                        <div class="flex items-center gap-3">
                            <x-ph class="size-12 rounded-full" icon="user" icon-class="size-6" :label="null" />
                            <div class="min-w-0">
                                <p class="text-base font-bold leading-snug">{{ $t['name'] }}</p>
                                <p class="mt-0.5 text-sm text-muted">{{ $t['flag'] }} {{ $t['country'] }} · {{ $t['trip'] }}</p>
                            </div>
                        </div>
                        <x-shared.rating :rating="$t['rating']" class="mt-3.5" />
                        <blockquote class="body-text mt-3.5 flex flex-1 gap-2">
                            <x-icon name="quote" class="mt-1 size-5 shrink-0 text-primary-300" />
                            <span>{{ $t['quote'] }}</span>
                        </blockquote>
                        <div class="mt-5 flex gap-2">
                            <x-ph class="h-14 flex-1 rounded-lg" icon="photo" icon-class="size-4" />
                            <x-ph class="h-14 flex-1 rounded-lg" icon="photo" icon-class="size-4" />
                            <div class="img-ph relative h-14 flex-1 rounded-lg">
                                <span class="absolute inset-0 flex items-center justify-center rounded-lg bg-ink/50 text-xs font-bold text-white">
                                    +{{ $t['photos'] }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <button type="button" @click="go(-1)" x-show="canPrev" x-cloak
                class="absolute top-1/2 -left-3 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600"
                aria-label="Xem cảm nhận trước">
                <x-icon name="chevron-left" class="size-5" />
            </button>
            <button type="button" @click="go(1)" x-show="canNext" x-cloak
                class="absolute top-1/2 -right-3 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600"
                aria-label="Xem cảm nhận tiếp theo">
                <x-icon name="chevron-right" class="size-5" />
            </button>
        </div>

        @if (! empty($data['ctaLabel']) && ! empty($data['ctaUrl']))
            <div class="mt-9 text-center">
                <a href="{{ $data['ctaUrl'] }}" class="btn-outline">{{ $data['ctaLabel'] }}</a>
            </div>
        @endif
    </div>
</section>
