@extends('layouts.app')

@section('content')
    <h1 class="sr-only">{{ $siteName ?? config('app.name') }} — Ghana and world sports news, live scores and insights</h1>

    <div class="space-y-12 pb-4 pt-6 sm:space-y-16">
        @foreach ($sections as $block)
            @includeIf('home.sections.'.$block['section']->type, ['section' => $block['section'], 'items' => $block['items']])
        @endforeach

        @if ($sections->isEmpty())
            <div class="container-page">
                <x-empty-state title="Homepage not configured">Add homepage sections in the administration panel.</x-empty-state>
            </div>
        @endif
    </div>
@endsection
