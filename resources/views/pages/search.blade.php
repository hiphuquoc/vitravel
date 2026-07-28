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

    <div class="container-site section-band--sm">
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
            <div class="mx-auto max-w-2xl page-follow">
                <p class="mb-3 text-sm font-semibold text-ink-soft">Điểm đến phổ biến</p>
                <div class="form-pills">
                    @foreach ($destinations as $c)
                        <a href="{{ route('tours.index', $c['slug']) }}" class="site-search-chip">{{ $c['name'] }}</a>
                    @endforeach
                </div>
                @if (count($keywords))
                    <p class="site-mt mb-3 text-sm font-semibold text-ink-soft">Từ khóa gợi ý</p>
                    <div class="form-pills">
                        @foreach ($keywords as $kw)
                            <a href="{{ route('search', ['q' => $kw]) }}" class="site-search-chip site-search-chip--soft">{{ $kw }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @elseif ($total === 0)
            <div class="card listing-empty mx-auto max-w-lg page-follow">
                <x-icon name="search" class="size-10 text-muted" />
                <p class="font-semibold">Không tìm thấy kết quả cho “{{ $q }}”</p>
                <p class="body-text text-muted">Thử từ khóa ngắn hơn, hoặc xem tour theo điểm đến.</p>
                <a href="{{ route('customize') }}" class="btn-primary site-mt">
                    <x-icon name="route" class="size-5 shrink-0" /> Tour riêng
                </a>
            </div>
        @else
            @if (count($results['destinations']))
                <section class="search-section">
                    <h2 class="search-section__title">Điểm đến</h2>
                    <div class="site-mt grid site-gap-sm sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($results['destinations'] as $c)
                            <a href="{{ route('tours.index', $c['slug']) }}" class="search-dest-card">
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
                <section class="search-section">
                    <h2 class="search-section__title">Tour ({{ count($results['tours']) }})</h2>
                    <div class="site-mt site-stack">
                        @foreach ($results['tours'] as $tour)
                            <x-tour.card :item="$tour" :href="route('tours.show', ['country' => $tour['countrySlug'], 'slug' => $tour['slug']])" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if (count($results['cruises']))
                <section class="search-section">
                    <h2 class="search-section__title">Du thuyền ({{ count($results['cruises']) }})</h2>
                    <div class="site-mt grid site-gap sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($results['cruises'] as $cruise)
                            <x-tour.card-compact :item="$cruise"
                                :href="route('cruises.show', ['type' => $cruise['typeSlug'] ?? 'du-thuyen-ha-long', 'slug' => $cruise['slug']])" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if (count($results['articles']))
                <section class="search-section">
                    <h2 class="search-section__title">Cẩm nang ({{ count($results['articles']) }})</h2>
                    <div class="site-mt grid site-gap sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($results['articles'] as $article)
                            <x-blog.card :article="$article" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
    </div>
@endsection
