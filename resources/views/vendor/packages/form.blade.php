@extends('layouts.app')

@section('title', $package->exists ? 'Edit Paket' : 'Tambah Paket')

@section('content')
    <a href="{{ route('vendor.packages.index') }}" class="text-sm text-ink/60 hover:underline">← Paket saya</a>
    <h1 class="font-serif text-3xl mt-3">{{ $package->exists ? 'Edit Paket' : 'Tambah Paket' }}</h1>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $package->exists ? route('vendor.packages.update', $package) : route('vendor.packages.store') }}"
          class="card p-6 mt-5 space-y-4 max-w-3xl">
        @csrf
        @if ($package->exists)
            @method('PUT')
        @endif

        @include('partials.errors')

        <div>
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

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="price">Harga sewa per periode (Rp)</label>
                <input class="input" type="number" min="0" id="price" name="price" value="{{ old('price', $package->price) }}" required>
            </div>
            <div>
                <label class="label" for="duration_days">Panjang 1 periode (hari)</label>
                <input class="input" type="number" min="1" max="365" id="duration_days" name="duration_days" value="{{ old('duration_days', $package->duration_days) }}" required>
            </div>
            <div>
                <label class="label" for="delivery_fee">Biaya pengiriman (Rp)</label>
                <input class="input" type="number" min="0" id="delivery_fee" name="delivery_fee" value="{{ old('delivery_fee', $package->delivery_fee ?? 0) }}">
            </div>
            <div>
                <label class="label" for="installation_fee">Biaya pemasangan (Rp)</label>
                <input class="input" type="number" min="0" id="installation_fee" name="installation_fee" value="{{ old('installation_fee', $package->installation_fee ?? 0) }}">
            </div>
            <div>
                <label class="label" for="deposit">Deposit, dapat dikembalikan (Rp)</label>
                <input class="input" type="number" min="0" id="deposit" name="deposit" value="{{ old('deposit', $package->deposit ?? 0) }}">
            </div>
        </div>

        <div>
            <label class="label" for="items">Daftar furniture</label>
            <textarea class="input font-mono" id="items" name="items" rows="6" placeholder="Sofa 3 dudukan | 1&#10;Meja kopi | 1&#10;Karpet | 1">{{ old('items', $itemsText) }}</textarea>
            <p class="text-xs text-ink/50 mt-1">Satu baris per item, format: Nama | jumlah.</p>
        </div>

        <div>
            <label class="label" for="image">Foto paket (jpg, png, webp, maks 2 MB)</label>
            @if ($package->image_url)
                <img src="{{ $package->image_url }}" alt="Foto saat ini" class="h-28 rounded-lg mb-2 object-cover">
            @endif
            <input class="input" type="file" id="image" name="image" accept="image/*">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked($errors->any() ? old('is_active') : $package->is_active)>
            Tampilkan di katalog (aktif)
        </label>

        <button type="submit" class="btn btn-primary">Simpan Paket</button>
    </form>
@endsection
