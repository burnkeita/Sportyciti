@extends('layouts.app')

@section('content')
    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <aside class="lg:col-span-3">@include('account.partials.nav')</aside>
        <div class="space-y-8 lg:col-span-9">
            <header>
                <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">Welcome, {{ str($user->name)->before(' ') }}</h1>
                <p class="mt-1 text-slate-600 dark:text-slate-300">{{ $savedCount }} saved {{ str('article')->plural($savedCount) }} · following {{ $favourites->count() }} {{ str('section')->plural($favourites->count()) }}</p>
            </header>

            <section aria-labelledby="feed-title">
                <x-section-header title="Your feed" :href="route('account.interests')" link-text="Edit interests" id="feed-title" />
                @if ($favourites->isEmpty())
                    <x-empty-state title="Personalise your feed" icon="ball">
                        Choose the sections you care about — Black Stars, Premier League, Insights and more — and we’ll collect the latest stories here.
                    </x-empty-state>
                    <div class="mt-4 text-center"><a href="{{ route('account.interests') }}" class="btn-primary">Choose interests</a></div>
                @elseif ($feed->isEmpty())
                    <x-empty-state title="No new stories in your sections yet" />
                @else
                    <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($feed as $article)<x-article-card :article="$article" :show-excerpt="false" />@endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
