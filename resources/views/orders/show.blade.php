@extends('layouts.app')

@section('title', 'Pesanan ' . $order->code)

@section('content')
    <a href="{{ route('orders.index') }}" class="text-sm text-ink/60 hover:underline">← Semua pesanan</a>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs text-ink/50">#{{ $order->code }}</p>
            <h1 class="font-serif text-3xl">{{ $order->package->name }}</h1>
            <p class="text-sm text-ink/60">oleh {{ $order->vendor->business_name }}</p>
        </div>
        <span class="badge {{ $order->status_class }}">{{ $order->status_label }}</span>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-5">
                <h2 class="font-semibold">Status pesanan</h2>
                @if (in_array($order->status, ['rejected', 'cancelled'], true))
                    <p class="text-sm text-rose-700 mt-2">Pesanan ini {{ strtolower($order->status_label) }}.</p>
                @else
                    <ol class="mt-3 space-y-3">
                        @foreach ($order->timeline() as $step)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="h-5 w-5 rounded-full flex items-center justify-center text-xs {{ $step['done'] ? 'bg-emerald-500 text-white' : 'bg-ink/10 text-ink/40' }}">{{ $step['done'] ? '✓' : '' }}</span>
                                <span class="{{ $step['done'] ? 'font-medium' : 'text-ink/50' }}">{{ $step['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="card p-5 text-sm space-y-2">
                <h2 class="font-semibold">Detail</h2>
                <p><span class="text-ink/60">Periode sewa:</span> {{ $order->start_date->format('d M Y') }} - {{ $order->end_date->format('d M Y') }}</p>
                <p><span class="text-ink/60">Alamat properti:</span> {{ $order->property_address }}</p>
                @if ($order->notes)
                    <p><span class="text-ink/60">Catatan:</span> {{ $order->notes }}</p>
                @endif
                <p class="text-ink/60">Isi paket:</p>
                <ul class="list-disc pl-5">
                    @foreach ($order->package->items as $item)
                        <li>{{ $item->name }} (x{{ $item->quantity }})</li>
                    @endforeach
                </ul>
            </div>

            @if ($order->status === 'completed')
                <div class="card p-5">
                    <h2 class="font-semibold">Ulasan</h2>
                    @if ($order->review)
                        <p class="text-amber-600 mt-2">{{ str_repeat('★', $order->review->rating) }}{{ str_repeat('☆', 5 - $order->review->rating) }}</p>
                        @if ($order->review->comment)
                            <p class="text-sm mt-1">{{ $order->review->comment }}</p>
                        @endif
                    @else
                        <form method="POST" action="{{ route('reviews.store', $order) }}" class="mt-3 space-y-3">
                            @csrf
                            @include('partials.errors')
                            <div>
                                <label class="label" for="rating">Rating</label>
                                <select class="input w-32" id="rating" name="rating" required>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ $i }} ★</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="label" for="comment">Komentar</label>
                                <textarea class="input" id="comment" name="comment" rows="3" placeholder="Bagaimana pengalaman Anda?">{{ old('comment') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="card p-5 text-sm">
                <h2 class="font-semibold mb-2">Rincian biaya</h2>
                <div class="space-y-1">
                    <div class="flex justify-between"><span>Biaya sewa</span><span>Rp {{ number_format($order->rental_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span>Pengiriman</span><span>Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span>Pemasangan</span><span>Rp {{ number_format($order->installation_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span>Deposit (dapat dikembalikan)</span><span>Rp {{ number_format($order->deposit, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between border-t pt-2 mt-2 font-semibold"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
                </div>
            </div>

            @if ($order->status === 'confirmed')
                <form method="POST" action="{{ route('orders.pay', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">Bayar Sekarang (simulasi)</button>
                </form>
            @endif

            @if (in_array($order->status, ['pending', 'confirmed'], true))
                <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                    @csrf
                    <button type="submit" class="btn btn-outline w-full">Batalkan Pesanan</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
