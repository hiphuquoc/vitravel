@props(['team' => null, 'showMore' => true, 'section' => null])

@php
    $members = $team ?? view_data()->team();
    $data = $section ?? view_data()->homeSection('team');
@endphp

{{-- Đội ngũ tận tâm — dùng ở Home, About Us, trang Đội ngũ --}}
<section {{ $attributes->merge(['class' => 'cv-auto section-band']) }} aria-label="{{ $data['title'] ?? 'Đội ngũ' }}">
    <div class="container-site">
        <x-shared.section-heading
            :eyebrow="$data['eyebrow'] ?? null"
            :title="$data['title'] ?? ''"
            :subtitle="$data['subtitle'] ?? null"
        />
        <div class="grid grid-cols-1 site-gap sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($members as $m)
                <article class="card team-card group card-body text-center transition hover:shadow-(--shadow-card-hover)">
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
                    </div>
                </article>
            @endforeach
        </div>
        @if ($showMore && ! empty($data['ctaLabel']) && ! empty($data['ctaUrl']))
            <div class="site-mt-lg text-center">
                <a href="{{ $data['ctaUrl'] }}" class="btn-outline">{{ $data['ctaLabel'] }}</a>
            </div>
        @endif
    </div>
</section>
