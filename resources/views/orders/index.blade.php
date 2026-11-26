@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
    <div class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Aktivitas</p>
        <h1 class="page-heading mt-2">Pesanan Saya</h1>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Total pesanan</p><p class="mt-2 text-2xl font-semibold sm:text-3xl">{{ $stats['total'] }}</p></div>
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Sedang berjalan</p><p class="mt-2 text-2xl font-semibold sm:text-3xl">{{ $stats['active'] }}</p></div>
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Menunggu pembayaran</p><p class="mt-2 text-2xl font-semibold sm:text-3xl">{{ $stats['awaiting_payment'] }}</p></div>
        <div class="card p-4 sm:p-5"><p class="text-xs font-medium text-ink/55 sm:text-sm">Pesanan selesai</p><p class="mt-2 text-2xl font-semibold sm:text-3xl">{{ $stats['completed'] }}</p></div>
    </div>

    <section class="card mb-5 p-4 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="section-heading">Ringkasan pembayaran</h2>
                <p class="mt-1 text-sm text-ink/55">Pembayaran berhasil dalam 6 bulan terakhir</p>
            </div>
            <p class="text-sm text-ink/60">Total dibayar: <span class="font-semibold text-ink">Rp {{ number_format($stats['spent'], 0, ',', '.') }}</span></p>
        </div>
        <div class="mt-5 flex h-40 items-end gap-2 sm:gap-4" role="img" aria-label="Grafik pembayaran bulanan enam bulan terakhir">
            @foreach ($monthlySpending as $month => $amount)
                @php $barHeight = $amount > 0 ? max(3, (int) round($amount / $maxMonthlySpending * 100)) : 0; @endphp
                <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                    <span class="truncate text-[10px] text-ink/55 sm:text-xs">{{ $amount > 0 ? number_format($amount / 1000000, 1, ',', '.') . ' jt' : '—' }}</span>
                    <div class="flex h-28 w-full items-end rounded-lg bg-sand/70 px-1">
                        <div class="w-full rounded-md bg-amber-700 transition-all" style="height: {{ $barHeight }}%" title="Rp {{ number_format($amount, 0, ',', '.') }}"></div>
                    </div>
                    <span class="text-xs font-medium text-ink/60">{{ $month }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <form method="GET" action="{{ route('orders.index') }}" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label" for="customer-order-status">Filter status</label>
            <select class="input" id="customer-order-status" name="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Terapkan</button>
        @if ($status)
            <a href="{{ route('orders.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>

    <div class="space-y-3 sm:space-y-4">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="card flex flex-col gap-3 p-4 transition hover:-translate-y-0.5 hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="min-w-0">
                    <p class="text-xs text-ink/50">#{{ $order->code }}</p>
                    <p class="mt-1 break-words font-semibold">{{ $order->package->name }}</p>
                    <p class="mt-1 text-xs leading-5 text-ink/60">{{ $order->vendor->business_name }} <span class="mx-1 text-gold">·</span> {{ $order->start_date->format('d M Y') }} – {{ $order->end_date->format('d M Y') }}</p>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-ink/5 pt-3 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                    <span class="badge {{ $order->status_class }}">{{ $order->status_label }}</span>
                    @if ($order->disputes->isNotEmpty())
                        <span class="badge {{ $order->disputes->contains('status', 'open') ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $order->disputes->contains('status', 'open') ? 'Komplain ditinjau' : $order->disputes->count() . ' komplain selesai' }}
                        </span>
                    @endif
                    <p class="font-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
            </a>
        @empty
            <div class="card px-5 py-10 text-center sm:py-14">
                @if ($status)
                    <p class="font-medium">Tidak ada pesanan dengan status “{{ $statuses[$status] }}”.</p>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline mt-4">Reset filter</a>
                @else
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-amber-50 text-2xl text-amber-800" aria-hidden="true">▣</span>
                    <p class="mt-4 font-serif text-xl">Belum ada pesanan</p>
                    <a href="{{ route('packages.index') }}" class="btn btn-outline mt-4">Jelajahi paket staging <span class="ml-2" aria-hidden="true">→</span></a>
                @endif
            </div>
        @endforelse
    </div>
@endsection
