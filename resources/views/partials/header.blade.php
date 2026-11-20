@php
    $footballLinks = [
        ['Live scores', route('football.scores'), 'ball'],
        ['Fixtures', route('football.fixtures'), 'calendar'],
        ['Results', route('football.results'), 'whistle'],
        ['Tables & competitions', route('football.competitions.index'), 'table'],
        ['Transfers', route('football.transfers.index'), 'swap'],
        ['Football news', route('football.news'), 'arrow-right'],
        ['Football insights', route('football.insights'), 'chart'],
    ];
    $socialIcons = ['facebook' => 'facebook', 'x' => 'x-social', 'instagram' => 'instagram', 'youtube' => 'youtube', 'tiktok' => 'tiktok', 'whatsapp' => 'whatsapp'];
@endphp

{{-- Utility bar --}}
<div class="hidden bg-navy-950 text-xs text-slate-300 md:block">
    <div class="container-page flex h-9 items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <x-local-time :at="now()" format="date" class="font-medium" />
            @if ($liveCount > 0)
                <a href="{{ route('football.scores', ['tab' => 'live']) }}" class="inline-flex items-center gap-1.5 font-semibold text-flame-400 hover:text-flame-300">
                    <span class="live-dot size-1.5 rounded-full bg-flame-500"></span> {{ $liveCount }} live {{ str('match')->plural($liveCount) }}
                </a>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('authors.index') }}" class="hover:text-white">Our team</a>
            <a href="{{ route('pages.show', 'advertise') }}" class="hover:text-white">Advertise</a>
            <a href="{{ route('pages.show', 'contact') }}" class="hover:text-white">Contact</a>
            @if ($socialLinks)
                <span class="h-4 w-px bg-white/20" aria-hidden="true"></span>
                <div class="flex items-center gap-2.5">
                    @foreach ($socialLinks as $network => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener me" class="hover:text-white" aria-label="SportyCiti on {{ ucfirst($network) }}">
                            <x-svg-icon :name="$socialIcons[$network] ?? 'link'" class="size-3.5" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Main header --}}
