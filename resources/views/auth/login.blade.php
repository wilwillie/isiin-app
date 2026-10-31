@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="max-w-md mx-auto card p-6">
        <h1 class="font-serif text-2xl">Masuk</h1>
        <p class="text-sm text-ink/60 mt-1">Masuk ke akun Isiin Anda.</p>

        <div class="mt-4">
            @include('partials.errors')
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input class="input" id="password" type="password" name="password" required>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1"> Ingat saya
            </label>
            <button type="submit" class="btn btn-primary w-full">Masuk</button>
        </form>

        <p class="text-sm text-center mt-4 text-ink/70">Belum punya akun? <a class="underline" href="{{ route('register') }}">Daftar</a></p>
    </div>
@endsection
