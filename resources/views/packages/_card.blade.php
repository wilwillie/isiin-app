<a href="{{ route('packages.show', $package) }}" class="card overflow-hidden block hover:shadow-md transition">
    @if ($package->image_url)
        <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="h-44 w-full object-cover">
    @else
        <div class="h-44 w-full bg-gradient-to-br from-gold/50 to-sand flex items-center justify-center text-ink/40 text-sm">Belum ada foto</div>
    @endif
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold">{{ $package->name }}</h3>
            @if ($package->reviews_count)
                <span class="text-xs text-amber-600 whitespace-nowrap">★ {{ number_format($package->reviews_avg_rating, 1) }} ({{ $package->reviews_count }})</span>
            @endif
        </div>
        <p class="text-xs text-ink/60 mt-0.5">{{ $package->room_label }} · {{ $package->style }}</p>
        <p class="mt-2 font-semibold">Rp {{ number_format($package->price, 0, ',', '.') }} <span class="text-xs font-normal text-ink/60">/ {{ $package->duration_days }} hari</span></p>
        <p class="text-xs text-ink/60 mt-1">oleh {{ $package->vendor->business_name }}</p>
    </div>
</a>
