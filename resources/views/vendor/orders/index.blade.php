@extends('layouts.app')

@section('title', 'Pesanan Masuk')

@section('content')
    <h1 class="font-serif text-3xl">Pesanan Masuk</h1>

    <div class="mt-5 space-y-4">
        @forelse ($orders as $order)
            <div class="card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-ink/50">#{{ $order->code }}</p>
                        <p class="font-semibold">{{ $order->package->name }}</p>
                        <p class="text-xs text-ink/60">{{ $order->customer->name }}@if ($order->customer->phone) · {{ $order->customer->phone }}@endif</p>
                    </div>
                    <span class="badge {{ $order->status_class }}">{{ $order->status_label }}</span>
                </div>

                <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                    <p><span class="text-ink/60">Periode:</span> {{ $order->start_date->format('d M Y') }} - {{ $order->end_date->format('d M Y') }}</p>
                    <p><span class="text-ink/60">Alamat:</span> {{ $order->property_address }}</p>
                    @if ($order->notes)
                        <p class="sm:col-span-2"><span class="text-ink/60">Catatan:</span> {{ $order->notes }}</p>
                    @endif
                    <p><span class="text-ink/60">Nilai sewa:</span> Rp {{ number_format($order->rental_fee, 0, ',', '.') }}</p>
                    <p><span class="text-ink/60">Komisi Isiin ({{ round(config('isiin.commission_rate') * 100) }}%):</span> Rp {{ number_format($order->commission, 0, ',', '.') }}</p>
                    <p class="sm:col-span-2"><span class="text-ink/60">Pendapatan vendor:</span> <strong>Rp {{ number_format($order->vendor_payout, 0, ',', '.') }}</strong></p>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($order->status === 'pending')
                        <form method="POST" action="{{ route('vendor.orders.accept', $order) }}">@csrf<button class="btn btn-primary" type="submit">Terima</button></form>
                        <form method="POST" action="{{ route('vendor.orders.reject', $order) }}" onsubmit="return confirm('Tolak pesanan ini?')">@csrf<button class="btn btn-outline" type="submit">Tolak</button></form>
                    @elseif ($order->status === 'paid')
                        <form method="POST" action="{{ route('vendor.orders.installed', $order) }}">@csrf<button class="btn btn-primary" type="submit">Tandai sudah terpasang</button></form>
                    @elseif ($order->status === 'installed')
                        <form method="POST" action="{{ route('vendor.orders.complete', $order) }}">@csrf<button class="btn btn-primary" type="submit">Tandai sudah diambil (selesai)</button></form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card p-6 text-center text-ink/60">Belum ada pesanan masuk.</div>
        @endforelse
    </div>
@endsection
