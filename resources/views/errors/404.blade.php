@extends('layouts.app')

@section('content')
    <div class="container-page max-w-xl py-24 text-center">
        <p class="font-display text-7xl font-extrabold text-pitch-500">404</p>
        <h1 class="mt-4 font-display text-3xl font-extrabold text-navy-900 dark:text-white">Offside! That page isn’t here.</h1>
        <p class="mt-3 text-slate-600 dark:text-slate-300">The page you’re looking for may have moved or no longer exists.</p>
        <form action="{{ route('search') }}" method="get" class="mx-auto mt-8 flex max-w-sm gap-2" role="search">
            <label for="q404" class="sr-only">Search</label>
            <input id="q404" type="search" name="q" class="input" placeholder="Search SportyCiti">
            <button class="btn-primary">Search</button>
        </form>
        <a href="{{ route('home') }}" class="mt-6 inline-block text-sm font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Back to the homepage</a>
    </div>
@endsection
