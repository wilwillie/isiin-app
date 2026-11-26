@extends('layouts.app')

@section('title', 'Home Staging Made Easy')

@section('content')
    @php
        $isGuest = auth()->guest();
        $isVendor = auth()->user()?->isVendor() ?? false;
        $vendorCtaUrl = $isGuest
            ? route('register', ['role' => 'vendor'])
            : ($isVendor ? route('vendor.dashboard') : null);
        $customerCtaUrl = $isGuest
            ? route('register', ['role' => 'customer'])
            : route('packages.index');
    @endphp

    <section class="relative isolate overflow-hidden rounded-3xl bg-ink text-white shadow-xl">
        <div class="absolute -left-20 -top-24 -z-10 h-72 w-72 rounded-full bg-gold/20 blur-3xl"></div>
        <div class="absolute -bottom-28 left-1/3 -z-10 h-64 w-64 rounded-full bg-amber-700/20 blur-3xl"></div>
        <div class="grid items-center gap-10 px-6 py-12 sm:px-10 md:grid-cols-2 md:px-14 md:py-16">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold tracking-wide text-gold">
                    <span class="h-1.5 w-1.5 rounded-full bg-gold"></span>
                    HOME STAGING MADE EASY
                </span>
                <h1 class="mt-5 font-serif text-4xl leading-tight sm:text-5xl md:text-[3.4rem]">Bikin properti kosong siap laku, <span class="text-gold">tanpa perlu beli furniture.</span></h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-white/70">Bikin properti siap dilirik tanpa perlu beli furniture. Sewa paket staging pilihan dari vendor terverifikasi, langsung dari satu tempat.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('packages.index') }}" class="btn btn-gold px-5 py-3">Jelajahi Paket <span class="ml-2" aria-hidden="true">→</span></a>
                    @if ($vendorCtaUrl)
                        <a href="{{ $vendorCtaUrl }}" class="btn border border-white/25 px-5 py-3 text-white hover:bg-white/10">{{ $isVendor ? 'Dashboard Vendor' : 'Jadi Vendor' }}</a>
                    @endif
                </div>
                <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-white/15 pt-5 text-sm text-white/70">
                    <span class="inline-flex items-center gap-2"><span class="text-gold" aria-hidden="true">✦</span> Harga transparan</span>
                    <span class="inline-flex items-center gap-2"><span class="text-gold" aria-hidden="true">✦</span> Vendor terverifikasi</span>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-lg md:pl-2">
                <div class="absolute -right-3 -top-4 h-24 w-24 rounded-full border border-gold/40 sm:-right-5 sm:-top-6"></div>
                <div class="absolute -bottom-4 -left-4 h-24 w-24 rounded-full bg-gold/20 blur-xl"></div>
                <div class="relative overflow-hidden rounded-[1.7rem] border border-white/15 bg-white/10 p-2 shadow-2xl">
                    <img
                        src="https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&amp;fit=crop&amp;w=1200&amp;q=85"
                        alt="Ruang keluarga tertata dengan sofa dan dekorasi hangat"
                        class="h-72 w-full rounded-[1.25rem] object-cover sm:h-96"
                    >
                    <div class="absolute bottom-5 left-5 right-5 flex items-center justify-between gap-3 rounded-2xl border border-white/30 bg-white/90 p-4 text-ink shadow-lg backdrop-blur">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-ink/55">Sentuhan kecil, hasil besar</p>
                            <p class="mt-1 font-serif text-lg">Siap bikin jatuh hati</p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-gold text-xl" aria-hidden="true">⌂</span>
                    </div>
                </div>
                <span class="absolute -right-2 top-10 hidden -rotate-6 rounded-xl bg-gold px-4 py-2 text-sm font-bold text-ink shadow-lg sm:block">Home, but better ✨</span>
            </div>
        </div>
    </section>

    <section class="mt-14">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700">Semudah itu</p>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl">Dari kosong sampai siap dipamerkan</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-ink/65">Semua kebutuhan staging properti, diringkas dalam tiga langkah praktis.</p>
        </div>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <article class="card group relative overflow-hidden p-6 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-4 top-1 text-7xl font-serif text-gold/20 transition group-hover:text-gold/40">01</div>
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-2xl text-amber-800" aria-hidden="true">⌕</span>
                <h3 class="mt-5 font-serif text-xl">Pilih paket</h3>
                <p class="mt-2 text-sm leading-6 text-ink/65">Temukan paket staging sesuai tipe ruangan, gaya, dan budget properti.</p>
            </article>
            <article class="card group relative overflow-hidden p-6 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-4 top-1 text-7xl font-serif text-gold/20 transition group-hover:text-gold/40">02</div>
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-2xl text-amber-800" aria-hidden="true">▣</span>
                <h3 class="mt-5 font-serif text-xl">Booking dan bayar</h3>
                <p class="mt-2 text-sm leading-6 text-ink/65">Atur tanggal sewa dan cek rincian biaya sebelum vendor mengonfirmasi.</p>
            </article>
            <article class="card group relative overflow-hidden p-6 transition hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-4 top-1 text-7xl font-serif text-gold/20 transition group-hover:text-gold/40">03</div>
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-2xl text-amber-800" aria-hidden="true">✧</span>
                <h3 class="mt-5 font-serif text-xl">Tinggal menikmati</h3>
                <p class="mt-2 text-sm leading-6 text-ink/65">Vendor mengantar dan memasang furniture, lalu mengambilnya saat sewa selesai.</p>
            </article>
        </div>
    </section>

    <section class="mt-14 grid gap-5 {{ $vendorCtaUrl ? 'md:grid-cols-2' : '' }}">
        <article class="relative isolate min-h-64 overflow-hidden rounded-2xl bg-amber-100 p-7 sm:p-9">
            <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&amp;fit=crop&amp;w=1000&amp;q=80" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover">
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/90 via-ink/75 to-ink/20"></div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold">Untuk pemilik properti</p>
            <h2 class="mt-3 max-w-sm font-serif text-2xl text-white sm:text-3xl">Buat calon pembeli membayangkan diri mereka di sini.</h2>
            <p class="mt-3 max-w-sm text-sm leading-6 text-white/75">Percantik unit kosong tanpa modal besar untuk membeli furniture.</p>
            <a href="{{ $customerCtaUrl }}" class="btn btn-gold mt-6">{{ $isGuest ? 'Mulai sebagai Customer' : 'Jelajahi Paket' }} <span class="ml-2" aria-hidden="true">→</span></a>
        </article>
        @if ($vendorCtaUrl)
            <article class="relative overflow-hidden rounded-2xl bg-white p-7 shadow-sm ring-1 ring-ink/5 sm:p-9">
                <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-amber-100"></div>
                <div class="relative">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-2xl text-amber-800" aria-hidden="true">✳</span>
                    <p class="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Untuk vendor furniture</p>
                    <h2 class="mt-2 max-w-sm font-serif text-2xl sm:text-3xl">Furniture-mu bisa jadi awal cerita baru.</h2>
                    <p class="mt-3 max-w-md text-sm leading-6 text-ink/65">Jangkau pelanggan baru tanpa promosi sendiri. Kelola paket, harga, dan pesanan dalam satu dashboard.</p>
                    <a href="{{ $vendorCtaUrl }}" class="btn btn-outline mt-6">{{ $isVendor ? 'Dashboard Vendor' : 'Daftar sebagai Vendor' }} <span class="ml-2" aria-hidden="true">→</span></a>
                </div>
            </article>
        @endif
    </section>

    <section class="mt-14 overflow-hidden rounded-3xl bg-gradient-to-br from-[#172329] via-ink to-[#344037] p-6 text-white shadow-lg sm:p-9">
        <div class="grid items-center gap-6 md:grid-cols-[1fr_auto]">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold">Prototype aplikasi</p>
                <h2 class="mt-2 font-serif text-2xl sm:text-3xl">Lihat pengalaman Isiin dari layar ponsel.</h2>
                <p class="mt-2 text-sm leading-6 text-white/70">Coba alur mencari paket, melihat detail, simulasi booking, sampai memantau pesanan. Ini prototype interaktif, bukan aplikasi yang sudah tersedia untuk diunduh.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('prototype.app') }}" target="_blank" rel="noopener" aria-label="Buka prototype Isiin melalui badge Google Play" class="inline-flex min-w-40 items-center gap-3 rounded-xl border border-white/30 bg-black px-4 py-2.5 text-white transition hover:bg-black/70">
                    <span class="text-2xl text-gold" aria-hidden="true">▶</span>
                    <span class="text-left"><span class="block text-[9px] uppercase tracking-wider text-white/70">GET IT ON</span><span class="block text-base font-semibold leading-5">Google Play</span></span>
                </a>
                <a href="{{ route('prototype.app') }}" target="_blank" rel="noopener" aria-label="Buka prototype Isiin melalui badge App Store" class="inline-flex min-w-40 items-center gap-3 rounded-xl border border-white/30 bg-black px-4 py-2.5 text-white transition hover:bg-black/70">
                    <span class="text-2xl font-semibold" aria-hidden="true">A</span>
                    <span class="text-left"><span class="block text-[9px] text-white/70">Download on the</span><span class="block text-base font-semibold leading-5">App Store</span></span>
                </a>
            </div>
        </div>
    </section>
@endsection
