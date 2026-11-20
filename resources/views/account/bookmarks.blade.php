@extends('layouts.app')

@section('content')
    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <aside class="lg:col-span-3">@include('account.partials.nav')</aside>
        <div class="lg:col-span-9">
            <h1 class="mb-6 font-display text-3xl font-extrabold text-navy-900 dark:text-white">Saved articles</h1>
            @if ($articles->isEmpty())
                <x-empty-state title="Nothing saved yet" icon="bookmark">Tap “Save” on any article to read it later.</x-empty-state>
            @else
                <div class="space-y-6">
                    @foreach ($articles as $article)
                        <div class="flex items-start gap-3">
                            <div class="flex-1"><x-article-card :article="$article" variant="horizontal" /></div>
                            <livewire:bookmark-button :article-id="$article->id" :key="'bm-'.$article->id" />
                        </div>
                    @endforeach
                </div>
                <div class="mt-10">{{ $articles->links() }}</div>
            @endif
        </div>
    </div>
@endsection
