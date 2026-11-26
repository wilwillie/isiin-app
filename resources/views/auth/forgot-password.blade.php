@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
    <div class="mx-auto max-w-xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-ink/10 ring-1 ring-ink/5">
        <div class="bg-gradient-to-r from-[#172329] to-[#344037] px-5 py-6 text-white sm:px-8 sm:py-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gold text-lg text-ink" aria-hidden="true">⌂</span>
                <span class="font-serif text-xl">isiin<span class="text-gold">.</span></span>
            </a>
        </div>
        <div class="p-5 sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Bantuan akun</p>
        <h1 class="mt-2 font-serif text-3xl sm:text-4xl">Lupa password?</h1>
        <p class="mt-2 text-sm leading-6 text-ink/60">Masukkan email akun Anda untuk menerima tautan pengaturan ulang password.</p>

        <div class="mt-4">
            @include('partials.errors')
        </div>

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="nama@email.com" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-full py-3">Kirim Tautan Reset <span class="ml-2" aria-hidden="true">→</span></button>
        </form>

        <p class="mt-5 text-center text-sm"><a class="font-semibold text-amber-800 hover:underline" href="{{ route('login') }}">← Kembali ke halaman masuk</a></p>
        </div>
    </div>
@endsection
