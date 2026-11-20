@props(['title', 'url'])
@php
    $u = urlencode($url);
    $t = urlencode($title);
    $links = [
        ['WhatsApp', 'whatsapp', "https://wa.me/?text={$t}%20{$u}", 'hover:bg-[#25D366]'],
        ['Facebook', 'facebook', "https://www.facebook.com/sharer/sharer.php?u={$u}", 'hover:bg-[#1877F2]'],
        ['X', 'x-social', "https://x.com/intent/post?text={$t}&url={$u}", 'hover:bg-black'],
    ];
@endphp
<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }} x-data="{ msg: '' }">
    <span class="mr-1 text-xs font-semibold uppercase tracking-wider text-slate-500">Share</span>
    @foreach ($links as [$name, $icon, $href, $hover])
        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="grid size-9 place-items-center rounded-full bg-slate-100 text-navy-900 transition hover:text-white dark:bg-white/10 dark:text-white {{ $hover }}" aria-label="Share on {{ $name }}">
            <x-svg-icon :name="$icon" class="size-4" />
        </a>
    @endforeach
    <button type="button" class="grid size-9 place-items-center rounded-full bg-slate-100 text-navy-900 transition hover:bg-navy-900 hover:text-white dark:bg-white/10 dark:text-white"
            aria-label="Share or copy link"
            @click="shareArticle(@js($title), @js($url)).then(r => { msg = r === 'copied' ? 'Link copied' : ''; setTimeout(() => msg = '', 2000) })">
        <x-svg-icon name="share" class="size-4" />
    </button>
    <span x-show="msg" x-text="msg" x-cloak class="text-xs font-medium text-pitch-600" role="status"></span>
</div>
