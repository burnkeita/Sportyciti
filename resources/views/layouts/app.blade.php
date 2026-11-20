<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#081B33">
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @include('partials.seo')
    <link rel="icon" href="{{ asset('images/icon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('images/icon-192.png') }}" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-180.png') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS" href="{{ route('feed') }}">
    @if ($adsenseClient)
        <link rel="preconnect" href="https://pagead2.googlesyndication.com" crossorigin>
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseClient }}" crossorigin="anonymous"></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only z-50 rounded bg-white px-4 py-2 text-navy-900 focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

    @include('partials.header')

    @if ($breaking->isNotEmpty())
        @include('partials.ticker')
    @endif

    <x-ad-slot placement="header" class="container-page hidden pt-4 md:flex" />

    <main id="main" class="flex-1">
        @if (session('status'))
            <div class="container-page pt-4">
                <div class="rounded-lg bg-pitch-50 px-4 py-3 text-sm font-medium text-pitch-800 ring-1 ring-pitch-200 dark:bg-pitch-500/10 dark:text-pitch-200 dark:ring-pitch-500/30" role="status">{{ session('status') }}</div>
            </div>
        @endif

        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @include('partials.footer')

    <x-ad-slot placement="mobile_banner" class="sticky bottom-0 z-30 border-t border-slate-200 bg-white/95 py-1 backdrop-blur md:hidden dark:border-white/10 dark:bg-navy-950/95" />

    @livewireScripts
    @stack('scripts')
</body>
</html>
