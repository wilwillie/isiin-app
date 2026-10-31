@extends('layouts.app')

@section('title', 'Home Staging Made Easy')

@section('content')
    <section class="rounded-2xl bg-ink text-white px-6 py-14 md:px-12 md:py-20">
        <p class="text-gold text-sm font-semibold tracking-widest uppercase">Home Staging Made Easy</p>
        <h1 class="font-serif text-4xl md:text-5xl mt-3 max-w-2xl leading-tight">Bikin properti kosong siap laku, tanpa perlu beli furniture.</h1>
        <p class="mt-4 text-white/70 max-w-xl">Sewa paket staging dari vendor terverifikasi. Harga transparan, booking, dan penjadwalan pasang-ambil dalam satu tempat.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('packages.index') }}" class="btn btn-gold">Lihat Paket Staging</a>
            <a href="{{ route('register', ['role' => 'vendor']) }}" class="btn border border-white/30 text-white hover:bg-white/10">Daftar sebagai Vendor</a>
        </div>
    </section>

    <section class="mt-12">
        <h2 class="font-serif text-2xl">Cara kerja</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div class="card p-5">
                <div class="text-gold font-serif text-3xl">1</div>
                <h3 class="font-semibold mt-1">Pilih paket</h3>
                <p class="text-sm text-ink/70 mt-1">Telusuri paket staging sesuai tipe ruangan, gaya, dan budget.</p>
            </div>
            <div class="card p-5">
                <div class="text-gold font-serif text-3xl">2</div>
                <h3 class="font-semibold mt-1">Booking dan bayar</h3>
                <p class="text-sm text-ink/70 mt-1">Pilih tanggal sewa, lihat rincian biaya, lalu bayar setelah vendor mengonfirmasi.</p>
            </div>
            <div class="card p-5">
                <div class="text-gold font-serif text-3xl">3</div>
                <h3 class="font-semibold mt-1">Dipasang dan diambil</h3>
                <p class="text-sm text-ink/70 mt-1">Vendor memasang furniture, dan mengambilnya kembali saat masa sewa selesai.</p>
            </div>
        </div>
    </section>

    <section class="mt-12 grid gap-4 md:grid-cols-2">
        <div class="card p-6">
            <h3 class="font-serif text-xl">Pemilik properti</h3>
            <p class="text-sm text-ink/70 mt-2">Percantik unit kosong tanpa modal besar untuk membeli furniture.</p>
            <a href="{{ route('register') }}" class="btn btn-primary mt-4">Daftar sebagai Customer</a>
        </div>
        <div class="card p-6">
            <h3 class="font-serif text-xl">Vendor furniture</h3>
            <p class="text-sm text-ink/70 mt-2">Dapatkan pelanggan baru tanpa promosi sendiri. Kelola paket, harga, dan pesanan di satu dashboard.</p>
            <a href="{{ route('register', ['role' => 'vendor']) }}" class="btn btn-outline mt-4">Daftar sebagai Vendor</a>
        </div>
    </section>
@endsection
