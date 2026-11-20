@php
    $items = [
        ['Overview', 'football.index'],
        ['Live scores', 'football.scores'],
        ['Fixtures', 'football.fixtures'],
        ['Results', 'football.results'],
        ['Tables', 'football.competitions.index'],
        ['Transfers', 'football.transfers.index'],
        ['News', 'football.news'],
        ['Insights', 'football.insights'],
    ];
@endphp
<div class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-navy-900">
    <div class="container-page">
        <div class="flex flex-wrap items-end justify-between gap-3 pt-6">
            <div>
                <x-breadcrumbs class="mb-2" />
                <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">{{ $title ?? 'Football' }}</h1>
            </div>
            <x-ad-slot placement="football_banner" class="hidden lg:flex" />
        </div>
        <nav class="-mx-1 mt-4 flex gap-1 overflow-x-auto px-1 pb-3 scrollbar-none" aria-label="Football">
            @foreach ($items as [$label, $route])
                <a href="{{ route($route) }}" class="tab {{ request()->routeIs($route) || ($route === 'football.competitions.index' && request()->routeIs('football.competitions.*')) ? 'tab-active' : '' }}"
                   @if (request()->routeIs($route)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</div>
