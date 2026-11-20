@php
    $title = $seo->fullTitle();
    $description = \Illuminate\Support\Str::limit(strip_tags($seo->metaDescription()), 300, '…');
    $canonical = $seo->canonical ?: url()->current();
    $image = $seo->image ?: asset('images/og-default.png');
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">
@if ($seo->shouldNoindex())
    <meta name="robots" content="noindex, follow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="en_GB">
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:title" content="{{ $seo->title ?: $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
@foreach ($seo->properties as $property => $value)
    <meta property="{{ $property }}" content="{{ $value }}">
@endforeach

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->title ?: $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

{{-- Structured data --}}
@foreach (array_filter([...$seo->schemas, $seo->breadcrumbSchema()]) as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
