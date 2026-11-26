@extends('layouts.app')

@section('title', 'Verifikasi Vendor')

@section('content')
    <div class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Administrasi</p>
        <h1 class="page-heading mt-2">Verifikasi Vendor</h1>
        <p class="page-subtitle">Tinjau status vendor yang dapat menampilkan paket di katalog.</p>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[42rem] text-sm">
            <thead class="bg-[#faf8f3] text-left text-ink/60">
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
                    <tr class="border-t border-ink/5 transition hover:bg-[#fcfbf8]">
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
    </div>
@endsection
