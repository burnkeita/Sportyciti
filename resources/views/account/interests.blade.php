@extends('layouts.app')

@section('content')
    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <aside class="lg:col-span-3">@include('account.partials.nav')</aside>
        <div class="lg:col-span-9">
            <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">My interests</h1>
            <p class="mt-1 text-slate-600 dark:text-slate-300">Pick the sections you want in your personal feed.</p>

            <form method="POST" action="{{ route('account.interests.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('PUT')
                @foreach ($tree as $root)
                    <fieldset class="card p-5">
                        <legend class="sr-only">{{ $root->name }}</legend>
                        <label class="flex items-center gap-2 font-display font-bold text-navy-900 dark:text-white">
                            <input type="checkbox" name="categories[]" value="{{ $root->id }}" @checked(in_array($root->id, $selected, true)) class="rounded border-slate-300 text-pitch-600 focus:ring-pitch-500">
                            {{ $root->name }} <span class="text-xs font-normal text-slate-400">(everything)</span>
                        </label>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($root->children as $child)
                                <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                    <input type="checkbox" name="categories[]" value="{{ $child->id }}" @checked(in_array($child->id, $selected, true)) class="rounded border-slate-300 text-pitch-600 focus:ring-pitch-500">
                                    {{ $child->name }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
                @error('categories')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button class="btn-primary">Save interests</button>
            </form>
        </div>
    </div>
@endsection
