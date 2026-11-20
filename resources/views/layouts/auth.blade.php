@extends('layouts.app')

@section('content')
    <div class="container-page grid min-h-[70vh] place-items-center py-12">
        <div class="w-full max-w-md">
            <div class="card p-6 sm:p-8">
                <h1 class="font-display text-2xl font-extrabold text-navy-900 dark:text-white">@yield('title')</h1>
                @hasSection('subtitle')
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">@yield('subtitle')</p>
                @endif
                <div class="mt-6">@yield('form')</div>
            </div>
            @hasSection('footer')
                <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">@yield('footer')</p>
            @endif
        </div>
    </div>
@endsection
