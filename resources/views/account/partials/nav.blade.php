<nav class="card flex gap-1 overflow-x-auto p-2 scrollbar-none lg:flex-col" aria-label="Account">
    @foreach ([['Dashboard', 'account.dashboard', 'user'], ['Saved articles', 'account.bookmarks', 'bookmark'], ['My interests', 'account.interests', 'ball'], ['Profile & security', 'account.profile', 'link']] as [$label, $route, $icon])
        <a href="{{ route($route) }}" class="flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs($route) ? 'bg-navy-900 text-white dark:bg-pitch-500' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10' }}">
            <x-svg-icon :name="$icon" class="size-4" /> {{ $label }}
        </a>
    @endforeach
    @if (auth()->user()->isStaff())
        <a href="{{ url('/admin') }}" class="flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-semibold text-flame-700 hover:bg-flame-50 dark:text-flame-400 dark:hover:bg-white/10">
            <x-svg-icon name="chart" class="size-4" /> Newsroom (admin)
        </a>
    @endif
    <form method="POST" action="{{ route('logout') }}" class="shrink-0 lg:mt-2 lg:border-t lg:border-slate-100 lg:pt-2 dark:lg:border-white/5">
        @csrf
        <button class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10"><x-svg-icon name="logout" class="size-4" /> Sign out</button>
    </form>
</nav>
