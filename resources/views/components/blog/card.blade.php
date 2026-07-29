@props(['article'])

@php
    $href = filled($article['slugFull'] ?? null)
        ? url(seo_url($article['slugFull']))
        : (filled($article['slug'] ?? null)
            ? locale_route('guide.show', [
                'country' => $article['countrySlug'] ?: ($article['categorySlug'] ?? ''),
                'slug' => $article['slug'],
            ])
            : locale_route('guide.index'));
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
    <div class="card-body flex flex-1 flex-col">
        <div class="card-inner flex-1">
            <p class="blog-card-meta">
                <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="size-3.5" /> {{ $article['publishedAt'] }}</span>
                <span class="inline-flex items-center gap-1.5"><x-icon name="eye" class="size-3.5" /> {{ number_format($article['views']) }} lượt xem</span>
            </p>
            <h3 class="item-title line-clamp-2 leading-snug">
                <a href="{{ $href }}" class="transition group-hover:text-primary-600">{{ $article['title'] }}</a>
            </h3>
            <p class="card-meta-row text-xs">
                <span class="inline-flex items-center gap-1.5 text-muted"><x-icon name="user" class="size-3.5" /> {{ $article['author'] }}</span>
                <span class="blog-card-tag">{{ $article['category'] }}</span>
            </p>
            <p class="body-text line-clamp-2">{{ $article['excerpt'] }}</p>
        </div>
    </div>
</article>
