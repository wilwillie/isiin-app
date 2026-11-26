@extends('layouts.app')

@section('title', 'Permintaan Perubahan')

@section('content')
    <div class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Administrasi</p>
        <h1 class="page-heading mt-2">Permintaan Vendor</h1>
        <p class="page-subtitle">Tinjau permintaan perubahan akun vendor.</p>
    </div>

    <div class="mt-4">
        @include('partials.errors')
    </div>

    <div class="mt-2 space-y-4">
        @forelse ($pending as $req)
            @php $current = $req->currentValues(); @endphp
            <div class="card p-4 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold">{{ $req->type_label }}</p>
                        <p class="text-sm text-ink/60">
                            {{ $req->user?->vendor?->business_name ?? $req->user?->name }} · {{ $req->user?->email }}
                            · diajukan {{ $req->created_at->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}
                        </p>
                    </div>
                    <span class="badge {{ $req->status_class }}">{{ $req->status_label }}</span>
                </div>

                <div class="mt-4 rounded-2xl bg-[#faf8f3] p-4 text-sm leading-6">
                    @if ($req->type === 'profile')
                        <ul class="space-y-1">
                            @foreach (($req->payload['changes'] ?? []) as $field => $value)
                                <li>
                                    <span class="text-ink/60">{{ \App\Models\ProfileChangeRequest::FIELD_LABELS[$field] ?? $field }}:</span>
                                    <span class="line-through text-ink/50">{{ $current[$field] ?? '-' }}</span>
                                    → <strong>{{ $value ?? '-' }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    @elseif ($req->type === 'password')
                        <p>Vendor meminta mengganti password akunnya. Password baru tersimpan sebagai hash dan tidak bisa Anda lihat.</p>
                    @else
                        <p>
                            Vendor meminta akunnya dihapus. Jumlah paket: <strong>{{ $req->user?->vendor?->packages()->count() ?? 0 }}</strong>.
                            @if ($req->user && \App\Services\AccountDeleter::hasActiveOrders($req->user))
                                <span class="text-rose-700 font-medium">Masih ada pesanan berjalan, jadi belum bisa disetujui.</span>
                            @endif
                        </p>
                        @if (! empty($req->payload['reason']))
                            <p class="mt-1"><span class="text-ink/60">Alasan:</span> {{ $req->payload['reason'] }}</p>
                        @endif
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.requests.approve', $req) }}" class="mt-4 grid gap-2 sm:flex sm:flex-wrap sm:items-center">
                    @csrf
                    <input class="input min-w-0 sm:min-w-[220px] sm:flex-1" name="admin_note" placeholder="Catatan untuk vendor (wajib jika menolak)">
                    <div class="grid grid-cols-2 gap-2 sm:flex">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto">Setujui</button>
                        <button type="submit" formaction="{{ route('admin.requests.reject', $req) }}" class="btn btn-outline w-full sm:w-auto">Tolak</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="card p-8 text-center text-ink/60">Tidak ada permintaan yang menunggu.</div>
        @endforelse
    </div>

    <div class="card mt-8 overflow-hidden">
        <div class="border-b border-ink/5 px-5 py-4 sm:px-6">
            <h2 class="section-heading">Riwayat terakhir</h2>
        </div>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[38rem] text-sm">
                <thead class="bg-[#faf8f3] text-left text-ink/60">
                    <tr>
                        <th class="py-2 pr-3">Tanggal</th>
                        <th class="py-2 pr-3">Vendor</th>
                        <th class="py-2 pr-3">Jenis</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $req)
                        <tr class="border-t border-ink/5">
                            <td class="py-2 pr-3">{{ ($req->reviewed_at ?? $req->updated_at)->copy()->timezone(config('isiin.display_timezone'))->format('d M Y H:i') }}</td>
                            <td class="py-2 pr-3">{{ $req->user?->email }}</td>
                            <td class="py-2 pr-3">{{ $req->type_label }}</td>
                            <td class="py-2 pr-3"><span class="badge {{ $req->status_class }}">{{ $req->status_label }}</span></td>
                            <td class="py-2">{{ $req->admin_note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-3 text-ink/60">Belum ada riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
