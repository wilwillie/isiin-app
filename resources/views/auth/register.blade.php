@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    @php $role = old('role', $role ?? 'customer'); @endphp

    <div class="max-w-md mx-auto card p-6">
        <h1 class="font-serif text-2xl">Daftar Akun</h1>
        <p class="text-sm text-ink/60 mt-1">Pilih tipe akun Anda.</p>

        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
            <a href="{{ route('register', ['role' => 'customer']) }}"
               class="rounded-lg border px-3 py-2 text-center {{ $role === 'customer' ? 'border-ink bg-ink text-white' : 'border-ink/20 bg-white' }}">
                Pemilik Properti
            </a>
            <a href="{{ route('register', ['role' => 'vendor']) }}"
               class="rounded-lg border px-3 py-2 text-center {{ $role === 'vendor' ? 'border-ink bg-ink text-white' : 'border-ink/20 bg-white' }}">
                Vendor
            </a>
        </div>

        <div class="mt-4">
            @include('partials.errors')
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">

            <div>
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
                <input class="input" id="password" type="password" name="password" required>
            </div>
            <div>
                <label class="label" for="password_confirmation">Ulangi password</label>
                <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn btn-primary w-full">Daftar</button>
        </form>

        <p class="text-sm text-center mt-4 text-ink/70">Sudah punya akun? <a class="underline" href="{{ route('login') }}">Masuk</a></p>
    </div>
@endsection
