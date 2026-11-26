@extends('layouts.app')

@section('title', $package->name)

@section('content')
    <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-ink/65 shadow-sm ring-1 ring-ink/5 transition hover:bg-white hover:text-ink">
        <span aria-hidden="true">←</span> Kembali ke katalog
    </a>

    <div class="mt-5 grid items-start gap-6 lg:mt-7 lg:grid-cols-12 lg:gap-8">
        <div class="min-w-0 space-y-5 lg:col-span-8 lg:space-y-7">
            <section class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-ink/5">
                @if ($package->image_url)
                    <div class="relative aspect-[4/3] overflow-hidden bg-sand sm:aspect-[16/9] lg:aspect-[5/3]">
                        <img src="{{ $package->image_url }}" alt="{{ $package->name }}" fetchpriority="high" class="h-full w-full object-cover">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent px-4 pb-4 pt-12 sm:px-6 sm:pb-6">
                            <span class="inline-flex rounded-full border border-white/40 bg-white/90 px-3 py-1.5 text-xs font-semibold text-ink shadow-sm backdrop-blur sm:text-sm">{{ $package->room_label }}</span>
                        </div>
                    </div>
                @else
                    <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-gradient-to-br from-[#efe1c9] via-[#f8f3e9] to-[#dfc9a7] sm:aspect-[16/9] lg:aspect-[5/3]">
                        <span class="absolute -right-10 -top-20 h-64 w-64 rounded-full border border-white/60"></span>
                        <span class="absolute -right-1 -top-12 h-48 w-48 rounded-full border border-white/50"></span>
                        <div class="relative text-center">
                            <span class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/55 text-3xl text-ink/55 shadow-sm" aria-hidden="true">⌂</span>
                            <p class="mt-3 text-sm font-medium text-ink/55">Foto paket belum tersedia</p>
                        </div>
                        <span class="absolute bottom-4 left-4 rounded-full bg-white/75 px-3 py-1.5 text-xs font-semibold text-ink/65">{{ $package->room_label }}</span>
                    </div>
                @endif

                <div class="p-5 sm:p-7 lg:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">{{ $package->style }}</span>
                        @if ($avgRating)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                <span aria-hidden="true">★</span> {{ \App\Support\RatingFormatter::formatAverage($avgRating) }}
                                <span class="font-normal text-amber-700/70">· {{ $package->reviews->count() }} ulasan</span>
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-3 break-words font-serif text-3xl leading-tight sm:text-4xl lg:text-[2.75rem]">{{ $package->name }}</h1>
                    <p class="mt-3 text-sm leading-6 text-ink/60 sm:text-base">
                        Disediakan oleh <span class="font-semibold text-ink/80">{{ $package->vendor->business_name }}</span>@if ($package->vendor->city)<span> · {{ $package->vendor->city }}</span>@endif
                    </p>
                    <a href="{{ route('vendors.profile.show', $package->vendor) }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-amber-800 transition hover:text-amber-950">
                        Lihat profil vendor <span aria-hidden="true">→</span>
                    </a>

                    @if ($package->description)
                        <div class="mt-6 border-t border-ink/5 pt-5 sm:mt-7 sm:pt-6">
                            <h2 class="font-serif text-xl sm:text-2xl">Tentang paket ini</h2>
                            <p class="mt-2 whitespace-pre-line text-sm leading-7 text-ink/70 sm:text-base">{{ $package->description }}</p>
                        </div>
                    @endif
                    @if ($package->minimum_room_length && $package->minimum_room_width)
                        <div class="mt-5 rounded-2xl bg-[#faf8f3] p-4 text-sm">
                            <p class="font-semibold">Kebutuhan ruang minimum</p>
                            <p class="mt-1 text-ink/65">{{ number_format($package->minimum_room_length, 2, ',', '.') }} × {{ number_format($package->minimum_room_width, 2, ',', '.') }} meter</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-ink/5 sm:p-7">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Sudah termasuk</p>
                        <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Isi paket</h2>
                    </div>
                    <span class="hidden rounded-full bg-sand px-3 py-1.5 text-xs font-medium text-ink/60 sm:inline-flex">{{ $package->items->count() }} item</span>
                </div>
                <ul class="mt-5 grid gap-2 sm:grid-cols-2 sm:gap-3">
                    @forelse ($package->items as $item)
                        <li class="flex min-w-0 items-start gap-3 rounded-2xl bg-[#faf8f3] px-3.5 py-3 text-sm sm:px-4">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800" aria-hidden="true">✓</span>
                            <span class="min-w-0 break-words text-ink/80">{{ $item->name }} @if ($item->quantity > 1)<span class="whitespace-nowrap text-ink/50">(x{{ $item->quantity }})</span>@endif</span>
                        </li>
                    @empty
                        <li class="text-sm text-ink/55 sm:col-span-2">Daftar furniture belum diisi vendor.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-ink/5 sm:p-7">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Cerita pelanggan</p>
                        <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Ulasan</h2>
                    </div>
                    @if ($avgRating)
                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700">★ {{ \App\Support\RatingFormatter::formatAverage($avgRating) }} <span class="font-normal text-amber-700/70">/ 5</span></span>
                    @endif
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($package->reviews->sortByDesc('created_at') as $review)
                        <article class="rounded-2xl border border-ink/5 bg-[#fcfbf8] p-4 sm:p-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-semibold">{{ $review->customer->name }}</span>
                                <span class="whitespace-nowrap text-sm tracking-wider text-amber-500" aria-label="{{ $review->rating }} dari 5 bintang">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </div>
                            @if ($review->comment)
                                <p class="mt-2 break-words text-sm leading-6 text-ink/70">{{ $review->comment }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl bg-[#faf8f3] px-4 py-6 text-center">
                            <span class="text-2xl text-gold" aria-hidden="true">✧</span>
                            <p class="mt-2 text-sm text-ink/60">Belum ada ulasan untuk paket ini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="min-w-0 lg:sticky lg:top-6 lg:col-span-4 lg:self-start">
            <div class="card overflow-hidden">
                <div class="bg-gradient-to-br from-[#172329] to-[#344037] p-5 text-white sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gold">Harga sewa</p>
                    <p class="mt-2 break-words text-2xl font-bold sm:text-3xl">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-white/65">untuk {{ $package->duration_days }} hari</p>
                </div>

                <div class="p-5 sm:p-6">
                    <h2 class="font-semibold">Rincian biaya</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-ink/60">Biaya pengiriman</dt>
                            <dd class="shrink-0 text-right font-medium">Rp {{ number_format($package->delivery_fee, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-ink/60">Biaya pemasangan</dt>
                            <dd class="shrink-0 text-right font-medium">Rp {{ number_format($package->installation_fee, 0, ',', '.') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 border-t border-ink/5 pt-5">
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary w-full">Masuk untuk memesan <span class="ml-2" aria-hidden="true">→</span></a>
                        @else
                            @if (auth()->user()->isCustomer())
                                <a href="{{ route('orders.create', $package) }}" class="btn btn-primary w-full">Pilih Tanggal dan Pesan <span class="ml-2" aria-hidden="true">→</span></a>
                            @else
                                <p class="rounded-xl bg-amber-50 p-3 text-center text-xs leading-5 text-amber-900">Pemesanan hanya tersedia untuk akun customer.</p>
                            @endif
                        @endguest
                        <p class="mt-3 text-center text-xs text-ink/45">Harga transparan, booking mudah.</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="h-24 lg:hidden" aria-hidden="true"></div>

    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-ink/10 bg-white/95 px-4 pt-3 shadow-[0_-8px_30px_rgba(15,23,32,0.12)] backdrop-blur-md lg:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
        <div class="mx-auto flex max-w-2xl items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-ink/50">Harga sewa</p>
                <p class="truncate text-base font-bold text-ink">Rp {{ number_format($package->price, 0, ',', '.') }} <span class="text-xs font-normal text-ink/55">/ {{ $package->duration_days }} hari</span></p>
            </div>
            <div class="shrink-0">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary px-4 py-2.5 text-xs sm:text-sm">Masuk untuk pesan <span class="ml-1" aria-hidden="true">→</span></a>
                @else
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('orders.create', $package) }}" class="btn btn-primary px-4 py-2.5 text-xs sm:text-sm">Pesan sekarang <span class="ml-1" aria-hidden="true">→</span></a>
                    @else
                        <span class="max-w-32 text-right text-xs leading-4 text-ink/55">Khusus akun customer</span>
                    @endif
                @endguest
            </div>
        </div>
    </div>
@endsection
