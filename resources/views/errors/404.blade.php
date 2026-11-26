@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    <section class="mx-auto max-w-2xl rounded-3xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-ink/5 sm:px-10 sm:py-16">
        <p class="font-serif text-6xl font-bold text-gold">404</p>
        <h1 class="mt-4 font-serif text-3xl sm:text-4xl">Halaman tidak ditemukan</h1>
        <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-ink/60 sm:text-base">Maaf, halaman yang kamu cari mungkin sudah dipindahkan atau alamatnya tidak tersedia.</p>
        <div class="mt-7 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke beranda</a>
            <a href="{{ route('packages.index') }}" class="btn btn-outline">Jelajahi paket</a>
        </div>
    </section>
@endsection
