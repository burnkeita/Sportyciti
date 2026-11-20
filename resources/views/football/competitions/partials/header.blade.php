<div class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-navy-900">
    <div class="container-page pt-6">
        <x-breadcrumbs class="mb-4" />
        <div class="flex items-center gap-4">
            @if ($competition->logo_url)
                <img src="{{ $competition->logo_url }}" alt="" class="size-14 object-contain">
            @else
                <span class="grid size-14 place-items-center rounded-xl bg-navy-900 text-white"><x-svg-icon name="trophy" class="size-7" /></span>
            @endif
            <div>
                <h1 class="font-display text-2xl font-extrabold text-navy-900 sm:text-3xl dark:text-white">{{ $competition->name }}</h1>
                <p class="text-sm text-slate-500">{{ $competition->country?->name }}@if ($competition->current_season_year) · Season {{ \App\Models\Season::labelFor($competition->current_season_year) }}@endif</p>
            </div>
        </div>
        <nav class="-mx-1 mt-5 flex gap-1 overflow-x-auto px-1 pb-3 scrollbar-none" aria-label="{{ $competition->name }}">
            <a href="{{ $competition->url() }}" class="tab {{ $active === 'overview' ? 'tab-active' : '' }}">Overview</a>
            <a href="{{ route('football.competitions.fixtures', $competition->slug) }}" class="tab {{ $active === 'fixtures' ? 'tab-active' : '' }}">Fixtures & results</a>
            @if ($competition->has_standings)
                <a href="{{ route('football.competitions.table', $competition->slug) }}" class="tab {{ $active === 'table' ? 'tab-active' : '' }}">Table</a>
            @endif
            @if ($competition->category)
                <a href="{{ $competition->category->url() }}" class="tab">News</a>
            @endif
        </nav>
    </div>
</div>
