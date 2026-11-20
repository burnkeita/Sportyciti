@extends('layouts.app')

@section('content')
    @include('football.competitions.partials.header', ['active' => 'table'])

    <div class="container-page pt-8">
        @if ($competition->provider === 'demo')<x-demo-notice class="mb-6" />@endif

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex rounded-lg bg-slate-100 p-1 dark:bg-white/5" role="group" aria-label="Table view">
                @foreach (['all' => 'Overall', 'home' => 'Home', 'away' => 'Away'] as $key => $label)
                    @php $hasSplit = $key === 'all' || $standings->flatten()->contains(fn ($r) => ! empty($r->{$key})); @endphp
                    @if ($hasSplit)
                        <a href="{{ request()->fullUrlWithQuery(['view' => $key === 'all' ? null : $key]) }}" class="rounded-md px-3 py-1.5 text-sm font-semibold {{ $view === $key ? 'bg-white text-navy-900 shadow-sm dark:bg-navy-800 dark:text-white' : 'text-slate-600 dark:text-slate-300' }}">{{ $label }}</a>
                    @endif
                @endforeach
            </div>

            @if ($seasons->count() > 1)
                <form method="get" class="flex items-center gap-2">
                    <label for="season" class="text-sm font-semibold text-slate-600 dark:text-slate-300">Season</label>
                    <select id="season" name="season" class="input w-auto py-2" onchange="this.form.submit()">
                        @foreach ($seasons as $s)
                            <option value="{{ $s->year }}" @selected($season?->id === $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <noscript><button class="btn-ghost">Go</button></noscript>
                </form>
            @endif
        </div>

        @if ($standings->isEmpty())
            <x-empty-state title="Table not available yet" icon="table">Standings appear once they are published by our data provider for this season.</x-empty-state>
        @else
            @include('football.partials.standings-table', ['standings' => $standings, 'view' => $view, 'caption' => $competition->name.' '.($season?->name ?? '').' table'])
            <p class="mt-4 text-xs text-slate-500">P played · W won · D drawn · L lost · GF goals for · GA goals against · GD goal difference · Pts points. Data from {{ $competition->provider === 'demo' ? 'fictional demonstration data' : 'our licensed sports data provider' }}.</p>
        @endif
    </div>
@endsection
