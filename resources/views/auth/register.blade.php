@extends('layouts.auth')

@section('title', 'Create your free account')
@section('subtitle', 'Reading SportyCiti is always free — an account lets you save articles, personalise your feed and comment.')

@section('form')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="label">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" maxlength="120" class="input">
            @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input">
            @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="input" aria-describedby="pw-help">
            <p id="pw-help" class="mt-1 text-xs text-slate-500">At least 8 characters.</p>
            @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
        </div>
        <p class="text-xs text-slate-500">By creating an account you agree to our <a href="{{ route('pages.show', 'terms') }}" class="underline">terms</a> and <a href="{{ route('pages.show', 'privacy') }}" class="underline">privacy policy</a>.</p>
        <button class="btn-primary w-full">Create account</button>
    </form>
@endsection

@section('footer')
    Already have an account? <a href="{{ route('login') }}" class="font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Sign in</a>
@endsection
