@extends('layouts.app')

@section('title', 'Atur Ulang Password')

@section('content')
    <div class="mx-auto max-w-xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-ink/10 ring-1 ring-ink/5">
        <div class="bg-gradient-to-r from-[#172329] to-[#344037] px-5 py-6 text-white sm:px-8 sm:py-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gold text-lg text-ink" aria-hidden="true">⌂</span>
                <span class="font-serif text-xl">isiin<span class="text-gold">.</span></span>
            </a>
        </div>
        <div class="p-5 sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Keamanan akun</p>
        <h1 class="mt-2 font-serif text-3xl sm:text-4xl">Atur ulang password</h1>
        <p class="mt-2 text-sm leading-6 text-ink/60">Buat password baru untuk akun Anda.</p>

        <div class="mt-4">
            @include('partials.errors')
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="username" required>
            </div>
            <div>
                <label class="label" for="password">Password baru (minimal 8 karakter)</label>
                <div class="relative">
                    <input class="input pr-12" id="password" type="password" name="password" autocomplete="new-password" required autofocus>
                    @include('partials.password-toggle', ['inputId' => 'password'])
                </div>
            </div>
            <div>
                <label class="label" for="password_confirmation">Ulangi password baru</label>
                <div class="relative">
                    <input class="input pr-12" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                    @include('partials.password-toggle', ['inputId' => 'password_confirmation'])
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-full py-3">Simpan Password Baru <span class="ml-2" aria-hidden="true">→</span></button>
        </form>
        </div>
    </div>
@endsection
