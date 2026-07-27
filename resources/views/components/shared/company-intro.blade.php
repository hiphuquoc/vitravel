@props(['section' => null])

@php
    $data = $section ?? view_data()->homeSection('company_intro');
@endphp

{{-- "Hành trình chân thật" — khối giới thiệu công ty dùng chung Home + About Us --}}
<section {{ $attributes->merge(['class' => 'cv-auto py-14']) }} aria-label="{{ $data['title'] ?? 'Giới thiệu ViTravel' }}">
    <div class="container-site">
        <div class="card grid overflow-hidden lg:grid-cols-2">
            <div class="flex flex-col justify-center p-8 sm:p-10 lg:p-12">
                @if (! empty($data['eyebrow']))
                    <p class="kicker text-primary-600">{{ $data['eyebrow'] }}</p>
                @endif
                @if (! empty($data['title']))
                    <h2 class="section-title mt-2">{{ $data['title'] }}</h2>
                @endif
                @if (! empty($data['body']))
                    <p class="body-text mt-4">{!! $data['body'] !!}</p>
                @endif
                @if (! empty($data['metaLine']))
                    <p class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-muted">
                        <x-icon name="shield" class="size-4 text-leaf-600" />
                        {{ $data['metaLine'] }}
                    </p>
                @endif
                @if (! empty($data['ctaLabel']) && ! empty($data['ctaUrl']))
                    <div class="mt-6">
                        <a href="{{ $data['ctaUrl'] }}" class="btn-primary">{{ $data['ctaLabel'] }}</a>
                    </div>
                @endif
            </div>
            @if (! empty($data['image']))
                <img src="{{ $data['image'] }}" alt="{{ $data['imageAlt'] ?? '' }}" class="min-h-64 w-full object-cover lg:min-h-full">
            @else
                <x-ph class="min-h-64 lg:min-h-full" :label="$data['imageAlt'] ?? 'Ảnh đội ngũ ViTravel'" icon="users" icon-class="size-12" />
            @endif
        </div>
    </div>
</section>
