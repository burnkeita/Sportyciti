@extends('layouts.app')

@section('content')
    @include('football.partials.nav', ['title' => 'Live scores'])
    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <div class="min-w-0 lg:col-span-8">
            <livewire:football.scores-board />
        </div>
        <aside class="lg:col-span-4">
            <div class="space-y-6 lg:sticky lg:top-24">
                <x-ad-slot placement="sidebar" />
                <div class="card p-5 text-sm text-slate-600 dark:text-slate-300">
                    <h2 class="mb-2 font-display font-bold text-navy-900 dark:text-white">About our scores</h2>
                    <p>Scores and match events come from our licensed sports data provider and refresh automatically. Data availability varies by competition.</p>
                </div>
            </div>
        </aside>
    </div>
@endsection
