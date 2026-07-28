@props(['article'])

@php
    $href = filled($article['countrySlug'] ?? null) && filled($article['slug'] ?? null)
        ? route('guide.show', ['country' => $article['countrySlug'], 'slug' => $article['slug']])
        : route('guide.index');
@endphp

<article {{ $attributes->merge(['class' => 'card group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-(--shadow-card-hover)']) }}>
    <a href="{{ $href }}" aria-hidden="true" tabindex="-1">
        <div class="relative aspect-[16/9]">
            @if (! empty($article['image']))
                <x-img
                    :src="$article['image']"
                    :srcset="$article['imageSrcset'] ?? null"
                    preset="card"
                    :alt="$article['title']"
                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
            @else
                <x-ph class="absolute inset-0" :label="'Ảnh bài viết: ' . $article['title']" />
            @endif
        </div>
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p class="flex items-center gap-4 text-xs text-muted">
            <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="size-3.5" /> {{ $article['publishedAt'] }}</span>
            <span class="inline-flex items-center gap-1.5"><x-icon name="eye" class="size-3.5" /> {{ number_format($article['views']) }} lượt xem</span>
        </p>
        <h3 class="item-title mt-2.5 line-clamp-2 text-lg leading-snug">
            <a href="{{ $href }}" class="transition group-hover:text-primary-600">{{ $article['title'] }}</a>
        </h3>
        <p class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 text-muted"><x-icon name="user" class="size-3.5" /> {{ $article['author'] }}</span>
            <span class="rounded-full bg-primary-50 px-2.5 py-0.5 font-semibold text-primary-700">{{ $article['category'] }}</span>
        </p>
        <p class="body-text mt-3 line-clamp-2 flex-1">{{ $article['excerpt'] }}</p>
    </div>
</article>
