@props(['team' => null, 'showMore' => true, 'section' => null])

@php
    $members = $team ?? view_data()->team();
    $data = $section ?? view_data()->homeSection('team');
@endphp

{{-- Đội ngũ tận tâm — dùng ở Home, About Us, trang Đội ngũ --}}
<section {{ $attributes->merge(['class' => 'cv-auto py-14']) }} aria-label="{{ $data['title'] ?? 'Đội ngũ' }}">
    <div class="container-site">
        <x-shared.section-heading
            :eyebrow="$data['eyebrow'] ?? null"
            :title="$data['title'] ?? ''"
            :subtitle="$data['subtitle'] ?? null"
        />
        <div class="grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4">
            @foreach ($members as $m)
                <article class="card group flex flex-col items-center p-5 text-center transition hover:shadow-(--shadow-card-hover) sm:p-6">
                    @if (!empty($m['image']))
                        <x-img
                            :src="$m['image']"
                            :srcset="$m['imageSrcset'] ?? null"
                            preset="avatar"
                            :alt="$m['name']"
                            class="size-24 rounded-full object-cover ring-4 ring-page transition group-hover:ring-primary-100 sm:size-28"
                        />
                    @else
                        <x-ph class="size-24 rounded-full ring-4 ring-page transition group-hover:ring-primary-100 sm:size-28" icon="user" icon-class="size-10" :label="null" />
                    @endif
                    <h3 class="item-title mt-4 text-base leading-snug sm:text-lg">{{ $m['name'] }}</h3>
                    <p class="mt-1 text-sm text-primary-600 italic">{{ $m['role'] }}</p>
                    <p class="body-text mt-2.5 line-clamp-3">{{ $m['bio'] }}</p>
                </article>
            @endforeach
        </div>
        @if ($showMore && ! empty($data['ctaLabel']) && ! empty($data['ctaUrl']))
            <div class="mt-9 text-center">
                <a href="{{ $data['ctaUrl'] }}" class="btn-outline">{{ $data['ctaLabel'] }}</a>
            </div>
        @endif
    </div>
</section>
