@extends('layouts.app')

@section('content')
    <div class="container-page max-w-3xl pt-6">
        <x-breadcrumbs class="mb-4" />
        <h1 class="font-display text-3xl font-extrabold text-navy-900 sm:text-4xl dark:text-white">{{ $title }}</h1>
        <div class="article-body mt-8">
            @yield('body')
        </div>
    </div>
@endsection
