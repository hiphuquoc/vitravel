@props([
    'items' => [], // [['id' =>, 'text' =>, 'level' => 2|3]]
])

@php
    $items = array_values(array_filter($items, fn ($e) => filled($e['id'] ?? null) && filled($e['text'] ?? null)));
    $ids = array_column($items, 'id');
    $count = count($items);
@endphp

@if ($count > 0)
<div
    class="article-toc-root"
    x-data="articleToc(@js($ids))"
    @keydown.escape.window="closeDrawer()"
>
    {{-- ── Mục lục đầu bài (đóng/mở) ── --}}
    <nav
        x-ref="tocInline"
        class="article-toc"
        aria-label="Mục lục bài viết"
    >
        <button
            type="button"
            class="article-toc__toggle"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="article-toc-panel"
        >
            <span class="article-toc__head-icon" aria-hidden="true">
                <x-icon name="list" class="article-toc__head-icon-svg" />
            </span>
            <span class="article-toc__toggle-copy">
                <span class="article-toc__toggle-title">Mục lục bài viết</span>
                <span class="article-toc__toggle-meta">
                    <span x-text="activeLabel ? ('Đang xem: ' + activeLabel) : '{{ $count }} mục'"></span>
                </span>
            </span>
            <span class="article-toc__chevron" :class="open && 'is-open'" aria-hidden="true">
                <x-icon name="chevron-down" class="size-4" />
            </span>
        </button>

        <div
            id="article-toc-panel"
            class="article-toc__panel"
            x-show="open"
            x-collapse
            role="region"
            aria-label="Danh sách mục"
        >
            <ol class="article-toc__list">
                @foreach ($items as $entry)
                    <li @class([
                        'article-toc__item',
                        'article-toc__item--sub' => ($entry['level'] ?? 2) >= 3,
                    ])>
                        <a
                            href="#{{ $entry['id'] }}"
                            class="article-toc__link"
                            :class="active === '{{ $entry['id'] }}' && 'is-active'"
                            @click="onNavigate()"
                        >
                            <span class="article-toc__link-text">{{ $entry['text'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>

    {{-- ── FAB cố định (hiện khi TOC đầu bài đã scroll khỏi viewport) ── --}}
    <button
        type="button"
        class="article-toc-fab"
        x-cloak
        x-show="fabVisible && !drawer"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        @click="openDrawer()"
        aria-label="Mở mục lục bài viết"
        title="Mục lục"
    >
        <x-icon name="list" class="article-toc-fab__icon" />
        <span class="article-toc-fab__label">Mục lục</span>
    </button>

    {{-- ── Drawer mục lục ── --}}
    <div
        class="article-toc-drawer"
        x-cloak
        x-show="drawer"
        role="dialog"
        aria-modal="true"
        aria-label="Mục lục bài viết"
    >
        <div
            class="article-toc-drawer__backdrop"
            @click="closeDrawer()"
            x-show="drawer"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        <div
            class="article-toc-drawer__panel"
            x-show="drawer"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-180"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            @click.stop
        >
            <div class="article-toc-drawer__head">
                <div class="article-toc-drawer__head-copy">
                    <span class="article-toc__head-icon" aria-hidden="true">
                        <x-icon name="list" class="article-toc__head-icon-svg" />
                    </span>
                    <div class="min-w-0">
                        <p class="article-toc-drawer__title">Mục lục</p>
                        <p class="article-toc-drawer__subtitle" x-text="activeLabel ? ('Đang xem: ' + activeLabel) : '{{ $count }} mục trong bài'"></p>
                    </div>
                </div>
                <button
                    type="button"
                    class="article-toc-drawer__close"
                    @click="closeDrawer()"
                    aria-label="Đóng mục lục"
                >
                    <x-icon name="close" class="size-5" />
                </button>
            </div>

            <ol class="article-toc__list article-toc__list--drawer">
                @foreach ($items as $entry)
                    <li @class([
                        'article-toc__item',
                        'article-toc__item--sub' => ($entry['level'] ?? 2) >= 3,
                    ])>
                        <a
                            href="#{{ $entry['id'] }}"
                            class="article-toc__link"
                            :class="active === '{{ $entry['id'] }}' && 'is-active'"
                            @click="onNavigate()"
                        >
                            <span class="article-toc__link-text">{{ $entry['text'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
@endif
