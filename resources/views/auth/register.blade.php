@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    @php $role = old('role', $role ?? 'customer'); @endphp

    <div class="mx-auto max-w-2xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-ink/10 ring-1 ring-ink/5">
        <div class="bg-gradient-to-r from-[#172329] to-[#344037] px-5 py-6 text-white sm:px-8 sm:py-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold">Selamat datang</p>
            <h1 class="mt-2 font-serif text-3xl sm:text-4xl">Buat akun Isiin</h1>
            <p class="mt-2 text-sm leading-6 text-white/70">Pilih jenis akun untuk memulai.</p>
        </div>
        <div class="p-5 sm:p-8">

        <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
            <a href="{{ route('register', ['role' => 'customer']) }}"
               class="rounded-2xl border px-4 py-3 text-center font-medium transition {{ $role === 'customer' ? 'border-ink bg-ink text-white shadow-sm' : 'border-ink/10 bg-[#faf8f3] text-ink/70 hover:border-gold hover:bg-amber-50' }}">
                Pemilik Properti
            </a>
            <a href="{{ route('register', ['role' => 'vendor']) }}"
               class="rounded-2xl border px-4 py-3 text-center font-medium transition {{ $role === 'vendor' ? 'border-ink bg-ink text-white shadow-sm' : 'border-ink/10 bg-[#faf8f3] text-ink/70 hover:border-gold hover:bg-amber-50' }}">
                Vendor
            </a>
        </div>

        <div class="mt-4">
            @include('partials.errors')
        </div>

        <form method="POST" action="{{ route('register') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">

            <div class="sm:col-span-2">
                <label class="label" for="name">Nama lengkap</label>
                <input class="input" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            @if ($role === 'vendor')
                <div>
                    <label class="label" for="business_name">Nama usaha</label>
                    <input class="input" id="business_name" name="business_name" value="{{ old('business_name') }}" required>
                </div>
                <div>
                    <label class="label" for="city">Kota</label>
                    <input class="input" id="city" name="city" value="{{ old('city') }}">
                </div>
            @endif

            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div>
                <label class="label" for="phone">Nomor telepon</label>
                <input class="input" id="phone" name="phone" value="{{ old('phone') }}">
            </div>
            <div>
                <label class="label" for="password">Password (minimal 8 karakter)</label>
                <div class="relative">
                    <input class="input pr-12" id="password" type="password" name="password" autocomplete="new-password" required>
                    @include('partials.password-toggle', ['inputId' => 'password'])
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="password_confirmation">Ulangi password</label>
                <div class="relative">
                    <input class="input pr-12" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                    @include('partials.password-toggle', ['inputId' => 'password_confirmation'])
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-1 w-full py-3 sm:col-span-2">Daftar <span class="ml-2" aria-hidden="true">→</span></button>
        </form>

        <p class="mt-5 text-center text-sm text-ink/65">Sudah punya akun? <a class="font-semibold text-amber-800 hover:underline" href="{{ route('login') }}">Masuk</a></p>
        </div>
    </div>
@endsection
