@extends('layouts.app')

@section('title', 'Paket Staging')

@section('content')
    <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-[#172329] via-ink to-[#344037] px-5 py-8 text-white shadow-lg shadow-ink/10 sm:px-8 sm:py-10 lg:px-11 lg:py-12">
        <div class="pointer-events-none absolute -right-10 -top-20 -z-10 h-56 w-56 rounded-full border border-gold/20 sm:h-72 sm:w-72"></div>
        <div class="pointer-events-none absolute -right-2 -top-12 -z-10 h-40 w-40 rounded-full border border-gold/10 sm:h-56 sm:w-56"></div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold">Koleksi pilihan</p>
        <h1 class="mt-3 max-w-2xl font-serif text-3xl leading-tight sm:text-4xl lg:text-5xl">Temukan gaya yang pas untuk ruangmu.</h1>
        <p class="mt-3 max-w-xl text-sm leading-6 text-white/70 sm:text-base">Jelajahi paket home staging dari vendor pilihan, lalu ubah ruang kosong jadi tempat yang terasa hidup.</p>
        <div class="mt-6 flex flex-wrap gap-2 text-xs font-medium text-white/80 sm:mt-7 sm:gap-3 sm:text-sm">
            <span class="rounded-full border border-white/15 bg-white/5 px-3 py-2">✦ Banyak pilihan gaya</span>
            <span class="rounded-full border border-white/15 bg-white/5 px-3 py-2">⌂ Siap untuk berbagai ruangan</span>
        </div>
        <a href="{{ route('vendors.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-gold px-4 py-2.5 text-sm font-semibold text-ink transition hover:brightness-95">Cari berdasarkan vendor <span aria-hidden="true">→</span></a>
    </section>

    <form method="GET" action="{{ route('packages.index') }}" class="card mt-5 p-4 sm:mt-7 sm:p-5 lg:p-6">
        <div class="mb-4 sm:mb-5">
            <h2 class="font-serif text-xl sm:text-2xl">Cari paket impianmu</h2>
            <p class="mt-1 text-xs text-ink/55 sm:text-sm">Saring berdasarkan nama, ruangan, atau gaya.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-12">
            <label class="block sm:col-span-2 xl:col-span-6">
                <span class="label">Nama paket</span>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Contoh: ruang keluarga minimalis">
            </label>
            <label class="block xl:col-span-3">
                <span class="label">Ruangan</span>
                <select class="input" name="room_type">
                    <option value="">Semua ruangan</option>
                    @foreach (config('isiin.room_types') as $key => $label)
                        <option value="{{ $key }}" @selected(request('room_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block xl:col-span-3">
                <span class="label">Gaya</span>
                <select class="input" name="style">
                    <option value="">Semua gaya</option>
                    @foreach (config('isiin.styles') as $style)
                        <option value="{{ $style }}" @selected(request('style') === $style)>{{ $style }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex gap-2 pt-1 sm:col-span-2 sm:justify-end xl:col-span-12">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Terapkan filter <span class="ml-2" aria-hidden="true">→</span></button>
                <a href="{{ route('packages.index') }}" class="btn btn-outline flex-1 sm:flex-none">Reset</a>
            </div>
        </div>
    </form>

    <div class="mt-8 flex flex-wrap items-end justify-between gap-2 sm:mt-10">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Inspirasi untuk ruangmu</p>
            <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Paket staging</h2>
        </div>
        <p class="text-xs text-ink/55 sm:text-sm">{{ $packages->total() }} paket ditemukan</p>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:mt-5 sm:grid-cols-3 sm:gap-5 xl:grid-cols-3">
        @forelse ($packages as $package)
            @include('packages._card', ['package' => $package])
        @empty
            <div class="card flex flex-col items-center px-5 py-10 text-center sm:col-span-2 xl:col-span-3 sm:py-14">
                <span class="grid h-14 w-14 place-items-center rounded-full bg-amber-50 text-2xl text-amber-800" aria-hidden="true">⌕</span>
                <h3 class="mt-4 font-serif text-xl">Paketnya belum ketemu</h3>
                <p class="mt-2 max-w-md text-sm leading-6 text-ink/60">Coba kata kunci atau filter lain. Bisa jadi paket yang cocok hanya berjarak satu pencarian.</p>
                <a href="{{ route('packages.index') }}" class="btn btn-outline mt-5">Lihat semua paket</a>
            </div>
        @endforelse
    </div>

    @if ($packages->hasPages())
        <div class="mt-7 flex justify-center sm:mt-9">{{ $packages->links() }}</div>
    @endif
@endsection
