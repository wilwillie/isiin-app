@extends('layouts.app')

@section('title', 'Dashboard Komisi')

@section('content')
    <div class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Administrasi</p>
        <h1 class="page-heading mt-2">Dashboard Komisi</h1>
        <p class="page-subtitle">Pantau komisi dari transaksi yang sudah selesai. Tarif komisi saat ini {{ number_format($stats['commission_rate'] * 100, 0, ',', '.') }}% dari biaya sewa, pengiriman, dan pemasangan.</p>
    </div>

    <section class="grid gap-3 sm:grid-cols-3 sm:gap-4" aria-label="Ringkasan komisi">
        <div class="card p-5 sm:p-6">
            <p class="text-sm text-ink/55">Total komisi terkumpul</p>
            <p class="mt-2 break-words text-2xl font-bold sm:text-3xl">Rp {{ number_format($stats['total_commission'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-ink/45">Seluruh transaksi selesai</p>
        </div>
        <div class="card p-5 sm:p-6">
            <p class="text-sm text-ink/55">Komisi bulan ini</p>
            <p class="mt-2 break-words text-2xl font-bold sm:text-3xl">Rp {{ number_format($stats['month_commission'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-ink/45">{{ now(config('isiin.display_timezone'))->translatedFormat('F Y') }}</p>
        </div>
        <div class="card p-5 sm:p-6">
            <p class="text-sm text-ink/55">Transaksi selesai</p>
            <p class="mt-2 text-2xl font-bold sm:text-3xl">{{ number_format($stats['completed_count'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-ink/45">Seluruh waktu</p>
        </div>
    </section>

    <a href="{{ route('admin.disputes.index') }}" class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm transition hover:bg-amber-100 sm:px-5">
        <span><strong>Komplain dan sengketa</strong> · {{ $stats['open_disputes'] }} kasus menunggu peninjauan admin</span>
        <span class="font-semibold text-amber-900">Tinjau kasus <span aria-hidden="true">→</span></span>
    </a>

    <section class="card mt-5 p-4 sm:mt-6 sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-amber-700">30 hari terakhir</p>
                <h2 class="section-heading mt-1">Tren Komisi Harian</h2>
            </div>
            <p class="text-xs text-ink/50">Komisi dihitung dari transaksi yang selesai setiap hari.</p>
        </div>

        <div class="mt-5 overflow-x-auto">
            <svg viewBox="0 0 840 250" role="img" aria-labelledby="commission-chart-title commission-chart-description" class="h-56 min-w-[42rem] w-full">
                <title id="commission-chart-title">Tren komisi harian selama 30 hari terakhir</title>
                <desc id="commission-chart-description">Grafik garis jumlah komisi transaksi selesai per hari.</desc>
                <defs>
                    <linearGradient id="commission-area" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#b7791f" stop-opacity=".24"></stop>
                        <stop offset="100%" stop-color="#b7791f" stop-opacity=".02"></stop>
                    </linearGradient>
                </defs>
                @foreach ([0, 1, 2, 3, 4] as $tick)
                    @php
                        $gridY = 205 - ($tick * 41);
                        $tickValue = (int) round($chartMax * ($tick / 4));
                    @endphp
                    <line x1="45" y1="{{ $gridY }}" x2="805" y2="{{ $gridY }}" stroke="#e8e2d8" stroke-width="1"></line>
                    <text x="39" y="{{ $gridY - 5 }}" text-anchor="end" fill="#7b756c" font-size="10">Rp {{ number_format($tickValue, 0, ',', '.') }}</text>
                @endforeach
                <polygon points="{{ $chartAreaPoints }}" fill="url(#commission-area)"></polygon>
                <polyline points="{{ $chartPoints }}" fill="none" stroke="#a66b19" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
                @foreach ($chart as $index => $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#a66b19" data-commission="{{ $point['commission'] }}">
                        <title>{{ \Illuminate\Support\Carbon::parse($point['date'])->translatedFormat('d M Y') }}: Rp {{ number_format($point['commission'], 0, ',', '.') }}</title>
                    </circle>
                    @if ($index % 5 === 0 || $index === 29)
                        <text x="{{ $point['x'] }}" y="230" text-anchor="middle" fill="#7b756c" font-size="10">{{ $point['label'] }}</text>
                    @endif
                @endforeach
            </svg>
        </div>
    </section>

    <section class="mt-5 sm:mt-6">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-amber-700">Riwayat transaksi</p>
                <h2 class="section-heading mt-1">Transaksi Selesai</h2>
            </div>
            <p class="text-xs text-ink/50">Komisi per transaksi = {{ number_format($stats['commission_rate'] * 100, 0, ',', '.') }}% dari biaya layanan.</p>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[58rem] text-left text-sm">
                    <thead class="bg-[#faf8f3] text-ink/60">
                        <tr>
                            <th class="p-3">Selesai</th>
                            <th class="p-3">Kode pesanan</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Vendor</th>
                            <th class="p-3">Paket</th>
                            <th class="p-3 text-right">Nilai transaksi</th>
                            <th class="p-3 text-right">Komisi Isiin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($completedOrders as $order)
                            <tr class="border-t border-ink/5 transition hover:bg-[#fcfbf8]">
                                <td class="whitespace-nowrap p-3">{{ ($order->completed_at ?? $order->updated_at)->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</td>
                                <td class="whitespace-nowrap p-3 font-medium">{{ $order->code }}</td>
                                <td class="p-3">{{ $order->customer->name ?? 'Akun dihapus' }}</td>
                                <td class="p-3">{{ $order->vendor->business_name ?? 'Vendor dihapus' }}</td>
                                <td class="p-3">{{ $order->package->name ?? 'Paket dihapus' }}</td>
                                <td class="whitespace-nowrap p-3 text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap p-3 text-right font-semibold text-emerald-800">Rp {{ number_format($order->commission, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-6 text-center text-ink/55" colspan="7">Belum ada transaksi selesai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($completedOrders->hasPages())
                <div class="border-t border-ink/5 px-4 py-3">
                    {{ $completedOrders->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
