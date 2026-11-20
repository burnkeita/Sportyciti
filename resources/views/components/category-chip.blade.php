@props(['category' => null, 'dark' => false])
@if ($category)
    <a href="{{ $category->url() }}"
       {{ $attributes->merge(['class' => 'chip relative z-10 '.($dark ? 'bg-white/15 text-white backdrop-blur hover:bg-white/25' : 'bg-pitch-50 text-pitch-700 hover:bg-pitch-100 dark:bg-pitch-500/15 dark:text-pitch-300')]) }}>
        {{ $category->name }}
    </a>
@endif
