@props([
    'categories' => [],
    'contentTags' => [],
    'keywords' => [],
    'activeCategory' => null,
])

@php
    $countries = array_values(array_filter(view_data()->countries(), fn ($c) => $c['slug'] !== 'tour-ket-hop'));
@endphp

<aside {{ $attributes->merge(['class' => 'blog-sidebar']) }} aria-label="Thông tin thêm">
    <div class="blog-sidebar__card">
        <section class="blog-sidebar__section">
            <h2 class="blog-sidebar__title">Danh mục cẩm nang</h2>
            <div class="blog-sidebar__tags" role="list">
                @foreach ($categories as $cat)
                    @if (filled($cat['countrySlug']))
                        <a href="{{ locale_route('guide.country', ['country' => $cat['slug']]) }}"
                            role="listitem"
                            @class([
                                'blog-sidebar__tag',
                                'blog-sidebar__tag--cat',
                                'is-active' => $activeCategory === $cat['slug'],
                            ])>
                            <span class="blog-sidebar__tag-label">{{ $cat['name'] }}</span>
                            <span class="blog-sidebar__tag-count">{{ $cat['count'] ?? 0 }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        @if (count($countries))
            <section class="blog-sidebar__section">
                <h2 class="blog-sidebar__title">Theo quốc gia</h2>
                <ul class="blog-sidebar__menu">
                    @foreach ($countries as $c)
                        <li>
                            <a href="{{ locale_route('guide.country', ['country' => $c['slug']]) }}"
                                class="blog-sidebar__nav-link">
                                <span class="blog-sidebar__nav-label">{{ $c['name'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    @if (count($contentTags))
        <div class="blog-sidebar__card">
            <section class="blog-sidebar__section">
                <h2 class="blog-sidebar__title">Chủ đề</h2>
                <div class="blog-sidebar__tags" role="list">
                    @foreach ($contentTags as $tag)
                        <a href="#" class="blog-sidebar__tag" role="listitem">{{ $tag }}</a>
                    @endforeach
                </div>
            </section>
        </div>
    @endif

    @if (count($keywords))
        <div class="blog-sidebar__card">
            <section class="blog-sidebar__section">
                <h2 class="blog-sidebar__title">Từ khoá phổ biến</h2>
                <div class="blog-sidebar__tags" role="list">
                    @foreach ($keywords as $kw)
                        <a href="#" class="blog-sidebar__tag blog-sidebar__tag--soft" role="listitem">{{ $kw }}</a>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
</aside>
