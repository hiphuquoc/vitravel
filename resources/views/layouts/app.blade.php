<!DOCTYPE html>
<html lang="{{ current_locale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', seo_home_title())</title>
    <meta name="description" content="@yield('meta_description', seo_default_description())">
    <meta property="og:title" content="@yield('title', seo_home_title())">
    <meta property="og:description" content="@yield('meta_description', seo_default_description())">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ current_locale() === 'en' ? 'en_US' : 'vi_VN' }}">
    <link rel="canonical" href="{{ url()->current() }}">

    @php
        $gcsPublic = rtrim((string) config('services.gcs.public_url', ''), '/');
        $gcsHost = $gcsPublic !== '' ? parse_url($gcsPublic, PHP_URL_HOST) : null;
        if (! $gcsHost && config('media.disk') === 'gcs' && config('services.gcs.bucket')) {
            $gcsHost = 'storage.googleapis.com';
        }
    @endphp
    @if ($gcsHost)
        <link rel="preconnect" href="https://{{ $gcsHost }}" crossorigin>
        <link rel="dns-prefetch" href="https://{{ $gcsHost }}">
    @endif

    {{-- JSON-LD sitewide: Organization (root) + WebSite (root) — tách script để validator/Ctrl+F dễ nhận --}}
    {!! schema_ld(schema()->organization()) !!}
    {!! schema_ld(schema()->website()) !!}

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
    <x-layout.project-switcher />

    @stack('scripts')
</body>

</html>
