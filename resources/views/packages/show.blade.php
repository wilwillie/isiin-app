@extends('layouts.app')

@section('title', $package->name)

@section('content')
    <a href="{{ route('packages.index') }}" class="text-sm text-ink/60 hover:underline">← Kembali ke katalog</a>

    <div class="mt-4 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($package->image_url)
                <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full h-80 object-cover rounded-xl">
            @else
                <div class="w-full h-80 rounded-xl bg-gradient-to-br from-gold/50 to-sand flex items-center justify-center text-ink/40">Belum ada foto</div>
            @endif

            <h1 class="font-serif text-3xl mt-5">{{ $package->name }}</h1>
            <p class="text-sm text-ink/60 mt-1">
                {{ $package->room_label }} · {{ $package->style }} ·
                oleh {{ $package->vendor->business_name }}@if ($package->vendor->city), {{ $package->vendor->city }}@endif
            </p>
            @if ($avgRating)
                <p class="text-sm text-amber-600 mt-1">★ {{ number_format($avgRating, 1) }} ({{ $package->reviews->count() }} ulasan)</p>
            @endif

            @if ($package->description)
                <h2 class="font-semibold mt-6">Deskripsi</h2>
                <p class="text-sm text-ink/80 mt-1 whitespace-pre-line">{{ $package->description }}</p>
            @endif

            <h2 class="font-semibold mt-6">Termasuk</h2>
            <ul class="mt-2 grid sm:grid-cols-2 gap-1 text-sm">
                @forelse ($package->items as $item)
                    <li>✓ {{ $item->name }} @if ($item->quantity > 1) (x{{ $item->quantity }}) @endif</li>
                @empty
                    <li class="text-ink/60">Daftar furniture belum diisi vendor.</li>
                @endforelse
            </ul>

            <h2 class="font-semibold mt-6">Ulasan</h2>
            <div class="mt-2 space-y-3">
                @forelse ($package->reviews->sortByDesc('created_at') as $review)
                    <div class="card p-4 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium">{{ $review->customer->name }}</span>
                            <span class="text-amber-600">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        </div>
                        @if ($review->comment)
                            <p class="mt-1 text-ink/80">{{ $review->comment }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-ink/60">Belum ada ulasan.</p>
                @endforelse
            </div>
        </div>

        <aside>
            <div class="card p-5 lg:sticky lg:top-6">
                <p class="text-2xl font-semibold">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                <p class="text-sm text-ink/60">per {{ $package->duration_days }} hari</p>

                <dl class="mt-4 space-y-1 text-sm">
                    <div class="flex justify-between"><dt>Biaya pengiriman</dt><dd>Rp {{ number_format($package->delivery_fee, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt>Biaya pemasangan</dt><dd>Rp {{ number_format($package->installation_fee, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt>Deposit (dapat dikembalikan)</dt><dd>Rp {{ number_format($package->deposit, 0, ',', '.') }}</dd></div>
                </dl>

                <div class="mt-5">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary w-full">Masuk untuk memesan</a>
                    @else
                        @if (auth()->user()->isCustomer())
                            <a href="{{ route('orders.create', $package) }}" class="btn btn-primary w-full">Pilih Tanggal dan Pesan</a>
                        @else
                            <p class="text-xs text-ink/60">Pemesanan hanya tersedia untuk akun customer.</p>
                        @endif
                    @endguest
                </div>
            </div>
        </aside>
    </div>
@endsection
