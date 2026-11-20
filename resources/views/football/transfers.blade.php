@extends('layouts.app')

@section('content')
    @include('football.partials.nav', ['title' => 'Transfers & rumours'])

    <div class="container-page pt-8">
        <div class="mb-6 rounded-xl bg-white p-4 text-sm text-slate-600 ring-1 ring-slate-200 dark:bg-navy-900 dark:text-slate-300 dark:ring-white/10">
            <p><span class="chip mr-1 bg-pitch-500 text-white">Confirmed</span> moves have been officially announced by a club or governing body.
               <span class="chip mx-1 bg-amber-400 text-navy-900">Rumour</span> items are unconfirmed reports — we name the source and never present speculation as fact. Fees are only shown when verified.</p>
        </div>

        <form method="get" class="card mb-8 flex flex-wrap items-end gap-3 p-4">
            <div>
                <label for="t-status" class="label">Show</label>
                <select id="t-status" name="status" class="input w-auto">
                    <option value="">Everything</option>
                    <option value="confirmed" @selected(($filters['status'] ?? '') === 'confirmed')>Confirmed only</option>
                    <option value="rumour" @selected(($filters['status'] ?? '') === 'rumour')>Rumours only</option>
                </select>
            </div>
            @if ($windows->isNotEmpty())
                <div>
                    <label for="t-window" class="label">Transfer window</label>
                    <select id="t-window" name="window" class="input w-auto">
                        <option value="">All windows</option>
                        @foreach ($windows as $window)
                            <option value="{{ $window }}" @selected(($filters['window'] ?? '') === $window)>{{ $window }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($team)
                <input type="hidden" name="team" value="{{ $team->slug }}">
                <p class="text-sm">Club: <strong>{{ $team->name }}</strong> <a href="{{ route('football.transfers.index', collect($filters)->except('team')->filter()->all()) }}" class="ml-1 text-pitch-700 underline">clear</a></p>
            @endif
            <button class="btn-primary">Apply</button>
        </form>

        @if ($transfers->isEmpty())
            <x-empty-state title="No transfers to show" icon="swap">Our newsroom adds confirmed deals and sourced rumours as they break.</x-empty-state>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($transfers as $transfer)
                    @include('football.partials.transfer-card', ['transfer' => $transfer])
                @endforeach
            </div>
            <div class="mt-10">{{ $transfers->links() }}</div>
        @endif
    </div>
@endsection
