@extends('layouts.app')

@section('title', 'Cari Vendor')

@section('content')
    <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-[#172329] via-ink to-[#344037] px-5 py-7 text-white shadow-lg shadow-ink/10 sm:px-8 sm:py-9">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold">Vendor terpercaya</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight sm:text-4xl">Cari profil vendor</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-white/70">Temukan penyedia home staging berdasarkan nama, lokasi, dan rating dari pesanan yang telah selesai.</p>
    </section>

    <form method="GET" action="{{ route('vendors.index') }}" class="card mt-5 p-4 sm:p-5">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-12">
            <label class="block sm:col-span-2 xl:col-span-4">
                <span class="label">Nama vendor</span>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama vendor">
            </label>
            <label class="block xl:col-span-3">
                <span class="label">Kota</span>
                <input class="input" type="search" name="city" value="{{ request('city') }}" placeholder="Semua kota">
            </label>
            <label class="block xl:col-span-2">
                <span class="label">Rating minimum</span>
                <select class="input" name="min_rating">
                    <option value="">Semua rating</option>
                    @foreach ([3, 4, 4.5] as $rating)
                        <option value="{{ $rating }}" @selected((string) request('min_rating') === (string) $rating)>★ {{ number_format($rating, 1, ',', '.') }} ke atas</option>
                    @endforeach
                </select>
            </label>
            <label class="block xl:col-span-3">
                <span class="label">Urutkan berdasarkan</span>
                <select class="input" name="sort">
                    <option value="rating" @selected(request('sort', 'rating') === 'rating')>Rating tertinggi</option>
                    <option value="reviews" @selected(request('sort') === 'reviews')>Ulasan terbanyak</option>
                    <option value="name" @selected(request('sort') === 'name')>Nama A–Z</option>
                </select>
            </label>
            <div class="flex gap-2 pt-1 sm:col-span-2 sm:justify-end xl:col-span-12">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Cari vendor <span class="ml-2" aria-hidden="true">→</span></button>
                <a href="{{ route('vendors.index') }}" class="btn btn-outline flex-1 sm:flex-none">Reset</a>
            </div>
        </div>
    </form>

    <div class="mt-8 flex flex-wrap items-end justify-between gap-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Rekomendasi layanan</p>
            <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Vendor terverifikasi</h2>
        </div>
        <p class="text-sm text-ink/55">{{ $vendors->total() }} vendor ditemukan</p>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($vendors as $vendor)
            <a href="{{ route('vendors.profile.show', $vendor) }}" class="card group flex h-full flex-col p-5 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="break-words font-serif text-xl group-hover:text-amber-800">{{ $vendor->business_name }}</h3>
                        <p class="mt-1 text-sm text-ink/55">{{ $vendor->city ?: 'Lokasi belum dicantumkan' }}</p>
                    </div>
                    @if ($vendor->completed_order_reviews_count > 0)
                        <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700">★ {{ \App\Support\RatingFormatter::formatAverage($vendor->completed_order_reviews_avg_rating) }}</span>
                    @else
                        <span class="shrink-0 rounded-full bg-sand px-3 py-1.5 text-xs font-medium text-ink/55">Belum ada rating</span>
                    @endif
                </div>
                @if ($vendor->description)
                    <p class="mt-4 line-clamp-3 text-sm leading-6 text-ink/65">{{ $vendor->description }}</p>
                @endif
                <div class="mt-auto flex flex-wrap gap-2 pt-5 text-xs text-ink/60">
                    @if ($vendor->completed_order_reviews_count > 0)
                        <span class="rounded-full bg-amber-50 px-3 py-1.5">{{ $vendor->completed_order_reviews_count }} ulasan pesanan selesai</span>
                    @endif
                </div>
                <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-amber-800">Lihat profil <span aria-hidden="true">→</span></span>
            </a>
        @empty
            <div class="card px-5 py-10 text-center sm:col-span-2 xl:col-span-3">
                <h3 class="font-serif text-xl">Vendor belum ditemukan</h3>
                <p class="mt-2 text-sm text-ink/60">Coba ubah nama, kota, atau filter rating.</p>
                <a href="{{ route('vendors.index') }}" class="btn btn-outline mt-5">Tampilkan semua vendor</a>
            </div>
        @endforelse
    </div>

    @if ($vendors->hasPages())
        <div class="mt-7 flex justify-center">{{ $vendors->links() }}</div>
    @endif
@endsection
