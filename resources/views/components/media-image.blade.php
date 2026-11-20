@props([
    'media' => null,
    'size' => 'md',
    'sizes' => '(min-width: 1024px) 33vw, 100vw',
    'eager' => false,
    'ratio' => 'aspect-[16/9]',
    'label' => null,
])
{{-- Responsive image with WebP srcset, explicit aspect ratio (prevents layout shift) and a branded placeholder. --}}
<div {{ $attributes->merge(['class' => "relative overflow-hidden bg-navy-900 {$ratio}"]) }}>
    @if ($media)
        <img
            src="{{ $media->url($size) }}"
            @if ($srcset = $media->srcset()) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
            alt="{{ $media->altText() }}"
            @if ($media->width && $media->height) width="{{ $media->width }}" height="{{ $media->height }}" @endif
            loading="{{ $eager ? 'eager' : 'lazy' }}"
            @if ($eager) fetchpriority="high" @endif
            decoding="async"
            class="absolute inset-0 size-full object-cover transition duration-500 group-hover:scale-[1.03]"
        >
    @else
        <div class="absolute inset-0 grid place-items-center bg-gradient-to-br from-navy-800 via-navy-900 to-navy-950" role="img" aria-label="{{ $label ?? 'SportyCiti' }}">
            <svg class="w-1/4 max-w-24 opacity-30" viewBox="0 0 40 40" aria-hidden="true">
                <path d="M7 27h26v4H7z" fill="#12B76A"/>
                <path d="M10 27V17h4v10zM16 27V11h5v16zM23 27v-8h4v8zM29 27V14h3v13z" fill="#FFFFFF"/>
                <circle cx="30.5" cy="9.5" r="3.5" fill="#FF7A00"/>
            </svg>
        </div>
    @endif
    {{ $slot }}
</div>
