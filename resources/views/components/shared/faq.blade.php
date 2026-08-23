@props([
    'faqs' => [],
    'title' => 'Câu hỏi thường gặp',
    'jsonLd' => true, // xuất schema.org FAQPage
])

@if (count($faqs))
    <section {{ $attributes->merge(['class' => 'cv-auto']) }} aria-label="{{ $title }}">
        <h2 class="section-title faq__title">{{ $title }}</h2>
        <div class="faq-list" x-data="{ open: 0 }">
            @foreach ($faqs as $faq)
                <div class="card overflow-hidden">
                    <h3>
                        <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}"
                            class="faq-item__trigger"
                            :aria-expanded="open === {{ $loop->index }}">
                            {{ apply_site_brand($faq['q'] ?? '') }}
                            <x-icon name="chevron-down" class="size-4 shrink-0 transition"
                                ::class="open === {{ $loop->index }} && 'rotate-180 text-primary-600'" />
                        </button>
                    </h3>
                    <div x-show="open === {{ $loop->index }}" x-collapse x-cloak>
                        <p class="body-text faq-item__answer">{{ apply_site_brand($faq['a'] ?? '') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($jsonLd)
            {!! schema_ld(schema()->faqPage($faqs)) !!}
        @endif
    </section>
@endif
