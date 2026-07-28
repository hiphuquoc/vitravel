@props([
    'section' => null,
    'showCta' => true,
])

@php
    $data = $section ?? view_data()->homeSection('company_intro');
    $licenseLabel = app()->getLocale() === 'en' ? 'Travel license' : 'Giấy chứng nhận';
@endphp

{{-- "Hành trình chân thật" — khối giới thiệu công ty dùng chung Home + About Us --}}
<section {{ $attributes->merge(['class' => 'cv-auto section-band']) }} aria-label="{{ $data['title'] ?? 'Giới thiệu ViTravel' }}">
    <div class="container-site">
        <div class="card grid overflow-hidden lg:grid-cols-2">
            <div class="flex h-full min-h-0 flex-col site-pad">
                <div>
                    @if (! empty($data['eyebrow']))
                        <span class="section-eyebrow">{{ $data['eyebrow'] }}</span>
                    @endif
                    @if (! empty($data['title']))
                        <h2 class="section-title mt-1 text-balance">{{ $data['title'] }}</h2>
                    @endif
                    @if (! empty($data['body']))
                        <p class="body-text site-mt">{!! $data['body'] !!}</p>
                    @endif
                    @if (! empty($data['metaLine']))
                        <div class="company-intro__license">
                            <span class="company-intro__license-icon" aria-hidden="true">
                                <x-icon name="shield" class="size-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="company-intro__license-label">{{ $licenseLabel }}</p>
                                <p class="company-intro__license-value">{{ $data['metaLine'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($showCta && ! empty($data['ctaLabel']) && ! empty($data['ctaUrl']))
                    <div class="company-intro__cta">
                        <a href="{{ $data['ctaUrl'] }}" class="btn-primary">
                            {{ $data['ctaLabel'] }}
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                    </div>
                @endif
            </div>
            @if (! empty($data['image']))
                <x-img
                    :src="$data['image']"
                    :srcset="$data['imageSrcset'] ?? null"
                    preset="section"
                    :alt="$data['imageAlt'] ?? ''"
                    class="min-h-64 w-full object-cover lg:min-h-full"
                />
            @else
                <x-ph class="min-h-64 lg:min-h-full" :label="$data['imageAlt'] ?? 'Ảnh đội ngũ ViTravel'" icon="users" icon-class="size-12" />
            @endif
        </div>
    </div>
</section>
