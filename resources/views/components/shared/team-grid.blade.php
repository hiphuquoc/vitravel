@props(['team' => null, 'showMore' => true, 'section' => null])

@php
    $members = $team ?? view_data()->team();
    $data = $section ?? view_data()->homeSection('team');
    $useCarousel = count($members) > 4;
@endphp

{{-- Đội ngũ tận tâm — dùng ở Home, About Us, trang Đội ngũ --}}
<section {{ $attributes->merge(['class' => 'cv-auto section-band']) }} aria-label="{{ $data['title'] ?? 'Đội ngũ' }}">
    <div class="container-site">
        <x-shared.section-heading
            :eyebrow="$data['eyebrow'] ?? null"
            :title="$data['title'] ?? ''"
            :subtitle="$data['subtitle'] ?? null"
        />

        @if ($useCarousel)
            <div x-data="carousel" class="relative listing-snap">
                <div x-ref="track" class="snap-carousel team-grid-carousel" role="list">
                    @foreach ($members as $m)
                        @php $profileUrl = $m['url'] ?? null; @endphp
                        <article role="listitem" class="snap-carousel__item card team-card group card-body text-center transition hover:shadow-(--shadow-card-hover)">
                            @if ($profileUrl)
                                <a href="{{ $profileUrl }}" class="team-card-link" aria-label="Xem hồ sơ {{ $m['name'] }}">
                            @endif
                            @if (!empty($m['image']))
                                <x-img
                                    :src="$m['image']"
                                    :srcset="$m['imageSrcset'] ?? null"
                                    preset="avatar"
                                    :alt="$m['name']"
                                    class="team-card-avatar rounded-full object-cover ring-4 ring-page transition group-hover:ring-primary-100"
                                />
                            @else
                                <x-ph class="team-card-avatar rounded-full ring-4 ring-page transition group-hover:ring-primary-100" icon="user" icon-class="size-10" :label="null" />
                            @endif
                            <div class="card-inner w-full">
                                <h3 class="item-title leading-snug">{{ $m['name'] }}</h3>
                                <p class="text-sm text-primary-600 italic">{{ $m['role'] }}</p>
                                <p class="body-text line-clamp-3">{{ $m['bio'] }}</p>
                                @if ($profileUrl)
                                    <span class="team-card-cta mt-2 inline-flex items-center justify-center gap-1 text-sm font-medium text-primary-700 transition group-hover:text-primary-800">
                                        Xem hồ sơ
                                        <x-icon name="arrow-right" class="size-3.5" />
                                    </span>
                                @endif
                            </div>
                            @if ($profileUrl)
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
                <button type="button" @click="go(-1)" x-show="canPrev" x-cloak
                    class="absolute top-1/2 -left-3 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600"
                    aria-label="Thành viên trước">
                    <x-icon name="chevron-left" class="size-5" />
                </button>
                <button type="button" @click="go(1)" x-show="canNext" x-cloak
                    class="absolute top-1/2 -right-3 z-10 flex size-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-(--shadow-card-hover) transition hover:scale-105 hover:text-primary-600"
                    aria-label="Thành viên tiếp">
                    <x-icon name="chevron-right" class="size-5" />
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 site-gap sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($members as $m)
                    @php $profileUrl = $m['url'] ?? null; @endphp
                    <article class="card team-card group card-body text-center transition hover:shadow-(--shadow-card-hover)">
                        @if ($profileUrl)
                            <a href="{{ $profileUrl }}" class="team-card-link" aria-label="Xem hồ sơ {{ $m['name'] }}">
                        @endif
                        @if (!empty($m['image']))
                            <x-img
                                :src="$m['image']"
                                :srcset="$m['imageSrcset'] ?? null"
                                preset="avatar"
                                :alt="$m['name']"
                                class="team-card-avatar rounded-full object-cover ring-4 ring-page transition group-hover:ring-primary-100"
                            />
                        @else
                            <x-ph class="team-card-avatar rounded-full ring-4 ring-page transition group-hover:ring-primary-100" icon="user" icon-class="size-10" :label="null" />
                        @endif
                        <div class="card-inner w-full">
                            <h3 class="item-title leading-snug">{{ $m['name'] }}</h3>
                            <p class="text-sm text-primary-600 italic">{{ $m['role'] }}</p>
                            <p class="body-text line-clamp-3">{{ $m['bio'] }}</p>
                            @if ($profileUrl)
                                <span class="team-card-cta mt-2 inline-flex items-center justify-center gap-1 text-sm font-medium text-primary-700 transition group-hover:text-primary-800">
                                    Xem hồ sơ
                                    <x-icon name="arrow-right" class="size-3.5" />
                                </span>
                            @endif
                        </div>
                        @if ($profileUrl)
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if ($showMore && ! empty($data['ctaLabel']) && ! empty($data['ctaUrl']))
            <div class="site-mt-lg text-center">
                <a href="{{ $data['ctaUrl'] }}" class="btn-outline">{{ $data['ctaLabel'] }}</a>
            </div>
        @endif
    </div>
</section>
