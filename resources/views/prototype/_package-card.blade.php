<a class="package-card" href="{{ route('prototype.app', ['screen' => 'detail', 'package' => $id]) }}">
    <span class="package-art" style="background:linear-gradient(140deg, {{ $package['color'] }}, #344037)" aria-hidden="true">{{ strtoupper(substr($package['name'], 0, 1)) }}</span>
    <span>
        <span class="muted-small">{{ $package['room'] }} · {{ $package['style'] }}</span>
        <h3 style="margin-top:4px">{{ $package['name'] }}</h3>
        <span class="price">Rp {{ number_format($package['price'], 0, ',', '.') }}</span>
        <span class="muted-small"> / {{ $package['duration'] }}</span>
    </span>
</a>
