@extends('layouts.auth')

@section('title', 'Sign in to SportyCiti')
@section('subtitle', 'Save stories, follow your favourite sections and join the conversation.')

@section('form')
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<p id="email-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="label">Password</label>
                <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Forgot password?</a>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="input">
            @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-pitch-600 focus:ring-pitch-500"> Keep me signed in
        </label>
        <button class="btn-primary w-full">Sign in</button>
    </form>
@endsection

@section('footer')
    New to SportyCiti? <a href="{{ route('register') }}" class="font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Create a free account</a>
@endsection
