@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-ink/10 ring-1 ring-ink/5 lg:grid lg:grid-cols-2">
        <section class="relative isolate hidden min-h-[36rem] overflow-hidden bg-ink text-white lg:flex lg:flex-col lg:justify-between lg:p-10 xl:p-12">
            <img src="https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&amp;fit=crop&amp;w=1200&amp;q=85" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover">
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-[#10191f]/95 via-[#10191f]/55 to-[#10191f]/20"></div>
            <div class="pointer-events-none absolute -right-16 -top-20 -z-10 h-64 w-64 rounded-full border border-white/20"></div>
            <a href="{{ route('home') }}" class="inline-flex w-fit items-center gap-2.5">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-gold text-lg text-ink" aria-hidden="true">⌂</span>
                <span class="font-serif text-2xl">isiin<span class="text-gold">.</span></span>
            </a>
            <div class="max-w-md">
                <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-gold backdrop-blur">Selamat datang kembali</span>
                <h1 class="mt-5 font-serif text-4xl leading-tight xl:text-5xl">Ruang yang indah dimulai dari sini.</h1>
                <p class="mt-4 max-w-sm leading-7 text-white/75">Masuk untuk mengelola paket staging dan membuat setiap ruang terasa lebih istimewa.</p>
            </div>
            <p class="text-xs text-white/60">Temukan inspirasi. Ciptakan kesan.</p>
        </section>

        <section class="p-5 sm:p-8 md:p-10 lg:flex lg:flex-col lg:justify-center lg:p-10 xl:p-14">
            <div class="mb-7 lg:hidden">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-gold text-lg text-ink shadow-sm" aria-hidden="true">⌂</span>
                    <span class="font-serif text-2xl">isiin<span class="text-amber-700">.</span></span>
                </a>
            </div>
            <div class="mx-auto w-full max-w-md">
                <span class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Akun Isiin</span>
                <h2 class="mt-2 font-serif text-3xl sm:text-4xl">Masuk</h2>
                <p class="mt-2 text-sm leading-6 text-ink/60">Senang bertemu lagi. Masuk ke akun Isiin Anda.</p>

                <div class="mt-5">
                    @include('partials.errors')
                </div>

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label class="label" for="email">Email</label>
                        <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="nama@email.com" required autofocus>
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <label class="label mb-0" for="password">Password</label>
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-amber-800 transition hover:text-amber-950 hover:underline">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <input class="input pr-12" id="password" type="password" name="password" autocomplete="current-password" placeholder="Masukkan password" required>
                            @include('partials.password-toggle', ['inputId' => 'password'])
                        </div>
                    </div>
                    <label class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-ink/70">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-ink/20 accent-amber-700">
                        Ingat saya
                    </label>
                    <button type="submit" class="btn btn-primary w-full py-3">Masuk ke akun <span class="ml-2" aria-hidden="true">→</span></button>
                </form>

                <p class="mt-6 text-center text-sm text-ink/65">Belum punya akun? <a class="font-semibold text-amber-800 hover:underline" href="{{ route('register') }}">Daftar sekarang</a></p>
                <div class="mt-8 flex items-center justify-center gap-2 text-xs text-ink/45">
                    <span class="text-amber-700" aria-hidden="true">✦</span>
                    <span>Tempat terpercaya untuk home staging</span>
                </div>
            </div>
        </section>
    </div>
@endsection
