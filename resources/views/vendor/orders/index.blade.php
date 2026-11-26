@extends('layouts.app')

@section('title', 'Pesanan Masuk')

@section('content')
    <div class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Aktivitas toko</p>
        <h1 class="page-heading mt-2">Pesanan Masuk</h1>
    </div>
    @include('partials.errors')

    <form method="GET" action="{{ route('vendor.orders.index') }}" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label" for="vendor-order-status">Filter status</label>
            <select class="input" id="vendor-order-status" name="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Terapkan</button>
        @if ($status)
            <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>

    <div class="space-y-4 sm:space-y-5">
        @forelse ($orders as $order)
            <div class="card p-4 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-ink/5 pb-4">
                    <div class="min-w-0">
                        <p class="text-xs text-ink/50">#{{ $order->code }}</p>
                        <p class="mt-1 break-words font-semibold">{{ $order->package->name }}</p>
                        <p class="text-xs text-ink/60">{{ $order->customer->name }}@if ($order->customer->phone) · {{ $order->customer->phone }}@endif</p>
                    </div>
                    <span class="badge {{ $order->status_class }}">{{ $order->status_label }}</span>
                </div>

                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <p class="leading-6"><span class="text-ink/60">Periode:</span> {{ $order->start_date->format('d M Y') }} – {{ $order->end_date->format('d M Y') }}</p>
                    <p class="break-words leading-6"><span class="text-ink/60">Alamat:</span> {{ $order->property_address }}</p>
                    @if ($order->room_length && $order->room_width)
                        <p class="leading-6"><span class="text-ink/60">Ukuran ruangan:</span> {{ number_format($order->room_length, 2, ',', '.') }} × {{ number_format($order->room_width, 2, ',', '.') }} m ({{ number_format($order->room_length * $order->room_width, 2, ',', '.') }} m²)</p>
                    @endif
                    @if ($order->ceiling_height)
                        <p class="leading-6"><span class="text-ink/60">Tinggi plafon:</span> {{ number_format($order->ceiling_height, 2, ',', '.') }} m</p>
                    @endif
                    @if ($order->package->minimum_room_length && $order->package->minimum_room_width)
                        <p class="leading-6"><span class="text-ink/60">Kebutuhan minimum paket:</span> {{ number_format($order->package->minimum_room_length, 2, ',', '.') }} × {{ number_format($order->package->minimum_room_width, 2, ',', '.') }} m</p>
                    @endif
                    @if ($order->notes)
                        <p class="break-words sm:col-span-2"><span class="text-ink/60">Catatan akses/kondisi:</span> {{ $order->notes }}</p>
                    @endif
                    @if ($order->customer_response)
                        <p class="whitespace-pre-line rounded-xl bg-sky-50 px-3 py-2 sm:col-span-2"><span class="font-semibold text-sky-900">Respons pelanggan:</span> {{ $order->customer_response }}</p>
                    @endif
                    <p><span class="text-ink/60">Nilai sewa:</span> Rp {{ number_format($order->rental_fee, 0, ',', '.') }}</p>
                    <p><span class="text-ink/60">Biaya pengiriman:</span> Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</p>
                    <p><span class="text-ink/60">Biaya pemasangan:</span> Rp {{ number_format($order->installation_fee, 0, ',', '.') }}</p>
                    <p><span class="text-ink/60">Komisi Isiin ({{ round(config('isiin.commission_rate') * 100) }}% dari total biaya layanan):</span> Rp {{ number_format($order->commission, 0, ',', '.') }}</p>
                    <p class="rounded-xl bg-emerald-50 px-3 py-2 sm:col-span-2"><span class="text-emerald-800/70">Pendapatan vendor:</span> <strong class="text-emerald-900">Rp {{ number_format($order->vendor_payout, 0, ',', '.') }}</strong></p>
                </div>

                @if (in_array($order->status, ['pending', 'needs_info'], true) && $order->room_photos)
                    <div class="mt-4">
                        <p class="text-sm font-semibold">Foto ruangan</p>
                        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($order->room_photos as $photoIndex => $photo)
                                <img src="{{ route('orders.photos.show', [$order, $photoIndex]) }}" alt="Foto ruangan {{ $photoIndex + 1 }}" class="aspect-square w-full rounded-xl object-cover">
                            @endforeach
                        </div>
                    </div>
                @endif

                @include('disputes._order-panel', ['order' => $order, 'disputes' => $order->disputes, 'isVendor' => true])

                <div class="mt-4 grid gap-2 border-t border-ink/5 pt-4 sm:flex sm:flex-wrap">
                    @if ($order->status === 'pending')
                        <form method="POST" action="{{ route('vendor.orders.accept', $order) }}">@csrf<button class="btn btn-primary w-full sm:w-auto" type="submit">Terima</button></form>
                        <form method="POST" action="{{ route('vendor.orders.reject', $order) }}" onsubmit="return confirm('Tolak pesanan ini?')">@csrf<button class="btn btn-outline w-full sm:w-auto" type="submit">Tolak</button></form>
                        <form method="POST" action="{{ route('vendor.orders.request-info', $order) }}" class="w-full space-y-2 sm:basis-full">
                            @csrf
                            <label class="label" for="vendor-request-note-{{ $order->id }}">Minta informasi tambahan atau jadwalkan survei</label>
                            <textarea class="input" id="vendor-request-note-{{ $order->id }}" name="vendor_request_note" rows="2" maxlength="2000" required placeholder="Contoh: Mohon kirim foto area dari sisi pintu, atau jadwalkan survei lokasi.">{{ old('vendor_request_note') }}</textarea>
                            <button class="btn btn-outline w-full sm:w-auto" type="submit">Minta Info / Survei</button>
                        </form>
                    @elseif ($order->disputes->contains('status', 'open'))
                        <p class="text-sm leading-6 text-amber-800">Perubahan status ditangguhkan selama admin meninjau komplain.</p>
                    @elseif ($order->status === 'paid')
                        <form method="POST" action="{{ route('vendor.orders.installed', $order) }}">@csrf<button class="btn btn-primary w-full sm:w-auto" type="submit">Tandai sudah terpasang</button></form>
                    @elseif ($order->status === 'installed')
                        <p class="text-sm leading-6 text-emerald-800">Masa sewa berlangsung hingga {{ $order->end_date->format('d M Y') }}. Pengambilan dapat diminta customer lebih awal atau otomatis tersedia sehari setelah masa sewa berakhir.</p>
                    @elseif ($order->status === 'pickup_pending')
                        <form method="POST" action="{{ route('vendor.orders.complete', $order) }}" onsubmit="return confirm('Konfirmasi furniture sudah benar-benar diambil dari properti? Pesanan akan ditandai selesai.')">@csrf<button class="btn btn-primary w-full sm:w-auto" type="submit">Tandai sudah diambil (selesai)</button></form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card px-5 py-10 text-center text-ink/60 sm:py-14">
                @if ($status)
                    <p>Tidak ada pesanan dengan status “{{ $statuses[$status] }}”.</p>
                    <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline mt-4">Reset filter</a>
                @else
                    Belum ada pesanan masuk.
                @endif
            </div>
        @endforelse
    </div>
@endsection
