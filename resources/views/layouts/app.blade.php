<!DOCTYPE html>
<html lang="{{ current_locale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ViTravel — Tour trọn gói & du thuyền Đông Nam Á')</title>
    <meta name="description" content="@yield('meta_description', 'ViTravel — đại lý du lịch bản địa thiết kế tour trọn gói, du thuyền và hành trình riêng tại Việt Nam, Campuchia, Lào, Thái Lan và Bali.')">
    <meta property="og:title" content="@yield('title', 'ViTravel — Tour trọn gói & du thuyền Đông Nam Á')">
    <meta property="og:description" content="@yield('meta_description', 'Tour trọn gói, du thuyền và hành trình thiết kế riêng bởi chuyên gia bản địa.')">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="vi_VN">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- JSON-LD Organization dùng chung toàn site --}}
    @php
        $orgJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            'name' => 'ViTravel',
            'url' => url('/'),
            'slogan' => 'Hài lòng hơn cả mong đợi',
            'telephone' => '+84 24 3999 8888',
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => '88 Xã Đàn, Đống Đa', 'addressLocality' => 'Hà Nội', 'addressCountry' => 'VN'],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($orgJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @stack('head')
    {{ Illuminate\Support\Facades\Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:shadow-lg">
        Bỏ qua điều hướng, tới nội dung chính
    </a>

    <x-layout.header />

    <main id="main-content">
        @yield('content')
    </main>

    {{-- Form hỏi nhanh lặp lại cuối hầu hết các trang; trang form riêng có thể tắt bằng @section('hide-inquiry') --}}
    @hasSection('hide-inquiry')
    @else
        <x-shared.quick-inquiry />
    @endif

    <x-layout.footer />
    <x-layout.floating-buttons />

    @stack('scripts')
</body>

</html>
