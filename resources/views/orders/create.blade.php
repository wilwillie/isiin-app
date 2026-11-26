@extends('layouts.app')

@section('title', 'Konfirmasi Pemesanan')

@section('content')
    <a href="{{ route('packages.show', $package) }}" class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-ink/65 shadow-sm ring-1 ring-ink/5 transition hover:bg-white hover:text-ink">← Kembali ke paket</a>
    <div class="mb-6 mt-5 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Langkah berikutnya</p>
        <h1 class="page-heading mt-2">Konfirmasi Pemesanan</h1>
        <p class="page-subtitle">Atur jadwal sewa dan alamat pemasangan.</p>
    </div>

    <div class="grid items-start gap-4 sm:gap-6 lg:grid-cols-12">
        <form method="POST" enctype="multipart/form-data" action="{{ route('orders.store', $package) }}" class="card space-y-5 p-5 sm:p-7 lg:col-span-8">
            @csrf
            @include('partials.errors')

            <h2 class="section-heading">Jadwal dan lokasi</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="start_date">Tanggal mulai sewa</label>
                    <input class="input" type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="label" for="end_date">Tanggal selesai sewa</label>
                    <input class="input" type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div>
                <label class="label" for="property_address">Alamat properti</label>
                <textarea class="input" id="property_address" name="property_address" rows="3" required placeholder="Alamat lengkap tempat furniture dipasang">{{ old('property_address') }}</textarea>
            </div>
            <div class="rounded-2xl bg-[#faf8f3] p-4 sm:p-5">
                <h3 class="font-semibold">Ukuran dan kondisi ruangan</h3>
                <p class="mt-1 text-xs leading-5 text-ink/60">Informasi ini dipakai vendor untuk memeriksa kecocokan paket sebelum pembayaran.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label" for="room_length">Panjang (meter)</label>
                        <input class="input" type="number" id="room_length" name="room_length" min="0.1" max="1000" step="0.01" value="{{ old('room_length') }}" required>
                    </div>
                    <div>
                        <label class="label" for="room_width">Lebar (meter)</label>
                        <input class="input" type="number" id="room_width" name="room_width" min="0.1" max="1000" step="0.01" value="{{ old('room_width') }}" required>
                    </div>
                    <div>
                        <label class="label" for="ceiling_height">Tinggi plafon (meter, opsional)</label>
                        <input class="input" type="number" id="ceiling_height" name="ceiling_height" min="0.1" max="100" step="0.01" value="{{ old('ceiling_height') }}">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="label" for="room_photos">Foto ruangan (1–4 foto, JPG/PNG/WEBP, maks 5 MB per foto)</label>
                    <input class="input" type="file" id="room_photos" name="room_photos[]" accept=".jpg,.jpeg,.png,.webp" multiple required>
                    <p class="mt-1 text-xs leading-5 text-ink/50">Ambil foto dari beberapa sudut. Foto hanya dapat dilihat oleh pemilik pesanan dan vendor terkait.</p>
                </div>
            </div>
            <div>
                <label class="label" for="notes">Catatan akses/kondisi khusus (opsional)</label>
                <textarea class="input" id="notes" name="notes" rows="2" placeholder="Contoh: lebar pintu 80 cm, akses mobil terbatas, lantai 3 tanpa lift">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary w-full sm:w-auto">Kirim Pesanan ke Vendor <span class="ml-2" aria-hidden="true">→</span></button>
        </form>

        <aside class="card h-fit overflow-hidden lg:sticky lg:top-6 lg:col-span-4">
            <div class="bg-gradient-to-br from-[#172329] to-[#344037] p-5 text-white sm:p-6">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-gold">Ringkasan paket</p>
                <h2 class="mt-2 break-words font-serif text-xl sm:text-2xl">{{ $package->name }}</h2>
                <p class="mt-2 text-xs leading-5 text-white/65">{{ $package->room_label }} · {{ $package->style }} · {{ $package->vendor->business_name }}</p>
                @if ($package->minimum_room_length && $package->minimum_room_width)
                    <p class="mt-2 text-xs leading-5 text-white/75">Kebutuhan ruang minimum: {{ number_format($package->minimum_room_length, 2, ',', '.') }} × {{ number_format($package->minimum_room_width, 2, ',', '.') }} m</p>
                @endif
                <a href="{{ route('vendors.profile.show', $package->vendor) }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-gold hover:text-white">
                    Lihat profil vendor <span aria-hidden="true">→</span>
                </a>
            </div>
            <div class="p-5 sm:p-6">

            <dl id="estimate" class="mt-4 space-y-1 text-sm"
                data-price="{{ $package->price }}" data-days="{{ $package->duration_days }}"
                data-delivery="{{ $package->delivery_fee }}" data-install="{{ $package->installation_fee }}">
                <div class="flex justify-between gap-3"><dt>Biaya sewa <span id="est-periods" class="block text-xs text-ink/50"></span></dt><dd id="est-rental" class="shrink-0 text-right font-medium">-</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-ink/60">Pengiriman</dt><dd class="shrink-0 text-right">Rp {{ number_format($package->delivery_fee, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-ink/60">Pemasangan</dt><dd class="shrink-0 text-right">Rp {{ number_format($package->installation_fee, 0, ',', '.') }}</dd></div>
                <div class="mt-3 flex justify-between gap-3 border-t border-dashed border-ink/10 pt-3 font-semibold"><dt>Estimasi total</dt><dd id="est-total" class="shrink-0 text-right">-</dd></div>
            </dl>
            <p class="mt-4 text-xs leading-5 text-ink/50">Vendor akan memeriksa kecocokan ruangan sebelum pesanan dapat dibayar. Tarif berlaku untuk setiap {{ $package->duration_days }} hari. Sisa hari dihitung sebagai kelipatan tarif berikutnya (dibulatkan ke atas).</p>
            </div>
        </aside>
    </div>

    <script>
        (function () {
            var box = document.getElementById('estimate');
            var start = document.getElementById('start_date');
            var end = document.getElementById('end_date');

            function rp(n) {
                return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function update() {
                if (!start.value || !end.value) return;
                var days = Math.round((new Date(end.value) - new Date(start.value)) / 86400000);
                if (days < 1) {
                    document.getElementById('est-rental').textContent = '-';
                    document.getElementById('est-total').textContent = '-';
                    document.getElementById('est-periods').textContent = '';
                    return;
                }
                var periods = Math.ceil(days / Number(box.dataset.days));
                var rental = periods * Number(box.dataset.price);
                var total = rental + Number(box.dataset.delivery) + Number(box.dataset.install);
                document.getElementById('est-periods').textContent = '(' + days + ' hari, dihitung ' + periods + ' kali tarif)';
                document.getElementById('est-rental').textContent = rp(rental);
                document.getElementById('est-total').textContent = rp(total);
            }

            start.addEventListener('change', update);
            end.addEventListener('change', update);
            update();
        })();
    </script>
@endsection
