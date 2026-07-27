@props([
    'faqs' => [],
    'title' => 'Câu hỏi thường gặp',
    'jsonLd' => true, // xuất schema.org FAQPage
])

@if (count($faqs))
    <section {{ $attributes->merge(['class' => 'cv-auto']) }} aria-label="{{ $title }}">
        <h2 class="section-title mb-6">{{ $title }}</h2>
        <div class="space-y-3" x-data="{ open: 0 }">
            @foreach ($faqs as $faq)
                <div class="card overflow-hidden">
                    <h3>
                        <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-base font-semibold transition hover:text-primary-600"
                            :aria-expanded="open === {{ $loop->index }}">
                            {{ $faq['q'] }}
                            <x-icon name="chevron-down" class="size-4 shrink-0 transition"
                                ::class="open === {{ $loop->index }} && 'rotate-180 text-primary-600'" />
                        </button>
                    </h3>
                    <div x-show="open === {{ $loop->index }}" x-collapse x-cloak>
                        <p class="body-text px-5 pb-4">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($jsonLd)
            @php
                $faqJsonLd = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => collect($faqs)->map(fn ($faq) => [
                        '@type' => 'Question',
                        'name' => $faq['q'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
                    ])->all(),
                ];
            @endphp
            <script type="application/ld+json">{!! json_encode($faqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif
    </section>
@endif
