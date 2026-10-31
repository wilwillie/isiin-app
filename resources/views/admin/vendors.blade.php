@extends('layouts.app')

@section('title', 'Verifikasi Vendor')

@section('content')
    <h1 class="font-serif text-3xl">Verifikasi Vendor</h1>
    <p class="text-ink/60 mt-1">Hanya paket dari vendor terverifikasi yang tampil di katalog publik.</p>

    <div class="card mt-5 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-ink/60 border-b">
                <tr>
                    <th class="p-3">Vendor</th>
                    <th class="p-3">Kontak</th>
                    <th class="p-3">Kota</th>
                    <th class="p-3">Paket</th>
                    <th class="p-3">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendors as $vendor)
                    <tr class="border-b last:border-0">
                        <td class="p-3 font-medium">{{ $vendor->business_name }}</td>
                        <td class="p-3">{{ $vendor->user->email }}@if ($vendor->user->phone)<br><span class="text-ink/60">{{ $vendor->user->phone }}</span>@endif</td>
                        <td class="p-3">{{ $vendor->city ?? '-' }}</td>
                        <td class="p-3">{{ $vendor->packages_count }}</td>
                        <td class="p-3">
                            <span class="badge {{ $vendor->is_verified ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $vendor->is_verified ? 'Terverifikasi' : 'Menunggu' }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <form method="POST" action="{{ route('admin.vendors.toggle', $vendor) }}">
                                @csrf
                                <button type="submit" class="btn {{ $vendor->is_verified ? 'btn-outline' : 'btn-primary' }}">
                                    {{ $vendor->is_verified ? 'Cabut' : 'Verifikasi' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-4 text-ink/60" colspan="6">Belum ada vendor terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
