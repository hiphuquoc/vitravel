@extends('layouts.app')

@section('title', 'Không tìm thấy trang — ViTravel')
@section('hide-inquiry', '1')

@section('content')
    <section class="container-site flex flex-col items-center py-24 text-center">
        <p class="font-display text-7xl font-bold text-primary-500 sm:text-8xl">404</p>
        <h1 class="mt-4 font-display text-2xl font-bold sm:text-3xl">Có vẻ bạn đã đi lạc đường rồi!</h1>
        <p class="mt-3 max-w-md text-sm leading-6 text-ink-soft">
            Trang bạn tìm không tồn tại hoặc đã được chuyển đi nơi khác.
            Đừng lo — mọi hành trình đẹp đều có vài khúc cua bất ngờ.
        </p>

        <form action="{{ route('search') }}" method="get" class="site-search-bar mt-8 w-full max-w-md" role="search">
            <x-icon name="search" class="site-search-bar__icon size-5" />
            <input type="search" name="q" placeholder="Tìm tour, điểm đến..." class="site-search-bar__input" autocomplete="off">
            <button type="submit" class="btn-primary-sm site-search-bar__submit">
                <x-icon name="search" class="size-5 shrink-0" />
                <span>Tìm</span>
            </button>
        </form>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary">Về trang chủ</a>
            <a href="{{ locale_route('tours.index', 'viet-nam') }}" class="btn-outline">Xem tour Việt Nam</a>
            <a href="{{ locale_route('guide.index') }}" class="btn-outline">Đọc cẩm nang du lịch</a>
        </div>
    </section>
@endsection
