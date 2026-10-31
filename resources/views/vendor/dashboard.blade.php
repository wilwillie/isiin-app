@extends('layouts.app')

@section('title', 'Dashboard Vendor')

@section('content')
    <h1 class="font-serif text-3xl">Selamat datang, {{ $vendor->business_name }}</h1>

    @unless ($vendor->is_verified)
        <div class="mt-4 rounded-lg bg-amber-50 text-amber-800 px-4 py-3 text-sm">
            Akun vendor Anda belum diverifikasi admin. Paket Anda belum tampil di katalog publik.
        </div>
    @endunless

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-5"><p class="text-sm text-ink/60">Paket</p><p class="text-3xl font-semibold mt-1">{{ $stats['packages'] }}</p></div>
        <div class="card p-5"><p class="text-sm text-ink/60">Pesanan baru</p><p class="text-3xl font-semibold mt-1">{{ $stats['pending'] }}</p></div>
        <div class="card p-5"><p class="text-sm text-ink/60">Pesanan berjalan</p><p class="text-3xl font-semibold mt-1">{{ $stats['active'] }}</p></div>
        <div class="card p-5"><p class="text-sm text-ink/60">Pendapatan selesai</p><p class="text-2xl font-semibold mt-1">Rp {{ number_format($stats['payout'], 0, ',', '.') }}</p></div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('vendor.packages.create') }}" class="btn btn-primary">Tambah Paket</a>
        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline">Lihat Pesanan Masuk</a>
    </div>
@endsection
