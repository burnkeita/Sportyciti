@extends('layouts.auth')

@section('title', 'Reset your password')
@section('subtitle', 'Enter your email and we’ll send you a link to choose a new password.')

@section('form')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input">
            @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary w-full">Email reset link</button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}" class="font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Back to sign in</a>
@endsection
