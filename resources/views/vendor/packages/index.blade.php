@extends('layouts.app')

@section('title', 'Paket Saya')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="font-serif text-3xl">Paket Saya</h1>
        <a href="{{ route('vendor.packages.create') }}" class="btn btn-primary">Tambah Paket</a>
    </div>

    <div class="mt-5 space-y-3">
        @forelse ($packages as $package)
            <div class="card p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $package->name }}
                        @unless ($package->is_active)
                            <span class="badge bg-ink/10 text-ink/60 ml-1">Nonaktif</span>
                        @endunless
                    </p>
                    <p class="text-xs text-ink/60">{{ $package->room_label }} · {{ $package->style }} · {{ $package->items_count }} item</p>
                    <p class="text-sm mt-1">Rp {{ number_format($package->price, 0, ',', '.') }} / {{ $package->duration_days }} hari</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('vendor.packages.edit', $package) }}" class="btn btn-outline">Edit</a>
                    <form method="POST" action="{{ route('vendor.packages.destroy', $package) }}" onsubmit="return confirm('Hapus paket ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card p-6 text-center text-ink/60">Belum ada paket. Tambahkan paket pertama Anda.</div>
        @endforelse
    </div>
@endsection
