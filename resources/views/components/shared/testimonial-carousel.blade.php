@props(['limit' => 12, 'section' => null, 'homeOnly' => true])

@php
    $testimonials = array_slice(view_data()->testimonials($homeOnly), 0, $limit);
    $data = $section ?? view_data()->homeSection('testimonials');
@endphp

{{-- Carousel cảm nhận khách hàng — trust block dùng chung nhiều trang --}}
<section {{ $attributes->merge(['class' => 'cv-auto section-band']) }} aria-label="{{ $data['title'] ?? 'Cảm nhận khách hàng' }}">
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
                        class="card snap-carousel__item flex shrink-0 flex-col card-body transition hover:shadow-(--shadow-card-hover)">
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
                            <div class="min-w-0">
                                <p class="text-base font-bold leading-snug">{{ $t['name'] }}</p>
                                <p class="mt-0.5 text-sm text-muted">{{ $t['flag'] }} {{ $t['country'] }}@if (! empty($t['trip'])) · {{ $t['trip'] }}@endif</p>
                            </div>
                        </div>
                        <div class="card-inner flex-1">
                            <x-shared.rating :rating="$t['rating']" />
                            <blockquote class="body-text flex flex-1 gap-2">
                                <x-icon name="quote" class="mt-1 size-5 shrink-0 text-primary-300" />
                                <span>{{ $t['quote'] }}</span>
                            </blockquote>
                        </div>
                        @php
                            $urls = $t['photoUrls'] ?? [];
                            $srcsets = $t['photoSrcsets'] ?? [];
                            $photoCount = (int) ($t['photos'] ?? 0);
                        @endphp
                        @if ($photoCount > 0 || count($urls) > 0)
                            <div class="card-footer flex gap-2">
                                @for ($i = 0; $i < 2; $i++)
                                    @if (! empty($urls[$i]))
                                        <x-img
                                            :src="$urls[$i]"
                                            :srcset="$srcsets[$i] ?? null"
                                            preset="gallery"
                                            alt=""
                                            class="h-14 flex-1 rounded-lg object-cover"
                                        />
                                    @else
                                        <x-ph class="h-14 flex-1 rounded-lg" icon="photo" icon-class="size-4" />
                                    @endif
                                @endfor
                                <div class="img-ph relative h-14 flex-1 overflow-hidden rounded-lg">
                                    @if (! empty($urls[2]))
                                        <x-img
                                            :src="$urls[2]"
                                            :srcset="$srcsets[2] ?? null"
                                            preset="gallery"
                                            alt=""
                                            class="absolute inset-0 h-full w-full object-cover"
                                        />
                                    @endif
                                    <span class="absolute inset-0 flex items-center justify-center rounded-lg bg-ink/50 text-xs font-bold text-white">
                                        +{{ max($photoCount, count($urls)) }}
                                    </span>
                                </div>
                            </div>
                        @endif
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
            <div class="site-mt-lg text-center">
                <a href="{{ $data['ctaUrl'] }}" class="btn-outline">{{ $data['ctaLabel'] }}</a>
            </div>
        @endif
    </div>
</section>