<header x-data="{ menu: false, search: false }" @keydown.escape.window="menu = false; search = false"
        class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/85 dark:border-white/10 dark:bg-navy-900/95">
    <div class="container-page flex h-16 items-center gap-4 sm:h-20">
        <button type="button" class="-ml-2 grid size-10 place-items-center rounded-lg text-navy-900 hover:bg-slate-100 lg:hidden dark:text-white dark:hover:bg-white/10"
                @click="menu = true" aria-label="Open menu" :aria-expanded="menu">
            <x-svg-icon name="menu" class="size-6" />
        </button>

        <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ $siteName }} home">
            <x-logo />
        </a>

        {{-- Desktop navigation --}}
        <nav class="ml-4 hidden flex-1 items-center gap-0.5 lg:flex" aria-label="Main">
            <div class="group relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                <a href="{{ route('football.index') }}" @focus="open = true"
                   class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold text-navy-900 hover:bg-slate-100 dark:text-white dark:hover:bg-white/10 {{ request()->routeIs('football.*') ? 'text-pitch-600 dark:text-pitch-400' : '' }}">
                    Football <x-svg-icon name="chevron-down" class="size-3.5 opacity-60" />
                </a>
                <div x-show="open" x-transition.opacity.duration.150ms x-cloak @click.outside="open = false"
                     class="absolute left-0 top-full w-[34rem] pt-2">
                    <div class="card grid grid-cols-2 gap-1 p-3 shadow-xl">
                        <div class="space-y-0.5">
                            @foreach ($footballLinks as [$label, $href, $icon])
                                <a href="{{ $href }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-pitch-700 dark:text-slate-200 dark:hover:bg-white/5">
                                    <x-svg-icon :name="$icon" class="size-4 text-pitch-500" /> {{ $label }}
                                </a>
                            @endforeach
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Competitions</p>
                            @foreach ($navCompetitions as $competition)
                                <a href="{{ $competition->url() }}" class="block rounded px-2 py-1.5 text-sm text-slate-700 hover:text-pitch-700 dark:text-slate-300">{{ $competition->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($navCategories as $category)
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <a href="{{ $category->url() }}" @focus="open = true"
                       class="inline-flex items-center gap-1 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold text-navy-900 hover:bg-slate-100 dark:text-white dark:hover:bg-white/10">
                        {{ str_replace([' Sports', 'SportyCiti '], '', $category->name) ?: $category->name }}
                        @if ($category->children->isNotEmpty())<x-svg-icon name="chevron-down" class="size-3.5 opacity-60" />@endif
                    </a>
                    @if ($category->children->isNotEmpty())
                        <div x-show="open" x-transition.opacity.duration.150ms x-cloak class="absolute left-0 top-full w-64 pt-2">
                            <div class="card p-2 shadow-xl">
                                @foreach ($category->children as $child)
                                    <a href="{{ $child->url() }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-pitch-700 dark:text-slate-200 dark:hover:bg-white/5">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <a href="{{ route('videos.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-navy-900 hover:bg-slate-100 dark:text-white dark:hover:bg-white/10">Videos</a>
        </nav>

        <div class="ml-auto flex items-center gap-1">
            <button type="button" @click="search = !search; $nextTick(() => search && $refs.q.focus())" class="grid size-10 place-items-center rounded-lg text-navy-900 hover:bg-slate-100 dark:text-white dark:hover:bg-white/10" aria-label="Search" :aria-expanded="search">
                <x-svg-icon name="search" />
            </button>
            <button type="button" onclick="toggleTheme()" class="grid size-10 place-items-center rounded-lg text-navy-900 hover:bg-slate-100 dark:text-white dark:hover:bg-white/10" aria-label="Toggle dark mode">
                <x-svg-icon name="moon" class="dark:hidden" />
                <x-svg-icon name="sun" class="hidden dark:block" />
            </button>
            @auth
                <a href="{{ route('account.dashboard') }}" class="ml-1 hidden size-9 place-items-center rounded-full bg-navy-900 text-xs font-bold text-white sm:grid dark:bg-pitch-500" aria-label="My account">
                    {{ auth()->user()->initials() }}
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-primary ml-1 hidden px-3 py-2 sm:inline-flex">Sign in</a>
            @endauth
        </div>
    </div>

    {{-- Search panel --}}
    <div x-show="search" x-transition x-cloak class="border-t border-slate-200 bg-white dark:border-white/10 dark:bg-navy-900">
        <form action="{{ route('search') }}" method="get" role="search" class="container-page flex gap-2 py-3">
            <label for="header-search" class="sr-only">Search SportyCiti</label>
            <input id="header-search" x-ref="q" type="search" name="q" placeholder="Search news, teams, players…" class="input" maxlength="120" required>
            <button class="btn-primary">Search</button>
        </form>
    </div>

    {{-- Mobile drawer (teleported to <body>: the header's backdrop-filter would otherwise trap position:fixed) --}}
    <template x-teleport="body">
    <div x-show="menu" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div x-show="menu" x-transition.opacity class="absolute inset-0 bg-navy-950/60" @click="menu = false"></div>
        <nav x-show="menu" x-transition:enter="transition duration-200" x-transition:enter-start="-translate-x-full" x-transition:leave="transition duration-150" x-transition:leave-end="-translate-x-full"
             class="absolute inset-y-0 left-0 flex w-[85%] max-w-sm flex-col overflow-y-auto bg-white shadow-2xl dark:bg-navy-900" x-trap.noscroll="menu">
            <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4 dark:border-white/10">
                <x-logo />
                <button type="button" @click="menu = false" class="grid size-10 place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Close menu"><x-svg-icon name="x" /></button>
            </div>
            <div class="flex-1 space-y-1 p-3">
                <div x-data="{ open: true }">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left font-semibold text-navy-900 dark:text-white" :aria-expanded="open">
                        Football <x-svg-icon name="chevron-down" class="size-4 transition" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div x-show="open" x-collapse class="mb-2 ml-3 space-y-0.5 border-l border-slate-200 pl-3 dark:border-white/10">
                        <a href="{{ route('football.index') }}" class="block rounded px-2 py-2 text-sm font-medium text-slate-700 dark:text-slate-300">Football hub</a>
                        @foreach ($footballLinks as [$label, $href])
                            <a href="{{ $href }}" class="block rounded px-2 py-2 text-sm text-slate-700 dark:text-slate-300">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                @foreach ($navCategories as $category)
                    <div x-data="{ open: false }">
                        <div class="flex items-center">
                            <a href="{{ $category->url() }}" class="flex-1 rounded-lg px-3 py-2.5 font-semibold text-navy-900 dark:text-white">{{ $category->name }}</a>
                            @if ($category->children->isNotEmpty())
                                <button type="button" @click="open = !open" class="grid size-10 place-items-center" :aria-expanded="open" aria-label="Show {{ $category->name }} sections">
                                    <x-svg-icon name="chevron-down" class="size-4 transition" x-bind:class="open && 'rotate-180'" />
                                </button>
                            @endif
                        </div>
                        <div x-show="open" x-collapse class="mb-2 ml-3 space-y-0.5 border-l border-slate-200 pl-3 dark:border-white/10">
                            @foreach ($category->children as $child)
                                <a href="{{ $child->url() }}" class="block rounded px-2 py-2 text-sm text-slate-700 dark:text-slate-300">{{ $child->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <a href="{{ route('videos.index') }}" class="block rounded-lg px-3 py-2.5 font-semibold text-navy-900 dark:text-white">Videos</a>
                <a href="{{ route('galleries.index') }}" class="block rounded-lg px-3 py-2.5 font-semibold text-navy-900 dark:text-white">Photo galleries</a>
            </div>
            <div class="border-t border-slate-200 p-4 dark:border-white/10">
                @auth
                    <a href="{{ route('account.dashboard') }}" class="btn-ghost w-full">My SportyCiti</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary w-full">Sign in</a>
                    <a href="{{ route('register') }}" class="mt-2 block text-center text-sm font-medium text-slate-600 dark:text-slate-300">Create a free account</a>
                @endauth
            </div>
        </nav>
    </div>
    </template>
</header>
