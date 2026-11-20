@extends('layouts.auth')

@section('title', 'Choose a new password')

@section('form')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" class="input">
            @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="label">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="input">
            @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
        </div>
        <button class="btn-primary w-full">Reset password</button>
    </form>
@endsection
