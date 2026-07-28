@props([
    'categories' => [],
    'contentTags' => [],
    'keywords' => [],
    'activeCategory' => null,
    'toc' => null, // [['id' =>, 'text' =>, 'level' =>]] — chỉ ở trang bài viết
])

@php
    $countries = array_values(array_filter(view_data()->countries(), fn ($c) => $c['slug'] !== 'tour-ket-hop'));
@endphp

<aside {{ $attributes->merge(['class' => 'blog-sidebar']) }} aria-label="Thông tin thêm">
    @if ($toc)
        <div class="card blog-sidebar__panel blog-sidebar__panel--sticky">
            <h2 class="item-title blog-sidebar__title flex items-center gap-2">
                <x-icon name="filter" class="size-4 text-primary-600" /> Mục lục bài viết
            </h2>
            <ol class="blog-sidebar__list">
                @foreach ($toc as $entry)
                    <li class="{{ $entry['level'] === 3 ? 'pl-4' : '' }}">
                        <a href="#{{ $entry['id'] }}"
                            class="blog-sidebar__link block"
                            :class="active === '{{ $entry['id'] }}' && 'is-active'">
                            {{ $entry['text'] }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    <div class="card blog-sidebar__panel">
        <h2 class="item-title blog-sidebar__title">Danh mục cẩm nang</h2>
        <ul class="blog-sidebar__list max-h-64 overflow-y-auto pr-1">
            @foreach ($categories as $cat)
                @if (filled($cat['countrySlug']))
                    <li>
                        <a href="{{ route('guide.country', ['country' => $cat['countrySlug']]) }}"
                            class="blog-sidebar__link {{ $activeCategory === $cat['slug'] ? 'is-active' : '' }}">
                            {{ $cat['name'] }}
                            <span class="text-sm text-muted">{{ $cat['count'] }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
        <div class="blog-sidebar__list site-mt">
            @foreach ($countries as $c)
                <a href="{{ route('guide.country', ['country' => $c['slug']]) }}"
                    class="blog-sidebar__link font-medium">
                    Cẩm nang {{ $c['name'] }}
                </a>
            @endforeach
        </div>
    </div>

    @if (count($contentTags))
        <div class="card blog-sidebar__panel">
            <h2 class="item-title blog-sidebar__title">Lọc bài viết</h2>
            <div class="form-pills">
                @foreach ($contentTags as $tag)
                    <a href="#" class="pill">{{ $tag }}</a>
                @endforeach
            </div>
        </div>
    @endif

    @if (count($keywords))
        <div class="card blog-sidebar__panel">
            <h2 class="item-title blog-sidebar__title">Từ khoá phổ biến</h2>
            <div class="form-pills">
                @foreach ($keywords as $kw)
                    <a href="#" class="pill bg-page">{{ $kw }}</a>
                @endforeach
            </div>
        </div>
    @endif
</aside>
