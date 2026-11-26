@extends('layouts.app')

@section('title', 'Profil ' . $vendor->business_name)

@section('content')
    <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-ink/65 shadow-sm ring-1 ring-ink/5 transition hover:bg-white hover:text-ink">← Kembali ke katalog</a>

    <section class="relative isolate mt-5 overflow-hidden rounded-3xl bg-gradient-to-br from-[#172329] via-ink to-[#344037] px-5 py-7 text-white shadow-lg shadow-ink/10 sm:px-8 sm:py-10">
        <div class="pointer-events-none absolute -right-10 -top-20 -z-10 h-64 w-64 rounded-full border border-gold/20"></div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold">Profil vendor</p>
        <h1 class="mt-2 break-words font-serif text-3xl leading-tight sm:text-4xl">{{ $vendor->business_name }}</h1>
        @if ($vendor->city)
            <p class="mt-2 text-sm text-white/65">{{ $vendor->city }}</p>
        @endif
        @if ($vendor->description)
            <p class="mt-4 max-w-3xl whitespace-pre-line text-sm leading-7 text-white/75 sm:text-base">{{ $vendor->description }}</p>
        @endif
        <div class="mt-5 flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5">{{ $vendor->portfolioProjects->count() }} proyek ditampilkan</span>
            <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5">{{ $packages->count() }} paket aktif</span>
            @if ($averageRating !== null)
                <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5" aria-label="Rata-rata rating {{ \App\Support\RatingFormatter::formatAverage($averageRating) }} dari pesanan selesai">★ {{ \App\Support\RatingFormatter::formatAverage($averageRating) }} / 5 · {{ $reviews->count() }} ulasan</span>
            @else
                <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5">Belum ada rating</span>
            @endif
        </div>
    </section>

    <section class="mt-8">
        <div class="mb-4">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Pengalaman kerja</p>
            <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Proyek pilihan</h2>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($vendor->portfolioProjects as $project)
                <article class="card overflow-hidden">
                    @if ($project->before_image_url && $project->after_image_url)
                        <div class="grid grid-cols-2 gap-1 bg-sand">
                            <figure class="min-w-0">
                                <img src="{{ $project->before_image_url }}" alt="Sebelum: {{ $project->title }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                                <figcaption class="bg-white px-3 py-2 text-xs font-semibold text-ink/60">Sebelum</figcaption>
                            </figure>
                            <figure class="min-w-0">
                                <img src="{{ $project->after_image_url }}" alt="Sesudah: {{ $project->title }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                                <figcaption class="bg-white px-3 py-2 text-xs font-semibold text-ink/60">Sesudah</figcaption>
                            </figure>
                        </div>
                    @elseif ($project->after_image_url)
                        <figure>
                            <img src="{{ $project->after_image_url }}" alt="Sesudah: {{ $project->title }}" loading="lazy" class="aspect-[16/10] w-full object-cover">
                            <figcaption class="bg-white px-3 py-2 text-xs font-semibold text-ink/60">Sesudah</figcaption>
                        </figure>
                    @elseif ($project->before_image_url)
                        <figure>
                            <img src="{{ $project->before_image_url }}" alt="Sebelum: {{ $project->title }}" loading="lazy" class="aspect-[16/10] w-full object-cover">
                            <figcaption class="bg-white px-3 py-2 text-xs font-semibold text-ink/60">Sebelum</figcaption>
                        </figure>
                    @elseif ($project->image_url)
                        <img src="{{ $project->image_url }}" alt="Foto proyek {{ $project->title }}" loading="lazy" class="aspect-[16/10] w-full object-cover">
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center bg-gradient-to-br from-[#efe1c9] via-[#f8f3e9] to-[#dfc9a7]">
                            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white/60 text-2xl text-ink/50" aria-hidden="true">⌂</span>
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">{{ config('isiin.room_types.' . $project->room_type) }}</span>
                            <span class="rounded-full bg-sand px-3 py-1 text-xs font-medium text-ink/60">{{ $project->completion_year }}</span>
                        </div>
                        <h3 class="mt-3 font-serif text-xl">{{ $project->title }}</h3>
                        <p class="mt-1 text-sm text-ink/55">{{ $project->location }}</p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-ink/70">{{ $project->description }}</p>
                    </div>
                </article>
            @empty
                <div class="card px-5 py-10 text-center sm:col-span-2 xl:col-span-3">
                    <p class="font-medium">Vendor belum menambahkan proyek portofolio.</p>
                    <p class="mt-1 text-sm text-ink/55">Lihat paket aktif vendor untuk mengetahui layanan yang tersedia.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-9">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Rekam jejak layanan</p>
                <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Pesanan selesai</h2>
                <p class="mt-1 text-sm text-ink/55">Nama pemilik properti ditampilkan, tetapi alamat tidak ditampilkan.</p>
            </div>
            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-ink/60 shadow-sm">{{ $completedOrders->count() }} pesanan</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($completedOrders as $order)
                <article class="card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="badge bg-emerald-100 text-emerald-800">Selesai</span>
                        <time class="text-xs text-ink/50" datetime="{{ $order->updated_at->copy()->timezone(config('isiin.display_timezone'))->toDateString() }}">Diselesaikan {{ $order->updated_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y') }}</time>
                    </div>
                    <p class="mt-4 text-xs font-medium uppercase tracking-wide text-ink/45">Pelanggan</p>
                    <p class="mt-1 font-medium">{{ $order->customer->name }}</p>
                    <h3 class="mt-4 font-serif text-xl">{{ $order->package->name }}</h3>
                    <p class="mt-1 text-sm text-ink/55">{{ config('isiin.room_types.' . $order->package->room_type) }}</p>
                </article>
            @empty
                <div class="card px-5 py-8 text-sm text-ink/55 sm:col-span-2 xl:col-span-3">Belum ada pesanan yang diselesaikan.</div>
            @endforelse
        </div>
    </section>

    <section class="mt-9">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Cerita pelanggan</p>
                <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Ulasan vendor</h2>
                <p class="mt-1 text-sm text-ink/55">Dari pelanggan yang pesanannya telah selesai.</p>
            </div>
            @if ($averageRating !== null)
                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700">
                    ★ {{ \App\Support\RatingFormatter::formatAverage($averageRating) }} <span class="font-normal text-amber-700/70">/ 5 · {{ $reviews->count() }} ulasan</span>
                </span>
            @endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($reviews as $review)
                <article class="card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="font-semibold">{{ $review->customer->name }}</span>
                        <span class="whitespace-nowrap text-sm tracking-wider text-amber-500" aria-label="{{ $review->rating }} dari 5 bintang">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    </div>
                    <p class="mt-2 text-xs text-ink/50">Paket: {{ $review->package->name }} · {{ $review->created_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y') }}</p>
                    @if ($review->comment)
                        <p class="mt-3 break-words text-sm leading-6 text-ink/70">{{ $review->comment }}</p>
                    @endif
                </article>
            @empty
                <div class="card px-5 py-8 text-sm text-ink/55 sm:col-span-2 xl:col-span-3">Belum ada ulasan dari pesanan yang selesai.</div>
            @endforelse
        </div>
    </section>

    <section class="mt-9">
        <div class="mb-4">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Pilih layanan</p>
            <h2 class="mt-1 font-serif text-2xl sm:text-3xl">Paket aktif</h2>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($packages as $package)
                <a href="{{ route('packages.show', $package) }}" class="card group overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
                    @if ($package->image_url)
                        <img src="{{ $package->image_url }}" alt="{{ $package->name }}" loading="lazy" class="aspect-[16/10] w-full object-cover">
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center bg-sand text-ink/35" aria-hidden="true">⌂</div>
                    @endif
                    <div class="p-5">
                        <p class="text-xs text-ink/55">{{ $package->room_label }} · {{ $package->style }}</p>
                        <h3 class="mt-1 font-semibold group-hover:text-amber-800">{{ $package->name }}</h3>
                        <p class="mt-3 font-semibold">Rp {{ number_format($package->price, 0, ',', '.') }} <span class="text-xs font-normal text-ink/55">/ {{ $package->duration_days }} hari</span></p>
                    </div>
                </a>
            @empty
                <div class="card px-5 py-8 text-sm text-ink/55 sm:col-span-2 xl:col-span-3">Belum ada paket aktif dari vendor ini.</div>
            @endforelse
        </div>
    </section>
@endsection
