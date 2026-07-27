@extends('layouts.app')

@section('title', ($q !== '' ? 'Kết quả “'.$q.'”' : 'Tìm kiếm').' — ViTravel')
@section('meta_description', 'Tìm tour, điểm đến, du thuyền và cẩm nang du lịch trên ViTravel.')

@section('content')
    <x-layout.page-header
        :title="$q !== '' ? 'Kết quả tìm kiếm' : 'Tìm kiếm'"
        :subtitle="$q !== '' ? 'Từ khóa: “'.$q.'” — '.$total.' kết quả' : 'Nhập từ khóa để tìm tour, điểm đến hoặc bài viết'"
        :breadcrumbs="[
            ['label' => 'Tìm kiếm'],
        ]" />

    <div class="container-site py-10">
        <form action="{{ route('search') }}" method="get" class="site-search-bar mx-auto max-w-2xl" role="search">
            <x-icon name="search" class="site-search-bar__icon size-5" />
            <input type="search" name="q" value="{{ $q }}" placeholder="VD: Hạ Long, Sapa 4 ngày, Bali…"
                class="site-search-bar__input" autofocus autocomplete="off">
            <button type="submit" class="btn-primary-sm site-search-bar__submit">
                <x-icon name="search" class="size-5 shrink-0" />
                <span>Tìm kiếm</span>
            </button>
        </form>

        @if ($q === '')
            <div class="mx-auto mt-10 max-w-2xl">
                <p class="mb-3 text-sm font-semibold text-ink-soft">Điểm đến phổ biến</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($destinations as $c)
                        <a href="{{ route('tours.index', $c['slug']) }}" class="site-search-chip">{{ $c['name'] }}</a>
                    @endforeach
                </div>
                @if (count($keywords))
                    <p class="mt-6 mb-3 text-sm font-semibold text-ink-soft">Từ khóa gợi ý</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($keywords as $kw)
                            <a href="{{ route('search', ['q' => $kw]) }}" class="site-search-chip site-search-chip--soft">{{ $kw }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @elseif ($total === 0)
            <div class="card mx-auto mt-10 flex max-w-lg flex-col items-center gap-3 p-10 text-center">
                <x-icon name="search" class="size-10 text-muted" />
                <p class="font-semibold">Không tìm thấy kết quả cho “{{ $q }}”</p>
                <p class="body-text text-muted">Thử từ khóa ngắn hơn, hoặc xem tour theo điểm đến.</p>
                <a href="{{ route('customize') }}" class="btn-primary mt-2">
                    <x-icon name="trekking" class="size-5 shrink-0" /> Tour riêng
                </a>
            </div>
        @else
            @if (count($results['destinations']))
                <section class="mt-10">
                    <h2 class="font-display text-xl font-bold tracking-tight text-ink sm:text-2xl">Điểm đến</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($results['destinations'] as $c)
                            <a href="{{ route('tours.index', $c['slug']) }}"
                                class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-3.5 transition hover:border-primary-300 hover:bg-primary-50">
                                <span>
                                    <span class="block font-semibold text-ink">Tour {{ $c['name'] }}</span>
                                    <span class="text-sm text-muted">{{ $c['tagline'] }}</span>
                                </span>
                                <span class="text-sm font-medium text-muted">{{ $c['tourCount'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (count($results['tours']))
                <section class="mt-10">
                    <h2 class="font-display text-xl font-bold tracking-tight text-ink sm:text-2xl">Tour ({{ count($results['tours']) }})</h2>
                    <div class="mt-4 space-y-6">
                        @foreach ($results['tours'] as $tour)
                            <x-tour.card :item="$tour" :href="route('tours.show', ['country' => $tour['countrySlug'], 'slug' => $tour['slug']])" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if (count($results['cruises']))
                <section class="mt-10">
                    <h2 class="font-display text-xl font-bold tracking-tight text-ink sm:text-2xl">Du thuyền ({{ count($results['cruises']) }})</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($results['cruises'] as $cruise)
                            <x-tour.card-compact :item="$cruise"
                                :href="route('cruises.show', ['type' => $cruise['typeSlug'] ?? 'du-thuyen-ha-long', 'slug' => $cruise['slug']])" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if (count($results['articles']))
                <section class="mt-10">
                    <h2 class="font-display text-xl font-bold tracking-tight text-ink sm:text-2xl">Cẩm nang ({{ count($results['articles']) }})</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($results['articles'] as $article)
                            <x-blog.card :article="$article" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
    </div>
@endsection
