@extends('layouts.app')

@section('content')
    @include('football.partials.nav', ['title' => 'Competitions & tables'])
    <div class="container-page space-y-10 pt-8">
        @forelse ($groups as $country => $competitions)
            <section aria-labelledby="c-{{ \Illuminate\Support\Str::slug($country) }}">
                <h2 id="c-{{ \Illuminate\Support\Str::slug($country) }}" class="mb-4 text-xs font-bold uppercase tracking-[0.2em] text-slate-500">{{ $country }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($competitions as $competition)
                        <div class="card flex items-center justify-between gap-4 p-4">
                            <a href="{{ $competition->url() }}" class="flex min-w-0 items-center gap-3">
                                @if ($competition->logo_url)
                                    <img src="{{ $competition->logo_url }}" alt="" class="size-10 object-contain" loading="lazy">
                                @else
                                    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-pitch-50 text-pitch-600 dark:bg-pitch-500/15"><x-svg-icon name="trophy" class="size-5" /></span>
                                @endif
                                <span class="min-w-0">
                                    <span class="block truncate font-bold text-navy-900 hover:text-pitch-700 dark:text-white">{{ $competition->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ ucfirst($competition->type) }}@if ($competition->provider === 'demo') · demo data @endif</span>
                                </span>
                            </a>
                            @if ($competition->has_standings)
                                <a href="{{ route('football.competitions.table', $competition->slug) }}" class="btn-ghost shrink-0 px-3 py-1.5 text-xs">Table</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <x-empty-state title="No competitions configured" icon="trophy">Competitions are managed in the administration panel.</x-empty-state>
        @endforelse
    </div>
@endsection
