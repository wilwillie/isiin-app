@extends('layouts.app')

@section('title', 'Dashboard Vendor')

@section('content')
    <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-[#172329] via-ink to-[#344037] px-5 py-7 text-white shadow-lg shadow-ink/10 sm:px-8 sm:py-9">
        <div class="pointer-events-none absolute -right-10 -top-20 -z-10 h-56 w-56 rounded-full border border-gold/20"></div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold">Ruang kerja vendor</p>
        <h1 class="mt-2 break-words font-serif text-3xl leading-tight sm:text-4xl">Halo, {{ $vendor->business_name }}</h1>
        <p class="mt-2 max-w-xl text-sm leading-6 text-white/65">Ringkasan aktivitas tokomu di Isiin.</p>
    </section>

    @unless ($vendor->is_verified)
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm leading-6 text-amber-800">
            Akun vendor Anda belum diverifikasi admin. Paket Anda belum tampil di katalog publik.
        </div>
    @endunless

    <div class="mt-5 grid grid-cols-2 gap-3 sm:mt-6 sm:gap-4 xl:grid-cols-5">
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Paket</p><p class="mt-2 text-3xl font-semibold">{{ $stats['packages'] }}</p></div>
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Pesanan perlu ditindaklanjuti</p><p class="mt-2 text-3xl font-semibold">{{ $stats['pending'] }}</p></div>
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Pesanan berjalan</p><p class="mt-2 text-3xl font-semibold">{{ $stats['active'] }}</p></div>
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Menunggu pengambilan</p><p class="mt-2 text-3xl font-semibold">{{ $stats['pickup'] }}</p></div>
        <div class="card col-span-2 p-4 sm:col-span-1 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Pendapatan selesai</p><p class="mt-2 break-words text-xl font-semibold sm:text-2xl">Rp {{ number_format($stats['payout'], 0, ',', '.') }}</p></div>
    </div>

    <section class="mt-5 grid gap-4 lg:grid-cols-5">
        <div class="card p-4 sm:p-6 lg:col-span-3">
            <div>
                <h2 class="section-heading">Pendapatan per bulan</h2>
                <p class="mt-1 text-sm text-ink/55">Pendapatan bersih dari pesanan selesai · 6 bulan terakhir</p>
            </div>
            <div class="mt-5 flex h-40 items-end gap-2 sm:gap-4" role="img" aria-label="Grafik pendapatan bulanan enam bulan terakhir">
                @foreach ($monthlyPayout as $month => $amount)
                    @php $barHeight = $amount > 0 ? max(3, (int) round($amount / $maxMonthlyPayout * 100)) : 0; @endphp
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <span class="truncate text-[10px] text-ink/55 sm:text-xs">{{ $amount > 0 ? number_format($amount / 1000000, 1, ',', '.') . ' jt' : '—' }}</span>
                        <div class="flex h-28 w-full items-end rounded-lg bg-sand/70 px-1">
                            <div class="w-full rounded-md bg-emerald-700 transition-all" style="height: {{ $barHeight }}%" title="Rp {{ number_format($amount, 0, ',', '.') }}"></div>
                        </div>
                        <span class="text-xs font-medium text-ink/60">{{ $month }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-4 sm:p-6 lg:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <h2 class="section-heading">Pesanan terbaru</h2>
                <a href="{{ route('vendor.orders.index') }}" class="text-sm font-semibold text-amber-800 hover:underline">Semua</a>
            </div>
            <div class="mt-4 divide-y divide-ink/5">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('vendor.orders.index', ['status' => $order->status]) }}" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $order->package->name }}</p>
                            <p class="mt-1 truncate text-xs text-ink/55">{{ $order->customer->name }} · {{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="badge shrink-0 {{ $order->status_class }}">{{ $order->status_label }}</span>
                    </a>
                @empty
                    <p class="py-5 text-sm text-ink/55">Belum ada aktivitas pesanan.</p>
                @endforelse
            </div>
        </div>
    </section>

    <div class="mt-5 grid gap-2 sm:mt-6 sm:flex sm:flex-wrap sm:gap-3">
        <a href="{{ route('vendor.portfolio.manage') }}" class="btn btn-primary w-full sm:w-auto">Kelola Profil Vendor</a>
        @if ($vendor->is_verified)
            <a href="{{ route('vendors.profile.show', $vendor) }}" class="btn btn-outline w-full sm:w-auto">Lihat Profil Publik</a>
        @endif
        <a href="{{ route('vendor.packages.create') }}" class="btn btn-primary w-full sm:w-auto">Tambah Paket <span class="ml-2" aria-hidden="true">+</span></a>
        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline w-full sm:w-auto">Lihat Pesanan Masuk</a>
    </div>
@endsection
