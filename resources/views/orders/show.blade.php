@extends('layouts.app')

@section('title', 'Pesanan ' . $order->code)

@section('content')
    <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-ink/65 shadow-sm ring-1 ring-ink/5 transition hover:bg-white hover:text-ink">← Semua pesanan</a>

    <div class="mt-5 flex flex-wrap items-end justify-between gap-3 sm:mt-7">
        <div>
            <p class="text-xs text-ink/50">#{{ $order->code }}</p>
            <h1 class="page-heading mt-1 break-words">{{ $order->package->name }}</h1>
            <p class="mt-2 text-sm text-ink/60">oleh {{ $order->vendor->business_name }}</p>
        </div>
        <span class="badge {{ $order->status_class }}">{{ $order->status_label }}</span>
    </div>

    <div class="mt-5 grid items-start gap-4 sm:mt-7 sm:gap-6 lg:grid-cols-12">
        <div class="space-y-4 sm:space-y-6 lg:col-span-8">
            <div class="card p-5 sm:p-6">
                <h2 class="section-heading">Status pesanan</h2>
                @if (in_array($order->status, ['rejected', 'cancelled'], true))
                    <p class="text-sm text-rose-700 mt-2">Pesanan ini {{ strtolower($order->status_label) }}.</p>
                @else
                    <ol class="mt-5 space-y-4">
                        @foreach ($order->timeline() as $step)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs {{ $step['done'] ? 'bg-emerald-500 text-white' : 'bg-ink/10 text-ink/40' }}">{{ $step['done'] ? '✓' : '' }}</span>
                                <span class="{{ $step['done'] ? 'font-medium' : 'text-ink/50' }}">{{ $step['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="card space-y-3 p-5 text-sm sm:p-6">
                <h2 class="section-heading mb-4">Detail pesanan</h2>
                <p class="break-words leading-6"><span class="text-ink/60">Periode sewa:</span> {{ $order->start_date->format('d M Y') }} – {{ $order->end_date->format('d M Y') }}</p>
                <p class="break-words leading-6"><span class="text-ink/60">Alamat properti:</span> {{ $order->property_address }}</p>
                @if ($order->room_length && $order->room_width)
                    <p class="break-words leading-6"><span class="text-ink/60">Ukuran ruangan:</span> {{ number_format($order->room_length, 2, ',', '.') }} × {{ number_format($order->room_width, 2, ',', '.') }} m ({{ number_format($order->room_length * $order->room_width, 2, ',', '.') }} m²)</p>
                @endif
                @if ($order->ceiling_height)
                    <p class="break-words leading-6"><span class="text-ink/60">Tinggi plafon:</span> {{ number_format($order->ceiling_height, 2, ',', '.') }} m</p>
                @endif
                @if ($order->notes)
                    <p class="break-words leading-6"><span class="text-ink/60">Catatan akses/kondisi:</span> {{ $order->notes }}</p>
                @endif
                <p class="pt-1 text-ink/60">Isi paket:</p>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($order->package->items as $item)
                        <li>{{ $item->name }} (x{{ $item->quantity }})</li>
                    @endforeach
                </ul>
            </div>

            @if (in_array($order->status, ['pending', 'needs_info'], true) && $order->room_photos)
                <div class="card p-5 sm:p-6">
                    <h2 class="section-heading">Foto ruangan</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($order->room_photos as $photoIndex => $photo)
                            <img src="{{ route('orders.photos.show', [$order, $photoIndex]) }}" alt="Foto ruangan {{ $photoIndex + 1 }}" class="aspect-square w-full rounded-xl object-cover">
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($order->vendor_request_note)
                <div class="card border border-amber-200 bg-amber-50 p-5 sm:p-6">
                    <h2 class="section-heading">Permintaan dari vendor</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $order->vendor_request_note }}</p>
                    @if ($order->customer_response)
                        <p class="mt-4 border-t border-amber-200 pt-3 text-sm leading-6"><span class="font-semibold">Respons terakhir Anda:</span> {{ $order->customer_response }}</p>
                    @endif
                </div>
            @endif

            @if ($order->status === 'needs_info')
                <form method="POST" enctype="multipart/form-data" action="{{ route('orders.respond', $order) }}" class="card space-y-4 p-5 sm:p-6">
                    @csrf
                    @include('partials.errors')
                    <h2 class="section-heading">Kirim informasi ke vendor</h2>
                    <p class="text-sm leading-6 text-ink/60">Perbarui ukuran jika diperlukan. Jika Anda mengunggah foto baru, foto ruangan sebelumnya akan diganti.</p>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="label" for="response-room-length">Panjang (meter)</label>
                            <input class="input" type="number" id="response-room-length" name="room_length" min="0.1" max="1000" step="0.01" value="{{ old('room_length', $order->room_length) }}" required>
                        </div>
                        <div>
                            <label class="label" for="response-room-width">Lebar (meter)</label>
                            <input class="input" type="number" id="response-room-width" name="room_width" min="0.1" max="1000" step="0.01" value="{{ old('room_width', $order->room_width) }}" required>
                        </div>
                        <div>
                            <label class="label" for="response-ceiling-height">Tinggi plafon (meter, opsional)</label>
                            <input class="input" type="number" id="response-ceiling-height" name="ceiling_height" min="0.1" max="100" step="0.01" value="{{ old('ceiling_height', $order->ceiling_height) }}">
                        </div>
                    </div>
                    <div>
                        <label class="label" for="customer_response">Jawaban / informasi tambahan</label>
                        <textarea class="input" id="customer_response" name="customer_response" rows="3" required>{{ old('customer_response') }}</textarea>
                    </div>
                    <div>
                        <label class="label" for="response-room-photos">Foto pengganti (opsional, maks. 4 foto, JPG/PNG/WEBP, 5 MB per foto)</label>
                        <input class="input" type="file" id="response-room-photos" name="room_photos[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                    </div>
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">Kirim ke Vendor</button>
                </form>
            @endif

            @include('disputes._order-panel', ['order' => $order, 'disputes' => $order->disputes, 'isVendor' => false])

            @if ($order->status === 'completed')
                <div class="card p-5 sm:p-6">
                    <h2 class="section-heading">Ulasan</h2>
                    @if ($order->disputes->contains('status', 'open'))
                        <p class="mt-2 text-sm leading-6 text-amber-800">Ulasan dapat dikirim setelah admin menyelesaikan komplain pada pesanan ini.</p>
                    @elseif ($order->review)
                        <p class="text-amber-600 mt-2" aria-label="{{ $order->review->rating }} dari 5 bintang">{{ str_repeat('★', $order->review->rating) }}{{ str_repeat('☆', 5 - $order->review->rating) }}</p>
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
                                    @for ($rating = 5; $rating >= 1; $rating--)
                                        <option value="{{ $rating }}" @selected((string) old('rating') === (string) $rating)>{{ $rating }} ★</option>
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

        <aside class="space-y-3 sm:space-y-4 lg:col-span-4 lg:sticky lg:top-6">
            <div class="card overflow-hidden text-sm">
                <div class="bg-gradient-to-br from-[#172329] to-[#344037] p-5 text-white">
                    <h2 class="font-serif text-xl">Rincian biaya</h2>
                    <p class="mt-2 text-2xl font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex justify-between gap-3"><span class="text-ink/60">Biaya sewa</span><span class="shrink-0 text-right">Rp {{ number_format($order->rental_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-ink/60">Pengiriman</span><span class="shrink-0 text-right">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-ink/60">Pemasangan</span><span class="shrink-0 text-right">Rp {{ number_format($order->installation_fee, 0, ',', '.') }}</span></div>
                </div>
            </div>

            @if ($order->disputes->contains('status', 'open'))
                <p class="rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-900">Pembayaran dan perubahan status pesanan ditangguhkan selama komplain ditinjau admin.</p>
            @endif

            @if ($order->latestPayment && in_array($order->latestPayment->status, ['initiated', 'pending'], true))
                <p class="rounded-xl bg-sky-50 p-3 text-xs leading-5 text-sky-900">
                    Menunggu konfirmasi pembayaran dari Midtrans. Halaman ini akan diperbarui setelah notifikasi diterima.
                </p>
            @endif

            @if ($order->latestPayment && in_array($order->latestPayment->status, ['expire', 'cancel', 'deny', 'failure'], true))
                <p class="rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                    Pembayaran {{ match ($order->latestPayment->status) {
                        'expire' => 'kedaluwarsa',
                        'cancel' => 'dibatalkan',
                        'deny' => 'ditolak',
                        default => 'gagal',
                    } }}. Kamu bisa mencoba pembayaran lagi.
                </p>
            @endif

            @if ($order->status === 'confirmed' && ! $order->disputes->contains('status', 'open'))
                <form method="POST" action="{{ route('orders.pay', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">Bayar via Midtrans</button>
                </form>
            @endif

            @if ($order->status === 'installed')
                <div class="rounded-xl bg-emerald-50 p-4 text-sm leading-6 text-emerald-900">
                    <p>Masa sewa berakhir {{ $order->end_date->format('d M Y') }}.</p>
                    @if ($order->end_date->toDateString() >= today(config('isiin.display_timezone', 'Asia/Jakarta'))->toDateString())
                        <p class="mt-1">Jika ingin mengakhiri sewa lebih awal, konfirmasikan permintaan pengambilan di bawah. Vendor tidak perlu menyetujuinya.</p>
                        @if (! $order->disputes->contains('status', 'open'))
                            <form method="POST" action="{{ route('orders.pickup.request', $order) }}" class="mt-3" onsubmit="return confirm('Konfirmasi permintaan pengambilan furniture lebih awal? Setelah dikonfirmasi, vendor dapat melanjutkan pengambilan.')">
                                @csrf
                                <button type="submit" class="btn btn-primary w-full">Minta Pengambilan Lebih Awal</button>
                            </form>
                        @endif
                    @else
                        <p class="mt-1">Tahap pengambilan akan diaktifkan otomatis setelah masa sewa berakhir.</p>
                    @endif
                </div>
            @elseif ($order->status === 'pickup_pending')
                <p class="rounded-xl bg-amber-50 p-4 text-sm leading-6 text-amber-900">Pengambilan furniture menunggu vendor. Setelah furniture benar-benar diambil, vendor akan menandai pesanan selesai.</p>
            @endif

            @if (in_array($order->status, ['pending', 'confirmed'], true) && ! $order->disputes->contains('status', 'open') && ! ($order->latestPayment && in_array($order->latestPayment->status, ['initiated', 'pending'], true)))
                <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                    @csrf
                    <button type="submit" class="btn btn-outline w-full">Batalkan Pesanan</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
