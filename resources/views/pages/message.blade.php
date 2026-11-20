@extends('layouts.app')

@section('content')
    <div class="container-page max-w-xl py-20 text-center">
        <span class="mx-auto grid size-14 place-items-center rounded-full bg-pitch-50 text-pitch-600 dark:bg-pitch-500/15"><x-svg-icon name="mail" class="size-7" /></span>
        <h1 class="mt-6 font-display text-3xl font-extrabold text-navy-900 dark:text-white">{{ $title }}</h1>
        <p class="mt-3 text-slate-600 dark:text-slate-300">{{ $message }}</p>
        <a href="{{ route('home') }}" class="btn-primary mt-8">Back to SportyCiti</a>
    </div>
@endsection
