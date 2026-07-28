@extends('layouts.app')

@section('title', $article['title'] . ' — ViTravel')
@section('meta_description', $article['excerpt'])

@php
    // TOC tự sinh từ heading H2/H3 trong nội dung — không lưu riêng để tránh lệch dữ liệu
    $toc = collect($article['content'])
        ->filter(fn ($b) => in_array($b['type'], ['h2', 'h3']))
        ->map(fn ($b) => ['id' => $b['id'], 'text' => $b['text'], 'level' => $b['type'] === 'h2' ? 2 : 3])
        ->values()
        ->all();

    $comments = [
        ['name' => 'Thu Hằng', 'date' => '18/07/2026', 'content' => 'Bài viết rất chi tiết, mình đã đi theo gợi ý tháng 9 và đúng là lúa chín đẹp mê. Cảm ơn tác giả!'],
        ['name' => 'Hoàng Long', 'date' => '05/07/2026', 'content' => 'Cho mình hỏi cuối tháng 10 còn kịp mùa lúa không ạ?'],
    ];
@endphp

@section('content')
    <div class="container-site pt-8" x-data="scrollSpy(@js(array_column($toc, 'id')))">
        <x-layout.breadcrumb :items="array_filter([
            ['label' => 'Cẩm nang du lịch', 'url' => route('guide.index')],
            filled($article['countrySlug'] ?? '')
                ? ['label' => 'Cẩm nang ' . $article['country'], 'url' => route('guide.country', ['country' => $article['countrySlug']])]
                : null,
            ['label' => $article['category']],
        ])" class="mb-4" />

        {{-- Gallery collage đầu bài: 1 ảnh lớn + 4 ảnh nhỏ --}}
        @php
            $coverSrc = $article['imageDetail'] ?? $article['image'] ?? null;
            $coverSrcset = $article['imageDetailSrcset'] ?? $article['imageSrcset'] ?? null;
            $gallery = $article['gallery'] ?? [];
            $thumbs = array_slice($gallery, 0, 4);
            if ($thumbs === [] && $coverSrc) {
                $thumbs[] = ['src' => $coverSrc, 'srcset' => $coverSrcset];
            }
        @endphp
        <div class="mt-5 grid gap-3 lg:grid-cols-[2fr_1fr]">
            @if ($coverSrc)
                <x-img
                    :src="$coverSrc"
                    :srcset="$coverSrcset"
                    preset="detail"
                    :alt="$article['title']"
                    loading="eager"
                    fetchpriority="high"
                    class="h-64 w-full rounded-2xl object-cover sm:h-80"
                />
            @else
                <x-ph class="h-64 w-full rounded-2xl sm:h-80" :label="'Ảnh: ' . $article['title']" icon-class="size-12" />
            @endif
            <div class="grid grid-cols-4 gap-3 lg:grid-cols-2">
                @for ($i = 0; $i < 4; $i++)
                    @php $thumb = $thumbs[$i] ?? null; @endphp
                    <div class="relative overflow-hidden rounded-xl">
                        <div class="relative aspect-[4/3] lg:aspect-auto lg:h-full lg:min-h-[90px]">
                            @if (! empty($thumb['src']))
                                <x-img
                                    :src="$thumb['src']"
                                    :srcset="$thumb['srcset'] ?? null"
                                    preset="gallery"
                                    :alt="$article['title']"
                                    class="absolute inset-0 h-full w-full object-cover"
                                />
                            @else
                                <x-ph class="absolute inset-0" icon-class="size-5" :label="null" />
                            @endif
                            @if ($i === 3 && ($article['galleryCount'] ?? 0) > 4)
                                <span class="absolute inset-0 flex items-center justify-center bg-ink/50 text-sm font-bold text-white">
                                    +{{ $article['galleryCount'] - 4 }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Meta + tiêu đề --}}
        <p class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-muted">
            <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="size-3.5" /> Đăng ngày {{ $article['publishedAt'] }}</span>
            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-3.5" /> Cập nhật {{ $article['updatedAt'] }}</span>
            <span class="inline-flex items-center gap-1.5"><x-icon name="eye" class="size-3.5" /> {{ number_format($article['views']) }} lượt xem</span>
            <span class="inline-flex items-center gap-1.5"><x-icon name="user" class="size-3.5" /> {{ $article['author'] }}</span>
        </p>
        <h1 class="mt-2 max-w-4xl font-display text-3xl font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl">{{ $article['title'] }}</h1>

        {{-- Layout 2 cột: nội dung TRÁI + sidebar PHẢI --}}
        <div class="mt-8 grid items-start gap-10 lg:grid-cols-[1fr_300px]">
            <div class="min-w-0">
                <div class="prose-travel">
                    @foreach ($article['content'] as $block)
                        @switch($block['type'])
                            @case('p')
                                <p>{{ $block['text'] }}</p>
                                @break

                            @case('h2')
                                <h2 id="{{ $block['id'] }}">{{ $block['text'] }}</h2>
                                @break

                            @case('h3')
                                <h3 id="{{ $block['id'] }}">{{ $block['text'] }}</h3>
                                @break

                            @case('ul')
                                <ul>
                                    @foreach ($block['items'] as $li)
                                        <li>{{ $li }}</li>
                                    @endforeach
                                </ul>
                                @break

                            @case('image')
                                <figure class="my-6">
                                    <x-ph class="h-64 w-full rounded-2xl" :label="$block['caption']" icon-class="size-10" />
                                    <figcaption>{{ $block['caption'] }}</figcaption>
                                </figure>
                                @break

                            @case('links')
                                {{-- Box liên kết nội bộ chèn giữa luồng đọc --}}
                                <div class="my-6 rounded-2xl border-l-4 border-primary-500 bg-primary-50 p-5">
                                    <p class="mb-2 text-base font-bold text-primary-800">{{ $block['title'] }}</p>
                                    <ul class="list-none space-y-1.5 !pl-0">
                                        @foreach ($block['links'] as $link)
                                            <li class="!mb-0">
                                                <a href="{{ route($link['route'][0], $link['route'][1] ?? []) }}"
                                                    class="inline-flex items-center gap-1.5 text-base">
                                                    <x-icon name="arrow-right" class="size-3.5" /> {{ $link['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @break
                        @endswitch
                    @endforeach
                </div>

                {{-- Rating cuối bài + chia sẻ --}}
                <div class="mt-10 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-(--shadow-card)">
                    <div class="flex items-center gap-3">
                        <x-shared.rating :rating="$article['rating']" :count="$article['ratingCount']" />
                    </div>
                    <div class="flex items-center gap-2" aria-label="Chia sẻ bài viết">
                        <span class="mr-1 text-sm font-semibold">Chia sẻ:</span>
                        @foreach (['facebook' => 'Facebook', 'twitter' => 'X (Twitter)', 'share' => 'Sao chép liên kết'] as $icon => $label)
                            <button type="button"
                                class="flex size-9 items-center justify-center rounded-full border border-line transition hover:border-primary-300 hover:text-primary-600"
                                aria-label="Chia sẻ qua {{ $label }}">
                                <x-icon :name="$icon" class="size-4" />
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Bình luận --}}
                <section class="mt-12" aria-label="Bình luận">
                    <h2 class="section-title mb-6">Bình luận ({{ count($comments) }})</h2>
                    <div class="space-y-4">
                        @foreach ($comments as $c)
                            <article class="card p-5">
                                <div class="flex items-center gap-3">
                                    <x-ph class="size-10 rounded-full" icon="user" icon-class="size-5" :label="null" />
                                    <div>
                                        <p class="text-base font-bold">{{ $c['name'] }}</p>
                                        <p class="text-xs text-muted">{{ $c['date'] }}</p>
                                    </div>
                                </div>
                                <p class="body-text mt-3">{{ $c['content'] }}</p>
                            </article>
                        @endforeach
                    </div>

                    @if (session('success') === 'comment')
                        <div class="card mt-6 flex flex-col items-center p-6 py-8 text-center">
                            <span class="flex size-12 items-center justify-center rounded-full bg-leaf-100 text-leaf-600">
                                <x-icon name="check" class="size-6" />
                            </span>
                            <p class="mt-3 font-semibold">Cảm ơn bạn! Bình luận đang chờ kiểm duyệt.</p>
                        </div>
                    @else
                        <form action="{{ route('leads.comment') }}" method="POST" class="card mt-6 p-6">
                            @csrf
                            <input type="hidden" name="article_id" value="{{ $article['id'] ?? '' }}">
                            <h3 class="item-title mb-4 text-lg">Để lại bình luận</h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="cm-name" class="field-label field-required">Họ và tên</label>
                                    <input id="cm-name" name="full_name" type="text" required value="{{ old('full_name') }}" class="field-input">
                                    @error('full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="cm-email" class="field-label field-required">Email</label>
                                    <input id="cm-email" name="email" type="email" required value="{{ old('email') }}" class="field-input">
                                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="cm-phone" class="field-label">Số điện thoại</label>
                                    <input id="cm-phone" name="phone" type="tel" value="{{ old('phone') }}" class="field-input">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="cm-content" class="field-label field-required">Bình luận</label>
                                    <textarea id="cm-content" name="content" rows="4" required class="field-input resize-none">{{ old('content') }}</textarea>
                                    @error('content')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                                <p class="text-xs text-muted sm:col-span-2">Bình luận sẽ hiển thị sau khi được kiểm duyệt.</p>
                                <div><button type="submit" class="btn-primary"><x-icon name="mail" class="size-4" /> Gửi bình luận</button></div>
                            </div>
                        </form>
                    @endif
                </section>

                <x-shared.faq :faqs="$article['faqs']" class="mt-12" title="Câu hỏi liên quan" />

                {{-- Bài viết liên quan --}}
                @if (count($related))
                    <section class="mt-12" aria-label="Bài viết cùng chuyên mục">
                        <h2 class="section-title mb-6">Bài viết cùng chuyên mục</h2>
                        <div class="grid gap-6 sm:grid-cols-2">
                            @foreach ($related as $r)
                                <x-blog.card :article="$r" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <x-blog.sidebar :categories="$categories" :keywords="$keywords" :toc="$toc"
                :active-category="$article['categorySlug']" :content-tags="[]" />
        </div>
    </div>

    {{-- JSON-LD Article --}}
    @php
        $articleJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['title'],
            'description' => $article['excerpt'],
            'author' => ['@type' => 'Person', 'name' => $article['author']],
            'datePublished' => $article['publishedAt'],
            'dateModified' => $article['updatedAt'],
            'publisher' => ['@type' => 'Organization', 'name' => 'ViTravel'],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($articleJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
