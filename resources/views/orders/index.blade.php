@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
    <h1 class="font-serif text-3xl">Pesanan Saya</h1>

    <div class="mt-5 space-y-3">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="card p-4 flex flex-wrap items-center justify-between gap-3 hover:shadow-md transition">
                <div>
                    <p class="text-xs text-ink/50">#{{ $order->code }}</p>
                    <p class="font-semibold">{{ $order->package->name }}</p>
                    <p class="text-xs text-ink/60">{{ $order->vendor->business_name }} · {{ $order->start_date->format('d M Y') }} - {{ $order->end_date->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="badge {{ $order->status_class }}">{{ $order->status_label }}</span>
                    <p class="font-semibold mt-1">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
            </a>
        @empty
            <div class="card p-6 text-center text-ink/60">
                Belum ada pesanan. <a href="{{ route('packages.index') }}" class="underline">Lihat paket staging</a>
            </div>
        @endforelse
    </div>
@endsection
