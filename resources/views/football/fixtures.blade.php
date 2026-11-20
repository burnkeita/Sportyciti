@extends('layouts.app')

@section('content')
    @include('football.partials.nav', ['title' => $mode === 'results' ? 'Results' : 'Fixtures'])
    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <div class="min-w-0 lg:col-span-8">
            <livewire:football.fixture-browser :mode="$mode" />
        </div>
        <aside class="lg:col-span-4">
            <div class="space-y-6 lg:sticky lg:top-24">
                <x-ad-slot placement="sidebar" />
            </div>
        </aside>
    </div>
@endsection
