@extends('layouts.app')

@section('content')
    <div class="container-page pt-6">
        <x-breadcrumbs class="mb-4" />
        <header class="card flex flex-wrap items-center gap-5 p-6">
            @if ($player->photo_url)
                <img src="{{ $player->photo_url }}" alt="{{ $player->name }}" class="size-24 rounded-full object-cover">
            @else
                <span class="grid size-24 place-items-center rounded-full bg-navy-900 text-white"><x-svg-icon name="user" class="size-10" /></span>
            @endif
            <div>
                <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">{{ $player->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ collect([$player->position, $player->nationality?->name, $player->team?->name, $player->birth_date ? 'Age '.$player->birth_date->age : null])->filter()->implode(' · ') }}
                </p>
            </div>
        </header>

        @if ($player->bio)
            <p class="mt-6 max-w-3xl text-slate-700 dark:text-slate-300">{{ $player->bio }}</p>
        @endif

        <section class="mt-10" aria-labelledby="p-tr">
            <x-section-header title="Transfer history & reports" id="p-tr" />
            @if ($player->transfers->isEmpty())
                <x-empty-state title="No transfer records" icon="swap" />
            @else
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($player->transfers as $transfer)
                        @include('football.partials.transfer-card', ['transfer' => $transfer])
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
