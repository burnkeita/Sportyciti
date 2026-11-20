@extends('layouts.app')

@section('content')
    @include('football.competitions.partials.header', ['active' => 'fixtures'])
    <div class="container-page grid gap-8 pt-8 lg:grid-cols-2">
        <section aria-labelledby="fx">
            <h2 id="fx" class="section-title mb-4">Fixtures</h2>
            <livewire:football.fixture-browser mode="fixtures" :competition-id="$competition->id" />
        </section>
        <section aria-labelledby="rs">
            <h2 id="rs" class="section-title mb-4">Results</h2>
            <livewire:football.fixture-browser mode="results" :competition-id="$competition->id" />
        </section>
    </div>
@endsection
