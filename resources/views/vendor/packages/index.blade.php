@extends('layouts.app')

@section('title', 'Paket Saya')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Kelola katalog</p>
            <h1 class="page-heading mt-2">Paket Saya</h1>
        </div>
        <a href="{{ route('vendor.packages.create') }}" class="btn btn-primary w-full sm:w-auto">Tambah Paket <span class="ml-2" aria-hidden="true">+</span></a>
    </div>

    <div class="mt-5 space-y-3 sm:mt-7 sm:space-y-4">
        @forelse ($packages as $package)
            <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="min-w-0">
                    <p class="break-words font-semibold">{{ $package->name }}
                        @unless ($package->is_active)
                            <span class="badge bg-ink/10 text-ink/60 ml-1">Nonaktif</span>
                        @endunless
                    </p>
                    <p class="mt-1 text-xs leading-5 text-ink/60">{{ $package->room_label }} · {{ $package->style }} · {{ $package->items_count }} item</p>
                    <p class="mt-2 text-sm font-medium">Rp {{ number_format($package->price, 0, ',', '.') }} <span class="text-xs font-normal text-ink/55">/ {{ $package->duration_days }} hari</span></p>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
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
