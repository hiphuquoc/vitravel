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

<aside {{ $attributes->merge(['class' => 'space-y-6']) }} aria-label="Thông tin thêm">
    {{-- Mục lục bài viết (chỉ trang article) --}}
    @if ($toc)
        <div class="card p-5 lg:sticky lg:top-36">
            <h2 class="item-title mb-3 flex items-center gap-2 text-base">
                <x-icon name="filter" class="size-4 text-primary-600" /> Mục lục bài viết
            </h2>
            <ol class="space-y-1.5 text-base">
                @foreach ($toc as $entry)
                    <li class="{{ $entry['level'] === 3 ? 'pl-4' : '' }}">
                        <a href="#{{ $entry['id'] }}"
                            class="block rounded-md px-2 py-1.5 transition"
                            :class="active === '{{ $entry['id'] }}' ? 'bg-primary-50 font-semibold text-primary-700' : 'text-ink-soft hover:text-primary-600'">
                            {{ $entry['text'] }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    {{-- Danh mục blog theo điểm đến --}}
    <div class="card p-5">
        <h2 class="item-title mb-3 text-base">Danh mục cẩm nang</h2>
        <ul class="max-h-64 space-y-0.5 overflow-y-auto pr-1 text-base">
            @foreach ($categories as $cat)
                <li>
                    <a href="{{ route('guide.country', $cat['countrySlug']) }}"
                        class="{{ $activeCategory === $cat['slug'] ? 'bg-primary-50 font-semibold text-primary-700' : 'text-ink-soft hover:text-primary-600' }} flex items-center justify-between rounded-md px-2 py-2 transition">
                        {{ $cat['name'] }}
                        <span class="text-sm text-muted">{{ $cat['count'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 space-y-0.5 border-t border-line pt-3 text-base">
            @foreach ($countries as $c)
                <a href="{{ route('guide.country', $c['slug']) }}"
                    class="block rounded-md px-2 py-2 font-medium text-ink-soft transition hover:text-primary-600">
                    Cẩm nang {{ $c['name'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Lọc theo loại nội dung --}}
    @if (count($contentTags))
        <div class="card p-5">
            <h2 class="item-title mb-3 text-base">Lọc bài viết</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($contentTags as $tag)
                    <a href="#" class="pill">{{ $tag }}</a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tag cloud từ khoá --}}
    @if (count($keywords))
        <div class="card p-5">
            <h2 class="item-title mb-3 text-base">Từ khoá phổ biến</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($keywords as $kw)
                    <a href="#" class="pill bg-page">{{ $kw }}</a>
                @endforeach
            </div>
        </div>
    @endif
</aside>
