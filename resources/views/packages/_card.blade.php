<a href="{{ route('packages.show', $package) }}" class="card group flex h-full flex-col overflow-hidden hover:-translate-y-1 hover:shadow-lg">
    @if ($package->image_url)
        <div class="relative h-48 overflow-hidden bg-sand sm:h-52">
            <img src="{{ $package->image_url }}" alt="{{ $package->name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            <span class="absolute bottom-3 left-3 rounded-full border border-white/60 bg-white/90 px-3 py-1 text-xs font-medium text-ink shadow-sm backdrop-blur">{{ $package->room_label }}</span>
        </div>
    @else
        <div class="relative flex h-48 items-center justify-center overflow-hidden bg-gradient-to-br from-[#efe1c9] via-[#f8f3e9] to-[#dfc9a7] text-ink/45 sm:h-52">
            <span class="absolute -right-5 -top-10 h-40 w-40 rounded-full border border-white/60"></span>
            <span class="absolute -right-1 -top-6 h-28 w-28 rounded-full border border-white/50"></span>
            <span class="relative grid h-14 w-14 place-items-center rounded-2xl bg-white/55 text-2xl shadow-sm" aria-hidden="true">⌂</span>
            <span class="absolute bottom-3 left-3 rounded-full bg-white/70 px-3 py-1 text-xs font-medium text-ink/60">{{ $package->room_label }}</span>
        </div>
    @endif
    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <div class="flex items-start justify-between gap-2">
            <h3 class="line-clamp-2 font-semibold leading-snug transition group-hover:text-amber-800">{{ $package->name }}</h3>
            @if ($package->reviews_count)
                <span class="shrink-0 rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">★ {{ \App\Support\RatingFormatter::formatAverage($package->reviews_avg_rating) }} <span class="font-normal text-amber-700/70">({{ $package->reviews_count }})</span></span>
            @endif
        </div>
        <p class="mt-2 text-xs text-ink/55">{{ $package->style }}</p>
        <div class="mt-auto border-t border-ink/5 pt-4">
            <p class="font-semibold leading-tight">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
            <div class="mt-1 flex flex-wrap items-center justify-between gap-2 text-xs text-ink/55">
                <span>{{ $package->duration_days }} hari sewa</span>
                <span class="max-w-[60%] truncate">oleh {{ $package->vendor->business_name }}</span>
            </div>
        </div>
    </div>
</a>
