@extends('layouts.app')

@section('title', $package->exists ? 'Edit Paket' : 'Tambah Paket')

@section('content')
    @php
        $commissionRate = (float) config('isiin.commission_rate');
        $estimatePrice = (int) old('price', $package->price ?? 0);
        $estimateDelivery = (int) old('delivery_fee', $package->delivery_fee ?? 0);
        $estimateInstallation = (int) old('installation_fee', $package->installation_fee ?? 0);
        $estimateServiceTotal = $estimatePrice + $estimateDelivery + $estimateInstallation;
        $estimateCommission = (int) round($estimateServiceTotal * $commissionRate);
        $estimateVendorIncome = $estimateServiceTotal - $estimateCommission;
    @endphp

    <a href="{{ route('vendor.packages.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-ink/65 shadow-sm ring-1 ring-ink/5 transition hover:bg-white hover:text-ink">← Paket saya</a>
    <div class="mb-6 mt-5 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Kelola katalog</p>
        <h1 class="page-heading mt-2">{{ $package->exists ? 'Edit Paket' : 'Tambah Paket' }}</h1>
        <p class="page-subtitle">Atur detail dan harga paket staging Anda.</p>
    </div>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $package->exists ? route('vendor.packages.update', $package) : route('vendor.packages.store') }}"
          class="card mx-auto max-w-4xl space-y-5 p-5 sm:p-7 lg:p-8">
        @csrf
        @if ($package->exists)
            @method('PUT')
        @endif

        @include('partials.errors')

        <div class="rounded-2xl bg-[#faf8f3] p-4 sm:p-5">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.15em] text-amber-800">Informasi paket</p>
            <label class="label" for="name">Nama paket</label>
            <input class="input" id="name" name="name" value="{{ old('name', $package->name) }}" required>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="room_type">Tipe ruangan</label>
                <select class="input" id="room_type" name="room_type" required>
                    @foreach (config('isiin.room_types') as $key => $label)
                        <option value="{{ $key }}" @selected(old('room_type', $package->room_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="style">Gaya</label>
                <select class="input" id="style" name="style" required>
                    @foreach (config('isiin.styles') as $style)
                        <option value="{{ $style }}" @selected(old('style', $package->style) === $style)>{{ $style }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="label" for="description">Deskripsi</label>
            <textarea class="input" id="description" name="description" rows="3">{{ old('description', $package->description) }}</textarea>
        </div>
        <div class="rounded-2xl bg-[#faf8f3] p-4 sm:p-5">
            <p class="font-semibold">Kebutuhan ruang minimum (opsional)</p>
            <p class="mt-1 text-xs leading-5 text-ink/55">Isi keduanya jika paket membutuhkan ukuran minimum tertentu agar pelanggan dan vendor bisa menilai kecocokan.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="minimum_room_length">Panjang minimum (meter)</label>
                    <input class="input" type="number" id="minimum_room_length" name="minimum_room_length" min="0.1" max="1000" step="0.01" value="{{ old('minimum_room_length', $package->minimum_room_length) }}">
                </div>
                <div>
                    <label class="label" for="minimum_room_width">Lebar minimum (meter)</label>
                    <input class="input" type="number" id="minimum_room_width" name="minimum_room_width" min="0.1" max="1000" step="0.01" value="{{ old('minimum_room_width', $package->minimum_room_width) }}">
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-[#faf8f3] p-4 sm:p-5">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.15em] text-amber-800">Harga dan layanan</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="price">Tarif sewa (Rp)</label>
                <input class="input" type="number" min="0" id="price" name="price" value="{{ old('price', $package->price) }}" required>
            </div>
            <div>
                <label class="label" for="duration_days">Durasi sewa per tarif (hari)</label>
                <input class="input" type="number" min="1" max="365" id="duration_days" name="duration_days" value="{{ old('duration_days', $package->duration_days) }}" required>
                <p class="mt-1 text-xs leading-5 text-ink/50">Tarif berlaku untuk durasi ini. Sewa yang lebih lama dihitung kelipatan tarif dan dibulatkan ke atas.</p>
            </div>
            <div>
                <label class="label" for="delivery_fee">Biaya pengiriman (Rp)</label>
                <input class="input" type="number" min="0" id="delivery_fee" name="delivery_fee" value="{{ old('delivery_fee', $package->delivery_fee ?? 0) }}">
            </div>
            <div>
                <label class="label" for="installation_fee">Biaya pemasangan (Rp)</label>
                <input class="input" type="number" min="0" id="installation_fee" name="installation_fee" value="{{ old('installation_fee', $package->installation_fee ?? 0) }}">
            </div>
        </div>
        <div id="vendor-income-estimate" data-commission-rate="{{ $commissionRate }}" aria-live="polite" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-800">Estimasi pendapatan vendor untuk 1 tarif sewa</p>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <p class="text-sm text-emerald-900/75">Komisi Isiin ({{ round($commissionRate * 100) }}% dari total biaya layanan): <span id="commission-estimate">Rp {{ number_format($estimateCommission, 0, ',', '.') }}</span></p>
                <p class="text-xl font-bold text-emerald-900" id="vendor-income-amount">Rp {{ number_format($estimateVendorIncome, 0, ',', '.') }}</p>
            </div>
        </div>
        </div>

        <div class="rounded-2xl bg-[#faf8f3] p-4 sm:p-5">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.15em] text-amber-800">Isi dan tampilan</p>
            <label class="label" for="items">Daftar furniture</label>
            <textarea class="input font-mono" id="items" name="items" rows="6" placeholder="Sofa 3 dudukan | 1&#10;Meja kopi | 1&#10;Karpet | 1">{{ old('items', $itemsText) }}</textarea>
            <p class="text-xs text-ink/50 mt-1">Satu baris per item, format: Nama | jumlah.</p>

            <label class="label" for="image">Foto paket (jpg, png, webp, maks 2 MB)</label>
            @if ($package->image_url)
                <img src="{{ $package->image_url }}" alt="Foto saat ini" class="mb-3 aspect-video max-h-64 w-full rounded-2xl object-cover sm:aspect-[2/1]">
            @endif
            <input class="input" type="file" id="image" name="image" accept="image/*">
        </div>

        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink/5 px-4 py-3.5 text-sm">
            <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 shrink-0 rounded accent-amber-700" @checked($errors->any() ? old('is_active') : $package->is_active)>
            Tampilkan di katalog (aktif)
        </label>

        <div class="flex flex-col-reverse gap-2 border-t border-ink/5 pt-4 sm:flex-row sm:justify-end">
            <a href="{{ route('vendor.packages.index') }}" class="btn btn-outline w-full sm:w-auto">Batal</a>
            <button type="submit" class="btn btn-primary w-full sm:w-auto">Simpan Paket <span class="ml-2" aria-hidden="true">→</span></button>
        </div>
    </form>

    <script>
        (function () {
            var price = document.getElementById('price');
            var delivery = document.getElementById('delivery_fee');
            var installation = document.getElementById('installation_fee');
            var estimate = document.getElementById('vendor-income-estimate');
            var rate = Number(estimate.dataset.commissionRate);

            function rupiah(value) {
                return 'Rp ' + Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function updateEstimate() {
                var serviceTotal = (parseInt(price.value, 10) || 0)
                    + (parseInt(delivery.value, 10) || 0)
                    + (parseInt(installation.value, 10) || 0);
                var commission = Math.round(serviceTotal * rate);
                var income = serviceTotal - commission;

                document.getElementById('commission-estimate').textContent = rupiah(commission);
                document.getElementById('vendor-income-amount').textContent = rupiah(income);
            }

            price.addEventListener('input', updateEstimate);
            price.addEventListener('change', updateEstimate);
            delivery.addEventListener('input', updateEstimate);
            delivery.addEventListener('change', updateEstimate);
            installation.addEventListener('input', updateEstimate);
            installation.addEventListener('change', updateEstimate);
            updateEstimate();
        })();
    </script>
@endsection
