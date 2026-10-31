@extends('layouts.app')

@section('title', 'Paket Staging')

@section('content')
    <h1 class="font-serif text-3xl">Paket Home Staging</h1>
    <p class="text-ink/60 mt-1">Pilih paket sesuai ruangan dan gaya yang Anda inginkan.</p>

    <form method="GET" action="{{ route('packages.index') }}" class="card p-4 mt-5 grid gap-3 md:grid-cols-4">
        <input class="input md:col-span-2" type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama paket...">
        <select class="input" name="room_type">
            <option value="">Semua ruangan</option>
            @foreach (config('isiin.room_types') as $key => $label)
                <option value="{{ $key }}" @selected(request('room_type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select class="input" name="style">
            <option value="">Semua gaya</option>
            @foreach (config('isiin.styles') as $style)
                <option value="{{ $style }}" @selected(request('style') === $style)>{{ $style }}</option>
            @endforeach
        </select>
        <div class="md:col-span-4 flex gap-2">
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="{{ route('packages.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($packages as $package)
            @include('packages._card', ['package' => $package])
        @empty
            <p class="text-ink/60 sm:col-span-2 lg:col-span-3">Belum ada paket yang cocok dengan pencarian Anda.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $packages->links() }}</div>
@endsection
