@props(['inverted' => false, 'height' => 'h-11 sm:h-14'])
{{-- Official SportyCiti.com wordmark. "inverted" forces the white-text version for dark backgrounds;
     otherwise the version switches automatically with dark mode. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    @if ($inverted)
        <img src="{{ asset('images/logo-dark.webp') }}" alt="SportyCiti.com" width="678" height="160" class="{{ $height }} w-auto" decoding="async">
    @else
        <img src="{{ asset('images/logo.webp') }}" alt="SportyCiti.com" width="678" height="160" class="{{ $height }} w-auto dark:hidden" decoding="async">
        <img src="{{ asset('images/logo-dark.webp') }}" alt="SportyCiti.com" width="678" height="160" class="{{ $height }} hidden w-auto dark:block" decoding="async">
    @endif
</span>
