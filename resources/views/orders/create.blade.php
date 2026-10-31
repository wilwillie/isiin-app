@extends('layouts.app')

@section('title', 'Konfirmasi Pemesanan')

@section('content')
    <a href="{{ route('packages.show', $package) }}" class="text-sm text-ink/60 hover:underline">← Kembali ke paket</a>
    <h1 class="font-serif text-3xl mt-3">Konfirmasi Pemesanan</h1>

    <div class="mt-5 grid gap-8 lg:grid-cols-3">
        <form method="POST" action="{{ route('orders.store', $package) }}" class="lg:col-span-2 card p-6 space-y-4">
            @csrf
            @include('partials.errors')

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
            <div>
                <label class="label" for="notes">Catatan (opsional)</label>
                <textarea class="input" id="notes" name="notes" rows="2" placeholder="Contoh: akses mobil terbatas, lantai 3 tanpa lift">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Pesanan ke Vendor</button>
        </form>

        <aside class="card p-5 h-fit">
            <h2 class="font-semibold">{{ $package->name }}</h2>
            <p class="text-xs text-ink/60">{{ $package->room_label }} · {{ $package->style }} · {{ $package->vendor->business_name }}</p>

            <dl id="estimate" class="mt-4 space-y-1 text-sm"
                data-price="{{ $package->price }}" data-days="{{ $package->duration_days }}"
                data-delivery="{{ $package->delivery_fee }}" data-install="{{ $package->installation_fee }}"
                data-deposit="{{ $package->deposit }}">
                <div class="flex justify-between"><dt>Biaya sewa <span id="est-periods" class="text-ink/50"></span></dt><dd id="est-rental">-</dd></div>
                <div class="flex justify-between"><dt>Pengiriman</dt><dd>Rp {{ number_format($package->delivery_fee, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt>Pemasangan</dt><dd>Rp {{ number_format($package->installation_fee, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt>Deposit (dapat dikembalikan)</dt><dd>Rp {{ number_format($package->deposit, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between border-t pt-2 mt-2 font-semibold"><dt>Estimasi total</dt><dd id="est-total">-</dd></div>
            </dl>
            <p class="text-xs text-ink/50 mt-3">Biaya sewa dihitung per {{ $package->duration_days }} hari (dibulatkan ke atas). Nilai akhir dihitung ulang oleh sistem saat pesanan dikirim.</p>
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
                var total = rental + Number(box.dataset.delivery) + Number(box.dataset.install) + Number(box.dataset.deposit);
                document.getElementById('est-periods').textContent = '(' + days + ' hari, ' + periods + ' periode)';
                document.getElementById('est-rental').textContent = rp(rental);
                document.getElementById('est-total').textContent = rp(total);
            }

            start.addEventListener('change', update);
            end.addEventListener('change', update);
            update();
        })();
    </script>
@endsection
