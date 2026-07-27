@props(['showMore' => true, 'section' => null])

@php
    $videos = view_data()->videos();
    $main = $videos[0];
    $others = array_slice($videos, 1, 3);
    $data = $section ?? view_data()->homeSection('videos');
@endphp

{{-- 1 video lớn trái + danh sách video nhỏ phải --}}
<section {{ $attributes->merge(['class' => 'cv-auto py-14']) }} aria-label="{{ $data['title'] ?? 'Video trải nghiệm' }}">
    <div class="container-site">
        <x-shared.section-heading :title="$data['title'] ?? ''" />
        <div class="grid gap-5 sm:gap-6 lg:grid-cols-[1.6fr_1fr]">
            <article class="card group overflow-hidden">
                <div class="relative aspect-video">
                    <x-ph class="absolute inset-0" :label="'Video: ' . $main['title']" icon="play" icon-class="size-12" />
                    <button type="button"
                        class="absolute inset-0 m-auto flex size-16 cursor-pointer items-center justify-center rounded-full bg-primary-500/95 text-white shadow-(--shadow-float) transition group-hover:scale-110"
                        aria-label="Phát video: {{ $main['title'] }}">
                        <x-icon name="play" class="size-7 translate-x-0.5" />
                    </button>
                    <span class="absolute right-3 bottom-3 rounded bg-ink/70 px-2 py-0.5 text-sm font-semibold text-white">{{ $main['duration'] }}</span>
                </div>
                <div class="p-5 sm:p-6">
                    <h3 class="item-title text-lg leading-snug">{{ $main['title'] }}</h3>
                    <p class="mt-2 flex items-center gap-1.5 text-sm text-muted">
                        <x-icon name="calendar" class="size-3.5" /> {{ $main['date'] }}
                    </p>
                </div>
            </article>

            <div class="flex flex-col gap-4">
                @foreach ($others as $v)
                    <article class="card group flex gap-4 p-3.5 transition hover:shadow-(--shadow-card-hover) sm:p-4">
                        <div class="relative w-36 shrink-0 overflow-hidden rounded-xl sm:w-40">
                            <div class="relative aspect-video">
                                <x-ph class="absolute inset-0" icon="play" icon-class="size-6" :label="null" />
                                <span class="absolute right-1.5 bottom-1.5 rounded bg-ink/70 px-1.5 py-px text-[11px] font-semibold text-white">{{ $v['duration'] }}</span>
                            </div>
                        </div>
                        <div class="min-w-0 py-0.5">
                            <h3 class="line-clamp-2 text-base font-semibold leading-snug transition group-hover:text-primary-600">{{ $v['title'] }}</h3>
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-muted">
                                <x-icon name="calendar" class="size-3.5" /> {{ $v['date'] }}
                            </p>
                        </div>
                    </article>
                @endforeach

                @if ($showMore && ! empty($data['ctaLabel']) && ! empty($data['ctaUrl']))
                    <a href="{{ $data['ctaUrl'] }}" class="btn-ghost mt-auto self-start">
                        {{ $data['ctaLabel'] }} <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
