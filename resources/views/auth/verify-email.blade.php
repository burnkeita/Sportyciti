@extends('layouts.auth')

@section('title', 'Verify your email')
@section('subtitle', 'We’ve sent a verification link to your email address. Click it to activate saving, interests and comments.')

@section('form')
    @if (session('status') === 'verification-link-sent')
        <p class="mb-4 rounded-lg bg-pitch-50 px-4 py-3 text-sm text-pitch-800 dark:bg-pitch-500/10 dark:text-pitch-200" role="status">A new verification link has been sent.</p>
    @endif
    <div class="flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn-primary">Resend verification email</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-ghost">Sign out</button>
        </form>
    </div>
@endsection
