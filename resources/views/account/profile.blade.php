@extends('layouts.app')

@section('content')
    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <aside class="lg:col-span-3">@include('account.partials.nav')</aside>
        <div class="space-y-8 lg:col-span-9">
            <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">Profile & security</h1>

            @if (session('status') === 'profile-information-updated')
                <p class="rounded-lg bg-pitch-50 px-4 py-3 text-sm text-pitch-800 dark:bg-pitch-500/10 dark:text-pitch-200" role="status">Profile updated.</p>
            @elseif (session('status') === 'password-updated')
                <p class="rounded-lg bg-pitch-50 px-4 py-3 text-sm text-pitch-800 dark:bg-pitch-500/10 dark:text-pitch-200" role="status">Password changed.</p>
            @endif

            <form method="POST" action="{{ route('user-profile-information.update') }}" class="card max-w-xl space-y-4 p-6">
                @csrf
                @method('PUT')
                <h2 class="font-display text-lg font-bold">Profile</h2>
                <div>
                    <label for="name" class="label">Name</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" class="input" autocomplete="name">
                    @error('name', 'updateProfileInformation')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="input" autocomplete="email">
                    @error('email', 'updateProfileInformation')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-500">Changing your email requires verifying the new address.</p>
                </div>
                <button class="btn-primary">Save profile</button>
            </form>

            <form method="POST" action="{{ route('user-password.update') }}" class="card max-w-xl space-y-4 p-6">
                @csrf
                @method('PUT')
                <h2 class="font-display text-lg font-bold">Change password</h2>
                <div>
                    <label for="current_password" class="label">Current password</label>
                    <input id="current_password" type="password" name="current_password" required class="input" autocomplete="current-password">
                    @error('current_password', 'updatePassword')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="new_password" class="label">New password</label>
                    <input id="new_password" type="password" name="password" required class="input" autocomplete="new-password">
                    @error('password', 'updatePassword')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="new_password_confirmation" class="label">Confirm new password</label>
                    <input id="new_password_confirmation" type="password" name="password_confirmation" required class="input" autocomplete="new-password">
                </div>
                <button class="btn-primary">Update password</button>
            </form>

            <div class="card max-w-xl p-6 text-sm text-slate-600 dark:text-slate-300">
                <h2 class="mb-2 font-display text-lg font-bold text-navy-900 dark:text-white">Your data</h2>
                <p>To download or delete your account data, contact <a href="mailto:privacy@sportyciti.com" class="font-semibold text-pitch-700 underline">privacy@sportyciti.com</a>. See our <a href="{{ route('pages.show', 'privacy') }}" class="underline">privacy policy</a>.</p>
            </div>
        </div>
    </div>
@endsection
